<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%contest_print}}".
 *
 * @property int $id
 * @property int $user_id
 * @property int $source
 * @property string $created_at
 * @property int $contest_id
 * @property int $status
 */
class ContestPrint extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%contest_print}}';
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
            [['source', 'created_at'], 'string'],
            [['source'], 'required'],
            [['id', 'user_id', 'contest_id', 'status', 'contest_id'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'source' => 'Source',
            'created_at' => 'Created At',
            'status' => 'Status',
            'contest_id' => 'Contest Id'
        ];
    }

    /**
     * This is invoked before the record is saved.
     * @return boolean whether the record should be saved.
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($this->isNewRecord) {
                $this->user_id = Yii::$app->user->id;
            }
            return true;
        } else {
            return false;
        }
    }

    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    public function getContest()
    {
        return $this->hasOne(Contest::className(), ['id' => 'contest_id']);
    }
}
