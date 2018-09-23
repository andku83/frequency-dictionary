<?php

namespace app\models;

/**
 * This is the model class for table "{{%text}}".
 *
 * @property int $id
 * @property string $name
 * @property string $file_path
 * @property integer $file_time
 * @property integer $status
 * @property int $length
 * @property int $count_words
 *
 * @property Word[] $words
 * @property TextWord[] $textWords
 */
class Text extends \yii\db\ActiveRecord
{
    const STATUS_DEFAULT = self::STATUS_NOT_PROCESSED;
    const STATUS_NOT_PROCESSED = 0;
    const STATUS_PROCESSED = 1;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%text}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['length', 'count_words', 'file_time', 'status'], 'integer'],
            [['name', 'file_path'], 'string', 'max' => 255],

            [['status'], 'default', 'value' => self::STATUS_DEFAULT],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'file_path' => 'File Path',
            'file_time' => 'File Time',
            'status' => 'Status',
            'length' => 'Length',
            'count_words' => 'Count Words',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTextWords()
    {
        return $this->hasMany(TextWord::class, ['text_id' => 'id'])
            ->inverseOf('text');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getWords()
    {
        return $this->hasMany(Word::class, ['id' => 'word_id'])
            ->via('textWords');
    }

    /**
     * {@inheritdoc}
     * @return \app\models\query\TextQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \app\models\query\TextQuery(get_called_class());
    }
}
