<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\User */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="user-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'newPassword')->textInput() ?>

    <?= $form->field($model, 'role')->radioList([
        $model::ROLE_PLAYER => '参赛用户',
        $model::ROLE_USER => '普通用户',
        $model::ROLE_ADMIN => '管理员'
    ]) ?>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('app', 'Save'), ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
