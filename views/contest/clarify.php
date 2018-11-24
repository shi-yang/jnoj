<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $form yii\widgets\ActiveForm */
/* @var $newClarify app\models\Discuss */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $discuss app\models\Discuss */

$this->title = $model->title;
$this->params['model'] = $model;

if ($discuss != null) {
    return $this->render('_clarify_view', [
        'clarify' => $discuss,
        'newClarify' => $newClarify
    ]);
}
?>
<div style="margin-top: 20px">
    <?php
    if ($dataProvider->count > 0) {
        echo GridView::widget([
            'layout' => '{items}{pager}',
            'dataProvider' => $dataProvider,
            'options' => ['class' => 'table-responsive', 'style' => 'margin:0 auto;width:50%;min-width:600px'],
            'columns' => [
                [
                    'attribute' => Yii::t('app', 'Announcement'),
                    'value' => function ($model, $key, $index, $column) {
                        return $model->content;
                    },
                    'format' => 'ntext',
                ],
                [
                    'attribute' => 'created_at',
                    'headerOptions' => ['width' => '34%'],
                    'format' => 'datetime'
                ]
            ],
        ]);
        echo '<hr>';
    }
    ?>
    <div class="well">
        如果你认为题目存在歧义，可以在这里提出．
    </div>

    <?= GridView::widget([
        'dataProvider' => $clarifies,
        'columns' => [
            [
                'attribute' => 'Who',
                'value' => function ($model, $key, $index, $column) {
                    return Html::a($model->user->colorname, ['/user/view', 'id' => $model->user->id]);
                },
                'format' => 'raw'
            ],
            [
                'attribute' => 'title',
                'value' => function ($model, $key, $index, $column) {
                    return Html::a(Html::encode($model->title), [
                        '/contest/clarify',
                        'id' => $model->entity_id,
                        'cid' => $model->id
                    ], ['data-pjax' => 0]);
                },
                'format' => 'raw'
            ],
            'created_at',
            'updated_at'
        ]
    ]); ?>

    <div class="well">
        <?php if ($model->getRunStatus() == \app\models\Contest::STATUS_RUNNING): ?>
        <?php $form = ActiveForm::begin(); ?>

        <?= $form->field($newClarify, 'title', [
            'template' => "{label}\n<div class=\"input-group\"><span class=\"input-group-addon\">" . Yii::t('app', 'Title') . "</span>{input}</div>{error}",
        ])->textInput(['maxlength' => 128, 'autocomplete'=>'off'])->label(false) ?>

        <?= $form->field($newClarify, 'content')->widget('app\widgets\ckeditor\CKeditor'); ?>

        <div class="form-group">
            <?= Html::submitButton(Yii::t('app', 'Submit'), ['class' => 'btn btn-primary']) ?>
        </div>
        <?php ActiveForm::end(); ?>
        <?php else: ?>
        <p>比赛已经结束</p>
        <?php endif; ?>
    </div>
</div>
