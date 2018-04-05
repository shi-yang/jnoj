<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Contest;

/**
 * ContestSearch represents the model behind the search form of `app\models\Contest`.
 */
class ContestSearch extends Contest
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['cid', 'hide_others', 'isvirtual', 'type', 'has_cha', 'owner_viewable'], 'integer'],
            [['title', 'description', 'start_time', 'end_time', 'lock_board_time', 'board_make', 'owner', 'report', 'mboard_make', 'allp', 'challenge_end_time', 'challenge_start_time', 'password'], 'safe'],
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
        $query = Contest::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'cid' => $this->cid,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'lock_board_time' => $this->lock_board_time,
            'hide_others' => $this->hide_others,
            'board_make' => $this->board_make,
            'isvirtual' => $this->isvirtual,
            'mboard_make' => $this->mboard_make,
            'type' => $this->type,
            'has_cha' => $this->has_cha,
            'challenge_end_time' => $this->challenge_end_time,
            'challenge_start_time' => $this->challenge_start_time,
            'owner_viewable' => $this->owner_viewable,
        ]);

        $query->andFilterWhere(['like', 'title', $this->title])
            ->andFilterWhere(['like', 'description', $this->description])
            ->andFilterWhere(['like', 'owner', $this->owner])
            ->andFilterWhere(['like', 'report', $this->report])
            ->andFilterWhere(['like', 'allp', $this->allp])
            ->andFilterWhere(['like', 'password', $this->password]);

        return $dataProvider;
    }
}
