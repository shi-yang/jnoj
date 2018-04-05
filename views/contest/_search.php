<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\ContestSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="contest-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
        'options' => [
            'data-pjax' => 1
        ],
    ]); ?>

    <?= $form->field($model, 'cid') ?>

    <?= $form->field($model, 'title') ?>

    <?= $form->field($model, 'description') ?>

    <?= $form->field($model, 'start_time') ?>

    <?php // echo $form->field($model, 'end_time') ?>

    <?php // echo $form->field($model, 'lock_board_time') ?>

    <?php // echo $form->field($model, 'hide_others') ?>

    <?php // echo $form->field($model, 'board_make') ?>

    <?php // echo $form->field($model, 'isvirtual') ?>

    <?php // echo $form->field($model, 'owner') ?>

    <?php // echo $form->field($model, 'report') ?>

    <?php // echo $form->field($model, 'mboard_make') ?>

    <?php // echo $form->field($model, 'allp') ?>

    <?php // echo $form->field($model, 'type') ?>

    <?php // echo $form->field($model, 'has_cha') ?>

    <?php // echo $form->field($model, 'challenge_end_time') ?>

    <?php // echo $form->field($model, 'challenge_start_time') ?>

    <?php // echo $form->field($model, 'password') ?>

    <?php // echo $form->field($model, 'owner_viewable') ?>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('app', 'Search'), ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton(Yii::t('app', 'Reset'), ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
