<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%group}}".
 *
 * @property int $id
 * @property string $name
 * @property string $description
 * @property int $status
 * @property int $join_policy
 * @property int $created_by
 * @property string $created_at
 * @property string $updated_at
 */
class Group extends ActiveRecord
{
    const STATUS_HIDDEN = 0;
    const STATUS_VISIBLE = 1;

    const JOIN_POLICY_INVITE = 0;
    const JOIN_POLICY_APPLICATION = 1;
    const JOIN_POLICY_FREE = 2;
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%group}}';
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'timestamp' => $this->timeStampBehavior(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'status', 'join_policy'], 'required'],
            [['status'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['name'], 'string', 'max' => 32],
            [['description'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'name' => Yii::t('app', 'Group Name'),
            'description' => Yii::t('app', 'Description'),
            'status' => Yii::t('app', 'Status'),
            'join_policy' => Yii::t('app', 'Join Policy'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
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
                $this->created_by = Yii::$app->user->id;
            }
            return true;
        } else {
            return false;
        }
    }

    /**
     * {@inheritdoc}
     * @return GroupQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new GroupQuery(get_called_class());
    }

    public function getJoinPolicy()
    {
        $policy = [
            Yii::t('app', 'Invite Only'),
            Yii::t('app', 'Application & Approve'),
            Yii::t('app', 'Free')
        ];
        return $policy[$this->join_policy];
    }

    public function getStatus()
    {
        $status = [
            Yii::t('app', 'Hidden'),
            Yii::t('app', 'Visible')
        ];
        return $status[$this->status];
    }

    /**
     * 获取当前登录用户的角色
     * @return mixed
     * @throws \Throwable
     */
    public function getRole()
    {
        return Yii::$app->db->cache(function ($db) {
            return $db->createCommand('SELECT role FROM {{%group_user}} WHERE user_id=:uid AND group_id=:gid',[
                ':uid' => Yii::$app->user->id,
                ':gid' => $this->id
            ])->queryScalar();
        }, 60);
    }

    public function getGroupUser()
    {
        return $this->hasMany(GroupUser::className(), ['group_id' => 'id']);
    }

    /**
     * 判断当前用户是否在小组中
     * @return boolean
     */
    public function isUserInGroup()
    {
        if (Yii::$app->user->isGuest) {
            return false;
        }
        return Yii::$app->db->createCommand('SELECT count(*) FROM {{%group_user}} WHERE user_id=:uid AND group_id=:gid', [
            ':uid' => Yii::$app->user->id,
            ':gid' => $this->id
        ])->queryScalar();
    }

    public function hasPermission()
    {
        return $this->getRole() == GroupUser::ROLE_LEADER || $this->getRole() == GroupUser::ROLE_MANAGER;
    }
}
