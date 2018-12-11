<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Problem */

$this->title = Yii::t('app', $model->title);
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Problems'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->title, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('app', 'Update');
?>
<div class="problem-header">
    <?= \yii\bootstrap\Nav::widget([
        'options' => ['class' => 'nav nav-pills'],
        'items' => [
            ['label' => Yii::t('app', 'Preview'), 'url' => ['/admin/problem/view', 'id' => $model->id]],
            ['label' => Yii::t('app', 'Edit'), 'url' => ['/admin/problem/update', 'id' => $model->id]],
            ['label' => Yii::t('app', 'Tests Data'), 'url' => ['/admin/problem/test-data', 'id' => $model->id]],
            ['label' => Yii::t('app', 'Verify Data'), 'url' => ['/admin/problem/verify', 'id' => $model->id]],
            ['label' => Yii::t('app', 'SPJ'), 'url' => ['/admin/problem/spj', 'id' => $model->id]]
        ],
    ]) ?>
</div>
<hr>
<div class="problem-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
