<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $contest app\models\Contest */

$this->title = 'Print Sources';
$this->params['breadcrumbs'][] = ['label' => Html::encode($contest->title), 'url' => ['/contest/view', 'id' => $contest->id]];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="print-source-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('打印代码', ['create', 'id' => $contest->id], ['class' => 'btn btn-success']) ?> 如需打印代码以供队友查看，请点击此按钮提交，工作人员打印好后会送至队伍前。
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            'id',
            [
                'attribute' => 'username',
                'value' => function ($model, $key, $index, $column) {
                    return Html::a($model->user->username, ['/user/view', 'id' => $model->user->id]);
                },
                'format' => 'raw'
            ],
            [
                'attribute' => 'nickname',
                'value' => function ($model, $key, $index, $column) {
                    return Html::a($model->user->nickname, ['/user/view', 'id' => $model->user->id]);
                },
                'format' => 'raw'
            ],
            'created_at:datetime',
            'status',
            [
                'class' => 'yii\grid\ActionColumn'
            ],
        ],
    ]); ?>
</div>
