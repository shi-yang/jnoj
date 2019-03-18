<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $contests array */
/* @var $news app\models\Discuss */

$this->title = Yii::$app->setting->get('ojName') . ' Online Judge';
?>
<div class="row blog">
    <div class="col-md-8">
        <div class="jumbotron">
            <h1>Hello, world!</h1>
            <p>欢迎来到<?= Yii::$app->setting->get('schoolName') ?>在线判题系统——<?= Yii::$app->setting->get('ojName') ?> Online Judge</p>
        </div>
        <hr>
        <div class="blog-main">
            <?php foreach ($news as $v): ?>
                <div class="blog-post">
                    <h2 class="blog-post-title"><?= Html::a(Html::encode($v['title']), ['/site/news', 'id' => $v['id']]) ?></h2>
                    <p class="blog-post-meta">
                        <span class="glyphicon glyphicon-time"></span> <?= Yii::$app->formatter->asDate($v['created_at']) ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <div class="col-md-4">
        <div class="sidebar-module sidebar-module-inset">
            <h4>关于</h4>
            <p>Online Judge系统（简称OJ）是一个在线的判题系统。 用户可以在线提交程序多种程序（如C、C++、Java）源代码，系统对源代码进行编译和执行， 并通过预先设计的测试数据来检验程序源代码的正确性。</p>
        </div>
        <?php if (!empty($contests)): ?>
        <div class="sidebar-module">
            <h4>最近比赛</h4>
            <ol class="list-unstyled">
                <?php foreach ($contests as $contest): ?>
                <li>
                    <?= Html::a(Html::encode($contest['title']), ['/contest/view', 'id' => $contest['id']]) ?>
                </li>
                <?php endforeach; ?>
            </ol>
        </div>
        <?php endif; ?>
        <?php if (!empty($discusses)): ?>
            <div class="sidebar-module">
                <h4>最近讨论</h4>
                <ol class="list-unstyled">
                    <?php foreach ($discusses as $discuss): ?>
                        <li class="index-discuss-item">
                            <div>
                                <?= Html::a(Html::encode($discuss['title']), ['/discuss/view', 'id' => $discuss['id']]) ?>
                            </div>
                            <small class="text-muted">
                                <span class="glyphicon glyphicon-user"></span>
                                <?= Html::a(Html::encode($discuss['nickname']), ['/user/view', 'id' => $discuss['username']]) ?>
                                &nbsp;•&nbsp;
                                <span class="glyphicon glyphicon-time"></span> <?= Yii::$app->formatter->asRelativeTime($discuss['created_at']) ?>
                                &nbsp;•&nbsp;
                                <?= Html::a(Html::encode($discuss['ptitle']), ['/problem/view', 'id' => $discuss['pid']]) ?>
                            </small>
                        </li>
                    <?php endforeach; ?>
                </ol>
            </div>
        <?php endif; ?>
    </div>
</div>
