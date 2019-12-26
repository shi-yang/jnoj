<?php

namespace app\components;

use Yii;
use yii\swiftmailer\Mailer as SwiftMailer;

class Mailer extends SwiftMailer
{
    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function init()
    {
        parent::init();
        $this->setTransport([
            'class' => 'Swift_SmtpTransport',
            'host' => Yii::$app->setting->get('emailHost'),
            'username' => Yii::$app->setting->get('emailUsername'),
            'password' => Yii::$app->setting->get('emailPassword'),
            'port' => Yii::$app->setting->get('emailPort'),
            'encryption' => Yii::$app->setting->get('emailEncryption')
        ]);
    }
}
