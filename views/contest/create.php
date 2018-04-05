<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\models\Contest */

$this->title = Yii::t('app', 'Create Contest');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Contests'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="contest-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
