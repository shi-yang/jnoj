<?php

namespace app\controllers;

use app\components\BaseController;

class WikiController extends BaseController
{
    public $layout = 'wiki';

    public function actionIndex()
    {
        return $this->render('index');
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

    public function actionSpj()
    {
        return $this->render('spj');
    }

    public function actionOi()
    {
        return $this->render('oi');
    }
}
