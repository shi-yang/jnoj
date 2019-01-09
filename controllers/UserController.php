<?php

namespace app\controllers;

use app\models\Contest;
use app\models\UserProfile;
use Yii;
use app\models\User;
use yii\base\Model;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\ForbiddenHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;

/**
 * UserController implements the CRUD actions for User model.
 */
class UserController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['setting'],
                'rules' => [
                    [
                        'actions' => ['setting'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    /**
     * 用户主页
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);

        $contests = Yii::$app->db->createCommand('
                SELECT `cu`.`rating_change`, `cu`.`rank`, `cu`.`contest_id`, `c`.`start_time`, `c`.title
                FROM `contest_user` `cu`
                LEFT JOIN `contest` `c` ON `c`.`id`=`cu`.`contest_id`
                WHERE `cu`.`user_id`=:uid AND `cu`.`rank` IS NOT NULL ORDER BY `c`.`id`
            ', [':uid' => $model->id])->queryAll();

        $totalScore = Contest::RATING_INIT_SCORE;

        foreach ($contests as &$contest) {
            $totalScore += $contest['rating_change'];
            $contest['total'] = $totalScore;
            $contest['url'] = Url::toRoute(['/contest/view', 'id' => $contest['contest_id']]);
            $contest['level'] = $model->getRatingLevel($totalScore);
            $contest['start_time'] = strtotime($contest['start_time']);
        }

        return $this->render('view', [
            'model' => $model,
            'contests' => Json::encode($contests)
        ]);
    }

    /**
     * 设置用户信息
     * @param string $action
     * @return mixed
     * @throws ForbiddenHttpException if the model cannot be viewed
     */
    public function actionSetting($action = 'profile')
    {
        $model = User::findOne(Yii::$app->user->id);
        if ($model->role === User::ROLE_PLAYER) {
            throw new ForbiddenHttpException('You are not allowed to perform this action.');
        }
        switch ($action) {
            case 'account':
                break;
            case 'profile':
                $model->scenario = 'profile';
                break;
            case 'security':
                $model->scenario = 'security';
                break;
            default:
                $action = 'profile';
                break;
        }
        $profile = UserProfile::findOne($model->id);
        if ($model->load(Yii::$app->request->post())) {
            if ($model->validate()) {
                if ($model->scenario == 'security') {
                    $model->setPassword($model->newPassword);
                }
            }
            $model->save();
            if ($profile->load(Yii::$app->request->post())) {
                $profile->save();
            }
            Yii::$app->session->setFlash('success', Yii::t('app', 'Saved successfully'));
            return $this->refresh();
        }

        return $this->render('setting', [
            'model' => $model,
            'action' => $action,
            'profile' => $profile
        ]);
    }

    /**
     * Finds the User model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return User the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (is_numeric($id)) {
            $model = User::findOne($id);
        } else {
            $model = User::find()->where(['username' => $id])->one();
        }

        if ($model !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
