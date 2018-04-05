<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\ContestPrint */

$this->title = $model->user->nickname;
$this->params['breadcrumbs'][] = ['label' => $model->contest->title, 'url' => ['/contest/view', 'id' => $model->contest_id]];
$this->params['breadcrumbs'][] = ['label' => 'Print Sources', 'url' => ['index', 'id' => $model->contest_id]];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="print-source-view">

    <h1><span class="glyphicon glyphicon-user"></span> <?= Html::encode($this->title) ?>[<?= Html::encode($model->user->username) ?>]</h1>
    <p>
        <?= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
    </p>
    <hr>
    <p><span class="glyphicon glyphicon-time"></span> <?= Yii::$app->formatter->asRelativeTime($model->created_at) ?></p>
    <pre><?= Html::encode($model->source) ?></pre>

</div>
