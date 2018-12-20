<?php

/* @var $this \yii\web\View */
/* @var $content string */

use yii\bootstrap\Nav;
?>
<?php $this->beginContent('@app/views/layouts/main.php'); ?>
<div class="col-md-2">
    <?= Nav::widget([
        'options' => ['class' => 'nav nav-pills nav-stacked'],
        'items' => [
            ['label' => Yii::t('app', 'Home'), 'url' => ['/admin/default/index']],
            ['label' => Yii::t('app', 'News'), 'url' => ['/admin/news/index']],
            ['label' => Yii::t('app', 'Problem'), 'url' => ['/admin/problem/index']],
            ['label' => Yii::t('app', 'User'), 'url' => ['/admin/user/index']],
            ['label' => Yii::t('app', 'Contest'), 'url' => ['/admin/contest/index']],
            ['label' => Yii::t('app', 'Rejudge'), 'url' => ['/admin/rejudge/index']],
            ['label' => Yii::t('app', 'Polygon System'), 'url' => ['/polygon']],
            ['label' => 'OJ ' . Yii::t('app', 'Update'), 'url' => ['/admin/update/index']]
        ],
    ]) ?>
</div>
<div class="col-md-10">
    <?= $content ?>
</div>
<?php $this->endContent(); ?>
<script type="text/javascript">
    $(document).ready(function () {
        // 连接服务端
        var socket = io(document.location.protocol + '://' + document.domain + ':2120');
        var uid = <?= Yii::$app->user->isGuest ? session_id() : Yii::$app->user->id ?>
            // 连接后登录
            socket.on('connect', function () {
                socket.emit('login', uid);
            });
        // 后端推送来消息时
        socket.on('msg', function (msg) {
            alert(msg);
        });
    })
</script>
