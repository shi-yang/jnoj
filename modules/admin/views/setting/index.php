<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $settings array */
/* @var $form yii\widgets\ActiveForm */
$this->title = Yii::t('app', 'Setting');
?>

<div class="setting-form">

    <?= Html::beginForm() ?>

    <div class="form-group">
        <?= Html::label(Yii::t('app', 'Problem Data Path'), 'problem_data_path') ?>
        <?= Html::textInput('problem_data_path', $settings['problem_data_path'], ['class' => 'form-control']) ?>
        <p class="help-block">绝对路径．举例：/srv/http/jnuoj/backend/data</p>
        <p class="help-block">该目录需能读写，否则无法从 Web 端上传数据</p>
    </div>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('app', 'Save'), ['class' => 'btn btn-primary']) ?>
    </div>
    <?= Html::endForm(); ?>

</div>
