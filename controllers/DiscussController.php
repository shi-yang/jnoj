<?php

namespace app\controllers;

use Yii;
use app\models\Discuss;
use app\models\User;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\ForbiddenHttpException;
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
            $newDiscuss->save();
            return $this->refresh();
        }
        return $this->render('view', [
            'newDiscuss' => $newDiscuss,
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

        $model->delete();

        return $this->redirect(['index']);
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
