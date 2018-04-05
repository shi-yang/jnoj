<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;


/* @var $this yii\web\View */
/* @var $model app\models\Homework */

$this->title = Yii::t('app', 'Create Homework');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Homework'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="homework-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <div class="homework-form">

        <?php $form = ActiveForm::begin(); ?>

        <?= $form->field($model, 'title')->textInput(['maxlength' => true]) ?>

        <?= $form->field($model, 'start_time')->widget('app\widgets\laydate\LayDate', [
            'clientOptions' => [
                'istoday' => true,
                'type' => 'datetime'
            ]
        ]) ?>

        <?= $form->field($model, 'end_time')->widget('app\widgets\laydate\LayDate', [
            'clientOptions' => [
                'istoday' => true,
                'type' => 'datetime'
            ]
        ]) ?>

        <div class="form-group">
            <?= Html::submitButton($model->isNewRecord ? Yii::t('app', 'Create') : Yii::t('app', 'Save'), ['class' => 'btn btn-success']) ?>
        </div>

        <?php ActiveForm::end(); ?>

    </div>

</div>
