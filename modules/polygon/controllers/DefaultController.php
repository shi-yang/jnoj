<?php

namespace app\modules\polygon\controllers;

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
        $dataProvider = new ActiveDataProvider([
            'query' => Problem::find(),
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }
}
