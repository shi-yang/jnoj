<?php

use yii\helpers\Html;
use app\models\Solution;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\Problem */
/* @var $spjContent string */

$this->title = $model->title;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Problems'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
$this->params['model'] = $model;
?>
<div class="solutions-view">
    <h1>
        <?= Html::encode($model->title) ?>
    </h1>
    <?php if (Yii::$app->setting->get('oiMode')): ?>
        <p>
            如果题目需要配置子任务的，可以在下面填写子任务的配置。参考：<?= Html::a('子任务配置要求', ['/wiki/oi']) ?>
        </p>
        <hr>

        <?= Html::beginForm() ?>

        <div class="form-group">
            <?= Html::label(Yii::t('app', 'Subtask'), 'subtaskContent', ['class' => 'sr-only']) ?>

            <?= \app\widgets\codemirror\CodeMirror::widget(['name' => 'subtaskContent', 'value' => $subtaskContent]);  ?>
        </div>

        <div class="form-group">
            <?= Html::submitButton(Yii::t('app', 'Submit'), ['class' => 'btn btn-primary']) ?>
        </div>
        <?= Html::endForm(); ?>
    <?php else: ?>
        <p>当前 OJ 运行模式不是 OI 模式，要启用子任务编辑，需要在后台设置页面启用 OI 模式。</p>
    <?php endif; ?>
</div>
