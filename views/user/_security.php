<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\User */
/* @var $form yii\widgets\ActiveForm */
?>

<?php $form = ActiveForm::begin(); ?>

<?= $form->field($model, 'oldPassword')->textInput() ?>

<?= $form->field($model, 'newPassword')->textInput() ?>

<?= $form->field($model, 'verifyPassword')->textInput() ?>

<div class="form-group">
    <?= Html::submitButton(Yii::t('app', 'Update'), ['class' => 'btn btn-primary']) ?>
</div>

<?php ActiveForm::end(); ?>
