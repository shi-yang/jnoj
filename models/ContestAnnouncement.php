<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%contest_announcement}}".
 *
 * @property int $contest_id
 * @property string $content
 * @property string $created_at
 */
class ContestAnnouncement extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%contest_announcement}}';
    }

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
            [['content'], 'required'],
            [['contest_id'], 'integer'],
            [['content', 'created_at'], 'string'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'contest_id' => Yii::t('app', 'Contest ID'),
            'content' => Yii::t('app', 'Content'),
            'created_at' => Yii::t('app', 'Created At'),
        ];
    }
}
