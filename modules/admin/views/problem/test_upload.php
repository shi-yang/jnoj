<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;


/* @var $this yii\web\View */
/* @var $model app\models\Problem */
/* @var $upload app\modules\admin\models\UploadForm */
?>

<div class="solutions-view">

    <h1>
        <?= Html::encode($model->title) ?>
    </h1>

    <p class="bg-danger">
        一个标准输入文件对应一个标准输出文件，输入文件以＂.in＂结尾，输出文件以＂.out＂结尾，文件名任意取，
        但输入文件跟输出文件的文件名必须一一对应．比如一组样例: 输入文件文件名"apple.in"，输出文件文件名"apple.out"．
        如有多个测试点，可以分开不同的文件上传
    </p>

    <?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]); ?>

    <?= $form->field($upload, 'file[]')->fileInput(['multiple' => true])->hint('可以一次选择多个文件') ?>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('app', 'Upload'), ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>
