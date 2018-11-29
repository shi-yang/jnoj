<?php

/* @var $this \yii\web\View */

/* @var $content string */
/* @var $model app\models\Contest */

use yii\helpers\Html;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\widgets\Breadcrumbs;
use app\assets\AppAsset;
use app\widgets\Alert;
use app\models\Contest;

AppAsset::register($this);

$model = $this->params['model'];
$status = $model->getRunStatus();
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="renderer" content="webkit">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
    <link rel="shortcut icon" href="<?= Yii::getAlias('@web') ?>/favicon.ico">
</head>
<body>
<?php $this->beginBody() ?>

<div class="wrap">
    <header id="header" class="hidden-xs">
        <div class="container">
            <div class="page-header">
                <div class="logo pull-left">
                    <div class="pull-left">
                        <a class="navbar-brand" href="<?= Yii::$app->request->baseUrl ?>">
                            <img src="<?= Yii::getAlias('@web') ?>/images/logo.png"/>
                        </a>
                    </div>
                    <div class="brand">
                        Online Judge
                    </div>
                </div>
                <div class="clearfix"></div>
            </div>
        </div>
    </header>
    <?php
    NavBar::begin([
        'brandLabel' => Yii::$app->params['ojName'] . ' OJ',
        'brandUrl' => Yii::$app->homeUrl,
        'options' => [
            'class' => 'navbar-default',
        ],
    ]);
    $menuItems = [
        ['label' => Yii::t('app', 'Home'), 'url' => ['/site/index']],
        ['label' => Yii::t('app', 'Problem'), 'url' => ['/problem/index']],
        ['label' => Yii::t('app', 'Status'), 'url' => ['/solution/index']],
        ['label' => Yii::t('app', 'Homework'), 'url' => ['/homework/index']],
        ['label' => Yii::t('app', 'Contest'), 'url' => ['/contest/index']],
        ['label' => 'Wiki', 'url' => ['/wiki/index']],
    ];
    if (Yii::$app->user->isGuest) {
        $menuItems[] = ['label' => Yii::t('app', 'Signup'), 'url' => ['/site/signup']];
        $menuItems[] = ['label' => Yii::t('app', 'Login'), 'url' => ['/site/login']];
    } else {
        if (Yii::$app->user->identity->role == \app\models\User::ROLE_ADMIN) {
            $menuItems[] = ['label' => Yii::t('app', 'Backend'), 'url' => ['/admin']];
        }
        $menuItems[] = [
            'label' => Yii::$app->user->identity->nickname,
            'items' => [
                ['label' => Yii::t('app', 'Profile'), 'url' => ['/user/view', 'id' => Yii::$app->user->id]],
                ['label' => Yii::t('app', 'Setting'), 'url' => ['/user/setting', 'action' => 'profile']],
                '<li class="divider"></li>',
                ['label' => Yii::t('app', 'Logout'), 'url' => ['/site/logout']],
            ]
        ];
    }
    echo Nav::widget([
        'options' => ['class' => 'navbar-nav navbar-right'],
        'items' => $menuItems,
    ]);
    NavBar::end();
    ?>

    <div class="container" id="contest-anchor">
        <?= Breadcrumbs::widget([
            'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
        ]) ?>
        <?= Alert::widget() ?>
        <div class="contest-info">
            <div class="row">
                <div class="col-md-3 text-left">
                    <strong>Start </strong>
                    <?= $model->start_time ?>
                </div>
                <div class="col-md-6 text-center">
                    <h2 class="contest-title">
                        <?= Html::encode($model->title) ?>
                        <?php if ($model->type == Contest::TYPE_HOMEWORK && !Yii::$app->user->isGuest && $model->created_by == Yii::$app->user->id): ?>
                        <small>
                            <?= Html::a('<span class="glyphicon glyphicon-cog"></span> ' . Yii::t('app', 'Setting'),
                                ['/homework/update', 'id' => $model->id]) ?>
                        </small>
                        <?php endif; ?>
                    </h2>
                </div>
                <div class="col-md-3 text-right">
                    <strong>End </strong>
                    <?= $model->end_time ?>
                </div>
            </div>
            <div class="progress">
                <div class="progress-bar progress-bar-success" id="contest-progress" role="progressbar" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100" style="width: 1%;">
                    <?php if ($status == $model::STATUS_NOT_START): ?>
                        Not start
                        <p><?= date('y-m-d H:i:s', time()) ?></p>
                    <?php elseif ($status == $model::STATUS_RUNNING): ?>
                        Running
                    <?php else: ?>
                        Contest is over.
                    <?php endif; ?>
                </div>
            </div>
            <div class="text-center"><strong>Now</strong> <span color=#993399><span id="nowdate"> <?php echo date("Y-m-d H:i:s") ?></span></span></div>
        </div>
        <hr>
        <?php if ($status == $model::STATUS_NOT_START): ?>
            <div class="contest-countdown text-center">
                <?= \russ666\widgets\Countdown::widget([
                    'datetime' => date('Y-m-d H:i:s O', strtotime($model->start_time)),
                    'format' => '%D:%H:%M:%S',
                    'events' => [
                        'finish' => 'function(){location.reload()}',
                    ],
                ]); ?>
            </div>
        <?php else: ?>
            <div class="contest-view">
                <?php
                $menuItems = [
                    [
                        'label' => '<span class="glyphicon glyphicon-home"></span> ' . Yii::t('app', 'Information'),
                        'url' => ['homework/view', 'id' => $model->id],
                    ],
                    [
                        'label' => '<span class="glyphicon glyphicon-list"></span> ' . Yii::t('app', 'Problem'),
                        'url' => ['homework/problem', 'id' => $model->id],
                        'linkOptions' => ['data-pjax' => 0]
                    ],
                    [
                        'label' => '<span class="glyphicon glyphicon-signal"></span> ' . Yii::t('app' , 'Status'),
                        'url' => ['homework/status', 'id' => $model->id],
                        'linkOptions' => ['data-pjax' => 0]
                    ],
                    [
                        'label' => '<span class="glyphicon glyphicon-glass"></span> ' . Yii::t('app', 'Standing'),
                        'url' => ['homework/standing', 'id' => $model->id],
                    ],
                    [
                        'label' => '<span class="glyphicon glyphicon-comment"></span> ' . Yii::t('app', 'Clarification'),
                        'url' => ['homework/clarify', 'id' => $model->id],
                    ],
                ];
                if ($model->getRunStatus() == $model::STATUS_ENDED) {
                    $menuItems[] = [
                        'label' => '<span class="glyphicon glyphicon-info-sign"></span> ' . Yii::t('app', 'Editorial'),
                        'url' => ['homework/editorial', 'id' => $model->id]
                    ];
                }
                echo Nav::widget([
                    'items' => $menuItems,
                    'options' => ['class' => 'nav nav-tabs'],
                    'encodeLabels' => false
                ]) ?>
                <?php \yii\widgets\Pjax::begin() ?>
                <?= $content ?>
                <?php \yii\widgets\Pjax::end() ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<footer class="footer">
    <div class="container">
        <p class="pull-left">&copy; <?= Yii::$app->params['ojName'] ?> OJ <?= date('Y') ?></p>
    </div>
</footer>
<?php $this->endBody() ?>
<script>
    var diff = new Date("<?= date("Y/m/d H:i:s")?>").getTime() - new Date().getTime();
    var start_time = new Date("<?= $model->start_time ?>");
    var end_time = new Date("<?= $model->end_time ?>");
    function clock() {
        var h, m, s, n, y, mon, d;
        var x = new Date(new Date().getTime() + diff);
        y = x.getYear() + 1900;
        if (y > 3000) y -= 1900;
        mon = x.getMonth() + 1;
        d = x.getDate();
        h = x.getHours();
        m = x.getMinutes();
        s = x.getSeconds();

        n = y + "-" + mon + "-" + d + " " + (h >= 10 ? h : "0" + h) + ":" + (m >= 10 ? m : "0" + m) + ":" + (s >= 10 ? s : "0" + s);
        var now_time = new Date(n);
        document.getElementById('nowdate').innerHTML = n;
        if (now_time < end_time) {
            var rate = (now_time - start_time) / (end_time - start_time) * 100;
            document.getElementById('contest-progress').style.width = rate + "%";
        } else {
            document.getElementById('contest-progress').style.width = "100%";
        }
        setTimeout("clock()", 1000);
    }
    clock();

    $(document).ready(function () {
        // 连接服务端
        var socket = io('http://' + document.domain + ':2120');
        var uid = <?= Yii::$app->user->isGuest ? session_id() : Yii::$app->user->id ?>
        // 连接后登录
        socket.on('connect', function(){
            socket.emit('login', uid);
        });
        // 后端推送来消息时
        socket.on('msg', function(msg){
            alert(msg);
        });
    });
</script>
</body>
</html>
<?php $this->endPage() ?>
