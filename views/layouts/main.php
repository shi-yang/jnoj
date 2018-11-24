<?php

/* @var $this \yii\web\View */
/* @var $content string */

use app\widgets\Alert;
use yii\helpers\Html;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\widgets\Breadcrumbs;
use app\assets\AppAsset;

AppAsset::register($this);
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
                            <img src="<?= Yii::getAlias('@web') ?>/images/logo.png" />
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
        ['label' => '<span class="glyphicon glyphicon-home"></span> ' . Yii::t('app', 'Home'), 'url' => ['/site/index']],
        ['label' => '<span class="glyphicon glyphicon-list"></span> ' . Yii::t('app', 'Problems'), 'url' => ['/problem/index']],
        ['label' => '<span class="glyphicon glyphicon-signal"></span> ' . Yii::t('app', 'Status'), 'url' => ['/solution/index']],
        ['label' => '<span class="glyphicon glyphicon-king"></span> ' . Yii::t('app', 'Rating'), 'url' => ['/rating/index']],
        ['label' => '<span class="glyphicon glyphicon-book"></span> ' . Yii::t('app', 'Homework'), 'url' => ['/homework/index']],
        ['label' => '<span class="glyphicon glyphicon-knight"></span> ' . Yii::t('app', 'Contests'), 'url' => ['/contest/index']],
        [
            'label' => '<span class="glyphicon glyphicon-info-sign"></span> Wiki',
            'url' => ['/wiki/index'],
            'active' => Yii::$app->controller->id == 'wiki'
        ],
    ];
    if (Yii::$app->user->isGuest) {
        $menuItems[] = ['label' => '<span class="glyphicon glyphicon-new-window"></span> ' . Yii::t('app', 'Signup'), 'url' => ['/site/signup']];
        $menuItems[] = ['label' => '<span class="glyphicon glyphicon-log-in"></span> ' . Yii::t('app', 'Login'), 'url' => ['/site/login']];
    } else {
        if (Yii::$app->user->identity->role == \app\models\User::ROLE_ADMIN) {
            $menuItems[] = [
                'label' => '<span class="glyphicon glyphicon-cog"></span> ' . Yii::t('app', 'Backend'),
                'url' => ['/admin'],
                'active' => Yii::$app->controller->module->id == 'admin'
            ];
        }
        $menuItems[] =  [
            'label' => '<span class="glyphicon glyphicon-user"></span> ' . Yii::$app->user->identity->nickname,
            'items' => [
                ['label' => '<span class="glyphicon glyphicon-home"></span> ' . Yii::t('app', 'Profile'), 'url' => ['/user/view', 'id' => Yii::$app->user->id]],
                ['label' => '<span class="glyphicon glyphicon-cog"></span> ' . Yii::t('app', 'Setting'), 'url' => ['/user/setting', 'action' => 'profile']],
                '<li class="divider"></li>',
                ['label' => '<span class="glyphicon glyphicon-log-out"></span> ' . Yii::t('app', 'Logout'), 'url' => ['/site/logout']],
            ]
        ];
    }
    echo Nav::widget([
        'options' => ['class' => 'navbar-nav navbar-right'],
        'items' => $menuItems,
        'encodeLabels' => false,
        'activateParents' => true
    ]);
    NavBar::end();
    ?>

    <div class="container">
        <?= Breadcrumbs::widget([
            'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
        ]) ?>
        <?= Alert::widget() ?>
        <?= $content ?>
    </div>
</div>

<footer class="footer">
    <div class="container">
        <p class="pull-left">&copy; <?= Yii::$app->params['ojName'] ?> OJ <?= date('Y') ?></p>
    </div>
</footer>

<?php $this->endBody() ?>
<script type="text/javascript">
    (function ($) {
        $(document).ready(function () {
            $(".katex.math.inline").each(function () {
                var parent = $(this).parent()[0];
                if (parent.localName !== "code") {
                    var texTxt = $(this).text();
                    var el = $(this).get(0);
                    try {
                        katex.render(texTxt, el);
                    } catch (err) {
                        $(this).html("<span class=\'err\'>" + err);
                    }
                } else {
                    $(this).parent().text($(this).parent().text());
                }
            });
            $(".katex.math.multi-line").each(function () {
                var texTxt = $(this).text();
                var el = $(this).get(0);
                try {
                    katex.render(texTxt, el, {displayMode: true})
                } catch (err) {
                    $(this).html("<span class=\'err\'>" + err)
                }
            });
            $('.pre p').each(function(i, block) {  // use <pre><p>
                hljs.highlightBlock(block);
            });
        })
    })(jQuery);
</script>
</body>
</html>
<?php $this->endPage() ?>
