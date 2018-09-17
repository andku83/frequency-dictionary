<?php

namespace app\models;

/**
 * This is the model class for table "{{%word}}".
 *
 * @property int $id
 * @property string $lemma
 * @property string $score
 * @property string $frequency
 * @property string $dispersion
 * @property string $context
 *
 * @property TextWord[] $textWords
 * @property TextWord $textWord
 */
class Word extends \yii\db\ActiveRecord
{
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
            [['context'], 'safe'],
            [['lemma'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'lemma' => 'Lemma',
            'score' => 'Score',
            'frequency' => 'Frequency',
            'dispersion' => 'Dispersion',
            'context' => 'Context',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTextWords()
    {
        return $this->hasMany(TextWord::class, ['word_id' => 'id']
        )->inverseOf('word');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTextWord()
    {
        return $this->hasOne(TextWord::class, ['word_id' => 'id']
        )->inverseOf('word');
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
        $word = self::find()->byName($name)->one();
        if (!$word) {
            $word = new Word(['lemma' => $name]);
            $word->save();
        }

        return $word;
    }
}
