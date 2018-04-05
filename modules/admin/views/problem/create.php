<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\models\Problem */

$this->title = Yii::t('app', 'Create Problem');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Problems'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$model->spj = 0;
$model->time_limit = 1;
$model->memory_limit = 128;
$model->status = $model::STATUS_HIDDEN;
?>
<div class="problem-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
