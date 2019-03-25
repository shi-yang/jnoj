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
     * 返回提交状态供 AJAX 查询
     * @param $id
     * @return false|string
     * @throws NotFoundHttpException
     */
    public function actionVerdict($id)
    {
        $query = Yii::$app->db->createCommand('SELECT id,result,contest_id FROM {{%solution}} WHERE id=:id', [
            ':id' => $id
        ])->queryOne();
        if ($query['contest_id'] != NULL) {
            $query['result'] = 0;
        }
        $res = [
            'id' => $query['id'],
            'verdict' => $query['result'],
            'waiting' => $query['result'] <= Solution::OJ_WAITING_STATUS ? 'true' : 'false',
            'result' => Solution::getResultList($query['result'])
        ];
        return json_encode($res);
    }

    /**
     * Displays source of a single Solution model.
     * @param integer $id
     * @return mixed
     * @throws ForbiddenHttpException if the model cannot be viewed.
     * @throws NotFoundHttpException
     */
    public function actionSource($id)
    {
        $this->layout = false;
        $model = $this->findModel($id);
        if ($model->canViewSource()) {
            return $this->render('source', [
                'model' => $model,
            ]);
        }

        throw new ForbiddenHttpException('You are not allowed to perform this action.');
    }

    /**
     * 提交记录的出错信息。如因 Wrong Answer、Runtime Error、Compile Error 所记录的信息
     * @param integer $id
     * @return mixed
     * @throws ForbiddenHttpException if the model cannot be viewed.
     * @throws NotFoundHttpException
     */
    public function actionResult($id)
    {
        $this->layout = false;
        $model = $this->findModel($id);
        if ($model->canViewErrorInfo()) {
            return $this->render('result', [
                'model' => $model,
            ]);
        }

        throw new ForbiddenHttpException('You are not allowed to perform this action.');
    }

    /**
     * 提交记录的详细信息
     * @param $id
     * @return mixed
     * @throws NotFoundHttpException
     */
    public function actionDetail($id)
    {
        $this->layout = 'main';
        $model = $this->findModel($id);

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
