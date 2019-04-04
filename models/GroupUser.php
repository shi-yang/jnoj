<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%group_user}}".
 *
 * @property int $id
 * @property int $group_id
 * @property int $user_id
 * @property int $role
 * @property string $created_at
 */
class GroupUser extends \yii\db\ActiveRecord
{
    /**
     * 权限对应：拒绝，邀请中，普通成员，管理员，领导（最高权限）
     */
    const ROLE_REUSE = 0;
    const ROLE_INVITING = 1;
    const ROLE_MEMBER = 2;
    const ROLE_MANAGER = 3;
    const ROLE_LEADER = 4;

    /**
     * @var string 邀请用户时用到
     */
    public $username;
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%group_user}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['group_id', 'user_id', 'created_at'], 'required'],
            [['group_id', 'user_id', 'role'], 'integer'],
            ['username', 'string'],
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
            'group_id' => Yii::t('app', 'Group ID'),
            'user_id' => Yii::t('app', 'User ID'),
            'role' => Yii::t('app', 'Role'),
            'created_at' => Yii::t('app', 'Created At'),
            'username' => Yii::t('app', 'Username')
        ];
    }

    /**
     * {@inheritdoc}
     * @return GroupUserQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new GroupUserQuery(get_called_class());
    }

    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    public function getRole($color = false)
    {
        $roles = [
            Yii::t('app', 'Refuse to join'),
            Yii::t('app', 'Inviting'),
            Yii::t('app', 'Member'),
            Yii::t('app', 'Manager'),
            Yii::t('app', 'Leader')
        ];
        if (!$color) {
            return $roles[$this->role];
        }
        $rolesColor = [
            'text-danger',
            'text-warning',
            'text-info',
            'text-primary',
            'text-success'
        ];
        return '<span class="' . $rolesColor[$this->role] . '">' . $roles[$this->role] . '</span>' ;
    }
}
