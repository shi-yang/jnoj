<?php

namespace app\controllers;

use app\components\BaseController;
use app\models\ContestPrint;
use app\models\User;
use Yii;
use yii\db\Expression;
use yii\db\Query;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;
use yii\web\ForbiddenHttpException;
use yii\filters\VerbFilter;
use app\models\ContestAnnouncement;
use app\models\ContestUser;
use app\models\Contest;
use app\models\Solution;
use app\models\SolutionSearch;
use app\models\Discuss;

/**
 * ContestController implements the CRUD actions for Contest model.
 */
class ContestController extends BaseController
{
    public $layout = 'contest';
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
     * Lists all Contest models.
     * @return mixed
     */
    public function actionIndex()
    {
        $this->layout = 'main';
        $dataProvider = new ActiveDataProvider([
            'query' => Contest::find()->where([
                '<>', 'status', Contest::STATUS_HIDDEN
            ])->andWhere([
                'group_id' => 0
            ])->orderBy(['id' => SORT_DESC]),
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * 用户在比赛中的提交记录
     * @param $id
     * @return mixed
     */
    public function actionStatus($id)
    {
        $model = $this->findModel($id);
        $searchModel = new SolutionSearch();
        // 访问权限检查
        if (!$model->canView()) {
            return $this->render('/contest/forbidden', ['model' => $model]);
        }

        if (Yii::$app->request->isPjax) {
            return $this->renderAjax('/contest/status', [
                'model' => $model,
                'searchModel' => $searchModel,
                'dataProvider' => $searchModel->search(Yii::$app->request->queryParams, $model)
            ]);
        }
        return $this->render('/contest/status', [
            'model' => $model,
            'searchModel' => $searchModel,
            'dataProvider' => $searchModel->search(Yii::$app->request->queryParams, $model)
        ]);
    }

    /**
     * 显示用户在某道题上的提交列表
     * @param $pid
     * @param $cid
     * @return mixed
     * @throws ForbiddenHttpException if the model cannot be viewed
     * @throws NotFoundHttpException
     */
    public function actionSubmission($pid, $cid, $uid)
    {
        $this->layout = false;
        $model = $this->findModel($cid);

        // 访问权限检查，比赛结束前提交列表仅作者可见，比赛结束后所有人可见
        if (!$model->isContestEnd() && $model->type == Contest::TYPE_OI) {
            throw new ForbiddenHttpException('You are not allowed to perform this action.');
        }
        if ((!$model->isContestEnd() || $model->isScoreboardFrozen()) && (Yii::$app->user->isGuest || Yii::$app->user->id != $model->created_by)) {
            throw new ForbiddenHttpException('You are not allowed to perform this action.');
        }
        $submissions = Yii::$app->db->createCommand(
            'SELECT id, result, created_at FROM {{%solution}} WHERE problem_id=:pid AND contest_id=:cid AND created_by=:uid ORDER BY id DESC',
            [':pid' => $pid, ':cid' => $model->id, ':uid' => $uid]
        )->queryAll();
        return $this->render('submission', [
            'submissions' => $submissions
        ]);
    }

    /**
     * 显示注册参赛的用户
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the contest cannot be found
     */
    public function actionUser($id)
    {
        $this->layout = 'main';
        $model = $this->findModel($id);
        $provider = new ActiveDataProvider([
            'query' => ContestUser::find()->where(['contest_id' => $model->id])->with('user')->with('userProfile'),
            'pagination' => [
                'pageSize' => 100
            ]
        ]);

        return $this->render('user', [
            'model' => $model,
            'provider' => $provider
        ]);
    }

    /**
     * 注册比赛的页面
     * @param integer $id
     * @param integer $register 等于 0 什么也不做，等于 1 就将当前用户注册到比赛列表中
     * @return mixed
     * @throws NotFoundHttpException if the contest cannot be found
     * @throws ForbiddenHttpException
     */
    public function actionRegister($id, $register = 0)
    {
        $this->layout = 'main';
        if (Yii::$app->user->isGuest) {
            return $this->redirect(['/site/login']);
        }
        $model = $this->findModel($id);

        // 线下赛只能在后台加入，在此处不给注册
        if ($model->scenario == Contest::SCENARIO_OFFLINE) {
            throw new ForbiddenHttpException('You are not allowed to perform this action.');
        }

        // 设为私有的比赛只能在后台加入，在此处不给注册
        if ($model->status == Contest::STATUS_PRIVATE) {
            throw new ForbiddenHttpException('You are not allowed to perform this action.');
        }

        if ($register == 1 && !$model->isUserInContest()) {
            Yii::$app->db->createCommand()->insert('{{%contest_user}}', [
                'contest_id' => $model->id,
                'user_id' => Yii::$app->user->id
            ])->execute();
            Yii::$app->session->setFlash('success', '成功注册');
            return $this->redirect(['/contest/view', 'id' => $model->id]);
        }
        return $this->render('register', [
            'model' => $model
        ]);
    }

    /**
     * 代码打印页面
     * @param $id
     * @return string
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    public function actionPrint($id)
    {
        $model = $this->findModel($id);
        $newContestPrint = new ContestPrint();

        // 访问权限检查
        if (!$model->canView()) {
            return $this->render('/contest/forbidden', ['model' => $model]);
        }
        // 只能在线下赛未结束时访问
        if ($model->scenario != Contest::SCENARIO_OFFLINE || $model->getRunStatus() == Contest::STATUS_ENDED) {
            throw new ForbiddenHttpException('该比赛现不提供打印服务功能。');
        }

        if ($newContestPrint->load(Yii::$app->request->post())) {
            $newContestPrint->contest_id = $model->id;
            $newContestPrint->save();
            return $this->redirect(['print', 'id' => $model->id]);
        }

        $query = ContestPrint::find()->where(['contest_id' => $model->id, 'user_id' => Yii::$app->user->id])->with('user');

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        return $this->render('print', [
            'model' => $model,
            'dataProvider' => $dataProvider,
            'newContestPrint' => $newContestPrint
        ]);
    }

    /**
     * Displays a single Contest model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the action cannot be found
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        // 访问权限检查
        if (!$model->canView()) {
            return $this->render('/contest/forbidden', ['model' => $model]);
        }
        $dataProvider = new ActiveDataProvider([
            'query' => ContestAnnouncement::find()->where(['contest_id' => $model->id]),
        ]);

        return $this->render('/contest/view', [
            'model' => $model,
            'dataProvider' => $dataProvider
        ]);
    }

    /**
     * 比赛题解
     * @param $id
     * @return string|\yii\web\Response
     * @throws ForbiddenHttpException
     */
    public function actionEditorial($id)
    {
        $model = $this->findModel($id);

        // 只能在比赛结束时访问
        if ($model->getRunStatus() == Contest::STATUS_ENDED) {
            return $this->render('/contest/editorial', [
                'model' => $model
            ]);
        }

        throw new ForbiddenHttpException('You are not allowed to perform this action.');
    }

    /**
     * 比赛问题答疑页面
     * @param $id
     * @param int $cid 该值等于 -1 时，显示所有的答疑列表，否则显示具体某个答疑
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionClarify($id, $cid = -1)
    {
        $model = $this->findModel($id);
        // 访问权限检查
        if (!$model->canView()) {
            return $this->render('/contest/forbidden', ['model' => $model]);
        }
        $newClarify = new Discuss();
        $discuss = null;
        $dataProvider = new ActiveDataProvider([
            'query' => ContestAnnouncement::find()->where(['contest_id' => $model->id]),
        ]);

        if ($cid != -1) {
            if (($discuss = Discuss::findOne(['id' => $cid, 'entity_id' => $model->id, 'entity' => Discuss::ENTITY_CONTEST])) === null) {
                throw new NotFoundHttpException('The requested page does not exist.');
            }
        }
        if (!Yii::$app->user->isGuest && $newClarify->load(Yii::$app->request->post())) {
            // 判断是否已经参赛，提交即参加比赛
            if (!$model->isUserInContest()) {
                Yii::$app->db->createCommand()->insert('{{%contest_user}}', [
                   'contest_id' => $model->id,
                   'user_id' => Yii::$app->user->id
                ])->execute();
            }
            $newClarify->entity = Discuss::ENTITY_CONTEST;
            $newClarify->entity_id = $model->id;
            if ($discuss !== null) {
                if (empty($newClarify->content)) {
                    Yii::$app->session->setFlash('error', '内容不能为空');
                    return $this->refresh();
                }
                $newClarify->parent_id = $discuss->id;
                $discuss->updated_at = new Expression('NOW()');
                $discuss->update();
            } else if (empty($newClarify->title)) {
                Yii::$app->session->setFlash('error', '标题不能为空');
                return $this->refresh();
            }
            $newClarify->status = Discuss::STATUS_PUBLIC;
            $newClarify->save();

            // 给所有管理员发送弹窗提醒
            try {
                $client = stream_socket_client('tcp://0.0.0.0:2121', $errno, $errmsg, 1);
                $uids = Yii::$app->db->createCommand('SELECT id FROM user WHERE role=' . User::ROLE_ADMIN)->queryColumn();
                $content = '比赛：' . $model->title .  ' - 有了新的答疑，请到后台查看并回复。';
                foreach ($uids as $uid) {
                    fwrite($client, json_encode([
                        'uid' => $uid,
                        'content' => $content
                    ]) . "\n");
                }
            } catch (\Exception $e) {
                echo 'Caught exception: ',  $e->getMessage(), "\n";
            }
            Yii::$app->session->setFlash('success', Yii::t('app', 'Submitted successfully'));
            return $this->refresh();
        }
        $query = Discuss::find()
            ->where(['parent_id' => 0, 'entity_id' => $model->id, 'entity' => Discuss::ENTITY_CONTEST])
            ->with('user')
            ->orderBy('created_at DESC');
        if (!$model->isContestAdmin()) {
            $query->andWhere('status=1');
        }
        if (!Yii::$app->user->isGuest) {
            $query->orWhere(['parent_id' => 0, 'entity_id' => $model->id, 'entity' => Discuss::ENTITY_CONTEST, 'created_by' => Yii::$app->user->id]);
        }
        $clarifies = new ActiveDataProvider([
            'query' => $query,
        ]);

        if ($discuss != null) {
            return $this->render('/contest/clarify_view', [
                'newClarify' => $newClarify,
                'clarify' => $discuss,
                'model' => $model
            ]);
        } else {
            return $this->render('/contest/clarify', [
                'model' => $model,
                'clarifies' => $clarifies,
                'newClarify' => $newClarify,
                'discuss' => $discuss,
                'dataProvider' => $dataProvider
            ]);
        }
    }

    /**
     * 比赛榜单
     * @param $id
     * @return string
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     * @throws \yii\db\Exception
     */
    public function actionStanding($id, $showStandingBeforeEnd = 1)
    {
        $model = $this->findModel($id);
        // 访问权限检查
        if (!$model->canView()) {
            return $this->render('/contest/forbidden', ['model' => $model]);
        }
        if ($showStandingBeforeEnd) {
            $rankResult = $model->getRankData(true);
        } else {
            $rankResult = $model->getRankData(true, time());
        }
        return $this->render('/contest/standing', [
            'model' => $model,
            'rankResult' => $rankResult,
            'showStandingBeforeEnd' => $showStandingBeforeEnd
        ]);
    }

    /**
     * 比赛期间可对外公布的榜单。任何用户均可访问。
     */
    public function actionStanding2($id, $showStandingBeforeEnd = 1)
    {
        $this->layout = 'basic';
        $model = $this->findModel($id);
        // 访问权限检查
        if ($model->status != Contest::STATUS_VISIBLE) {
            return $this->render('/contest/forbidden', ['model' => $model]);
        }
        $rankResult = $model->getRankData(true);

        if ($showStandingBeforeEnd) {
            $rankResult = $model->getRankData(true);
        } else {
            $rankResult = $model->getRankData(true, time());
        }
        return $this->render('/contest/standing2', [
            'model' => $model,
            'rankResult' => $rankResult,
            'showStandingBeforeEnd' => $showStandingBeforeEnd
        ]);
    }

    /**
     * 显示比赛问题
     * @param integer $id Contest Id
     * @param integer $pid Problem Id
     * @return mixed
     */
    public function actionProblem($id, $pid = 0)
    {
        $model = $this->findModel($id);
        // 访问权限检查
        if (!$model->canView()) {
            return $this->render('/contest/forbidden', ['model' => $model]);
        }
        $solution = new Solution();

        $problem = $model->getProblemById(intval($pid));

        if (!Yii::$app->user->isGuest && $solution->load(Yii::$app->request->post())) {
            // 判断是否已经参赛，提交即参加比赛
            if (!$model->isUserInContest()) {
                Yii::$app->db->createCommand()->insert('{{%contest_user}}', [
                    'contest_id' => $model->id,
                    'user_id' => Yii::$app->user->id
                ])->execute();
            }
            if ($model->getRunStatus() == Contest::STATUS_NOT_START) {
                Yii::$app->session->setFlash('error', 'The contest has not started.');
                return $this->refresh();
            }
            if ($model->isContestEnd() && time() < strtotime($model->end_time) + 5 * 60) {
                Yii::$app->session->setFlash('error', '比赛已结束。比赛结束五分钟后开放提交。');
                return $this->refresh();
            }
            $solution->problem_id = $problem['id'];
            $solution->contest_id = $model->id;
            $solution->status = Solution::STATUS_HIDDEN;
            $solution->save();
            Yii::$app->session->setFlash('success', Yii::t('app', 'Submitted successfully'));
            return $this->refresh();
        }
        $submissions = [];
        if (!Yii::$app->user->isGuest) {
            $submissions = (new Query())->select('created_at, result, id')
                ->from('{{%solution}}')
                ->where([
                    'problem_id' => $problem['id'] ?? null,
                    'contest_id' => $model->id,
                    'created_by' => Yii::$app->user->id
                ])
                ->orderBy('id DESC')
                ->limit(10)
                ->all();
        }
        if (Yii::$app->request->isPjax) {
            return $this->renderAjax('/contest/problem', [
                'model' => $model,
                'solution' => $solution,
                'problem' => $problem,
                'submissions' => $submissions
            ]);
        } else {
            return $this->render('/contest/problem', [
                'model' => $model,
                'solution' => $solution,
                'problem' => $problem,
                'submissions' => $submissions
            ]);
        }
    }

    /**
     * Finds the Contest model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Contest the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     * @throws ForbiddenHttpException if the model cannot be viewed
     */
    protected function findModel($id)
    {
        if (($model = Contest::findOne($id)) !== null) {
            if ($model->status != Contest::STATUS_HIDDEN || !Yii::$app->user->isGuest && Yii::$app->user->id === $model->created_by) {
                return $model;
            } else {
                throw new ForbiddenHttpException('You are not allowed to perform this action.');
            }
        }
        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
