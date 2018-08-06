<?php

namespace app\controllers;

use app\models\Solution;
use app\models\User;
use Yii;
use yii\db\Query;
use yii\web\Controller;
use yii\data\Pagination;

class RatingController extends Controller
{
    public function actionIndex()
    {
        $query = User::find()->orderBy('rating DESC');
        $top3users = $query->limit(3)->all();
        $defaultPageSize = 50;
        $countQuery = clone $query;
        $pages = new Pagination([
            'totalCount' => $countQuery->count(),
            'defaultPageSize' => $defaultPageSize
        ]);
        $users = $query->offset($pages->offset)
            ->limit($pages->limit)
            ->all();

        return $this->render('index', [
            'top3users' => $top3users,
            'users' => $users,
            'pages' => $pages,
            'currentPage' => $pages->page,
            'defaultPageSize' => $defaultPageSize
        ]);
    }

    public function actionProblem()
    {
        $submissions = Yii::$app->db->createCommand('
            SELECT * FROM {{%solution}}
            WHERE result=:r
        ', [':r' => Solution::OJ_AC])->queryAll();
        $submissions = (new Query())->select('*')
            ->from('{{%solution}}')
            ->where(['result' => Solution::OJ_AC]);

        $query = User::find()->orderBy('rating DESC');
        $top3users = $query->limit(3)->all();
        $defaultPageSize = 50;
        $countQuery = clone $query;
        $pages = new Pagination([
            'totalCount' => $countQuery->count(),
            'defaultPageSize' => $defaultPageSize
        ]);
        $users = $query->offset($pages->offset)
            ->limit($pages->limit)
            ->all();

        return $this->render('problem', [
            'top3users' => $top3users,
            'users' => $users,
            'pages' => $pages,
            'currentPage' => $pages->page,
            'defaultPageSize' => $defaultPageSize
        ]);
    }
}
