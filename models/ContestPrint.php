<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%print_source}}".
 *
 * @property int $id
 * @property int $user_id
 * @property int $source
 * @property int $created_at
 * @property int $status
 */
class ContestPrint extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%contest_print}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['source'], 'string'],
            [['source'], 'required'],
            [['id', 'user_id', 'contest_id','created_at', 'status'], 'integer'],
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
                $this->created_at = time();
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
        return $this->hasOne(Contest::className(), ['contest_id' => 'contest_id']);
    }
}
