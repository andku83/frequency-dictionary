<?php

namespace app\components\dictionary;


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
    /**
     * @var array|null
     */
    static $stopWords;

    /**
     * @param $filePath
     * @throws ErrorException
     */
    public static function calculate($filePath)
    {
        $file = Yii::getAlias($filePath);
        $text = file_get_contents($file);
        preg_match_all('/[^\W\d][\w]*/', $text, $words);

        $filterWords = static::filterStopWords($words[0]);
//        var_dump($words[0]);
//        var_dump(static::getStopWords());

        $countWords = array_count_values($filterWords);

        $textModel = new Text([
            'filePath' => $file,
            'name' => pathinfo($file, PATHINFO_FILENAME),
            'count_words' => array_sum($countWords),
            'length' => mb_strlen($text),
        ]);
        if (!$textModel->save()) {
            throw new ErrorException('Error!');
        }

        foreach ($countWords as $word => $count) {
            $word = Word::getWordByName($word);
            $textModel->link('words', $word, [
                'count_words' => $count,
                'context' => '',
            ]);
        }

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
        if (!static::$stopWords) {
            static::$stopWords = explode(PHP_EOL, file_get_contents(__DIR__.DIRECTORY_SEPARATOR.'stop_words.txt'));
        }
        return static::$stopWords;
    }

    public static function truncate()
    {
        TextWord::deleteAll();
        Text::deleteAll();
        Word::deleteAll();
    }

    /**
     * @param string $path
     * @return int
     */
    public static function calculateMultiple($path = '')
    {
        static::truncate();

        $files = [];
        if (empty($path)) {
            $path = Yii::getAlias('@app/files');
        }
        $handle = opendir($path);
        while (($file = readdir($handle)) !== false) {
            if (is_file($file) || pathinfo($path.DIRECTORY_SEPARATOR.$file, PATHINFO_EXTENSION) == 'txt') {
                $files[] = $path.DIRECTORY_SEPARATOR.$file;
            }
        }
        closedir($handle);

        $count = 0;
        foreach ($files as $file) {
            static::calculate($file);
            $count++;
        }
        return $count;
    }
}