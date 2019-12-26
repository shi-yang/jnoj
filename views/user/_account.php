<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\User */
/* @var $form yii\widgets\ActiveForm */

$verifyLink = Yii::$app->urlManager->createAbsoluteUrl(['/user/verify-email']);

if ($model->isVerifyEmail()) {
    $emailTemplate = '{label}<div class="input-group">{input}<div class="input-group-addon">已验证</div></div>{hint}{error}';
} else {
    $emailTemplate = '{label}<div class="input-group">{input}<div class="input-group-addon">
        未验证 <a href="' . $verifyLink . '">发送验证链接</a>
        </div></div>{hint}{error}';
}
?>

<?php $form = ActiveForm::begin(); ?>

<?= $form->field($model, 'username')->textInput() ?>

<?= $form->field($model, 'email', [
        'template' => $emailTemplate
])->textInput() ?>

<div class="form-group">
    <?= Html::submitButton(Yii::t('app', 'Save'), ['class' => 'btn btn-success']) ?>
</div>

<?php ActiveForm::end(); ?>
