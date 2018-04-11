<?php

namespace app\models;

use Yii;
use yii\db\Query;

/**
 * This is the model class for table "{{%solution}}".
 *
 * @property int $id
 * @property int $problem_id
 * @property int $created_by
 * @property int $time
 * @property int $memory
 * @property string $created_at
 * @property string $source
 * @property int $result
 * @property int $language
 * @property int $contest_id
 * @property int $status
 * @property int $code_length
 * @property string $judgetime
 * @property string $pass_info
 * @property string $judge
 */
class Solution extends ActiveRecord
{
    const STATUS_HIDDEN = 0;
    const STATUS_VISIBLE = 1;

    const OJ_WT0 = 0;
    const OJ_WT1 = 1;
    const OJ_CI  = 2;
    const OJ_RI  = 3;
    const OJ_AC  = 4;
    const OJ_PE  = 5;
    const OJ_WA  = 6;
    const OJ_TL  = 7;
    const OJ_ML  = 8;
    const OJ_OL  = 9;
    const OJ_RE  = 10;
    const OJ_CE  = 11;
    const OJ_CO  = 12;

    const CLANG = 0;
    const CPPLANG = 1;
    const JAVALANG = 2;
    const PYLANG = 3;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%solution}}';
    }


    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'timestamp' => $this->timeStampBehavior(false),
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['problem_id', 'created_by', 'time', 'memory', 'result', 'language', 'contest_id', 'status', 'code_length'], 'integer'],
            [['created_at', 'judgetime'], 'safe'],
            [['language', 'source'], 'required'],
            [['language'], 'in', 'range' => [0, 1, 2, 3], 'message' => 'Please select a language'],
            [['source', 'pass_info'], 'string'],
            [['judge'], 'string', 'max' => 16],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'Run ID'),
            'problem_id' => Yii::t('app', 'Problem ID'),
            'created_by' => Yii::t('app', 'Created By'),
            'time' => Yii::t('app', 'Time'),
            'memory' => Yii::t('app', 'Memory'),
            'created_at' => Yii::t('app', 'Submit Time'),
            'source' => Yii::t('app', 'Source'),
            'result' => Yii::t('app', 'Result'),
            'language' => Yii::t('app', 'Language'),
            'contest_id' => Yii::t('app', 'Contest ID'),
            'status' => Yii::t('app', 'Status'),
            'code_length' => Yii::t('app', 'Code Length'),
            'judgetime' => Yii::t('app', 'Judgetime'),
            'pass_info' => Yii::t('app', 'Pass Info'),
            'judge' => Yii::t('app', 'Judge'),
        ];
    }

    /**
     * @inheritdoc
     * @return SolutionQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new SolutionQuery(get_called_class());
    }

    /**
     * This is invoked before the record is saved.
     * @return boolean whether the record should be saved.
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($this->isNewRecord) {
                $this->created_by = Yii::$app->user->id;
                $this->code_length = strlen($this->source);
            }
            return true;
        } else {
            return false;
        }
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        if ($this->language != Yii::$app->user->identity->language) {
            User::setLanguage($this->language);
        }
    }

    public function getTestCount()
    {
        return intval(substr(strstr($this->pass_info,'/'), 1));
    }

    public function getPassedTestCount()
    {
        return intval(strstr($this->pass_info,'/', true));
    }

    public function getLang()
    {
        switch ($this->language) {
            case Solution::CLANG:
                $res = 'C';
                break;
            case Solution::CPPLANG:
                $res = 'C++';
                break;
            case Solution::JAVALANG:
                $res = 'Java';
                break;
            case Solution::PYLANG:
                $res = 'Python3';
                break;
            default:
                $res = 'not set';
                break;
        }
        return $res;
    }

    public function getResult()
    {
        $res = self::getResultList($this->result);
        if ($this->result == Solution::OJ_AC) {
            return '<p class="text-success"><strong>' . $res . '</strong></p>';
        } else {
            return '<p class="text-danger"><strong>' . $res . '</strong></p>';
        }
    }

    public static function getResultList($res = '')
    {
        $results = [
            '' => 'All',
            Solution::OJ_WT0 => Yii::t('app', 'Pending'),
            Solution::OJ_WT1 => Yii::t('app', 'Pending Rejudge'),
            Solution::OJ_CI => Yii::t('app', 'Compiling'),
            Solution::OJ_RI => Yii::t('app', 'Running & Judging'),
            Solution::OJ_AC => Yii::t('app', 'Accepted'),
            Solution::OJ_PE => Yii::t('app', 'Presentation Error'),
            Solution::OJ_WA => Yii::t('app', 'Wrong Answer'),
            Solution::OJ_TL => Yii::t('app', 'Time Limit Exceeded'),
            Solution::OJ_ML => Yii::t('app', 'Memory Limit Exceeded'),
            Solution::OJ_OL => Yii::t('app', 'Output Limit Exceeded'),
            Solution::OJ_RE => Yii::t('app', 'Runtime Error'),
            Solution::OJ_CE => Yii::t('app', 'Compile Error'),
            //Solution::OJ_CO => Yii::t('app', 'Compile Succeed')
        ];
        return $res === '' ? $results : $results[$res];
    }

    public static function getLanguageList($status = '')
    {
        $arr = [
            '' => 'All',
            '0' => 'C',
            '1' => 'C++',
            '2' => 'Java',
            '3' => 'Python3'
        ];
        return $status === '' ? $arr : $arr[$status];
    }

    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'created_by']);
    }

    public function getProblem()
    {
        return $this->hasOne(Problem::className(), ['id' => 'problem_id']);
    }

    public function getUsername()
    {
        return $this->user->username;
    }

    public function getSolutionInfo()
    {
        return $this->hasOne(SolutionInfo::className(), ['solution_id' => 'id']);
    }

    public function getContestProblem()
    {
        return $this->hasOne(ContestProblem::className(), ['problem_id' => 'problem_id']);
    }

    public function getProblemInContest()
    {
        return $this->contestProblem;
    }
}
