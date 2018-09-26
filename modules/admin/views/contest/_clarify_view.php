<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\grid\GridView;
use yii\bootstrap\Modal;

/* @var $this yii\web\View */
/* @var $form yii\widgets\ActiveForm */
/* @var $data array */

?>
<div style="padding-top: 20px">
    <div class="well">
        <?php $form = ActiveForm::begin(); ?>
        <?= $form->field($clarify, 'status')->radioList([
            1 => Yii::t('app', '对所有人可见'),
            2 => Yii::t('app', '仅对提问人可见')
        ])?>
        <div class="form-group">
            <?= Html::submitButton(Yii::t('app', 'Save'), ['class' => 'btn btn-primary']) ?>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
    <div class="well">
        <?= Html::encode($clarify->title) ?>
        <hr>
        <?= Yii::$app->formatter->asHtml($clarify->content) ?>
        <hr>
        <span class="glyphicon glyphicon-user"></span> <?= $clarify->user->username ?>
        &nbsp;•&nbsp;
        <span class="glyphicon glyphicon-time"></span> <?= Yii::$app->formatter->asRelativeTime($clarify->created_at) ?>
    </div>
    <?php foreach ($clarify->reply as $reply): ?>
        <div class="well">
            <?= Yii::$app->formatter->asMarkdown($reply->content) ?>
            <hr>
            <span class="glyphicon glyphicon-user"></span> <?= Html::encode($reply->user->username) ?>
            &nbsp;•&nbsp;
            <span class="glyphicon glyphicon-time"></span> <?= Yii::$app->formatter->asRelativeTime($reply->created_at) ?>
        </div>
    <?php endforeach; ?>
    <div class="well">
        <?php $form = ActiveForm::begin(); ?>

        <?= $form->field($new_clarify, 'content')->widget('app\widgets\ckeditor\CKeditor'); ?>

        <div class="form-group">
            <?= Html::submitButton(Yii::t('app', 'Reply'), ['class' => 'btn btn-primary']) ?>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
</div>
