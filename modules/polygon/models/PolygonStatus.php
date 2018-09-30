<?php

namespace app\modules\polygon\models;

use app\models\Solution;
use Yii;
use yii\db\Expression;


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
 * @property int $created_by
 * @property int $language
 * @property string $source
 */
class PolygonStatus extends Solution
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
            [['problem_id', 'result', 'time', 'memory', 'language', 'created_by'], 'integer'],
            [['info', 'source'], 'string'],
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

    /**
     * 屏蔽父类的 beforeSave()
     * This is invoked before the record is saved.
     */
    public function beforeSave($insert)
    {
        return true;
    }
}
