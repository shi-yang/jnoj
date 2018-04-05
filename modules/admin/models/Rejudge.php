<?php

namespace app\modules\admin\models;

use Yii;
use yii\db\Query;
use yii\base\Model;
use app\models\Solution;

/**
 * ContactForm is the model behind the contact form.
 */
class Rejudge extends Model
{
    public $problem_id;
    public $contest_id;
    public $run_id;

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            [['problem_id', 'conteset_id', 'run_id'], 'integer'],
        ];
    }

    /**
     * @return array customized attribute labels
     */
    public function attributeLabels()
    {
        return [
            'problem_id' => 'Problem ID',
            'contest_id' => 'Contest ID',
            'run_id' => 'Run ID',
        ];
    }

    public function getContestIdList()
    {
        $list = (new Query())->select('id, title')
            ->from('{{%contest}}')
            ->where(['status' => 1])
            ->orderBy(['id' => SORT_DESC])
            ->all();
        $res = ['' => ''];
        foreach ($list as $v) {
            $res[$v['id']] = $v['title'] . ' [' . $v['id'] . ']';
        }
        return $res;
    }

    public function run()
    {
        if (!empty($this->problem_id)) {
            Solution::updateAll(['result' => 0], ['problem_id' => $this->problem_id]);
        }
        if (!empty($this->contest_id)) {
            Solution::updateAll(['result' => 0], ['problem_id' => $this->contest_id]);
        }
        if (!empty($this->run_id)) {
            Solution::updateAll(['result' => 0], ['solution_id' => $this->run_id]);
        }
    }
}
