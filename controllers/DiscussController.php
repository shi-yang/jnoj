<?php

namespace app\controllers;

use Yii;
use app\models\Discuss;
use yii\data\Pagination;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\ForbiddenHttpException;
use yii\db\Expression;
use yii\filters\VerbFilter;

/**
 * PrintController implements the CRUD actions for PrintSource model.
 */
class DiscussController extends Controller
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
     * Displays a single PrintSource model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        $newDiscuss = new Discuss();

        if ($newDiscuss->load(Yii::$app->request->post())) {
            $newDiscuss->parent_id = $model->id;
            $newDiscuss->entity = Discuss::ENTITY_PROBLEM;
            $newDiscuss->entity_id = $model->entity_id;
            $model->updated_at = new Expression('NOW()');
            $model->update();
            $newDiscuss->save();
            Yii::$app->session->setFlash('success', Yii::t('app', 'Submitted successfully'));
            return $this->refresh();
        }

        // 查询回复
        $query = Discuss::find()->where(['parent_id' => $model->id])->with('user');
        $countQuery = clone $query;
        $pages = new Pagination(['totalCount' => $countQuery->count()]);
        $replies = $query->offset($pages->offset)
            ->limit($pages->limit)
            ->all();

        return $this->render('view', [
            'newDiscuss' => $newDiscuss,
            'model' => $model,
            'replies' => $replies,
            'pages' => $pages
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

        if (Yii::$app->user->id != $model->created_by) {
            throw new ForbiddenHttpException('You are not allowed to perform this action.');
        }

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
        if (Yii::$app->user->id === $model->created_by) {
            $model->delete();
            Discuss::deleteAll(['parent_id' => $model->id]);
            Yii::$app->session->setFlash('success', Yii::t('app', 'Deleted successfully'));
            return $this->redirect(['/site/index']);
        }
        throw new ForbiddenHttpException('You are not allowed to perform this action.');
    }

    /**
     * Finds the PrintSource model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Discuss the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     * @throws ForbiddenHttpException if the model cannot be viewed
     */
    protected function findModel($id)
    {
        if (($model = Discuss::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
