<?php

namespace app\modules\admin\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use app\components\AccessRule;
use app\models\User;
use app\modules\admin\models\Rejudge;

/**
 * Default controller for the `admin` module
 */
class RejudgeController extends Controller
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
                'only' => ['index'],
                'rules' => [
                    [
                        'actions' => ['index'],
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
        $rejudge = new Rejudge();
        if ($rejudge->load(Yii::$app->request->post())) {
            $rejudge->run();
            Yii::$app->session->setFlash('success', 'Submit Successfully');
            return $this->refresh();
        }
        return $this->render('index', [
            'rejudge' => $rejudge
        ]);
    }
}
