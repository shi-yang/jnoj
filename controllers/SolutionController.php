<?php

namespace app\controllers;

use Yii;
use app\models\User;
use app\models\Solution;
use app\models\SolutionSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\ForbiddenHttpException;
use yii\filters\VerbFilter;

/**
 * SolutionController implements the CRUD actions for Solution model.
 */
class SolutionController extends Controller
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
        ];
    }

    /**
     * Lists all Solution models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new SolutionSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays source of a single Solution model.
     * @param integer $id
     * @return mixed
     * @throws ForbiddenHttpException if the model cannot be viewed.
     */
    public function actionSource($id)
    {
        $this->layout = false;
        $model = $this->findModel($id);

        // 验证是否有权限查看
        if ($model->contest_id != null && $model->status == Solution::STATUS_HIDDEN) {
            if (Yii::$app->user->isGuest
                || ($model->user_id != Yii::$app->user->id && Yii::$app->user->identity->role == User::ROLE_USER)) {
                throw new ForbiddenHttpException('You are not allowed to perform this action.');
            }
        }
        return $this->render('source', [
            'model' => $model,
        ]);
    }

    /**
     * Displays result of a single Solution model.
     * @param integer $id
     * @return mixed
     * @throws ForbiddenHttpException if the model cannot be viewed.
     */
    public function actionResult($id)
    {
        $this->layout = false;
        $model = $this->findModel($id);

        // 验证是否有权限查看
        if ($model->contest_id != null && $model->status == Solution::STATUS_HIDDEN) {
            if (Yii::$app->user->isGuest
                || ($model->user_id != Yii::$app->user->id && Yii::$app->user->identity->role == User::ROLE_USER)
                || ($model->result != Solution::OJ_CE && Yii::$app->user->identity->role == User::ROLE_USER)) {
                throw new ForbiddenHttpException('You are not allowed to perform this action.');
            }
        }
        return $this->render('result', [
            'model' => $model,
        ]);
    }

    /**
     * 提交记录的详细信息
     * @param $id
     * @return mixed
     * @throws ForbiddenHttpException
     */
    public function actionDetail($id)
    {
        $this->layout = 'main';
        $model = $this->findModel($id);

        // 验证是否有权限查看
        if ($model->contest_id != null && $model->status == Solution::STATUS_HIDDEN) {
            if (Yii::$app->user->isGuest
                || ($model->user_id != Yii::$app->user->id && Yii::$app->user->identity->role == User::ROLE_USER)
                || ($model->result != Solution::OJ_CE && Yii::$app->user->identity->role == User::ROLE_USER)) {
                throw new ForbiddenHttpException('You are not allowed to perform this action.');
            }
        }
        return $this->render('detail', [
            'model' => $model,
        ]);
    }

    /**
     * Finds the Solution model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Solution the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Solution::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
