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

        // 验证是否有权限查看。以下代码中 isShareCode 的说明参见 config\params.php 文件。
        // 当系统允许用户可以查看其他用户的代码时，此时只限制比赛过程中不能被查看。
        if (($model->contest_id != null && $model->status == Solution::STATUS_HIDDEN) ||
            !Yii::$app->params['isShareCode']) {
            // 未登录用户不能查看，不是自己提交的记录且不是管理员的情况下不能查看
            if (Yii::$app->user->isGuest
                || ($model->created_by != Yii::$app->user->id && Yii::$app->user->identity->role != User::ROLE_ADMIN)) {
                throw new ForbiddenHttpException('You are not allowed to perform this action.');
            }
        }
        return $this->render('source', [
            'model' => $model,
        ]);
    }

    /**
     * 提交记录的出错信息。如因 Wrong Answer、Runtime Error、Compile Error 所记录的信息
     * @param integer $id
     * @return mixed
     * @throws ForbiddenHttpException if the model cannot be viewed.
     */
    public function actionResult($id)
    {
        $this->layout = false;
        $model = $this->findModel($id);

        // 验证是否有权限查看。以下代码中 isShareCode 的说明参见 config\params.php 文件。
        // 对于比赛中的提交记录，只允许查看 Compile Error。
        if ($model->contest_id != null && $model->status == Solution::STATUS_HIDDEN ||
            !Yii::$app->params['isShareCode']) {
            $role = true;
            if (!Yii::$app->user->isGuest) {
                $role = Yii::$app->user->identity->role != User::ROLE_ADMIN;
            }
            if (Yii::$app->user->isGuest
                || ($model->created_by != Yii::$app->user->id && $role)
                || ($model->result != Solution::OJ_CE && $role)) {
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

        // 验证是否有权限查看。以下代码中 isShareCode 的说明参见 config\params.php 文件。
        // 对于比赛中的提交记录，只允许 Compile Error 的情况下打开该页面。
        if ($model->contest_id != null && $model->status == Solution::STATUS_HIDDEN) {
            $role = true;
            if (!Yii::$app->user->isGuest) {
                $role = Yii::$app->user->identity->role != User::ROLE_ADMIN;
            }
            if (Yii::$app->user->isGuest
                || ($model->created_by != Yii::$app->user->id && $role)
                || ($model->result != Solution::OJ_CE && $role)) {
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
