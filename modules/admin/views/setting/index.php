<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $settings array */
/* @var $form yii\widgets\ActiveForm */
$this->title = Yii::t('app', 'Setting');

?>

<div class="setting-form">
    <h1><?= Html::encode($this->title) ?></h1>

    <hr>
    <?= Html::beginForm() ?>

    <div class="form-group">
        <?= Html::label(Yii::t('app', 'OJ名称'), 'ojName') ?>
        <?= Html::textInput('ojName', $settings['ojName'], ['class' => 'form-control']) ?>
        <p class="hint-block">OJ名称，这里填写 ‘<?= $settings['ojName'] ?>’ 则表示 `<?= $settings['ojName'] ?>OJ` 或者 `<?= $settings['ojName'] ?>Online Judge`</p>
    </div>

    <div class="form-group">
        <?= Html::label(Yii::t('app', 'OI 模式'), 'oiMode') ?>
        <?= Html::radioList('oiMode', $settings['oiMode'], [
            1 => '是',
            0 => '否'
        ]) ?>
        <p class="hint-block">
            注意，如需启动 OI 模式，除了在此处选择是外，还需要在启动判题服务时加上 -o 参数。
        </p>
        <p class="hint-block">即需要在 jnoj/judge 目录下通过 <code>sudo ./disptacher -o</code>来启动判题服务。</p>
    </div>

    <div class="form-group">
        <?= Html::label(Yii::t('app', '学校名称'), 'ojName') ?>
        <?= Html::textInput('schoolName', $settings['schoolName'], ['class' => 'form-control']) ?>
    </div>

    <div class="form-group">
        <?= Html::label(Yii::t('app', '是否要共享代码'), 'isShareCode') ?>
        <?= Html::radioList('isShareCode', $settings['isShareCode'], [
            1 => '用户可以查看其他用户的代码',
            0 => '用户的代码只能由自己或者管理员查看'
        ]) ?>
    </div>

    <div class="form-group">
        <?= Html::label(Yii::t('app', '封榜时间'), 'scoreboardFrozenTime') ?>
        <?= Html::textInput('scoreboardFrozenTime', $settings['scoreboardFrozenTime'], ['class' => 'form-control']) ?>
        <p class="hint-block">单位：秒。这个时间是从比赛结束后开始计算，如值为
            <?= $settings['scoreboardFrozenTime'] ?> 时，表示比赛结束 <?= intval($settings['scoreboardFrozenTime'] / 3600) ?> 个小时后不再封榜。
        </p>
    </div>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('app', 'Save'), ['class' => 'btn btn-primary']) ?>
    </div>
    <?= Html::endForm(); ?>

</div>
