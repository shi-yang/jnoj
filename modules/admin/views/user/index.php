<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\bootstrap\Modal;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $searchModel app\models\UserSearch */

$this->title = Yii::t('app', 'Users');
?>
<div class="user-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <?php echo $this->render('_search', ['model' => $searchModel]); ?>

    <hr>
    <p>
        选中项：
        <a id="general-user" class="btn btn-success" href="javascript:void(0);">
            设为普通用户
        </a>
        <a id="vip-user" class="btn btn-success" href="javascript:void(0);">
            设为VIP用户
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
            // 'status',
            // 'created_at',
            // 'updated_at',
            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]);
    $this->registerJs('
    $("#general-user").on("click", function () {
        var keys = $("#grid").yiiGridView("getSelectedRows");
        $.post({
           url: "'.\yii\helpers\Url::to(['/admin/user/index', 'action' => \app\models\User::ROLE_USER]).'", 
           dataType: \'json\',
           data: {keylist: keys}
        });
    });
    $("#vip-user").on("click", function () {
        var keys = $("#grid").yiiGridView("getSelectedRows");
        $.post({
           url: "'.\yii\helpers\Url::to(['/admin/user/index', 'action' => \app\models\User::ROLE_VIP]).'", 
           dataType: \'json\',
           data: {keylist: keys}
        });
    });
    ');
    ?>
</div>
