<?php

namespace app\controllers;

use Yii;
use yii\db\Query;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\ForbiddenHttpException;
use yii\data\Pagination;
use yii\data\ActiveDataProvider;
use yii\filters\VerbFilter;
use app\models\Problem;
use app\models\ProblemSearch;
use app\models\Solution;
use app\models\User;
use justinvoelker\tagging\TaggingQuery;
use app\models\Discuss;

/**
 * ProblemController implements the CRUD actions for Problem model.
 */
class ProblemController extends Controller
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
     * Lists all Problem models.
     * @return mixed
     */
    public function actionIndex()
    {
        $query = Problem::find();

        if (Yii::$app->request->isGet && !empty($tag = Yii::$app->request->get('tag'))) {
            $query->andWhere('tags LIKE :tag', [':tag' => '%' . $tag . '%']);
        }
        if (($post = Yii::$app->request->post())) {
            $query->orWhere(['like', 'title', $post['q']])
                ->orWhere(['like', 'id', $post['q']]);
        }
        $query->andWhere(['status' => Problem::STATUS_VISIBLE]);
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 50
            ]
        ]);

        $tags = (new TaggingQuery())->select('tags')
            ->from('{{%problem}}')
            ->limit(20)
            ->displaySort(['freq' => SORT_DESC])
            ->getTags();

        $solvedProblem = [];
        if (!Yii::$app->user->isGuest) {
            $solved = (new Query())->select('problem_id')
                ->from('{{%solution}}')
                ->where(['created_by' => Yii::$app->user->id, 'result' => Solution::OJ_AC])
                ->all();
            foreach ($solved as $k) {
                $solvedProblem[$k['problem_id']] = true;
            }
        }

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'tags' => $tags,
            'solvedProblem' => $solvedProblem
        ]);
    }

    public function actionStatistics($id)
    {
        $model = $this->findModel($id);

        $dataProvider = new ActiveDataProvider([
            'query' => Solution::find()->with('user')
                ->where(['problem_id' => $model->id, 'result' => Solution::OJ_AC])
                ->orderBy(['time' => SORT_ASC, 'memory' => SORT_ASC, 'code_length' => SORT_ASC]),
            'pagination' => [
                'pageSize' => 10,
            ],
        ]);

        return $this->render('stat', [
            'model' => $model,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionDiscuss($id)
    {
        $model = $this->findModel($id);
        $newDiscuss = new Discuss();
        $newDiscuss->setScenario('problem');

        $query = Discuss::find()->where([
            'entity' => Discuss::ENTITY_PROBLEM,
            'entity_id' => $model->id,
            'parent_id' => 0
        ])->orderBy('updated_at DESC');

        $countQuery = clone $query;
        $pages = new Pagination(['totalCount' => $countQuery->count()]);
        $discusses = $query->offset($pages->offset)
            ->limit($pages->limit)
            ->all();

        if ($newDiscuss->load(Yii::$app->request->post())) {
            if (Yii::$app->user->isGuest) {
                Yii::$app->session->setFlash('error', 'Please login.');
                return $this->redirect(['/site/login']);
            }
            $newDiscuss->entity = Discuss::ENTITY_PROBLEM;
            $newDiscuss->entity_id = $id;
            $newDiscuss->save();
            Yii::$app->session->setFlash('success', 'Submit Successfully');
            return $this->refresh();
        }

        return $this->render('discuss', [
            'model' => $model,
            'discusses' => $discusses,
            'pages' => $pages,
            'newDiscuss' => $newDiscuss
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
        $solution = new Solution();
        $submissions = NULL;
        if (!Yii::$app->user->isGuest) {
            $submissions = (new Query())->select('created_at, result, id')
                ->from('{{%solution}}')
                ->where([
                    'problem_id' => $id,
                    'created_by' => Yii::$app->user->id
                ])
                ->orderBy('id DESC')
                ->limit(10)
                ->all();
        }
        if ($solution->load(Yii::$app->request->post())) {
            if (Yii::$app->user->isGuest) {
                Yii::$app->session->setFlash('error', 'Please login.');
                return $this->redirect(['/site/login']);
            }
            $solution->problem_id = $model->id;
            $solution->status = Solution::STATUS_VISIBLE;
            $solution->save();
            Yii::$app->session->setFlash('success', 'Submit Successfully');
            return $this->redirect(['/solution/index']);
        }

        return $this->render('view', [
            'solution' => $solution,
            'model' => $model,
            'submissions' => $submissions
        ]);
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
            if ($model->status == Problem::STATUS_VISIBLE) {
                return $model;
            } else {
                throw new ForbiddenHttpException('You are not allowed to perform this action.');
            }
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
