<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $model app\models\Discuss */
/* @var $newDiscuss app\models\Discuss */

$this->title = $model->title;

$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Problem'), 'url' => ['/problem/index']];
$this->params['breadcrumbs'][] = ['label' => Html::encode($model->problem->title), 'url' => ['problem/view', 'id' => $model->problem->id]];
?>
<div class="row">
    <div class="col-md-9">
        <h1><?= Html::encode($model->title) ?></h1>
        <p>
            <span class="glyphicon glyphicon-user"></span> <?= $model->user->username ?>
            &nbsp;•&nbsp;
            <span class="glyphicon glyphicon-time"></span> <?= Yii::$app->formatter->asRelativeTime($model->created_at) ?>
        </p>
        <hr>
        <?= Yii::$app->formatter->asHtml($model->content) ?>
        <hr>
        <p>Comments:</p>
        <?php foreach ($model->reply as $reply): ?>
            <div class="well">
                <?= Yii::$app->formatter->asHtml($reply->content) ?>
                <hr>
                <span class="glyphicon glyphicon-user"></span> <?= Html::encode($reply->user->username) ?>
                &nbsp;•&nbsp;
                <span class="glyphicon glyphicon-time"></span> <?= Yii::$app->formatter->asRelativeTime($reply->created_at) ?>
            </div>
        <?php endforeach; ?>
        <div class="well">
            <?php $form = ActiveForm::begin(); ?>

            <?= $form->field($newDiscuss, 'content')->widget('app\widgets\ckeditor\CKeditor'); ?>

            <div class="form-group">
                <?= Html::submitButton(Yii::t('app', 'Reply'), ['class' => 'btn btn-primary']) ?>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
    <div class="col-md-3">

    </div>
</div>
