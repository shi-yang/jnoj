<?php

namespace app\modules\admin\controllers;

use Yii;
use yii\web\Controller;
use yii\helpers\ArrayHelper;
use yii\filters\AccessControl;
use app\components\AccessRule;
use app\models\User;
use app\modules\admin\models\SettingForm;

/**
 * Default controller for the `admin` module
 */
class SettingController extends Controller
{
    public $layout = 'main';

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'ruleConfig' => [
                    'class' => AccessRule::className(),
                ],
                'rules' => [
                    [
                        'allow' => true,
                        // Allow users, moderators and admins to create
                        'roles' => [
                            User::ROLE_ADMIN
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Renders the index view for the module
     * @return string
     */
    public function actionIndex()
    {
        $setting_date = Yii::$app->db->createCommand('SELECT * FROM {{%setting}}')->queryAll();
        $settings = ArrayHelper::map($setting_date, 'key', 'value');

        if (($post = Yii::$app->request->post())) {
            unset($post['_csrf']);
            if (!is_writable($post['problem_data_path'])) {
                Yii::$app->session->setFlash('error', Yii::t('app', 'Path can not write'));
                return $this->refresh();
            }
            Yii::$app->setting->set($post);
            Yii::$app->session->setFlash('success', Yii::t('app', 'Submitted successfully'));
            return $this->refresh();
        }

        return $this->render('index', [
            'settings' => $settings,
        ]);
    }
}
