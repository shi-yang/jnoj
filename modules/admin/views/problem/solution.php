<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\Problem */

$this->title = Yii::t('app', $model->title);
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Problems'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->title, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('app', 'Update');
$this->params['model'] = $model;
?>

<div class="problem-solution">
    <h1>
        <?= Html::encode($model->title) ?>
    </h1>
    <p>
        您可以在此处为题目编写详细的解答过程。注意，若题目内容是被用户可见的，那么此处填写的题解也会被用户可见。查看题解按钮会出现在前台题目详情页面中。
    </p>
    <hr>

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'solution')->widget('app\widgets\editormd\Editormd')->label(false) ?>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('app', 'Save'), ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>
