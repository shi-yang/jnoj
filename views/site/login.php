<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model app\models\LoginForm */

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

$this->title = Yii::t('app', 'Login');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-login">
    <?php $form = ActiveForm::begin([
        'id' => 'login-form',
        'options' => [
            'class' => 'form-signin'
        ]
    ]); ?>
        <h1><?= Html::encode($this->title) ?></h1>
        <?= $form->field($model, 'username', [
            'template' => '<div class="input-group"><span class="input-group-addon"><i class="glyphicon glyphicon-user"></i></span>{input}</div>{error}',
            'inputOptions' => [
                'placeholder' => $model->getAttributeLabel('username'),
            ],
        ])->label(false);
        ?>
        <?= $form->field($model, 'password', [
            'template' => '<div class="input-group"><span class="input-group-addon"><i class="glyphicon glyphicon-lock"></i></span>{input}</div>{error}',
            'inputOptions' => [
                'placeholder' => $model->getAttributeLabel('password'),
            ],
        ])->passwordInput()->label(false);
        ?>

        <?php if ($model->scenario == 'withCaptcha'): ?>
            <?= $form->field($model, 'verifyCode')->widget(\yii\captcha\Captcha::className()); ?>
        <?php endif; ?>
        <?= $form->field($model, 'rememberMe')->checkbox() ?>

        <div class="form-group">
            <?= Html::submitButton(Yii::t('app', 'Login'), ['class' => 'btn btn-primary', 'name' => 'login-button']) ?>
        </div>

    <?php ActiveForm::end(); ?>

</div>
