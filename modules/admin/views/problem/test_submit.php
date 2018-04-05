<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $solutions array */
/* @var $model app\models\Problem */
/* @var $solution app\models\Solution */

$solution->language = Yii::$app->user->identity->language;
?>
<div class="solutions-view">
    <h1>
        <?= Html::encode($model->title) ?>
    </h1>
    <p class="text-muted">提示：题目的提交将不会在前台展示．不会出现泄题情况</p>

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($solution, 'language')->dropDownList($solution::getLanguageList()) ?>

    <?= $form->field($solution, 'source')->textarea(['rows' => 8, 'autocomplete'=>'off']) ?>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('app', 'Submit'), ['class' => 'btn btn-primary']) ?>
    </div>
    <?php ActiveForm::end(); ?>

</div>
