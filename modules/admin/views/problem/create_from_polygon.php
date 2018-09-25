<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\models\Problem */

$this->title = Yii::t('app', 'Import Problem From Polygon System');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Problems'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="problem-create">

    <h1><?= Html::encode($this->title) ?></h1>
    <hr>
    <?= Html::beginForm() ?>
    <?= Html::label(Yii::t('app', 'Polygon Problem ID'), 'polygon_problem_id') ?>
    <?= Html::textInput('polygon_problem_id', '', ['class' => 'form-control']) ?>
    <p class="help-block">请提供位于 <?= Html::a(Yii::t('app', 'Polygon System'), ['/polygon/problem']) ?> 问题对应的 ID</p>
    <p class="help-block">
        注意：当你重复添加同一个位于 <?= Html::a(Yii::t('app', 'Polygon System'), ['/polygon/problem']) ?> 问题对应的 ID 会覆盖现有题库的题目内容及测试数据。
        如果涉及需要修改题面或数据时，可以使用重复添加来实现Polygon中的题目与题库中题目的同步。
    </p>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('app', 'Submit'), ['class' => 'btn btn-success']) ?>
    </div>
    <?= Html::endForm() ?>
</div>
