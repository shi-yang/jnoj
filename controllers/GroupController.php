<?php

namespace app\controllers;

use Yii;
use app\models\Group;
use app\models\GroupUser;
use app\models\GroupSearch;
use app\models\Contest;
use yii\db\Query;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\db\Expression;
use yii\data\ActiveDataProvider;


/**
 * GroupController implements the CRUD actions for Group model.
 */
class GroupController extends Controller
{
    private $_model;
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
        ];
    }

    /**
     * Lists all Group models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new GroupSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Lists all Group models.
     * @return mixed
     */
    public function actionExplore()
    {
        $searchModel = new GroupSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Group model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        $newGroupUser = new GroupUser();
        $newContest = new Contest();
        $newContest->type = Contest::TYPE_RANK_GROUP;
        $contestDataProvider = new ActiveDataProvider([
            'query' => Contest::find()->where([
                'group_id' => $model->id
            ])->orderBy(['id' => SORT_DESC]),
        ]);

        $userDataProvider = new ActiveDataProvider([
            'query' => GroupUser::find()->where([
                'group_id' => $model->id
            ])->with('user')->orderBy(['role' => SORT_DESC])
        ]);

        if ($newContest->load(Yii::$app->request->post())) {
            $newContest->group_id = $model->id;
            $newContest->scenario = Contest::SCENARIO_ONLINE;
            $newContest->save();
        }

        if ($newGroupUser->load(Yii::$app->request->post())) {
            //　查找用户ID 以及查看是否已经加入比赛中
            $query = (new Query())->select('u.id as user_id, count(g.user_id) as exist')
                ->from('{{%user}} as u')
                ->leftJoin('{{%group_user}} as g', 'g.user_id=u.id')
                ->where('u.username=:name and g.group_id=:gid', [':name' => $newGroupUser->username, ':gid' => $model->id])
                ->one();
            if (!isset($query['user_id'])) {
                Yii::$app->session->setFlash('error', '不存在该用户');
            } else if (!$query['exist']) {
                $newGroupUser->role = GroupUser::ROLE_INVITING;
                $newGroupUser->created_at = new Expression('NOW()');
                $newGroupUser->user_id = $query['user_id'];
                $newGroupUser->group_id = $model->id;
                $newGroupUser->save();
                Yii::$app->session->setFlash('success', '已邀请');
            } else {
                Yii::$app->session->setFlash('error', '用户已在小组中');
            }
        }

        return $this->render('view', [
            'model' => $model,
            'contestDataProvider' => $contestDataProvider,
            'userDataProvider' => $userDataProvider,
            'newGroupUser' => $newGroupUser,
            'newContest' => $newContest
        ]);
    }

    /**
     * Creates a new Group model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Group();
        $model->status = Group::STATUS_VISIBLE;
        $model->join_policy = Group::JOIN_POLICY_INVITE;

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            $groupUser = new GroupUser();
            $groupUser->role = GroupUser::ROLE_LEADER;
            $groupUser->created_at = new Expression('NOW()');
            $groupUser->user_id = Yii::$app->user->id;
            $groupUser->group_id = $model->id;
            $groupUser->save();
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Group model.
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

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Group model.
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
     * Finds the Group model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Group the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Group::findOne($id)) !== null) {
            if 
            $this->_model = $model;
            return $model;
        }

        throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
    }
}
