<?php
namespace app\models;

use Yii;
use yii\base\Model;
use app\models\User;

/**
 * Signup form
 *
 * @author Shiyang <dr@shiyang.me>
 */
class SignupForm extends Model
{
    public $username;
    public $email;
    public $password;
    public $verifyCode;
    public $studentNumber;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['username', 'filter', 'filter' => 'trim'],
            ['username', 'required'],
            ['studentNumber', 'integer'],
            ['username', 'unique', 'targetClass' => '\app\models\User', 'message' => 'This username has already been taken.'],
            ['username', 'string', 'max' => 32, 'min' => 4],
            ['username', 'match', 'pattern' => '/^(?!_)(?!.*?_$)(?!\d{4,32}$)[a-z\d_]{4,32}$/i', 'message' => '用户名只能以数字、字母、下划线，且非纯数字，长度在 4 - 32 位之间'],
            ['username', 'match', 'pattern' => '/^(?!c[\d]+user[\d])/', 'message' => '以c+数字+user+数字作为账户名系统保留'],

            ['email', 'filter', 'filter' => 'trim'],
            ['email', 'required'],
            ['email', 'email'],
            ['email', 'unique', 'targetClass' => '\app\models\User', 'message' => 'This email address has already been taken.'],

            ['password', 'required'],
            ['password', 'string', 'min' => 6],

            ['verifyCode', 'captcha']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'username' => Yii::t('app', 'Username'),
            'password' => Yii::t('app', 'Password'),
            'email' => Yii::t('app', 'Email'),
            'verifyCode' => Yii::t('app', 'Verify Code'),
            'studentNumber' => Yii::t('app', 'Student Number')
        ];
    }

    /**
     * Signs user up.
     *
     * @return null whether the creating new account was successful and email was sent
     * @throws \yii\db\Exception
     */
    public function signup()
    {
        if (!$this->validate()) {
            return null;
        }
        $user = new User();
        $user->username = $this->username;
        $user->nickname = $this->username;
        $user->email = $this->email;
        $user->is_verify_email = User::VERIFY_EMAIL_NO;
        $user->setPassword($this->password);
        $user->generateAuthKey();
        if (Yii::$app->setting->get('mustVerifyEmail')) {
            // 发送邮箱
            $user->generateEmailVerificationToken();
            if (!$this->sendEmail($user)) {
                Yii::$app->session->setFlash('error',
                    '验证邮箱发送失败。可能原因：1. 该邮箱不存在；2. 本网站系统邮箱配置信息有误，需联系管理员检查系统的发送邮箱配置信息。');
                return null;
            }
            $user->status = User::STATUS_INACTIVE;
        } else {
            $user->status = User::STATUS_ACTIVE;
        }
        if (!$user->save()) {
            return null;
        }
        Yii::$app->db->createCommand()->insert('{{%user_profile}}', [
            'user_id' => $user->id,
            'student_number' => $this->studentNumber
        ])->execute();
        return $user;
    }

    /**
     * Sends confirmation email to user
     * @param User $user user model to with email should be send
     * @return bool whether the email was sent
     */
    protected function sendEmail($user)
    {
        return Yii::$app
            ->mailer
            ->compose(
                ['html' => 'emailVerify-html', 'text' => 'emailVerify-text'],
                ['user' => $user]
            )
            ->setFrom([Yii::$app->setting->get('emailUsername') => Yii::$app->setting->get('ojName')])
            ->setTo($this->email)
            ->setSubject('帐号注册 - ' . Yii::$app->setting->get('ojName'))
            ->send();
    }
}
