<?php
namespace app\models;

use Yii;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;
use yii\db\Query;
use yii\helpers\Html;
use yii\web\IdentityInterface;

/**
 * User model
 *
 * @property integer $id
 * @property string $username
 * @property string $nickname
 * @property string $password_hash
 * @property string $password_reset_token
 * @property string $email
 * @property string $auth_key
 * @property integer $status
 * @property integer $role
 * @property integer $language
 * @property string $created_at
 * @property string $updated_at
 * @property string $password write-only password
 * @property integer $rating
 * @property integer $is_verify_email
 * @property string $verification_token
 */
class User extends ActiveRecord implements IdentityInterface
{
    const STATUS_DELETED = 0;
    const STATUS_DISABLE = 8;
    const STATUS_INACTIVE = 9;
    const STATUS_ACTIVE = 10;

    /**
     * 比赛账户，该账户用于参加比赛，跟普通用户的区别在于禁止私自修改个人信息，用户名、昵称、密码
     * 普通用户
     * VIP用户
     * 管理员
     */
    const ROLE_PLAYER = 0;
    const ROLE_USER = 10;
    const ROLE_VIP = 20;
    const ROLE_ADMIN = 30;

    public $oldPassword;
    public $newPassword;
    public $verifyPassword;

    /**
     * 是否已经验证邮箱
     */
    const VERIFY_EMAIL_NO = 0;
    const VERIFY_EMAIL_YES = 1;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%user}}';
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
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['language', 'rating', 'role', 'is_verify_email'], 'integer'],
            ['verification_token', 'string'],
            [['username', 'nickname'], 'required'],
            [['nickname'], 'string', 'max' => 16],
            ['username', 'match', 'pattern' => '/^(?!_)(?!.*?_$)(?!\d{4,32}$)[a-z\d_]{4,32}$/i', 'message' => '用户名只能以数字、字母、下划线，且非纯数字，长度在 4 - 32 位之间'],
            ['username', 'match', 'pattern' => '/^(?!c[\d]+user[\d])/', 'message' => '以c+数字+user+数字作为账户名系统保留', 'when' => function($model) {
                return $model->role != User::ROLE_PLAYER;
            }],
            ['username', 'unique', 'targetClass' => '\app\models\User', 'message' => 'This username has already been taken.'],
            ['status', 'in', 'range' => [self::STATUS_ACTIVE, self::STATUS_DELETED, self::STATUS_INACTIVE]],
            ['role', 'in', 'range' => [self::ROLE_PLAYER, self::ROLE_USER, self::ROLE_VIP, self::ROLE_ADMIN]],

            // oldPassword is validated by validateOldPassword()
            [['oldPassword'], 'validateOldPassword'],
            [['verifyPassword'], 'compare', 'compareAttribute' => 'newPassword'],
            [['oldPassword', 'verifyPassword', 'newPassword'], 'required']
        ];
    }

    public function validateOldPassword()
    {
        $user = self::findOne($this->id);

        if (!$user || !$user->validatePassword($this->oldPassword)) {
            Yii::$app->getSession()->setFlash('error', 'Incorrect old password.');
            $this->addError('password', 'Incorrect old password.');
        }
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'username' => Yii::t('app', 'Username'),
            'nickname' => Yii::t('app', 'Nickname'),
            'password' => Yii::t('app', 'Password'),
            'oldPassword' => Yii::t('app', 'Old Password'),
            'newPassword' => Yii::t('app', 'New Password'),
            'verifyPassword' => Yii::t('app', 'Verify Password'),
            'status' => Yii::t('app', 'Status'),
            'email' => Yii::t('app', 'Email'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
            'avatar' => Yii::t('app', 'User Icon'),
            'rating' => Yii::t('app', 'Rating'),
            'role' => Yii::t('app', 'Role')
        ];
    }

    public function scenarios()
    {
        return [
            'default' => ['username', 'email'],
            'profile' => ['nickname'],
            'security' => ['oldPassword', 'newPassword', 'verifyPassword'],
        ];
    }

    /**
     * @inheritdoc
     */
    public static function findIdentity($id)
    {
        return static::findOne(['id' => $id, 'status' => self::STATUS_ACTIVE]);
    }

    /**
     * @inheritdoc
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        throw new NotSupportedException('"findIdentityByAccessToken" is not implemented.');
    }

    /**
     * 登陆时输入的字符串
     * Finds user by loginID
     *
     * @param $loginID
     * @return User|null
     */
    public static function findByLoginID($loginID)
    {
        if (is_numeric($loginID)) {
            $param = 'id';
        } elseif (strpos($loginID, '@')) {
            $param = 'email';
        } else {
            $param = 'username';
        }
        return static::findOne([$param => $loginID]);
    }

    /**
     * Finds user by password reset token
     *
     * @param string $token password reset token
     * @return static|null
     */
    public static function findByPasswordResetToken($token)
    {
        if (!static::isPasswordResetTokenValid($token)) {
            return null;
        }

        return static::findOne([
            'password_reset_token' => $token,
            'status' => self::STATUS_ACTIVE,
        ]);
    }

    /**
     * Finds user by verification email token
     *
     * @param string $token verify email token
     * @return static|null
     */
    public static function findByVerificationToken($token) {
        return static::findOne([
            'verification_token' => $token,
        ]);
    }

    /**
     * Finds out if password reset token is valid
     *
     * @param string $token password reset token
     * @return bool
     */
    public static function isPasswordResetTokenValid($token)
    {
        if (empty($token)) {
            return false;
        }

        $timestamp = (int) substr($token, strrpos($token, '_') + 1);
        $expire = Yii::$app->setting->get('passwordResetTokenExpire');
        return $timestamp + $expire >= time();
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->getPrimaryKey();
    }

    /**
     * @inheritdoc
     */
    public function getAuthKey()
    {
        return $this->auth_key;
    }

    /**
     * @inheritdoc
     */
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return bool if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    /**
     * Generates password hash from password and sets it to the model
     *
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->generateAuthKey();
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
    }

    public static function setLanguage($language)
    {
        return Yii::$app->db->createCommand()->update('{{%user}}', [
            'language' => $language
        ], ['id' => Yii::$app->user->id])->execute();
    }

    /**
     * Generates "remember me" authentication key
     */
    public function generateAuthKey()
    {
        $this->auth_key = Yii::$app->security->generateRandomString();
    }

    /**
     * Generates new password reset token
     */
    public function generatePasswordResetToken()
    {
        $this->password_reset_token = Yii::$app->security->generateRandomString() . '_' . time();
    }

    /**
     * Generates new token for email verification
     */
    public function generateEmailVerificationToken()
    {
        $this->verification_token = Yii::$app->security->generateRandomString() . '_' . time();
    }

    /**
     * Removes password reset token
     */
    public function removePasswordResetToken()
    {
        $this->password_reset_token = null;
    }

    /**
     * 获取个人基本资料
     * @return \yii\db\ActiveQuery
     */
    public function getProfile()
    {
        return $this->hasOne(UserProfile::className(), ['user_id' => 'id']);
    }

    /**
     *
     */
    public function getSolutionStats()
    {
        $data = Yii::$app->db->createCommand(
            'SELECT problem_id, language, result FROM {{%solution}} WHERE created_by=:uid',
            [':uid' => $this->id]
        )->queryAll();

        $ac_count = 0;
        $tle_count = 0;
        $ce_count = 0;
        $wa_count = 0;
        $all_count = count($data);
        $solved_problem = [];
        $unsolved_problem = [];
        foreach ($data as $v) {
            if ($v['result'] == Solution::OJ_AC) {
                array_push($solved_problem, $v['problem_id']);
            } else {
                array_push($unsolved_problem, $v['problem_id']);
            }

            if ($v['result'] == Solution::OJ_WA) {
                $wa_count++;
            } else if ($v['result'] == Solution::OJ_AC) {
                $ac_count++;
            } else if ($v['result'] == Solution::OJ_CE) {
                $ce_count++;
            } else if ($v['result'] == Solution::OJ_TL) {
                $tle_count++;
            }
        }
        $solved_problem = array_unique($solved_problem);
        $unsolved_problem = array_unique($unsolved_problem);
        $unsolved_problem = array_diff($unsolved_problem, $solved_problem);
        $solved_problem = array_values($solved_problem);
        $unsolved_problem = array_values($unsolved_problem);
        return [
            'ac_count' => $ac_count,
            'ce_count' => $ce_count,
            'wa_count' => $wa_count,
            'tle_count' => $tle_count,
            'all_count' => $all_count,
            'solved_problem' => $solved_problem,
            'unsolved_problem' => $unsolved_problem
        ];
    }

    public function getRecentSubmission() {
        return (new Query())->select('s.id, p.title, p.id as problem_id, s.created_at, s.result')
            ->from('{{%solution}} s')
            ->leftJoin('{{%problem}} p', 'p.id=s.problem_id')
            ->where('s.created_by=:id AND s.status=:status', [':id' => $this->id, ':status' => Solution::STATUS_VISIBLE])
            ->orderBy('s.id DESC')
            ->limit(10)
            ->all();
    }

    public function getRatingLevel($rating = -1)
    {
        if ($rating == -1)
            $rating = $this->rating;
        if ($this->role == self::ROLE_ADMIN) {
            return Yii::t('app', 'Headquarters');
        } else if ($rating == NULL) {
            return Yii::t('app', 'Unrated');
        } else if ($rating < 1150) {
            return Yii::t('app', 'Bronze');
        } else if ($rating < 1400) {
            return Yii::t('app', 'Silver');
        } else if ($rating < 1650) {
            return Yii::t('app', 'Gold');
        } else if ($rating < 1900) {
            return Yii::t('app', 'Platinum');
        } else if ($rating < 2150) {
            return Yii::t('app', 'Diamond');
        } else if ($rating < 2400) {
            return Yii::t('app', 'Master');
        } else {
            return Yii::t('app', 'Challenger');
        }
    }

    /**
     * 根据段位返回颜色
     * 该方法在榜单显示时调用
     *
     * @param string $nickname
     * @param integer $rating
     * @return string 含有颜色的HTML昵称
     */
    public static function getColorNameByRating($nickname, $rating)
    {
        $nickname = Html::encode($nickname);
        $colors = [
            'user-black',
            'user-gray',
            'user-green',
            'user-cyan',
            'user-blue',
            'user-orange',
            'user-violet',
            'user-yellow',
            'user-fire',
            'user-red',
            'user-admin'
        ];
        if (empty($rating)) {
            $tmp = $colors[0];
        } else if ($rating < 1150) {
            $tmp = $colors[1];
        } else if ($rating < 1400) {
            $tmp = $colors[2];
        } else if ($rating < 1650) {
            $tmp = $colors[3];
        } else if ($rating < 1900) {
            $tmp = $colors[4];
        } else if ($rating < 2150) {
            $tmp = $colors[5];
        } else {
            $tmp = $colors[6];
        }
        return "<span class=\"{$tmp} rated-user\">{$nickname}</span>";
    }

    /**
     * 根据段位返回颜色
     */
    public function getColorName()
    {
        $rating = $this->rating;
        $nickname = Html::encode($this->nickname);
        $colors = [
            'user-black',
            'user-gray',
            'user-green',
            'user-cyan',
            'user-blue',
            'user-orange',
            'user-violet',
            'user-yellow',
            'user-fire',
            'user-red',
            'user-admin'
        ];
        if ($this->role == self::ROLE_ADMIN) {
            $tmp = $colors[10];
        } else if ($rating == NULL) {
            $tmp = $colors[0];
        } else if ($rating < 1150) {
            $tmp = $colors[1];
        } else if ($rating < 1400) {
            $tmp = $colors[2];
        } else if ($rating < 1650) {
            $tmp = $colors[3];
        } else if ($rating < 1900) {
            $tmp = $colors[4];
        } else if ($rating < 2150) {
            $tmp = $colors[5];
        } else {
            $tmp = $colors[6];
        }
        return "<span class=\"{$tmp} rated-user\">{$nickname}</span>";
    }

    public function isAdmin()
    {
        return $this->role == self::ROLE_ADMIN;
    }

    /**
     * 是否已经验证邮箱
     */
    public function isVerifyEmail()
    {
        return $this->is_verify_email == self::VERIFY_EMAIL_YES;
    }
}
