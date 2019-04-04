<?php

use yii\grid\GridView;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Group */
/* @var $userDataProvider yii\data\ActiveDataProvider */

?>

<h2>邀请你加入小组 <?= Html::a(Html::encode($model->name), ['/group/view', 'id' => $model->id]) ?></h2>
    <h4><?= Yii::$app->formatter->asHtml($model->description) ?></h4>
<?= Html::a('同意加入', ['/group/accept', 'id' => $model->id, 'accept' => 1], ['class' => 'btn btn-success']) ?>

<?= Html::a('残忍拒绝', ['/group/accept', 'id' => $model->id, 'accept' => 0], ['class' => 'btn btn-danger']) ?>

<hr>
<h3>小组成员列表</h3>
<?= GridView::widget([
    'layout' => '{items}{pager}',
    'dataProvider' => $userDataProvider,
    'options' => ['class' => 'table-responsive'],
    'columns' => [
        [
            'attribute' => 'role',
            'value' => function ($model, $key, $index, $column) {
                return $model->getRole(true);
            },
            'format' => 'raw',
            'options' => ['width' => '150px']
        ],
        [
            'attribute' => Yii::t('app', 'Nickname'),
            'value' => function ($model, $key, $index, $column) {
                return Html::a(Html::encode($model->user->nickname), ['/user/view', 'id' => $model->user->id]);
            },
            'format' => 'raw',
        ],
        [
            'attribute' => 'created_at',
            'value' => function ($model, $key, $index, $column) {
                return Yii::$app->formatter->asRelativeTime($model->created_at);
            },
            'options' => ['width' => '150px']
        ]
    ],
]); ?>