<?php

namespace app\models;

/**
 * This is the model class for table "{{%word}}".
 *
 * @property int $id
 * @property string $headword
 * @property string $lemma
 * @property string $score
 * @property string $frequency
 * @property string $dispersion
 *
 * @property TextWord[] $textWords
 * @property TextWord $textWord
 */
class Word extends \yii\db\ActiveRecord
{
    /**
     * @var Word[]
     */
    static $words = [];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%word}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['score', 'frequency', 'dispersion'], 'number'],
            [['headword'], 'string', 'max' => 255],
            [['lemma'], 'string'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'headword' => 'Headword',
            'lemma' => 'Lemma',
            'score' => 'Score',
            'frequency' => 'Frequency',
            'dispersion' => 'Dispersion',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTextWords()
    {
        return $this->hasMany(TextWord::class, ['word_id' => 'id'])
            ->inverseOf('word');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTextWord()
    {
        return $this->hasOne(TextWord::class, ['word_id' => 'id'])
            ->groupBy('word_id')
            ->inverseOf('word');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTexts()
    {
        return $this->hasMany(Text::class, ['id' => 'text_id'])
            ->via('textWords');
    }

    /**
     * {@inheritdoc}
     * @return \app\models\query\WordQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \app\models\query\WordQuery(get_called_class());
    }

    /**
     * @param $name
     * @return Word|array|null
     */
    public static function getWordByName($name)
    {
        if (!isset(static::$words[$name])) {
            $word = self::find()->byName($name)->one();
            if (!$word) {
                $word = new Word(['headword' => $name]);
                $word->save();
            }
            static::$words[$name] = $word;
        }

        return static::$words[$name];
    }
}
