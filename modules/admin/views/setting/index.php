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
        <p class="hint-block">即需要在 jnoj/judge 目录下通过 <code>sudo ./dispatcher -o</code>来启动判题服务。</p>
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

    <hr>
    <div class="form-horizontal">
        <h4>配置 SMTP 发送邮箱</h4>
        <p class="hint-block">
            在用户忘记密码时，需要通过此处配置的邮箱来发送"重置密码"的邮箱给用户。
            若使用默认的 "no-reply@jnoj.org"，不能保证此默认邮箱长期可用，建议自行配置自己的邮箱。
        </p>

        <div class="form-group">
            <?= Html::label('邮箱验证码有效时间', 'passwordResetTokenExpire', ['class' => 'col-sm-2 control-label']) ?>
            <div class="col-sm-10">
                <?= Html::textInput('passwordResetTokenExpire', $settings['passwordResetTokenExpire'], ['class' => 'form-control']) ?>
                <p class="hint-block">单位：秒。即 <?= intval($settings['passwordResetTokenExpire'] / 3600) ?> 小时后，用户邮箱确认链接失效。</p>
            </div>
        </div>
        <div class="form-group">
            <?= Html::label('是否要验证邮箱？', 'mustVerifyEmail', ['class' => 'col-sm-2 control-label']) ?>
            <div class="col-sm-10">
                <?= Html::radioList('mustVerifyEmail', $settings['mustVerifyEmail'], [
                    1 => '新注册用户必须验证邮箱，且更改邮箱后必须验证邮箱',
                    0 => '否'
                ]) ?>
            </div>
        </div>
        <div class="form-group">
            <?= Html::label('Host', 'emailHost', ['class' => 'col-sm-2 control-label']) ?>
            <div class="col-sm-10">
                <?= Html::textInput('emailHost', $settings['emailHost'], ['class' => 'form-control', 'placeholder' => 'smtp.exmail.qq.com']) ?>
            </div>
        </div>
        <div class="form-group">
            <?= Html::label('Username', 'emailUsername', ['class' => 'col-sm-2 control-label']) ?>
            <div class="col-sm-10">
                <?= Html::textInput('emailUsername', $settings['emailUsername'], ['class' => 'form-control', 'placeholder' => 'no-reply@jnoj.org']) ?>
            </div>
        </div>
        <div class="form-group">
            <?= Html::label('Password', 'emailPassword', ['class' => 'col-sm-2 control-label']) ?>
            <div class="col-sm-10">
                <?= Html::textInput('emailPassword', $settings['emailPassword'], ['class' => 'form-control', 'placeholder' => 'you_password']) ?>
            </div>
        </div>
        <div class="form-group">
            <?= Html::label('Port', 'emailPort', ['class' => 'col-sm-2 control-label']) ?>
            <div class="col-sm-10">
                <?= Html::textInput('emailPort', $settings['emailPort'], ['class' => 'form-control', 'placeholder' => '465']) ?>
            </div>
        </div>
        <div class="form-group">
            <?= Html::label('Encryption', 'emailEncryption', ['class' => 'col-sm-2 control-label']) ?>
            <div class="col-sm-10">
                <?= Html::textInput('emailEncryption', $settings['emailEncryption'], ['class' => 'form-control', 'placeholder' => 'ssl']) ?>
            </div>
        </div>
    </div>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('app', 'Save'), ['class' => 'btn btn-primary']) ?>
    </div>
    <?= Html::endForm(); ?>

</div>
