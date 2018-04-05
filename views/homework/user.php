<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $model app\models\Contest */
/* @var $provider yii\data\ActiveDataProvider */

$this->title = $model->title;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Homework'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => Html::encode($model->title), 'url' => ['view', 'id' => $model->id]];
?>

<div class="contest-index">
    <?= GridView::widget([
        'layout' => '{items}{pager}',
        'dataProvider' => $provider,
        'options' => ['class' => 'table-responsive'],
        'columns' => [
            [
                'attribute' => 'Who',
                'value' => function ($model, $key, $index, $column) {
                    return Html::a(Html::encode($model->user->nickname), ['/user/view', 'id' => $model->user->id]);
                },
                'format' => 'raw',
            ],
            [
                'attribute' => Yii::t('app', 'Student Number'),
                'value' => function ($model, $key, $index, $column) {
                    return $model->userProfile->student_number;
                },
                'format' => 'raw'
            ]
        ],
    ]); ?>
</div>
