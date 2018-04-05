<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\Contest */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="contest-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'title')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'start_time')->widget('app\widgets\laydate\LayDate', [
        'clientOptions' => [
            'istoday' => true,
            'type' => 'datetime'
        ]
    ]) ?>

    <?= $form->field($model, 'end_time')->widget('app\widgets\laydate\LayDate', [
        'clientOptions' => [
            'istoday' => true,
            'type' => 'datetime'
        ]
    ]) ?>

    <?= $form->field($model, 'status')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'description')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'password')->passwordInput(['maxlength' => true]) ?>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('app', 'Save'), ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
