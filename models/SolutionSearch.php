<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Solution;
use yii\db\Query;

/**
 * SolutionSearch represents the model behind the search form of `app\models\Solution`.
 */
class SolutionSearch extends Solution
{
    public $username;
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'problem_id', 'time', 'memory', 'result', 'language', 'contest_id', 'status', 'code_length'], 'integer'],
            [['user_id', 'created_at', 'ip', 'judgetime', 'judge'], 'safe'],
            [['username', 'pass_info'], 'string'],
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
     * @param integer $contest_id
     *
     * @return ActiveDataProvider
     */
    public function search($params, $contest_id = NULL)
    {
        $query = Solution::find()->with('user');

        if ($contest_id != NULL) {
            $query = $query->where(['contest_id' => $contest_id])->with([
                'contestProblem' => function (\yii\db\ActiveQuery $query) use ($contest_id) {
                    $query->andWhere(['contest_id' => $contest_id]);
                }
            ]);
        } else {
            $query = $query->where(['status' => Solution::STATUS_VISIBLE]);
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query->orderBy(['id' => SORT_DESC]),
            'pagination' => [
                'pageSize' => 30,
            ],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }
        $user_id = '';
        if (isset($this->username)) {
            $user_id = (new Query())->select('id')
                ->from('{{%user}}')
                ->andWhere('nickname=:name', [':name' => $this->username])
                ->orWhere('username=:name', [':name' => $this->username])
                ->column();
        }
        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'problem_id' => $this->problem_id,
            'time' => $this->time,
            'memory' => $this->memory,
            'created_at' => $this->created_at,
            'result' => $this->result,
            'language' => $this->language,
            'user_id' => $user_id,
            'code_length' => $this->code_length,
            'judgetime' => $this->judgetime,
            'pass_info' => $this->pass_info,
        ]);

        $query->andFilterWhere(['like', 'user_id', $this->user_id])
            ->andFilterWhere(['like', 'judge', $this->judge]);

        return $dataProvider;
    }
}
