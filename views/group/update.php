<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Group */

$this->title = $model->name;
?>
<div class="group-update">

    <h1><?= Html::a(Html::encode($this->title), ['/group/view', 'id' => $model->id]) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
