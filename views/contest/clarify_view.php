<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $form yii\widgets\ActiveForm */
/* @var $clarify app\models\Discuss */
/* @var $newClarify app\models\Discuss */

$this->params['model'] = $model;
?>
<div style="padding-top: 20px">
    <div class="well">
        <?= Html::encode($clarify->title) ?>
        <hr>
        <?= Yii::$app->formatter->asMarkdown($clarify->content) ?>
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

        <?= $form->field($newClarify, 'content')->widget('app\widgets\editormd\Editormd', [
            'clientOptions' => [
                'placeholder' => 'description',
                'height' => 300,
                'imageUpload' => true,
                'tex' => true,
                'flowChart' => true,
                'sequenceDiagram' => true
            ]
        ])->label(); ?>

        <div class="form-group">
            <?= Html::submitButton(Yii::t('app', 'Reply'), ['class' => 'btn btn-primary']) ?>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
</div>
