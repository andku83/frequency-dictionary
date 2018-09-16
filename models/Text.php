<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%text}}".
 *
 * @property int $id
 * @property string $name
 * @property string $filePath
 * @property int $length
 * @property int $count_words
 *
 * @property TextWord[] $textWords
 */
class Text extends \yii\db\ActiveRecord
{
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
            [['length', 'count_words'], 'integer'],
            [['name', 'filePath'], 'string', 'max' => 255],
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
            'filePath' => 'File Path',
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
