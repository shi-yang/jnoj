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
            [['created_by', 'created_at', 'ip', 'judgetime', 'judge'], 'safe'],
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
            if (Yii::$app->user->isGuest || Yii::$app->user->identity->role != User::ROLE_ADMIN) {
                $time = Yii::$app->db->createCommand('SELECT lock_board_time, end_time FROM {{%contest}} WHERE id = :id', [
                    ':id' => $contest_id
                ])->queryOne();
                $lockTime = strtotime($time['lock_board_time']);
                $endTime = strtotime($time['end_time']);
                if (!empty($lockTime) && $lockTime <= time() && time() <= $endTime + 120 * 60) {
                    $query->andWhere('created_by=:uid OR created_at < :lock_board_time', [
                        ':uid' => Yii::$app->user->id,
                        ':lock_board_time' => $time['lock_board_time']
                    ]);
                }
            }
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
        $created_by = '';
        if (isset($this->username)) {
            $created_by = (new Query())->select('id')
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
            'created_by' => $created_by,
            'code_length' => $this->code_length,
            'judgetime' => $this->judgetime,
            'pass_info' => $this->pass_info,
        ]);

        $query->andFilterWhere(['like', 'created_by', $this->created_by])
            ->andFilterWhere(['like', 'judge', $this->judge]);

        return $dataProvider;
    }
}
