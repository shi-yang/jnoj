<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $model app\models\Contest */
/* @var $provider yii\data\ActiveDataProvider */

$this->title = $model->title;
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="contest-index">
    <?= GridView::widget([
        'layout' => '{items}{pager}',
        'dataProvider' => $provider,
        'options' => ['class' => 'table-responsive'],
        'columns' => [
            [
                'attribute' => 'participants',
                'value' => function ($model, $key, $index, $column) {
                    return Html::a(Html::encode($model->user->nickname), ['/user/view', 'id' => $model->user->id]);
                },
                'format' => 'raw',
            ],
        ],
    ]); ?>
</div>
