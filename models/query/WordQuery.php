<?php

namespace app\models\query;

/**
 * This is the ActiveQuery class for [[\app\models\Word]].
 *
 * @see \app\models\Word
 */
class WordQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * @param $name
     * @return WordQuery
     */
    public function byName($name)
    {
        return $this->andWhere(['headword' => $name]);
    }

    /**
     * {@inheritdoc}
     * @return \app\models\Word[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return \app\models\Word|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
