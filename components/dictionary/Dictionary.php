<?php

namespace app\components\dictionary;


use app\models\Glossary;
use app\models\Text;
use app\models\TextWord;
use app\models\Word;
use Yii;
use yii\base\ErrorException;
use yii\db\Expression;

/**
 * Class Dictionary
 * @package app\components\dictionary
 */
class Dictionary
{
    const MAX_EXECUTION_TIME = 2; // max time for work script

    /**
     * @var array
     */
    protected static $stopWords;

    /**
     * @var array
     */
    protected static $files = [];

    /**
     * @param string $path
     * @param bool $truncate
     * @return int
     */
    public static function calculateMultiple($truncate = false, $path = '')
    {
        if ($truncate) {
            // clear DB
            static::truncate();
        }

        $cache = Yii::$app->cache;

        if (true ) {
            foreach (static::getFiles($path) as $file) {
                static::addFile($file);

                if (static::checkTimeToEnd()) {
                    return static::getAnswer();
                }
            }
            $cache->set('dictionary-files-end', true, 120);
        }

        $textsQuery = Text::find()->andWhere(['status' => Text::STATUS_NOT_PROCESSED]);
        foreach ($textsQuery->each() as $text) {

            static::calculateText($text);

            if (static::checkTimeToEnd()){
                return static::getAnswer();
            }
        }

        static::filterDuplicate();
        if (self::checkTimeToEnd()) {
            return self::getAnswer();
        }

        static::calculateSum();

        return static::getAnswer(true);
    }

    /**
     *
     */
    public static function truncate()
    {
        Yii::$app->cache->delete('dictionary-files-end');
        Yii::$app->cache->delete('dictionary-filterDuplicate-offset');
        Yii::$app->cache->delete('dictionary-filterDuplicate-end');

        TextWord::deleteAll();
        Text::deleteAll();
        Word::deleteAll();
    }

    /**
     * @param $filePath
     * @throws ErrorException
     */
    public static function addFile($filePath)
    {
        $filePath = Yii::getAlias($filePath);
        $fileName = pathinfo($filePath, PATHINFO_FILENAME);
        $fileTime = filemtime($filePath);

        $text = Text::find()->andWhere(['name' => $fileName])->one();

        if ($text && $text->file_time == $fileTime){
            return ;
        } else {
            Text::deleteAll(['name' => $fileName]);
            $text = new Text();
        }

        $text->attributes = [
            'name' => $fileName,
            'file_path' => $filePath,
            'file_time' => $fileTime,
            'status' => Text::STATUS_DEFAULT,
            'length' => filesize($filePath),
        ];
        if (!$text->save()) {
            throw new ErrorException('Error!');
        }
    }

    /**
     * @param Text $textModel
     */
    public static function calculateText(Text $textModel)
    {
        $text = static::readFile(Yii::getAlias($textModel->file_path));
//        preg_match_all('/[^\W\d][\w-]*/', $text, $words);
//        preg_match_all('~[^\W\s\d][\D\w][\w-]*~', $text, $words);
        preg_match_all('~[^\W][\w-]*[\w]~', $text, $words);
        $words = array_filter($words[0], function ($word){ return !preg_match("~\d~", $word) && (mb_strlen($word) > 1); });
        $filterWords = static::filterStopWords($words);

        $countWords = array_count_values($filterWords);

        $sentences = static::textToSentences($text);

        foreach ($countWords as $word => $count) {
            $wordModel = Word::getWordByName($word);
            $textModel->link('words', $wordModel, [
                'count_words' => $count,
                'context' => static::getContext($word, $sentences),
            ]);
        }

        $textModel->count_words = array_sum($countWords);
        $textModel->status = Text::STATUS_PROCESSED;
        $textModel->save();

        return ;
    }

    /**
     * @param $words
     * @return array
     */
    public static function filterStopWords($words)
    {
        $stopWords = static::getStopWords();
        $filterWords = [];
        foreach ($words as $word) {
            $word = strtolower($word);
            if (false === array_search($word, $stopWords)) {
                $filterWords[] = $word;
            }
        }
        return $filterWords;
    }

    /**
     * @return mixed
     */
    public static function getStopWords()
    {
        if (empty(static::$stopWords)) {
            static::$stopWords = explode(PHP_EOL, static::readFile(__DIR__.DIRECTORY_SEPARATOR.'stop_words.txt'));
        }
        return static::$stopWords;
    }

    public static function loadGlossary()
    {
        $list = array_filter(explode(PHP_EOL, static::readFile(Yii::getAlias('@app/files/glossary/glossary.txt'))));

        foreach ($list as $string) {
            if (mb_stripos($string, ' n. ')) {
                $one = explode(' n. ', $string, 2);
                $model = Glossary::getWordByName($one['0']);
                $model->description = $one['1'];
                $model->save(false);
            }
        }
    }

    /**
     * @param string $path
     * @return mixed
     */
    public static function getFiles($path = '')
    {
        //load files and check exists in DB
        if (empty(static::$files)) {

            if (empty($path)) {
                $path = Yii::getAlias('@app/files');
            }
            $handle = opendir($path);
            while (($file = readdir($handle)) !== false) {
                if (is_file($file) || pathinfo($path . DIRECTORY_SEPARATOR . $file, PATHINFO_EXTENSION) == 'txt') {
                    static::$files[] = $path . DIRECTORY_SEPARATOR . $file;
                }
            }
            closedir($handle);
        }

        return static::$files;
    }

    /**
     * @param bool $complete
     * @return mixed
     */
    public static function getAnswer($complete = false)
    {
        $result['status'] = 'processed';
        $textProcessed = Text::find()->count();
        $result['load_text'] = [
            Text::STATUS_NOT_PROCESSED => count(self::getFiles()) - $textProcessed,
            Text::STATUS_PROCESSED => $textProcessed,
        ];
        $result['processed_text'] = [
            Text::STATUS_NOT_PROCESSED => 0,
            Text::STATUS_PROCESSED => 0,
        ];
        $result['filtering'] = [
            Text::STATUS_NOT_PROCESSED => 0,
            Text::STATUS_PROCESSED => 0,
        ];

        $groupText = Text::find()->select(['status', 'count' => 'COUNT(*)'])
            ->groupBy('status')
            ->asArray()->all();
        foreach ($groupText as $group) {
            $result['processed_text'][$group['status']] = $group['count'];
        }

        if ($offset = Yii::$app->cache->get('dictionary-filterDuplicate-offset')) {
            $countDuplicates = Word::find()
                ->andWhere(new Expression('`headword` LIKE "%s"'))
                ->count();
            $remainingDuplicates = Word::find()->andWhere(new Expression('`headword` LIKE "%s"'))
                ->andWhere(['>', 'headword', $offset])->count();

            $result['filtering'] = [
                Text::STATUS_NOT_PROCESSED => $remainingDuplicates,
                Text::STATUS_PROCESSED => $countDuplicates - $remainingDuplicates,
            ];
        }

        $result['load_text']['percent'] = static::getPercent($result['load_text']);
        $result['processed_text']['percent'] = static::getPercent($result['processed_text']);
        $result['filtering']['percent'] = static::getPercent($result['filtering']);

        if ($complete) {
            $result['filtering']['percent'] = 100;
            $result['status'] = 'complete';
        }
        return $result;
    }

    /**
     * Remove BOM
     * @param string $path
     * @return string $str
     */
    protected static  function readFile($path) {
        $text = file_get_contents($path);

        return static::removeBOM($text);
    }

    /**
     * Remove BOM
     * @param string $str
     * @return string $str
     */
    protected static function removeBOM($str="") {
        if(substr($str, 0, 3) == pack('CCC', 0xef, 0xbb, 0xbf)) {
            $str = substr($str, 3);
        }
        return $str;
    }

    /**
     * @param string $word
     * @param array $sentences
     * @return mixed
     */
    protected static function getContext($word, $sentences)
    {
        foreach ($sentences as $sentence) {
            if (preg_match("~(?<=^|\s|\W)($word)(?=\s|\W|$)~im", $sentence)) {
                return ($sentence);
            }
        }
        return null;
    }

    /**
     * @param $text
     * @return array
     */
    protected static function textToSentences($text)
    {
        preg_match_all("/(([^\s].+?)(?:[.?!]|(\.\[\d\])|$)(?=[\s][A-Z]|$))/m",  $text,$sentenceGroups, PREG_PATTERN_ORDER);
//        preg_match_all("/(([^\s].+?)(?:[.?!]|(\.\[\d\])|(?=[^\n]$))(?=\s[A-Z][^\n]|[^\n]$))/m",  $text,$sentenceGroups, PREG_PATTERN_ORDER);

        $sentences = array_map('trim', $sentenceGroups[0]);
        return $sentences;
    }

    /**
     * @param array $state
     * @return float|int
     */
    protected static function getPercent(array $state)
    {
        if (empty($state[Text::STATUS_NOT_PROCESSED]) && empty($state[Text::STATUS_PROCESSED])) {
            return 0;
        }
        return (int)(100 * $state[Text::STATUS_PROCESSED] /
            ($state[Text::STATUS_NOT_PROCESSED] + $state[Text::STATUS_PROCESSED]));
    }

    /**
     *
     */
    protected static function calculateSum()
    {
        $countText = TextWord::find()->select(['COUNT(DISTINCT text_id)'])->scalar();
        $countSumWords = TextWord::find()->sum('count_words');

        if ($countText && $countSumWords) {
            $useWords = TextWord::find()
                ->select([
                    'word_id',
                    'dispersion' => "CAST(COUNT(text_id) / $countText as decimal(10,8))",
                    'frequency' => "CAST(SUM(count_words) / $countSumWords as decimal(10,8))",
                ])
                ->groupBy('word_id')
                ->asArray();

            foreach ($useWords->each(500) as $useW) {
                Word::updateAll([
                    'frequency' => $useW['frequency'],
                    'dispersion' => $useW['dispersion'],
                    'score' => $useW['frequency'] * $useW['dispersion'],
                ], ['id' => $useW['word_id']]);
            }
        }
    }

    protected static function filterDuplicate()
    {
        $cache = Yii::$app->cache;
        if (true !== $cache->get('dictionary-filterDuplicate-end')) {
            $duplicatesQuery = Word::find()
                ->andWhere(new Expression('`headword` LIKE "%s"'))
                ->orderBy('headword');

            if ($offset = $cache->get('dictionary-filterDuplicate-offset')) {
                $duplicatesQuery->andWhere(['>', 'headword', $offset]);
            }
            /** @var Word $duplicate */
            foreach ($duplicatesQuery->each() as $duplicate) {
                $word = null;
                if (mb_substr($duplicate['headword'], -2) === 'es') {
                    $word = Word::findOne(['headword' => mb_substr($duplicate['headword'], 0, -2)]);
                }
                if (!$word) {
                    $word = Word::findOne(['headword' => mb_substr($duplicate['headword'], 0, -1)]);
                }
                if ($word) {
                    $duplicateTextWords = $duplicate->getTextWords()->indexBy('text_id')->all();
                    $textWords = $word->getTextWords()->indexBy('text_id')->all();

                    foreach ($duplicateTextWords as $text_id => $dTW) {
                        if (isset($textWords[$text_id])) {
                            $textWords[$text_id]->count_words += $dTW->count_words;
                            $textWords[$text_id]->save(false);
                        } else {
                            $dTW->text->link('words', $word, ['count_words' => $dTW->count_words, 'context' => $dTW->context]);
                        }
                    }
                    $duplicate->unlinkAll('texts', true);
                    $duplicate->delete();
                }
                $cache->set('dictionary-filterDuplicate-offset', $duplicate['headword'], 120);
                if (self::checkTimeToEnd()) {
                    return;
                }
            }
            $cache->set('dictionary-filterDuplicate-end', true, 120);
        }
    }

    /**
     * @return bool
     */
    protected static function checkTimeToEnd()
    {
        $time = microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'];

        return $time > self::MAX_EXECUTION_TIME;
    }
}
