<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%contest_user}}".
 *
 * @property int $id
 * @property int $user_id
 * @property int $contest_id
 * @property string $user_password
 */
class ContestUser extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%contest_user}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'contest_id'], 'required'],
            [['user_id', 'contest_id'], 'integer'],
            [['user_password'], 'string']
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
            'contest_id' => 'Contest ID',
            'user_password' => Yii::t('app', 'User Password')
        ];
    }

    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    public function getUserProfile()
    {
        return $this->hasOne(UserProfile::className(), ['user_id' => 'user_id']);
    }

    public function getContest()
    {
        return $this->hasOne(Contest::className(), ['contest_id' => 'contest_id']);
    }
}
