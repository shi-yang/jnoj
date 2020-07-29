<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\modules\polygon\models\Problem */

$this->title = $model->title;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Problems'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
$this->params['model'] = $model;
?>
<div class="problem-solution">
    <p>
        您可以在此处为题目编写详细的解答过程。
    </p>
    <hr>

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'solution')->widget('app\widgets\editormd\Editormd')->label(false) ?>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('app', 'Save'), ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>
