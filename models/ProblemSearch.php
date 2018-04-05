<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Problem;

/**
 * ProblemSearch represents the model behind the search form of `app\models\Problem`.
 */
class ProblemSearch extends Problem
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'time_limit', 'memory_limit', 'accepted', 'submit', 'solved'], 'integer'],
            [['title', 'description', 'input', 'output', 'sample_input', 'sample_output', 'spj', 'hint', 'source', 'created_at', 'status'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
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
        $query = Problem::find()->where(['status' => Problem::STATUS_VISIBLE]);

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 50
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
            'created_at' => $this->created_at,
            'time_limit' => $this->time_limit,
            'memory_limit' => $this->memory_limit,
            'accepted' => $this->accepted,
            'submit' => $this->submit,
            'solved' => $this->solved,
        ]);

        $query->andFilterWhere(['like', 'title', $this->title])
            ->andFilterWhere(['like', 'description', $this->description])
            ->andFilterWhere(['like', 'input', $this->input])
            ->andFilterWhere(['like', 'output', $this->output])
            ->andFilterWhere(['like', 'sample_input', $this->sample_input])
            ->andFilterWhere(['like', 'sample_output', $this->sample_output])
            ->andFilterWhere(['like', 'spj', $this->spj])
            ->andFilterWhere(['like', 'hint', $this->hint])
            ->andFilterWhere(['like', 'source', $this->source])
            ->andFilterWhere(['like', 'status', $this->status]);

        return $dataProvider;
    }
}
