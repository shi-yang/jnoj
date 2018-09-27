<?php

namespace app\modules\polygon\models;

use Yii;

/**
 * This is the model class for table "{{%polygon_status}}".
 *
 * @property int $id
 * @property int $problem_id
 * @property int $result
 * @property int $time
 * @property int $memory
 * @property string $info
 * @property string $created_at
 */
class PolygonStatus extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%polygon_status}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['problem_id', 'result', 'time', 'memory'], 'integer'],
            [['info'], 'string'],
            [['created_at'], 'required'],
            [['created_at'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'problem_id' => Yii::t('app', 'Problem ID'),
            'result' => Yii::t('app', 'Result'),
            'time' => Yii::t('app', 'Time'),
            'memory' => Yii::t('app', 'Memory'),
            'info' => Yii::t('app', 'Info'),
            'created_at' => Yii::t('app', 'Created At'),
        ];
    }
}
