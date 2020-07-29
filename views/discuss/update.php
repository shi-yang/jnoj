<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\Discuss */
/* @var $form yii\widgets\ActiveForm */

?>

<div class="contest-form">

    <?php $form = ActiveForm::begin(); ?>

    <?php if ($model->entity == \app\models\Discuss::ENTITY_PROBLEM && $model->parent_id == 0): ?>
    <?= $form->field($model, 'title')->textInput(['maxlength' => true]) ?>
    <?php endif; ?>

    <?= $form->field($model, 'content')->widget('app\widgets\editormd\Editormd'); ?>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('app', 'Save'), ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
