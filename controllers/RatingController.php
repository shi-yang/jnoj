<?php

namespace app\controllers;

use app\models\User;
use yii\web\Controller;
use yii\data\Pagination;

class RatingController extends Controller
{

    public function actionIndex()
    {
        $query = User::find();
        $defaultPageSize = 50;
        $countQuery = clone $query;
        $pages = new Pagination([
            'totalCount' => $countQuery->count(),
            'defaultPageSize' => $defaultPageSize
        ]);
        $users = $query->offset($pages->offset)
            ->limit($pages->limit)
            ->all();

        $top3users = array_slice($users, 0, 3);
        $users = array_slice($users, 3);

        return $this->render('index', [
            'top3users' => $top3users,
            'users' => $users,
            'pages' => $pages,
            'currentPage' => $pages->page,
            'defaultPageSize' => $defaultPageSize
        ]);
    }
}
