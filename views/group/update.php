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

    <hr>

    <?= Html::a('删除该小组', ['/group/delete', 'id' => $model->id], [
        'class' => 'btn btn-danger',
        'data-confirm' => '此操作会把该小组的比赛信息及提交记录全部删除，且不可恢复，你确定要删除吗？',
        'data-method' => 'post',
    ]) ?>

</div>
