<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\bootstrap\Modal;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $searchModel app\models\UserSearch */

$this->title = Yii::t('app', 'Users');

$url = \yii\helpers\Url::to(['/admin/user/index', 'action' => \app\models\User::ROLE_USER]);
$roleUser = \app\models\User::ROLE_USER;
$roleVIP = \app\models\User::ROLE_VIP;
$statusDisable = \app\models\User::STATUS_DISABLE;
$statusActive = \app\models\User::STATUS_ACTIVE;
$js = <<<EOF
$("#general-user").on("click", function () {
    const keys = $("#grid").yiiGridView("getSelectedRows");
    $.post({
        url: "$url", 
        dataType: "json",
        data: {ids: keys, action: "setRole", value:"${roleUser}"}
    });
});
$("#vip-user").on("click", function () {
    const keys = $("#grid").yiiGridView("getSelectedRows");
    $.post({
        url: "$url", 
        dataType: "json",
        data: {ids: keys, action: "setRole", value:"${roleVIP}"}
    });
});
$("#disable-user").on("click", function () {
    const keys = $("#grid").yiiGridView("getSelectedRows");
    $.post({
        url: "$url", 
        dataType: "json",
        data: {ids: keys, action: "setStatus", value:"${statusDisable}"}
    });
});
$("#enable-info").on("click", function () {
    const keys = $("#grid").yiiGridView("getSelectedRows");
    $.post({
        url: "$url", 
        dataType: "json",
        data: {ids: keys, action: "setStatus", value:"${statusActive}"}
    });
});
EOF;
$this->registerJs($js);
?>
<div class="user-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <?php echo $this->render('_search', ['model' => $searchModel]); ?>

    <hr>
    <p>
        <?php Modal::begin([
            'header' => '<h2>' . Yii::t('app', '批量创建用户') . '</h2>',
            'toggleButton' => ['label' => Yii::t('app', '批量创建用户'), 'class' => 'btn btn-success'],
        ]);?>
        <?php $form = ActiveForm::begin(['options' => ['target' => '_blank']]); ?>

        <p class="hint-block">1. 一个用户占据一行，每行格式为<code>username password</code>，即用户名与密码之间有一个空格。自行删除多余的空行。</p>
        <p class="hint-block">2. 用户名只能以数字、字母、下划线，且非纯数字，长度在 4 - 32 位之间</p>
        <p class="hint-block">3. 密码至少六位</p>

        <?= $form->field($generatorForm, 'names')->textarea(['rows' => 10])  ?>

        <div class="form-group">
            <?= Html::submitButton(Yii::t('app', 'Generate'), ['class' => 'btn btn-success']) ?>
        </div>

        <?php ActiveForm::end(); ?>
        <?php Modal::end(); ?>

        选中项：
        <a id="general-user" class="btn btn-default" href="javascript:void(0);">
            设为普通用户
        </a>
        <a id="vip-user" class="btn btn-success" href="javascript:void(0);">
            设为VIP用户
        </a>
        <a id="disable-user" class="btn btn-warning" href="javascript:void(0);">
            禁用账号
        </a>
        <a id="enable-info" class="btn btn-success" href="javascript:void(0);">
            启用账号
        </a>
    </p>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'options' => ['id' => 'grid'],
        'columns' => [
            [
                'class' => 'yii\grid\CheckboxColumn',
                'name' => 'id',
            ],
            'id',
            'username',
            'nickname',
            'email:email',
            [
                'attribute' => 'status',
                'value' => function ($model, $key, $index, $column) {
                    return $model->getStatusLabel();
                },
                'format' => 'raw'
            ],
            [
                'attribute' => 'role',
                'value' => function ($model, $key, $index, $column) {
                    if ($model->role == \app\models\User::ROLE_PLAYER) {
                        return '参赛用户';
                    } else if ($model->role == \app\models\User::ROLE_USER) {
                        return '普通用户';
                    } else if ($model->role == \app\models\User::ROLE_VIP) {
                        return 'VIP 用户';
                    } else if ($model->role == \app\models\User::ROLE_ADMIN) {
                        return '管理员';
                    }
                    return 'not set';
                },
                'format' => 'raw'
            ],
            [
                'attribute' => Yii::t('app', 'class'),
                'value' => function ($model, $widget) {
                    return Html::encode($model->profile->class);
                },
                'format' => 'raw'
            ],
            // 'status',
            // 'created_at',
            // 'updated_at',
            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]);
    ?>
</div>
