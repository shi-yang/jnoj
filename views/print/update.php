<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\ContestPrint */

$this->title = 'Update Print Source:';
$this->params['breadcrumbs'][] = ['label' => $model->contest->title, 'url' => ['/contest/view', 'id' => $model->contest_id]];
$this->params['breadcrumbs'][] = ['label' => 'Print Sources', 'url' => ['index', 'id' => $model->contest_id]];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="print-source-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
