<?php

namespace app\modules\admin\controllers;

use Yii;
use yii\data\ActiveDataProvider;
use yii\db\Query;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\UploadedFile;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use app\components\AccessRule;
use app\models\User;
use app\models\Problem;
use app\models\Solution;
use app\modules\admin\models\UploadForm;

/**
 * ProblemController implements the CRUD actions for Problem model.
 */
class ProblemController extends Controller
{
    public $layout = 'main';
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
                'ruleConfig' => [
                    'class' => AccessRule::className(),
                ],
                'only' => ['index', 'create', 'update', 'delete'],
                'rules' => [
                    [
                        'actions' => ['index', 'view', 'create', 'update', 'testData', 'source', 'result', 'delete', 'img_upload'],
                        'allow' => true,
                        // Allow users, moderators and admins to create
                        'roles' => [
                            User::ROLE_MODERATOR,
                            User::ROLE_ADMIN
                        ],
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
        $dataProvider = new ActiveDataProvider([
            'query' => Problem::find()->orderBy(['id' => SORT_DESC]),
            'pagination' => [
                'pageSize' => 50
            ]
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Problem model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        return $this->render('view', [
            'model' => $model,
        ]);
    }

    /**
     * Creates a new Problem model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        if (Yii::$app->request->isPost) {
            $id = intval(Yii::$app->request->post('polygon_problem_id'));
            $polygonProblem = Yii::$app->db->createCommand('SELECT * FROM {{%polygon_problem}} WHERE id=:id', [':id' => $id])->queryOne();
            if (!empty($polygonProblem)) {
                $in = Yii::$app->db->createCommand('SELECT id FROM {{%problem}} WHERE polygon_problem_id=:id', [':id' => $id])->queryColumn();
                $problem = new Problem();
                if (!empty($in)) {
                    $problem = Problem::findOne(['polygon_problem_id' => $id]);
                }
                $problem->title = $polygonProblem['title'];
                $problem->description = $polygonProblem['description'];
                $problem->input = $polygonProblem['input'];
                $problem->output = $polygonProblem['output'];
                $problem->sample_input = $polygonProblem['sample_input'];
                $problem->sample_output = $polygonProblem['sample_output'];
                $problem->spj = $polygonProblem['spj'];
                $problem->hint = $polygonProblem['hint'];
                $problem->memory_limit = $polygonProblem['memory_limit'];
                $problem->time_limit = $polygonProblem['time_limit'];
                $problem->created_by = $polygonProblem['created_by'];
                $problem->tags = $polygonProblem['tags'];
                $problem->status = Problem::STATUS_HIDDEN;
                $problem->polygon_problem_id = $id;
                $problem->save();

                $this->copyDir(Yii::$app->params['polygonProblemDataPath'] . $polygonProblem['id'], Yii::$app->params['judgeProblemDataPath'] . $problem->id);
                Yii::$app->session->setFlash('success', Yii::t('app', 'Create Successfully'));
                return $this->redirect(['view', 'id' => $problem->id]);
            } else {
                Yii::$app->session->setFlash('error', Yii::t('app', 'No such problem.'));
            }
        }
        return $this->render('create');
    }

    /**
     * Updates an existing Problem model.
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
        $model->setSamples();

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    public function actionTestData($id)
    {
        $model = $this->findModel($id);
        //$this->layout = false;

        return $this->render('test_data', [
            'model' => $model
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
        if (!Yii::$app->request->isAjax) {
            throw new ForbiddenHttpException('You are not allowed to perform this action.');
        }
        $this->layout = false;
        return $this->render('source', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Displays result of a single Solution model.
     * @param integer $id
     * @return mixed
     */
    public function actionResult($id)
    {
        $this->layout = false;
        return $this->render('result', [
            'model' => $this->findModel($id),
        ]);
    }

    public function actionTestUpload($id)
    {
        $model = $this->findModel($id);
        $this->layout = false;

        $upload = new UploadForm();

        if (Yii::$app->request->isPost) {
            $upload->file = UploadedFile::getInstances($model, 'file');

            if ($upload->file && $upload->validate()) {
                $ok = false;
                foreach ($upload->file as $file) {
                    $ok = $file->saveAs(Yii::$app->setting->problem_data_path . $file->baseName . '.' . $file->extension);
                }
                if ($ok) {
                    Yii::$app->session->setFlash('success', 'Submit Successfully');
                } else {
                    Yii::$app->session->setFlash('error', 'Something error');
                }
            }
            return $this->refresh();
        }

        return $this->render('test_upload', [
            'model' => $model,
            'upload' => $upload
        ]);
    }

    /**
     * @param $id
     * @return mixed
     * @throws ForbiddenHttpException if the model cannot be viewed
     */
    public function actionTestStatus($id)
    {
        if (Yii::$app->request->isAjax) {
            $this->layout = false;
            $model = $this->findModel($id);
            $solutions = (new Query())->select('solution_id, result, created_at, memory, time, language, code_length')
                ->from('{{%solution}}')
                ->where(['problem_id' => $id, 'status' => 0])
                ->limit(10)
                ->orderBy(['solution_id' => SORT_DESC])
                ->all();
            return $this->render('test_status', [
                'solutions' => $solutions,
                'model' => $model
            ]);
        }

        throw new ForbiddenHttpException('You are not allowed to perform this action.');
    }

    /**
     * @param $id
     * @return mixed
     * @throws ForbiddenHttpException if the model cannot be viewed
     */
    public function actionTestSubmit($id)
    {
        $solution = new Solution();

        if ($solution->load(Yii::$app->request->post())) {
            $solution->problem_id = $id;
            $solution->status = 0;
            if ($solution->save()) {
                Yii::$app->session->setFlash('success', 'Submit Successfully');
            } else {
                Yii::$app->session->setFlash('error', 'Please select a language');
            }
            return $this->redirect('index');
        }

        if (Yii::$app->request->isAjax) {
            $this->layout = false;
            $model = $this->findModel($id);

            return $this->render('test_submit', [
                'solution' => $solution,
                'model' => $model
            ]);
        }

        throw new ForbiddenHttpException('You are not allowed to perform this action.');
    }

    /**
     * Deletes an existing Problem model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
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
     */
    protected function findModel($id)
    {
        if (($model = Problem::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    protected function copyDir($src, $dst)
    {
        $dir = opendir($src);
        @mkdir($dst);
        while ( false !== ($file = readdir($dir))) {
            if (($file != '.') && ($file != '..')) {
                copy($src . '/' . $file, $dst . '/' . $file);
            }
        }
        closedir($dir);
    }
}
