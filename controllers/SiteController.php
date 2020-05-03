<?php

namespace app\controllers;

use app\models\User;
use Yii;
use yii\db\Query;
use yii\web\Controller;
use yii\web\Response;
use yii\data\Pagination;
use yii\web\NotFoundHttpException;
use yii\web\BadRequestHttpException;
use yii\base\InvalidArgumentException;
use app\models\LoginForm;
use app\models\ContactForm;
use app\models\SignupForm;
use app\models\Contest;
use app\models\Discuss;
use app\models\ResendVerificationEmailForm;
use app\models\PasswordResetRequestForm;
use app\models\ResetPasswordForm;
use app\models\VerifyEmailForm;

class SiteController extends Controller
{
    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
                'fontFile' => '@webroot/fonts/Astarisborn.TTF',
                'width' => 180
            ],
        ];
    }

    public function actionConstruction()
    {
        return $this->render('construction');
    }

    public function actionNews($id)
    {
        $model = Discuss::find()->where(['id' => $id, 'status' => Discuss::STATUS_PUBLIC, 'entity' => Discuss::ENTITY_NEWS])->one();

        if ($model === null) {
            throw new NotFoundHttpException('The requested page does not exist.');
        }

        return $this->render('news', [
            'model' => $model
        ]);
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
        $contests = Yii::$app->db->createCommand('
            SELECT id, title FROM {{%contest}}
            WHERE status = :status AND type != :type AND end_time >= :time
            ORDER BY start_time DESC LIMIT 3
        ', [':status' => Contest::STATUS_VISIBLE, ':type' => Contest::TYPE_HOMEWORK, ':time' => date('Y:m:d h:i:s', time())])->queryAll();

        $newsQuery = (new Query())->select('id, title, content, created_at')
            ->from('{{%discuss}}')
            ->where(['entity' => Discuss::ENTITY_NEWS, 'status' => Discuss::STATUS_PUBLIC])
            ->orderBy('id DESC');

        $discusses = (new Query())->select('d.id, d.title, d.created_at, u.nickname, u.username, p.title as ptitle, p.id as pid')
            ->from('{{%discuss}} as d')
            ->leftJoin('{{%user}} as u', 'd.created_by=u.id')
            ->leftJoin('{{%problem}} as p', 'd.entity_id=p.id')
            ->where(['entity' => Discuss::ENTITY_PROBLEM, 'parent_id' => 0])
            ->andWhere('DATE_SUB(CURDATE(), INTERVAL 30 DAY) <= date(d.updated_at)')
            ->orderBy('d.updated_at DESC')
            ->limit(10)
            ->all();

        $pages = new Pagination(['totalCount' => $newsQuery->count()]);
        $news = $newsQuery->offset($pages->offset)
            ->limit($pages->limit)
            ->all();

        return $this->render('index', [
            'contests' => $contests,
            'pages' => $pages,
            'news' => $news,
            'discusses' => $discusses
        ]);
    }

    public function actionPrint()
    {
        return $this->render('print');
    }

    /**
     * Login action.
     *
     * @return Response|string
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if (Yii::$app->session->get('attempts-login') > 2) {
            $model->scenario = 'withCaptcha';
        }
        if ($model->load(Yii::$app->request->post())) {
            if ($model->login()) {
                $status = $model->getUser()->status;
                if ($status == User::STATUS_INACTIVE) {
                    $url = Yii::$app->urlManager->createAbsoluteUrl(['/site/resend-verification-email']);
                    $a = "<a href=\"$url\">$url</a>";
                    Yii::$app->session->setFlash('error', '该用户尚未激活，无法登陆。请先验证邮箱：' . $a);
                } else if ($status == User::STATUS_DISABLE) {
                    Yii::$app->session->setFlash('error', '该用户已被禁用');
                }
                return $this->goBack();
            } else {
                Yii::$app->session->set('attempts-login', Yii::$app->session->get('attempts-login', 0) + 1);
                if (Yii::$app->session->get('attempts-login') > 2) {
                    $model->scenario = 'withCaptcha';
                }
            }
        }
        return $this->render('login', [
            'model' => $model,
        ]);
    }

    public function actionSignup()
    {
        $model = new SignupForm();

        if ($model->load(Yii::$app->request->post())) {
            if ($user = $model->signup()) {
                if (Yii::$app->setting->get('mustVerifyEmail')) {
                    $url = Yii::$app->urlManager->createAbsoluteUrl(['/site/resend-verification-email']);
                    $a = "<a href=\"$url\">没有收到？</a>";
                    Yii::$app->session->setFlash('success', '欢迎注册使用！请检查你的邮箱收件箱，对邮箱进行验证。' . $a);
                    return $this->goHome();
                }
                if (Yii::$app->getUser()->login($user)) {
                    return $this->goHome();
                }
            }
        }

        return $this->render('signup', [
            'model' => $model,
        ]);
    }

    /**
     * Logout action.
     *
     * @return Response
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    /**
     * Displays contact page.
     *
     * @return Response|string
     */
    public function actionContact()
    {
        $model = new ContactForm();
        if ($model->load(Yii::$app->request->post()) && $model->contact(Yii::$app->params['adminEmail'])) {
            Yii::$app->session->setFlash('contactFormSubmitted');

            return $this->refresh();
        }
        return $this->render('contact', [
            'model' => $model,
        ]);
    }

    /**
     * Displays about page.
     *
     * @return string
     */
    public function actionAbout()
    {
        return $this->render('about');
    }

    /**
     * Requests password reset.
     *
     * @return mixed
     */
    public function actionRequestPasswordReset()
    {
        $model = new PasswordResetRequestForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->sendEmail()) {
                Yii::$app->session->setFlash('success', '重置密码的链接已发送到您的邮箱。请检查您的邮箱获取重置密码的链接。');

                return $this->goHome();
            } else {
                Yii::$app->session->setFlash('error', '根据提供的邮箱无法发送重置密码链接。可能原因：1. 该邮箱不存在；2. 本网站系统邮箱配置信息有误，需联系管理员检查系统的发送邮箱配置信息。');
            }
        }

        return $this->render('request_password_reset_token', [
            'model' => $model,
        ]);
    }

    /**
     * Resets password.
     *
     * @param string $token
     * @return mixed
     * @throws BadRequestHttpException
     */
    public function actionResetPassword($token)
    {
        try {
            $model = new ResetPasswordForm($token);
        } catch (InvalidArgumentException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }

        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->resetPassword()) {
            Yii::$app->session->setFlash('success', 'New password saved.');

            return $this->goHome();
        }

        return $this->render('reset_password', [
            'model' => $model,
        ]);
    }

    /**
     * Verify email address
     *
     * @param string $token
     * @throws BadRequestHttpException
     * @return yii\web\Response
     */
    public function actionVerifyEmail($token)
    {
        try {
            $model = new VerifyEmailForm($token);
        } catch (InvalidArgumentException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }
        if ($user = $model->verifyEmail()) {
            Yii::$app->session->setFlash('success', '已验证您的邮箱');
            return $this->goHome();
        }

        Yii::$app->session->setFlash('error', 'Sorry, we are unable to verify your account with provided token.');
        return $this->goHome();
    }

    /**
     * Resend verification email
     *
     * @return mixed
     */
    public function actionResendVerificationEmail()
    {
        $model = new ResendVerificationEmailForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->sendEmail()) {
                Yii::$app->session->setFlash('success', '检查您的邮箱去获取验证链接');
                return $this->goHome();
            }
            Yii::$app->session->setFlash('error', '验证邮箱发送失败。可能原因：1. 该邮箱不存在；2. 本网站系统邮箱配置信息有误，需联系管理员检查系统的发送邮箱配置信息。');
        }

        return $this->render('resend_verification_email', [
            'model' => $model
        ]);
    }
}
