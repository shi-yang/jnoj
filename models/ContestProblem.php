<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%contest_problem}}".
 *
 * @property int $problem_id
 * @property int $contest_id
 * @property int $num
 */
class ContestProblem extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%contest_problem}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['problem_id', 'contest_id', 'num'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'problem_id' => Yii::t('app', 'Problem ID'),
            'contest_id' => Yii::t('app', 'Contest ID'),
            'num' => Yii::t('app', 'Num'),
        ];
    }

    public function getProblem()
    {
        return $this->hasOne(Problem::className(), ['problem_id' => 'problem_id']);
    }
}
