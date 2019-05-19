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
    <?php if ($model->spj): ?>
        <p>
            如果该题目需要特判的，请在下面填写特判程序。参考：<?= Html::a('如何编写特判程序？', ['/wiki/spj']) ?>
        </p>
        <hr>

        <?= Html::beginForm() ?>

        <div class="form-group">
            <?= Html::textInput('spjLang', 'C、C++', ['disabled' => true, 'class' => 'form-control']); ?>
            <p class="hint-block">当前仅支持 C\C++ 语言。</p>
        </div>

        <div class="form-group">
            <?= Html::label(Yii::t('app', 'Spj'), 'spj', ['class' => 'sr-only']) ?>

            <?= \app\widgets\codemirror\CodeMirror::widget(['name' => 'spjContent', 'value' => $spjContent]);  ?>
        </div>

        <div class="form-group">
            <?= Html::submitButton(Yii::t('app', 'Submit'), ['class' => 'btn btn-primary']) ?>
        </div>
        <?= Html::endForm(); ?>
    <?php else: ?>
        <p>当前题目不是 SPJ 判题，如需启用 SPJ 判题，请先到题目信息编辑页面将 Special Judge 改为是。</p>
    <?php endif; ?>
</div>
