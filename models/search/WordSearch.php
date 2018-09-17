<?php

namespace app\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Word;

/**
 * WordSearch represents the model behind the search form of `app\models\Word`.
 */
class WordSearch extends Word
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['lemma'], 'string'],
            [['score', 'frequency', 'dispersion'], 'number'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = Word::find()->with('textWord.text');

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'score' => SORT_DESC,
                    'lemma' => SORT_ASC,
                    'frequency' => SORT_DESC,
                    'dispersion' => SORT_DESC,
                ]
            ]
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'score' => $this->score,
            'frequency' => $this->frequency,
            'dispersion' => $this->dispersion,
        ]);

        $query->andFilterWhere(['like', 'lemma', $this->lemma]);

        return $dataProvider;
    }
}
