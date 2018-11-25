<?php

namespace app\controllers;

use app\models\User;
use app\models\Contest;
use app\models\ContestPrint;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\ForbiddenHttpException;
use yii\filters\VerbFilter;

/**
 * PrintController implements the CRUD actions for PrintSource model.
 */
class PrintController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['index', 'view', 'create', 'delete', 'update'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all PrintSource models.
     * @throws NotFoundHttpException if the contest cannot be found
     * @return mixed
     */
    public function actionIndex($id)
    {
        $contest = Contest::findOne($id);
        if ($contest === null || $contest->scenario != Contest::SCENARIO_OFFLINE) {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
        if (Yii::$app->user->identity->role == User::ROLE_ADMIN) {
            $query = ContestPrint::find()->where(['contest_id' => $contest->id])->with('user');
        } else {
            $query = ContestPrint::find()->where(['contest_id' => $contest->id, 'user_id' => Yii::$app->user->id])->with('user');
        }
        $dataProvider = new ActiveDataProvider([
            'query' => $query->orderBy('id DESC'),
        ]);

        return $this->render('index', [
            'contest' => $contest,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Contest Print model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException
     * @throws ForbiddenHttpException
     * @throws \Exception|\Throwable
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);

        if (Yii::$app->user->identity->role == User::ROLE_ADMIN) {
            $model->status = ContestPrint::STATUS_HAVE_READ;
            $model->update();
        }

        return $this->render('view', [
            'model' => $model,
        ]);
    }

    /**
     * Creates a new PrintSource model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @param integer $id Contest Id
     * @throws NotFoundHttpException if the contest cannot be found
     * @return mixed
     */
    public function actionCreate($id)
    {
        $model = new ContestPrint();
        $contest = Contest::findOne($id);
        if ($contest === null || $contest->scenario != Contest::SCENARIO_OFFLINE) {
            throw new NotFoundHttpException('The requested page does not exist.');
        }

        if ($model->load(Yii::$app->request->post())) {
            $model->contest_id = $contest->id;
            $model->save();
            Yii::$app->session->setFlash('success', 'Submit Successfully');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'contest' => $contest,
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing PrintSource model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing PrintSource model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        $cid = $model->contest_id;
        $model->delete();

        return $this->redirect(['index', 'id' => $cid]);
    }

    /**
     * Finds the PrintSource model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return ContestPrint the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     * @throws ForbiddenHttpException if the model cannot be viewed
     */
    protected function findModel($id)
    {
        if (($model = ContestPrint::findOne($id)) !== null || !Yii::$app->user->isGuest) {
            if ($model->user_id == Yii::$app->user->id || Yii::$app->user->identity->role == User::ROLE_ADMIN) {
                return $model;
            }
            throw new ForbiddenHttpException('You are not allowed to perform this action.');
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
