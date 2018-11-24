<?php

namespace app\controllers;

use yii\web\Controller;

class WikiController extends Controller
{
    public $layout = 'wiki';

    public function actionIndex()
    {
        return $this->render('index');
    }

    public function actionInfo()
    {
        return $this->render('info');
    }

    public function actionAbout()
    {
        return $this->render('about');
    }

    public function actionContest()
    {
        return $this->render('contest');
    }

    public function actionProblem()
    {
        return $this->render('problem');
    }
}
