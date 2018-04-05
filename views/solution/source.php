<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Solution */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Status'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="solution-view">
    <h3>Run id: <?= Html::encode($this->title) ?></h3>
    <pre><?= Html::encode($model->source) ?></pre>
</div>
