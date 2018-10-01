<?php

namespace app\components\dictionary;


use app\models\Glossary;
use app\models\Text;
use app\models\TextWord;
use app\models\Word;
use Yii;
use yii\base\ErrorException;

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

        foreach (static::getFiles($path) as $file) {
            static::addFile($file);

            if (static::checkTimeToEnd()){
                return static::getAnswer();
            }
        }

        $texts = Text::find()->andWhere(['status' => Text::STATUS_NOT_PROCESSED])->all();
        foreach ($texts as $text) {

            static::calculateText($text);

            if (static::checkTimeToEnd()){
                return static::getAnswer();
            }
        }

        static::calculateSum();

        return static::getAnswer();
    }

    /**
     *
     */
    public static function truncate()
    {
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
        $file = Yii::getAlias($textModel->file_path);
        $text = file_get_contents($file);
        preg_match_all('/[^\W\d][\w-]*/', $text, $words);

        $filterWords = static::filterStopWords($words[0]);

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
//            if ($word == 'a') {
//                var_dump($word);
//                var_dump(ord($word));
//                var_dump(($stopWords[0]));
//                var_dump(ord($stopWords[0]));
//                var_dump(array_search($word, $stopWords));
//            }
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
            static::$stopWords = explode(PHP_EOL, file_get_contents(__DIR__.DIRECTORY_SEPARATOR.'stop_words.txt'));
        }
        return static::$stopWords;
    }

    public static function loadGlossary()
    {
        $list = array_filter(explode(PHP_EOL, file_get_contents(Yii::getAlias('@app/files/glossary/glossary.txt'))));

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
     * @return mixed
     */
    public static function getAnswer()
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
        $result['processed_lemma'] = [
            Text::STATUS_NOT_PROCESSED => 0,
            Text::STATUS_PROCESSED => 0,
        ];

        $groupText = Text::find()->select(['status', 'count' => 'COUNT(*)'])
            ->groupBy('status')
            ->asArray()->all();
        foreach ($groupText as $group) {
            $result['processed_text'][$group['status']] = $group['count'];
        }

        $result['load_text']['percent'] = static::getPercent($result['load_text']);
        $result['processed_text']['percent'] = static::getPercent($result['processed_text']);
        $result['processed_lemma']['percent'] = static::getPercent($result['processed_lemma']);

        if ($result['processed_text']['percent'] == 100 && !Word::find()->andWhere(['score' => NULL])->exists()) {
            $result['status'] = 'complete';
        }
        return $result;
    }

    /**
     * @param string $word
     * @param array $sentences
     * @return mixed
     */
    protected static function getContext($word, $sentences)
    {

        foreach ($sentences as $sentence) {
            $sentence_lower = mb_strtolower($sentence);
            if (
                false !== mb_stripos($sentence_lower, $word.' ')
                || false !== mb_stripos($sentence_lower, $word.'.')
                || false !== mb_stripos($sentence_lower, $word.',')
                || false !== mb_stripos($sentence_lower, $word.')')
            ) {
                return trim($sentence);
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
        $sentences = [];

        preg_match_all("/(.*?([.?!]|(\.\[\d\]))(?:\s|$))/s",  $text,$sentenceGroups, PREG_PATTERN_ORDER);

        foreach ($sentenceGroups[0] as $group) {
            foreach (explode(PHP_EOL, $group) as $line) {
                $sentences[] = $line;
            }
        }
        $sentences = array_filter($sentences, function ($val){ return $val !== "\r";});
        $sentences = array_filter($sentences);
        $sentences = array_map('trim', $sentences);
        return array_values($sentences);
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

//        $useWords = TextWord::find()
//            ->select(['word_id', 'COUNT(text_id)', 'SUM(count_words)', 'context'])
//            ->groupBy('word_id')
//            ->asArray()->all();

        if ($countText && $countSumWords) {
            $useWords = TextWord::find()
                ->select([
                    'word_id',
                    'dispersion' => "cast(COUNT(text_id) / $countText as decimal(10,8))",
                    'frequency' => "cast(SUM(count_words) / $countSumWords as decimal(10,8))",
                ])
                ->groupBy('word_id')
//                ->orderBy('frequency')
                ->asArray();
//                ->all();

            foreach ($useWords->each(500) as $word) {
                Word::updateAll([
                    'frequency' => $word['frequency'],
                    'dispersion' => $word['dispersion'],
                    'score' => $word['frequency'] / $word['dispersion'],
                ], ['id' => $word['word_id']]);
            }
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
