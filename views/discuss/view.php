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
        <h1 class="discuss-title"><?= Html::encode($model->title) ?></h1>
        <p>
            <span class="glyphicon glyphicon-user"></span> <?= Html::a(Html::encode($model->user->nickname), ['/user/view', 'id' => $model->user->username]) ?>
            &nbsp;•&nbsp;
            <span class="glyphicon glyphicon-time"></span> <?= Yii::$app->formatter->asRelativeTime($model->created_at) ?>
            <?php if (Yii::$app->user->id === $model->created_by): ?>
                &nbsp;•&nbsp;
                <span class="glyphicon glyphicon-edit"></span> <?= Html::a(Yii::t('app', 'Edit'), ['/discuss/update', 'id' => $model->id]) ?>
                &nbsp;•&nbsp;
                <span class="glyphicon glyphicon-trash"></span>
                <?= Html::a(Yii::t('app', 'Delete'), ['/discuss/delete', 'id' => $model->id], [
                    'data' => [
                        'confirm' => Yii::t('yii', 'Are you sure you want to delete this item?'),
                        'method' => 'post',
                    ],
                ]) ?>
            <?php endif; ?>
        </p>
        <hr>
        <?= Yii::$app->formatter->asHtml($model->content) ?>
        <hr>
        <p><?= Yii::t('app', 'Comments') ?>:</p>
        <?php foreach ($replies as $reply): ?>
            <div class="well">
                <?= Yii::$app->formatter->asHtml($reply->content) ?>
                <hr>
                <span class="glyphicon glyphicon-user"></span> <?= Html::a(Html::encode($reply->user->nickname), ['/user/view', 'id' => $reply->user->id]) ?>
                &nbsp;•&nbsp;
                <span class="glyphicon glyphicon-time"></span> <?= Yii::$app->formatter->asRelativeTime($reply->created_at) ?>
            </div>
        <?php endforeach; ?>
        <?= \yii\widgets\LinkPager::widget([
            'pagination' => $pages,
        ]); ?>
        <div class="well">
            <?php $form = ActiveForm::begin(); ?>

            <?= $form->field($newDiscuss, 'content')->widget('app\widgets\ckeditor\CKeditor')->label(false); ?>

            <div class="form-group">
                <?= Html::submitButton(Yii::t('app', 'Reply'), ['class' => 'btn btn-primary']) ?>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
    <div class="col-md-3">

    </div>
</div>
