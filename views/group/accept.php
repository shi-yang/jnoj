<?php

use yii\grid\GridView;
use yii\helpers\Html;
use app\models\Group;
use app\models\GroupUser;

/* @var $this yii\web\View */
/* @var $model app\models\Group */
/* @var $userDataProvider yii\data\ActiveDataProvider */
$this->title = Html::encode($model->name);
?>
<h2><?= Html::a(Html::encode($model->name), ['/group/view', 'id' => $model->id]) ?></h2>
<?php if ($model->getRole() == GroupUser::ROLE_INVITING): ?>
    <h3>邀请你加入小组：</h3>
    <h4><?= Yii::$app->formatter->asHtml($model->description) ?></h4>
    <?= Html::a('同意加入', ['/group/accept', 'id' => $model->id, 'accept' => 1], ['class' => 'btn btn-success']) ?>
    <?= Html::a('残忍拒绝', ['/group/accept', 'id' => $model->id, 'accept' => 0], ['class' => 'btn btn-danger']) ?>
<?php elseif ($model->join_policy == Group::JOIN_POLICY_APPLICATION): ?>
    <?= Html::a('申请加入', ['/group/accept', 'id' => $model->id, 'accept' => 3], ['class' => 'btn btn-success']) ?>
<?php elseif ($model->join_policy == Group::JOIN_POLICY_FREE): ?>
    <?= Html::a('加入小组', ['/group/accept', 'id' => $model->id, 'accept' => 2], ['class' => 'btn btn-success']) ?>
<?php endif; ?>

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