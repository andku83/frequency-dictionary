<?php

namespace app\models;

/**
 * This is the model class for table "{{%glossary}}".
 *
 * @property int $id
 * @property string $headword
 * @property string $description
 */
class Glossary extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%glossary}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['description'], 'string'],
            [['headword'], 'string', 'max' => 255],
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
            'description' => 'Description',
        ];
    }

    /**
     * @param $name
     * @return self
     */
    public static function getWordByName($name)
    {
            $word = self::find()->andWhere(['headword' => $name])->one();
            if (!$word) {
                $word = new self(['headword' => $name]);
                $word->save();
            }

        return $word;
    }

}
