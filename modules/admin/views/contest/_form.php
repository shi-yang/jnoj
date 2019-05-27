<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\models\Contest;

/* @var $this yii\web\View */
/* @var $model app\models\Contest */
/* @var $form yii\widgets\ActiveForm */
$scoreboardFrozenTime = Yii::$app->setting->get('scoreboardFrozenTime') / 3600;
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
    ])->hint("如果不需要封榜请留空，当前会在比赛结束{$scoreboardFrozenTime}小时后才会自动在前台页面解除封榜限制。
        如需提前结束封榜也可选择清空该表单项。使用封榜功能，后台管理界面的比赛榜单仍然处于实时榜单。
        <p class='text-danger'>注意：比赛类型为OI时，如果不填写“封榜时间”则会成为实时榜单。如需要非实时榜单，则填写为开始时间或开始时间之前的时间即可。</p>") ?>

    <?= $form->field($model, 'status')->radioList([
        Contest::STATUS_VISIBLE => Yii::t('app', 'Public'),
        Contest::STATUS_PRIVATE => Yii::t('app', 'Private'),
        Contest::STATUS_HIDDEN => Yii::t('app', 'Hidden')
    ])->hint('公开：任何用户均可参加比赛（线下赛场景除外）。私有：任何时候比赛均只能由参赛用户访问，且比赛用户需要在后台手动添加。隐藏：前台无法看到比赛') ?>

    <?= $form->field($model, 'scenario')->radioList([
        $model::SCENARIO_ONLINE => Yii::t('app', 'Online'),
        $model::SCENARIO_OFFLINE => Yii::t('app', 'Offline'),
    ])->hint('线下场景会有额外的功能：滚榜；在该比赛的页面开放打印链接；限定参赛账号．' . '参考：' . Html::a('线下赛与线上赛的区别', ['/wiki/contest'], ['target' => '_blank'])) ?>

    <?= $form->field($model, 'type')->radioList([
        Contest::TYPE_RANK_SINGLE => Yii::t('app', 'Single Ranked'),
        Contest::TYPE_RANK_GROUP => Yii::t('app', 'ICPC'),
        Contest::TYPE_HOMEWORK => Yii::t('app', 'Homework'),
        Contest::TYPE_OI => Yii::t('app', 'OI'),
        Contest::TYPE_IOI => Yii::t('app', 'IOI'),
    ])->hint('不同类型的区别只在于榜单的排名方式。详见：' . Html::a('比赛类型', ['/wiki/contest'], ['target' => '_blank']) . '。如需使用OI比赛，请在后台设置页面启用OI模式。') ?>


    <?= $form->field($model, 'description')->widget('app\widgets\ckeditor\CKeditor'); ?>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('app', 'Save'), ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
