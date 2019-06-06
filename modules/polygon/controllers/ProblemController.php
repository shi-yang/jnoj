<?php

namespace app\modules\polygon\controllers;

use app\models\Solution;
use app\modules\polygon\models\PolygonStatus;
use app\modules\polygon\models\ProblemSearch;
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
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
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
        $searchModel = new ProblemSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel
        ]);
    }

    /**
     * 显示某个解决方案的详细信息
     * @param $id
     * @param $sid
     */
    public function actionSolutionDetail($id, $sid)
    {
        $model = $this->findModel($id);
        $status = PolygonStatus::findOne(['id' => $sid, 'problem_id' => $id]);
        if ($status === null) {
            throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
        }

        return $this->render('detail', [
            'model' => $model,
            'status' => $status
        ]);
    }

    /**
     * 题解
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionAnswer($id)
    {
        $model = $this->findModel($id);
        if ($model->load(Yii::$app->request->post())) {
            $model->save();
            return $this->refresh();
        }
        return $this->render('answer', [
            'model' => $model,
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
        $model = $this->findModel($id);
        $model->setSamples();
        return $this->render('view', [
            'model' => $model,
        ]);
    }

    public function actionRun($id)
    {
        $model = $this->findModel($id);
        if ($model->solution_lang === null || empty($model->solution_source)) {
            Yii::$app->session->setFlash('error', '请提供解决方案');
            return $this->redirect(['tests', 'id' => $id]);
        }
        Yii::$app->db->createCommand()->delete('{{%polygon_status}}',
            'problem_id=:pid AND source IS NULL', [':pid' => $model->id])->execute();
        Yii::$app->db->createCommand()->insert('{{%polygon_status}}', [
            'problem_id' => $model->id,
            'created_at' => new Expression('NOW()'),
            'created_by' => Yii::$app->user->id
        ])->execute();
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

        if ($model->load(Yii::$app->request->post())) {
            $model->spj_lang = Solution::CPPLANG;
            $model->save();
            $dataPath = Yii::$app->params['polygonProblemDataPath'] . $model->id;
            if (!is_dir($dataPath)) {
                @mkdir($dataPath);
            }
            $fp = fopen($dataPath . '/spj.cc',"w");
            fputs($fp, $model->spj_source);
            fclose($fp);
            exec("g++ -fno-asm -std=c++11 -O2 {$dataPath}/spj.cc -o {$dataPath}/spj -I" . Yii::getAlias('@app/libraries'));
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
        if ($model->solution_lang == null) {
            $model->solution_lang = Yii::$app->user->identity->language;
        }
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['solution', 'id' => $model->id]);
        }
        return $this->render('solution', [
            'model' => $model,
        ]);
    }

    /**
     * 验题页面
     * @param integer $id
     * @return mixed
     */
    public function actionVerify($id)
    {
        $model = $this->findModel($id);
        $solution = new PolygonStatus();
        $dataProvider = new ActiveDataProvider([
            'query' => PolygonStatus::find()->where('problem_id=:pid AND source IS NOT NULL', [':pid' => $id]),
            'pagination' => [
                'pageSize' => 10
            ]
        ]);

        if ($solution->load(Yii::$app->request->post())) {
            $solution->problem_id = $id;
            $solution->created_by = Yii::$app->user->id;
            $solution->created_at = new Expression('NOW()');
            $solution->save();
            return $this->refresh();
        }
        return $this->render('verify', [
            'model' => $model,
            'solution' => $solution,
            'dataProvider' => $dataProvider
        ]);
    }

    public function actionTests($id)
    {
        $model = $this->findModel($id);
        $solutionStatus = Yii::$app->db->createCommand("SELECT * FROM {{%polygon_status}} WHERE problem_id=:pid AND language IS NULL", [
            ':pid' => $model->id
        ])->queryOne();
        if (Yii::$app->request->isPost) {
            $inputFile = file_get_contents($_FILES["file"]["tmp_name"]);
            file_put_contents($_FILES["file"]["tmp_name"], preg_replace("(\r\n)","\n", $inputFile));
            @move_uploaded_file($_FILES["file"]["tmp_name"], Yii::$app->params['polygonProblemDataPath'] . $model->id . '/' . $_FILES["file"]["name"]);
        }
        return $this->render('tests', [
            'model' => $model,
            'solutionStatus' => $solutionStatus
        ]);
    }


    /**
     * 下载测试数据
     */
    public function actionDownloadData($id)
    {
        $model = $this->findModel($id);
        $filename = Yii::$app->params['polygonProblemDataPath'] . $model->id;
        $zipName = '/tmp/' . time() . $id . '.zip';
        if (!file_exists($filename)) {
            return false;
        }
        $zipArc = new \ZipArchive();
        if (!$zipArc->open($zipName, \ZipArchive::CREATE)) {
            return false;
        }
        $res = $zipArc->addGlob("{$filename}/*", GLOB_BRACE, ['remove_all_path' => true]);
        $zipArc->close();
        if (!$res) {
            return false;
        }
        if (!file_exists($zipName)) {
            return false;
        }
        Yii::$app->response->on(\yii\web\Response::EVENT_AFTER_SEND, function($event) { unlink($event->data); }, $zipName);
        return Yii::$app->response->sendFile($zipName, $model->id . '-' . $model->title . '.zip');
    }

    public function actionDeletefile($id, $name)
    {
        $model = $this->findModel($id);
        if ($name == 'in') {
            $files = $model->getDataFiles();
            foreach ($files as $file) {
                if (strpos($file['name'], '.in')) {
                    @unlink(Yii::$app->params['polygonProblemDataPath'] . $model->id . '/' . $file['name']);
                }
            }
        } else if ($name == 'out') {
            $files = $model->getDataFiles();
            foreach ($files as $file) {
                if (strpos($file['name'], '.out')) {
                    @unlink(Yii::$app->params['polygonProblemDataPath'] . $model->id . '/' . $file['name']);
                }
                if (strpos($file['name'], '.ans')) {
                    @unlink(Yii::$app->params['polygonProblemDataPath'] . $model->id . '/' . $file['name']);
                }
            }
        } else {
            @unlink(Yii::$app->params['polygonProblemDataPath'] . $model->id . '/' . $name);
        }
        return $this->redirect(['tests', 'id' => $model->id]);
    }

    public function actionViewfile($id, $name)
    {
        $model = $this->findModel($id);
        echo '<pre>';
        echo file_get_contents(Yii::$app->params['polygonProblemDataPath'] . $model->id . '/' . $name);
        echo '</pre>';
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

        if ($model->load(Yii::$app->request->post())) {
            $sample_input = [$model->sample_input, $model->sample_input_2, $model->sample_input_3];
            $sample_output = [$model->sample_output, $model->sample_output_2, $model->sample_output_3];
            $model->sample_input = serialize($sample_input);
            $model->sample_output = serialize($sample_output);
            $model->save();
            return $this->redirect(['view', 'id' => $model->id]);
        }

        $model->setSamples();

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    public function actionSubtask($id)
    {
        $model = $this->findModel($id);

        $dataPath = Yii::$app->params['polygonProblemDataPath'] . $model->id;
        $subtaskContent = '';

        if (file_exists($dataPath . '/config')) {
            $subtaskContent = file_get_contents($dataPath . '/config');
        }
        if (Yii::$app->request->isPost) {
            $spjContent = Yii::$app->request->post('subtaskContent');
            if (!is_dir($dataPath)) {
                mkdir($dataPath);
            }
            $fp = fopen($dataPath . '/config',"w");
            fputs($fp, $spjContent);
            fclose($fp);
        }
        return $this->render('subtask', [
            'model' => $model,
            'subtaskContent' => $subtaskContent
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
            if (Yii::$app->user->id === $model->created_by ||
                Yii::$app->user->identity->role === User::ROLE_ADMIN) {
                return $model;
            } else {
                throw new ForbiddenHttpException('You are not allowed to perform this action.');
            }
        }

        throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
    }
}
