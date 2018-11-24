<?php

use yii\bootstrap\Nav;

/* @var $this \yii\web\View */
/* @var $content string */

$this->title = 'Wiki';
?>
<?php $this->beginContent('@app/views/layouts/main.php'); ?>
<div class="row">
    <div class="col-md-3">
        <?= Nav::widget([
            'items' => [
                ['label' => Yii::t('app', 'OJ 信息'), 'url' => ['wiki/index']],
                ['label' => Yii::t('app', 'Contest'), 'url' => ['wiki/contest']],
                ['label' => 'OJ设计与实现', 'url' => ['wiki/design']],
                ['label' => '出题要求', 'url' => ['wiki/problem']],
                ['label' => Yii::t('app', 'About'), 'url' => ['wiki/about']]
            ],
            'options' => ['class' => 'nav nav-pills nav-stacked']
        ]) ?>
    </div>
    <div class="col-md-9">
        <div class="wiki-contetn">
            <?= $content ?>
        </div>
    </div>
</div>
<?php $this->endContent(); ?>

