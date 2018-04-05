<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\SolutionSearch */
/* @var $form yii\widgets\ActiveForm */
/* @var $contest_id integer */
/* @var $nav string */
?>

<div class="solution-search">

    <?php $form = ActiveForm::begin([
        'action' => ['status', 'id' => $contest_id],
        'method' => 'get',
        'options' => [
            'class' => 'form-inline',
            'data-pjax' => 1
        ],
    ]); ?>

    <?= $form->field($model, 'problem_id', [
        'template' => "{label}\n<div class=\"input-group\"><span class=\"input-group-addon\"><span class='glyphicon glyphicon-sunglasses'></span> pid</span>{input}</div>",
    ])->dropDownList($nav)->label(false) ?>

    <?= $form->field($model, 'username', [
        'template' => "{label}\n<div class=\"input-group\"><span class=\"input-group-addon\"><span class='glyphicon glyphicon-user'></span></span>{input}</div>",
    ])->textInput(['maxlength' => 128, 'autocomplete'=>'off', 'placeholder' => 'Who'])->label(false) ?>


    <?= $form->field($model, 'result', [
        'template' => "{label}\n<div class=\"input-group\"><span class=\"input-group-addon\">Result</span>{input}</div>",
    ])->dropDownList($model::getResultList())->label(false) ?>


    <?= $form->field($model, 'language', [
        'template' => "{label}\n<div class=\"input-group\"><span class=\"input-group-addon\">Lang</span>{input}</div>",
    ])->dropDownList($model::getLanguageList())->label(false) ?>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('app', 'Search'), ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton(Yii::t('app', 'Reset'), ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
