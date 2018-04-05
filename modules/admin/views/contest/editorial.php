<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use yii\widgets\ActiveForm;
use yii\bootstrap\Modal;
use app\models\Contest;

/* @var $this yii\web\View */
/* @var $model app\models\Contest */
/* @var $newAnnouncement app\models\ContestAnnouncement */
/* @var $announcements yii\data\ActiveDataProvider */

$this->title = $model->title;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Contests'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $this->title, 'url' => ['view', 'id' => $model->id]];
?>
<div class="contest-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'editorial')->widget('app\widgets\editormd\Editormd', [
        'clientOptions' => [
            'placeholder' => 'output',
            'height' => 300,
            'imageUpload' => true,
            'tex' => true,
            'flowChart' => true,
            'sequenceDiagram' => true
        ]
    ])->label(); ?>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('app', 'Save'), ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>