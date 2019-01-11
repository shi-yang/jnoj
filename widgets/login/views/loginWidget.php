<?php

use yii\bootstrap\ActiveForm;
use yii\helpers\Html;

/* @var $this \yii\web\View */
/* @var $user \app\models\LoginForm */
/* @var $title string */

$this->registerCss('
#login-form .form-control {
  position: relative;
  height: auto;
  -webkit-box-sizing: border-box;
  -moz-box-sizing: border-box;
  box-sizing: border-box;
  padding: 10px;
  font-size: 16px;
}
');
?>
<div class="panel panel-default">
  <div class="panel-heading"><?= Yii::t('app', 'Login') ?></div>
  <div class="panel-body">
    <?php $form = ActiveForm::begin(['id' => 'login-form', 'enableClientValidation' => false]); ?>
        <?= $form->field($user, 'username', [
            'template' => '<div class="input-group"><span class="input-group-addon"><i class="glyphicon glyphicon-user"></i></span>{input}</div>{error}',
            'inputOptions' => [
              'placeholder' => $user->getAttributeLabel('username'),
            ],
          ])->label(false);
        ?>
        <?= $form->field($user, 'password', [
            'template' => '<div class="input-group"><span class="input-group-addon"><i class="glyphicon glyphicon-lock"></i></span>{input}</div>{error}',
            'inputOptions' => [
              'placeholder' => $user->getAttributeLabel('password'),
            ],
          ])->passwordInput()->label(false);
        ?>
        <div class="form-group">
            <?= Html::submitButton(Yii::t('app', 'Login'), ['class' => 'btn btn-primary', 'name' => 'login-button']) ?>
            <?= Html::a(Yii::t('app', 'Signup'), ['/site/signup']) ?>
        </div>
    <?php ActiveForm::end(); ?>
  </div>
</div>
