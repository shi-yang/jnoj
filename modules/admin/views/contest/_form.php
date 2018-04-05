<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\Contest */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="contest-form">

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

    <?= $form->field($model, 'lock_board_time')->widget('app\widgets\laydate\LayDate', [
        'clientOptions' => [
            'istoday' => true,
            'type' => 'datetime'
        ]
    ])->hint('如果不需要封榜请留空') ?>

    <?= $form->field($model, 'status')->radioList([
        1 => Yii::t('app', 'Visible'),
        0 => Yii::t('app', 'Hidden')
    ])->hint('是否在前台的比赛列表页面显示') ?>

    <?= $form->field($model, 'scenario')->radioList([
        $model::SCENARIO_ONLINE => Yii::t('app', 'Online'),
        $model::SCENARIO_OFFLINE => Yii::t('app', 'Offline'),
    ])->hint('线下场景会有额外的功能：滚榜；在该比赛的页面开放打印链接；限定参赛账号') ?>

<?php //echo $form->field($model, 'type')->radioList([
//        $model::TYPE_EDUCATIONAL => Yii::t('app', 'Educational'),
//        $model::TYPE_RANK_SINGLE => Yii::t('app', 'Ranked'),
//        $model::TYPE_RANK_GROUP => Yii::t('app', 'Group Ranked'),
//    ]) ?>

    <?= $form->field($model, 'description')->textarea(['rows' => 6]) ?>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('app', 'Save'), ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
