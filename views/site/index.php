<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $contests array */
/* @var $news app\models\Discuss */

$this->title = '江南 Online Judge';
?>
<div class="row blog">
    <div class="col-md-8">
        <div class="jumbotron">
            <h1>Hello, world!</h1>
            <p>欢迎来到江南大学在线判题系统——江南 Online Judge</p>
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
            <h4>About</h4>
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
        <div class="sidebar-module">
            <h4>ACM-ICPC竞赛</h4>
            <ol class="list-unstyled">
                <li>
                    <?= Html::a('校队介绍', ['/site/construction']) ?>
                </li>
                <li>
                    <?= Html::a('竞赛成绩', ['/site/construction']) ?>
                </li>
                <li>
                    <?= Html::a('赛场照片', ['/site/construction']) ?>
                </li>
            </ol>
        </div>
    </div>
</div>
