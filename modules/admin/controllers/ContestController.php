<?php

namespace app\modules\admin\controllers;

use Yii;
use yii\db\Query;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\db\Expression;
use app\components\AccessRule;
use app\models\ContestAnnouncement;
use app\models\User;
use app\models\ContestUser;
use app\models\Problem;
use app\models\Discuss;
use app\models\Contest;
use app\models\SolutionSearch;
use app\models\ContestPrint;
use app\models\ContestProblem;
use app\models\Solution;
use app\modules\admin\models\GenerateUserForm;

/**
 * ContestController implements the CRUD actions for Contest model.
 */
class ContestController extends Controller
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
                    'delete' => ['post'],
                ],
            ],
            'access' => [
                'class' => AccessControl::className(),
                'ruleConfig' => [
                    'class' => AccessRule::className(),
                ],
                //'only' => ['index', 'create', 'update', 'delete'],
                'rules' => [
                    [
                        'actions' => ['index', 'board', 'rank', 'print', 'status', 'view', 'create', 'update',
                            'clarify', 'newproblem', 'addproblem', 'deleteproblem', 'updateproblem', 'register',
                            'editorial', 'delete', 'printuser', 'rated'
                        ],
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

    /**
     * Lists all Contest models.
     * @return mixed
     */
    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => Contest::find()->orderBy(['id' => SORT_DESC]),
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionUpdateproblem($id)
    {
        $model = $this->findModel($id);

        if (($post = Yii::$app->request->post())) {
            $pid = intval($post['problem_id']);
            $new_pid = intval($post['new_problem_id']);
            $has_problem1 = (new Query())->select('problem_id')
                ->from('{{%problem}}')
                ->where('problem_id=:id', [':id' => $pid])
                ->exists();
            $has_problem2 = (new Query())->select('problem_id')
                ->from('{{%problem}}')
                ->where('problem_id=:id', [':id' => $pid])
                ->exists();
            if ($has_problem1 && $has_problem2) {
                $problem_in_contest = (new Query())->select('problem_id')
                    ->from('{{%contest_problem}}')
                    ->where(['problem_id' => $new_pid, 'contest_id' => $model->id])
                    ->exists();
                if ($problem_in_contest) {
                    Yii::$app->session->setFlash('info', Yii::t('app', 'This problem has in the contest.'));
                    return $this->refresh();
                }

                Yii::$app->db->createCommand()->update('{{%contest_problem}}', [
                    'problem_id' => $new_pid,
                ], ['problem_id' => $pid, 'contest_id' => $model->contest_id])->execute();
                Yii::$app->session->setFlash('success', Yii::t('app', 'Submit successfully'));
            } else {
                Yii::$app->session->setFlash('error', Yii::t('app', 'No such problem.'));
            }
            return $this->redirect(['contest/view', 'id' => $id]);
        }
    }

    /**
     * 比赛积分计算
     * @param $id
     */
    public function actionRated($id)
    {
        $model = $this->findModel($id);
        if (Yii::$app->request->get('cal')) {
            $model->calRating();
            Yii::$app->session->setFlash('success', Yii::t('app', 'Done'));
            return $this->redirect(['rated', 'id' => $model->id]);
        }
        $dataProvider = new ActiveDataProvider([
            'query' => ContestUser::find()
                ->where(['contest_id' => $model->id])
                ->with('user')
                ->orderBy(['rating_change' => SORT_DESC]),
        ]);
        return $this->render('rated', [
            'model' => $model,
            'dataProvider' => $dataProvider
        ]);
    }

    /**
     * 比赛的题解
     * @param integer $id
     * @return mixed
     */
    public function actionEditorial($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['editorial', 'id' => $model->id]);
        }

        return $this->render('editorial', [
            'model' => $model
        ]);
    }

    /**
     * 打印参加比赛的用户及用户密码
     * @param $id
     * @return string
     */
    public function actionPrintuser($id)
    {
        $model = $this->findModel($id);
        $this->layout = false;
        $users = ContestUser::findAll(['contest_id' => $model->id]);
        return $this->render('printuser', [
            'users' => $users
        ]);
    }

    /**
     * 显示已经参加比赛的用户
     * @param $id
     * @return string|\yii\web\Response
     */
    public function actionRegister($id)
    {
        $model = $this->findModel($id);
        $generatorForm = new GenerateUserForm();

        if ($generatorForm->load(Yii::$app->request->post())) {
            $generatorForm->contest_id = $model->id;
            $generatorForm->prefix = 't' . $model->id;
            $generatorForm->save();
            return $this->refresh();
        }
        if (Yii::$app->request->isPost) {
            if (Yii::$app->request->get('uid')) {
                $uid = Yii::$app->request->get('uid');
                $in_contest = Yii::$app->db->createCommand('SELECT count(1) FROM {{%contest_user}} WHERE user_id=:uid AND contest_id=:cid', [
                    ':uid' => $uid,
                    ':cid' => $model->id
                ])->queryScalar();
                if ($in_contest) {
                    ContestUser::findOne(['user_id' => $uid, 'contest_id' => $model->id])->delete();
                    Yii::$app->session->setFlash('success', Yii::t('app', 'Deleted successfully'));
                }
            } else {
                $post = Yii::$app->request->post();
                $user = User::findByUsername($post['user']);
                if ($user === null) {
                    Yii::$app->session->setFlash('error', Yii::t('app', 'Failed. No such user.'));
                } else {
                    $in_contest = Yii::$app->db->createCommand('SELECT count(1) FROM {{%contest_user}} WHERE user_id=:uid AND contest_id=:cid', [
                        ':uid' => $user->id,
                        ':cid' => $model->id
                    ])->queryScalar();
                    if ($in_contest) {
                        Yii::$app->session->setFlash('success', Yii::t('app', 'This user has registered for the contest.'));
                    } else {
                        Yii::$app->db->createCommand()->insert('{{%contest_user}}', [
                            'user_id' => $user->id,
                            'contest_id' => $model->id,
                        ])->execute();
                        Yii::$app->session->setFlash('success', Yii::t('app', 'Add successfully'));
                    }
                }
            }
            return $this->refresh();
        }

        $dataProvider = new ActiveDataProvider([
            'query' => ContestUser::find()->where(['contest_id' => $model->id])->with('user')->with('contest'),
            'pagination' => [
                'pageSize' => 100
            ]
        ]);
        return $this->render('register', [
            'model' => $model,
            'dataProvider' => $dataProvider,
            'generatorForm' => $generatorForm
        ]);
    }

    public function actionAddproblem($id)
    {
        $model = $this->findModel($id);

        if (($post = Yii::$app->request->post())) {
            $pid = intval($post['problem_id']);
            $has_problem = (new Query())->select('id')
                ->from('{{%problem}}')
                ->where('id=:id', [':id' => $pid])
                ->exists();
            if ($has_problem) {
                $problem_in_contest = (new Query())->select('problem_id')
                    ->from('{{%contest_problem}}')
                    ->where(['problem_id' => $pid, 'contest_id' => $model->id])
                    ->exists();
                if ($problem_in_contest) {
                    Yii::$app->session->setFlash('info', Yii::t('app', 'This problem has in the contest.'));
                    return $this->redirect(['contest/view', 'id' => $id]);
                }
                $count = (new Query())->select('contest_id')
                    ->from('{{%contest_problem}}')
                    ->where(['contest_id' => $model->id])
                    ->count();

                Yii::$app->db->createCommand()->insert('{{%contest_problem}}', [
                    'problem_id' => $pid,
                    'contest_id' => $model->id,
                    'num' => $count
                ])->execute();
                Yii::$app->session->setFlash('success', Yii::t('app', 'Submit successfully'));
            } else {
                Yii::$app->session->setFlash('error', Yii::t('app', 'No such problem.'));
            }
            return $this->redirect(['contest/view', 'id' => $id]);
        }
    }

    /**
     * 滚榜
     * @param $id
     * @param bool $json
     * @return string
     */
    public function actionBoard($id, $json = false)
    {
        $model = $this->findModel($id);

        $this->layout = 'basic';

        if ($json) {
            $data = (new Query())->select('s.id, u.username, u.nickname, s.result, s.created_at, p.num')
                ->from('{{%solution}} as s')
                ->leftJoin('{{%user}} as u', 'u.id=s.created_by')
                ->leftJoin('{{%contest_problem}} as p', 'p.problem_id=s.problem_id')
                ->where(['s.contest_id' => $model->id])
                ->all();

            foreach ($data as &$v) {
                $v['submitId'] = $v['id'];
                $v['subTime'] = $v['created_at'];
                $v['alphabetId'] = chr(65 + $v['num']);
                $v['resultId'] = $v['result'];
                unset($v['id']);
                unset($v['created_at']);
                unset($v['num']);
                unset($v['result']);
            }

            return json_encode(['total' => count($data), 'data' => $data]);
        }

        return $this->render('board', [
            'model' => $model
        ]);
    }

    /**
     * 显示榜单
     * @param integer $id 比赛 ID
     * @param integer $who 显示方式： 为 0 时只显示用户名，为 1 时只显示昵称， 为 2 时显示用户名跟昵称
     * @return string
     */
    public function actionRank($id, $who = 0)
    {
        $model = $this->findModel($id);

        $this->layout = 'basic';

        return $this->render('rank', [
            'model' => $model,
            'who' => $who
        ]);
    }

    public function actionPrint($id)
    {
        $model = $this->findModel($id);

        $this->layout = 'basic';

        $problems = (new Query())->select('p.title, p.description, p.input, p.output, p.sample_input,
                                          p.sample_output, p.hint, c.num, p.time_limit, p.memory_limit')
            ->from('{{%problem}} as p')
            ->leftJoin('{{%contest_problem}} as c', ['c.contest_id' => $model->id])
            ->where('p.id=c.problem_id')
            ->orderBy('c.num ASC')
            ->all();

        return $this->render('print', [
            'model' => $model,
            'problems' => $problems
        ]);
    }

    /**
     * 显示该比赛的所有提交记录
     * @param integer $id
     * @param integer $active 该值等于 0 就什么也不做，等于 1 就将所有提交记录显示在前台的提交记录列表，等于 2 就隐藏提交记录
     * @return mixed
     */
    public function actionStatus($id, $active = 0)
    {
        $this->layout = 'basic';
        $model = $this->findModel($id);
        $searchModel = new SolutionSearch();

        if ($active == 1) {
            Solution::updateAll(['status' => Solution::STATUS_VISIBLE], ['contest_id' => $model->id]);
            $this->redirect(['status', 'id' => $id]);
        } else if ($active == 2) {
            Solution::updateAll(['status' => Solution::STATUS_HIDDEN], ['contest_id' => $model->id]);
            $this->redirect(['status', 'id' => $id]);
        }

        return $this->render('status', [
            'model' => $model,
            'searchModel' => $searchModel,
            'dataProvider' => $searchModel->search(Yii::$app->request->queryParams, $model->id)
        ]);
    }

    /**
     * Displays a single Contest model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);

        $announcements = new ActiveDataProvider([
            'query' => ContestAnnouncement::find()->where(['contest_id' => $model->id])
        ]);

        $newAnnouncement = new ContestAnnouncement();
        if ($newAnnouncement->load(Yii::$app->request->post())) {
            $newAnnouncement->contest_id = $model->id;
            $newAnnouncement->save();
            try {
                // 给前台所有用户提醒
                $client = stream_socket_client('tcp://0.0.0.0:2121', $errno, $errmsg, 1);
                fwrite($client, json_encode(['content' => Yii::t('app', 'New announcement: ') . $newAnnouncement->content])."\n");
            } catch (\Exception $e) {
                echo 'Caught exception: ',  $e->getMessage(), "\n";
            }
            Yii::$app->session->setFlash('success', Yii::t('app', 'Save successfully'));
            return $this->refresh();
        }

        return $this->render('view', [
            'model' => $model,
            'announcements' => $announcements,
            'newAnnouncement' => $newAnnouncement
        ]);
    }

    /**
     * Creates a new Contest model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Contest();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Contest model.
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

    public function actionClarify($id, $cid = -1)
    {
        $model = $this->findModel($id);
        $new_clarify = new Discuss();
        $discuss = null;

        if ($cid != -1) {
            if (($discuss = Discuss::findOne(['id' => $cid, 'entity_id' => $model->id, 'entity' => Discuss::ENTITY_CONTEST])) === null) {
                throw new NotFoundHttpException('The requested page does not exist.');
            }
        }
        if ($new_clarify->load(Yii::$app->request->post())) {
            if (empty($new_clarify->content)) {
                $discuss->load(Yii::$app->request->post());
                $discuss->update();
                return $this->refresh();
            }
            $new_clarify->entity = Discuss::ENTITY_CONTEST;
            $new_clarify->entity_id = $model->id;
            if ($discuss !== null) {
                Yii::$app->db->createCommand()->update('{{%discuss}}', ['updated_at' => new Expression('NOW()')], ['id' => $cid])->execute();
                $new_clarify->parent_id = $discuss->id;
            }
            $new_clarify->save();

            try {
                // 给前台用户提醒
                $client = stream_socket_client('tcp://0.0.0.0:2121', $errno, $errmsg, 1);
                fwrite($client, json_encode([
                    'uid' => $discuss->user_id,
                    'content' => Yii::t('app', 'New reply') . ': '. $new_clarify->content
                ])."\n");
            } catch (\Exception $e) {
                echo 'Caught exception: ',  $e->getMessage(), "\n";
            }

            Yii::$app->session->setFlash('success', 'Submit Successfully');
            return $this->refresh();
        }

        $clarifies = new ActiveDataProvider([
            'query' => Discuss::find()
                ->where(['parent_id' => 0, 'entity_id' => $model->id, 'entity' => Discuss::ENTITY_CONTEST])
                ->with('user')
                ->orderBy('created_at DESC'),
        ]);

        return $this->render('clarify', [
            'model' => $model,
            'clarifies' => $clarifies,
            'new_clarify' => $new_clarify,
            'discuss' => $discuss
        ]);
    }

    public function actionNewProblem()
    {
        $this->layout = false;
        $model = new Problem();
        return $this->renderAjax('/problem/create', [
            'model' => $model
        ]);
    }

    public function actionDeleteproblem($id, $pid)
    {
        $ok = Yii::$app->db->createCommand()
            ->delete('{{%contest_problem}}', ['contest_id' => $id, 'problem_id' => $pid])
            ->execute();
        if ($ok) {
            Yii::$app->session->setFlash('success', Yii::t('app', 'Delete successfully'));
        } else {
            Yii::$app->session->setFlash('success', Yii::t('app', 'Delete failed'));
        }
        return $this->redirect(['contest/view', 'id' => $id]);
    }

    /**
     * Deletes an existing Contest model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();
        Solution::deleteAll(['contest_id' => $id]);
        ContestUser::deleteAll(['contest_id' => $id]);
        Discuss::deleteAll(['contest_id' => $id]);
        ContestProblem::deleteAll(['contest_id' => $id]);
        ContestPrint::deleteAll(['contest_id' => $id]);
        ContestAnnouncement::deleteAll(['contest_id' => $id]);
        return $this->redirect(['index']);
    }

    /**
     * Finds the Contest model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Contest the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Contest::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
