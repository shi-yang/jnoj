<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%solution_info}}".
 *
 * @property int $solution_id
 * @property string $error
 */
class SolutionInfo extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%solution_info}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['solution_id'], 'required'],
            [['solution_id'], 'integer'],
            [['error'], 'string'],
            [['solution_id'], 'unique'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'solution_id' => Yii::t('app', 'Solution ID'),
            'error' => Yii::t('app', 'Error'),
        ];
    }
}
