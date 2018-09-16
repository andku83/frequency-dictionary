<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%text_word}}".
 *
 * @property int $text_id
 * @property int $word_id
 * @property int $count_words
 * @property string $context
 *
 * @property Text $text
 * @property Word $word
 */
class TextWord extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%text_word}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['text_id', 'word_id', 'count_words'], 'integer'],
            [['context'], 'safe'],
            [['text_id'], 'exist', 'skipOnError' => true, 'targetClass' => Text::class, 'targetAttribute' => ['text_id' => 'id']],
            [['word_id'], 'exist', 'skipOnError' => true, 'targetClass' => Word::class, 'targetAttribute' => ['word_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'text_id' => 'Text ID',
            'word_id' => 'Word ID',
            'count_words' => 'Count Words',
            'context' => 'Context',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getText()
    {
        return $this->hasOne(Text::class, ['id' => 'text_id'])
            ->inverseOf('textWords');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getWord()
    {
        return $this->hasOne(Word::class, ['id' => 'word_id'])
            ->inverseOf('textWords');
    }

    /**
     * {@inheritdoc}
     * @return \app\models\query\TextWordQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \app\models\query\TextWordQuery(get_called_class());
    }
}
