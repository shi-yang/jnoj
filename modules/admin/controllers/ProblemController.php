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
                            User::ROLE_ADMIN
                        ],
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
        $dataProvider = new ActiveDataProvider([
            'query' => Problem::find()->orderBy(['id' => SORT_DESC])->with('user'),
            'pagination' => [
                'pageSize' => 50
            ]
        ]);

        if (Yii::$app->request->isPost) {
            $keys = Yii::$app->request->post('keylist');
            $action = Yii::$app->request->get('action');
            foreach ($keys as $key) {
                Yii::$app->db->createCommand()->update('{{%problem}}', [
                    'status' => $action
                ], ['id' => $key])->execute();
            }
            return $this->refresh();
        }

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionDeletefile($id, $name)
    {
        $model = $this->findModel($id);
        @unlink(Yii::$app->params['judgeProblemDataPath'] . $model->id . '/' . $name);
        return $this->redirect(['test-data', 'id' => $model->id]);
    }

    public function actionViewfile($id, $name)
    {
        $model = $this->findModel($id);
        return file_get_contents(Yii::$app->params['judgeProblemDataPath'] . $model->id . '/' . $name);
    }

    /**
     * Displays a single Problem model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        $model->setSamples();

        return $this->render('view', [
            'model' => $model,
        ]);
    }

    /**
     * Import Problem.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionImport()
    {
        $model = new UploadForm();

        if (Yii::$app->request->isPost) {
            $model->problemFile = UploadedFile::getInstance($model, 'problemFile');
            if ($model->upload()) {
                Yii::$app->session->setFlash('success', Yii::t('app', 'Import Successfully'));
            }
            return $this->refresh();
        }

        return $this->render('import', [
            'model' => $model,
        ]);
    }

    /**
     * Create Problem model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Problem();

        // 配置默认的题目要求
        $model->time_limit = 1;
        $model->memory_limit = 128;
        $model->status = $model::STATUS_HIDDEN;
        $model->spj = 0;

        if ($model->load(Yii::$app->request->post())) {
            $sample_input = [$model->sample_input, $model->sample_input_2, $model->sample_input_3];
            $sample_output = [$model->sample_output, $model->sample_output_2, $model->sample_output_3];
            $model->sample_input = serialize($sample_input);
            $model->sample_output = serialize($sample_output);
            $model->created_by = Yii::$app->user->id;
            $model->save();
            mkdir(Yii::$app->params['judgeProblemDataPath'] . $model->id);
            return $this->redirect(['view', 'id' => $model->id]);
        }
        $model->setSamples();

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * 从Polygon系统中同步题目到题库中
     * @return mixed
     */
    public function actionCreateFromPolygon()
    {
        if (Yii::$app->request->isPost) {
            $id = intval(Yii::$app->request->post('polygon_problem_id'));
            $polygonProblem = Yii::$app->db->createCommand('SELECT * FROM {{%polygon_problem}} WHERE id=:id', [':id' => $id])->queryOne();
            if (!empty($polygonProblem)) {
                $in = Yii::$app->db->createCommand('SELECT id FROM {{%problem}} WHERE polygon_problem_id=:id', [':id' => $id])->queryColumn();
                $problem = new Problem();
                if (!empty($in)) {
                    $problem = Problem::findOne(['polygon_problem_id' => $id]);
                    $this->makeDirEmpty(Yii::$app->params['judgeProblemDataPath'] . $problem->id);
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
        return $this->render('create_from_polygon');
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

    public function actionTestData($id)
    {
        $model = $this->findModel($id);
        if (Yii::$app->request->isPost) {
            @move_uploaded_file($_FILES["file"]["tmp_name"], Yii::$app->params['judgeProblemDataPath'] . $model->id . '/' . $_FILES["file"]["name"]);
        }
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
    public function actionSource($id, $solution_id)
    {
        $solution = Yii::$app->db->createCommand('SELECT * FROM {{%solution}} WHERE id=:id', [':id' => intval($solution_id)])->queryOne();
        return $this->render('source', [
            'model' => $this->findModel($id),
            'solution' => $solution
        ]);
    }

    /**
     * Displays result of a single Solution model.
     * @param integer $id
     * @return mixed
     */
    public function actionResult($id, $solution_id)
    {
        $solution = Yii::$app->db->createCommand('SELECT * FROM {{%solution_info}} WHERE solution_id=:id', [':id' => intval($solution_id)])->queryOne();
        return $this->render('result', [
            'model' => $this->findModel($id),
            'solution' => $solution
        ]);
    }

    /**
     * @param $id
     * @return mixed
     */
    public function actionTestStatus($id)
    {
        $this->layout = false;
        $model = $this->findModel($id);
        $solutions = (new Query())->select('id, result, created_at, memory, time, language, code_length')
            ->from('{{%solution}}')
            ->where(['problem_id' => $id, 'status' => 0])
            ->limit(10)
            ->orderBy(['id' => SORT_DESC])
            ->all();
        return $this->render('test_status', [
            'solutions' => $solutions,
            'model' => $model
        ]);
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

    /**
     * 将一个文件夹复制为另一个文件夹
     * @param $src string 源文件夹
     * @param $dst string 目标文件夹
     */
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

    /**
     * 删除文件夹下的所有文件
     * @param $dir string
     */
    protected function makeDirEmpty($dir)
    {
        $dh = opendir($dir);
        while ($file = readdir($dh)) {
            if($file != "." && $file != "..") {
                $fullpath = $dir . "/" . $file;
                if(!is_dir($fullpath)) {
                    @unlink($fullpath);
                } else {
                    $this->makeDirEmpty($fullpath);
                }
            }
        }
        closedir($dh);
    }
}
