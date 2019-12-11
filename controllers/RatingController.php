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
        $query = (new Query())->select('u.id, u.nickname, u.rating, s.solved')
            ->from('{{%user}} AS u')
            ->innerJoin('(SELECT COUNT(DISTINCT problem_id) AS solved, created_by FROM {{%solution}} WHERE result=4 AND status=1 GROUP BY created_by ORDER BY solved DESC) as s',
                'u.id=s.created_by')
            ->orderBy('solved DESC, id');
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
