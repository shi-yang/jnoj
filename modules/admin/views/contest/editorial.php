<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use yii\widgets\ActiveForm;
use yii\bootstrap\Modal;
use app\models\Contest;

/* @var $this yii\web\View */
/* @var $model app\models\Contest */

$this->title = $model->title;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Contests'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $this->title, 'url' => ['view', 'id' => $model->id]];
?>
<div class="contest-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <hr>

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'editorial')->widget('app\widgets\editormd\Editormd', [
        'clientOptions' => [
            'placeholder' => 'Editorial',
            'height' => 300,
            'imageUpload' => true,
            'tex' => true,
            'flowChart' => true,
            'sequenceDiagram' => true
        ]
    ])->label(false)->hint('在此填写比赛题解，题解内容将在比赛结束后，才会出现在前台的比赛页面中；只有过了比赛结束时间，用户才能查看题解'); ?>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('app', 'Save'), ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>