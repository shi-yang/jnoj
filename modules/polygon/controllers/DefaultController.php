<?php

namespace app\modules\polygon\controllers;

use app\models\User;
use Yii;
use yii\web\Controller;
use app\modules\polygon\models\Problem;
use yii\data\ActiveDataProvider;

/**
 * Default controller for the `polygon` module
 */
class DefaultController extends Controller
{
    /**
     * Renders the index view for the module
     * @return string
     */
    public function actionIndex()
    {
        $query = Problem::find()->with('user')->orderBy(['id' => SORT_DESC]);
        if (Yii::$app->user->isGuest || Yii::$app->user->identity->role != User::ROLE_ADMIN) {
            $query->andWhere(['created_by' => Yii::$app->user->id]);
        }
        $dataProvider = new ActiveDataProvider([
            'query' => $query
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }
}
