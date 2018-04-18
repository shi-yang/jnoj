<?php

namespace app\modules\polygon\controllers;

use Yii;
use app\models\User;
use app\modules\polygon\models\Problem;
use yii\data\ActiveDataProvider;
use yii\db\Expression;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\web\ForbiddenHttpException;

/**
 * ProblemController implements the CRUD actions for Problem model.
 */
class ProblemController extends Controller
{
    public $enableCsrfValidation = false;
    public $layout = 'problem';
    /**
     * {@inheritdoc}
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
                'rules' => [
                    [
                        'actions' => ['index', 'view', 'create', 'delete', 'update', 'solution', 'tests', 'spj',
                                      'img_upload', 'run', 'deletefile', 'viewfile'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    public function actions()
    {
        return [
            'img_upload' => [
                'class' => 'app\widgets\editormd\EditormdAction',
            ],
        ];
    }

    /**
     * Lists all Problem models.
     * @return mixed
     */
    public function actionIndex()
    {
        $this->layout = '/main';
        $dataProvider = new ActiveDataProvider([
            'query' => Problem::find()->where(['created_by' => Yii::$app->user->id]),
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Problem model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    public function actionRun($id)
    {
        $model = $this->findModel($id);
        if ($model->solution_lang === null || empty($model->solution_source)) {
            Yii::$app->session->setFlash('error', '请提供解决方案');
            return $this->redirect(['tests', 'id' => $id]);
        }
        Yii::$app->db->createCommand()->delete('{{%polygon_status}}', ['problem_id' => $model->id])->execute();
        Yii::$app->db->createCommand()->insert('{{%polygon_status}}', ['problem_id' => $model->id, 'created_at' => new Expression('NOW()')])->execute();
        return $this->redirect(['tests', 'id' => $id]);
    }

    /**
     * Displays a single Problem model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionSpj($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['spj', 'id' => $model->id]);
        }
        return $this->render('spj', [
            'model' => $model,
        ]);
    }

    /**
     * Displays a single Problem model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionSolution($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['solution', 'id' => $model->id]);
        }
        return $this->render('solution', [
            'model' => $model,
        ]);
    }

    public function actionTests($id)
    {
        $model = $this->findModel($id);
        $solutionStatus = Yii::$app->db->createCommand("SELECT * FROM {{%polygon_status}} WHERE problem_id=:pid", [':pid' => $model->id])->queryOne();
        if (Yii::$app->request->isPost) {
            @move_uploaded_file($_FILES["file"]["tmp_name"], Yii::$app->params['polygonProblemDataPath'] . $model->id . '/' . $_FILES["file"]["name"]);
        }
        return $this->render('tests', [
            'model' => $model,
            'solutionStatus' => $solutionStatus
        ]);
    }

    public function actionDeletefile($id, $name)
    {
        $model = $this->findModel($id);
        @unlink(Yii::$app->params['polygonProblemDataPath'] . $model->id . '/' . $name);
        return $this->redirect(['tests', 'id' => $model->id]);
    }

    public function actionViewfile($id, $name)
    {
        $model = $this->findModel($id);
        return file_get_contents(Yii::$app->params['polygonProblemDataPath'] . $model->id . '/' . $name);
    }

    /**
     * Creates a new Problem model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $this->layout = '/main';
        $model = new Problem();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            @mkdir(Yii::$app->params['polygonProblemDataPath'] . $model->id);
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Problem model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        $model->setSamples();

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Problem model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the Problem model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Problem the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     * @throws ForbiddenHttpException if the model cannot be viewed
     */
    protected function findModel($id)
    {
        if (($model = Problem::findOne($id)) !== null) {
            if (Yii::$app->user->id === $model->created_by || (Yii::$app->user->identity->role === User::ROLE_MODERATOR ||
                    Yii::$app->user->identity->role === User::ROLE_ADMIN)) {
                return $model;
            } else {
                throw new ForbiddenHttpException('You are not allowed to perform this action.');
            }
        }

        throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
    }
}
