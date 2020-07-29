<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $model app\models\Problem */
/* @var $form yii\widgets\ActiveForm */

?>

<div class="problem-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'id')->textInput(['placeholder' => '可不填'])
        ->hint('此处用于指定题目ID，若不填，新建题目时题目ID会自动增长。新建题目时填写的ID不能为已经存在的ID') ?>

    <?= $form->field($model, 'title')->textInput() ?>

    <?= $form->field($model, 'time_limit', [
        'template' => "{label}\n<div class=\"input-group\">{input}<span class=\"input-group-addon\">s</span></div>",
    ])->textInput(['maxlength' => 128, 'autocomplete'=>'off']) ?>

    <?= $form->field($model, 'memory_limit', [
        'template' => "{label}\n<div class=\"input-group\">{input}<span class=\"input-group-addon\">MByte</span></div>",
    ])->textInput(['maxlength' => 128, 'autocomplete'=>'off']) ?>

    <?= $form->field($model, 'status')->radioList([
        1 => Yii::t('app', 'Visible'),
        0 => Yii::t('app', 'Hidden'),
        2 => Yii::t('app', 'Private')
    ])->hint(Yii::t('app', '可见：题目将在首页展示，任何用户可见。隐藏：题目仅在后台显示。私有：题目标题在前台可见，但信息仅VIP用户可见')) ?>

    <?= $form->field($model, 'description')->widget('app\widgets\editormd\Editormd'); ?>

    <?= $form->field($model, 'input')->widget('app\widgets\editormd\Editormd'); ?>

    <?= $form->field($model, 'output')->widget('app\widgets\editormd\Editormd'); ?>

    <div class="row">
        <div class="col-md-6">
            <?= $form->field($model, 'sample_input')->textarea(['rows' => 6]) ?>
        </div>
        <div class="col-md-6">
            <?= $form->field($model, 'sample_output')->textarea(['rows' => 6]) ?>
        </div>

        <div class="col-md-6">
            <?= $form->field($model, 'sample_input_2')->textarea(['rows' => 6]) ?>
        </div>
        <div class="col-md-6">
            <?= $form->field($model, 'sample_output_2')->textarea(['rows' => 6]) ?>
        </div>

        <div class="col-md-6">
            <?= $form->field($model, 'sample_input_3')->textarea(['rows' => 6]) ?>
        </div>
        <div class="col-md-6">
            <?= $form->field($model, 'sample_output_3')->textarea(['rows' => 6]) ?>
        </div>
    </div>

    <?= $form->field($model, 'spj')->radioList([
        '1' => Yii::t('app', 'Yes'),
        '0' => Yii::t('app', 'No')
    ]) ?>


    <?= $form->field($model, 'hint')->widget('app\widgets\editormd\Editormd'); ?>

    <?= $form->field($model, 'source')->textarea(['maxlength' => true]) ?>

    <?= $form->field($model, 'tags')->textarea(['maxlength' => true, 'placeholder' => '可不填'])
        ->hint('多标签用逗号隔开。如：dfs, bfs, dp, 暴力，贪心，最短路') ?>

    <?= $form->field($model, 'contest_id')->dropDownList(\app\models\Contest::getContestList()) ?>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('app', 'Save'), ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
