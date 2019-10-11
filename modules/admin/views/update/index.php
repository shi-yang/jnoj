<?php


/* @var $this yii\web\View */
/* @var $settings array */
/* @var $form yii\widgets\ActiveForm */
$this->title = Yii::t('app', 'Update');
?>
<h1>当前版本：<?= date("Y.m.d", filemtime(Yii::getAlias('@app/CHANGELOG.md'))) ?></h1>
<p>更新方法：<a href="https://github.com/shi-yang/jnoj/blob/master/docs/update.md" target="_blank">更新OJ教程</a></p>
<p>
    如果你在使用过程中发现 Bug，或者希望增加一些额外的功能，欢迎使用
    <a href="https://github.com/shi-yang/jnoj/issues" target="_blank">GitHub Issues</a> 来报告．
</p>
<p>项目主页：<a href="https://github.com/shi-yang/jnoj" target="_blank">JNOJ GitHub Project</a></p>
<hr>
<p>
    提示：当前页面打开可能会有些慢，是因为为了保证可以及时看到开发的变化，
    以下内容是读取自
    <a href="https://github.com/shi-yang/jnoj/blob/master/CHANGELOG.md" target="_blank">https://github.com/shi-yang/jnoj/blob/master/CHANGELOG.md</a>
    这个链接下的文件，在读取过程中访问慢导致的。
</p>
<hr>
<div>
    <?= Yii::$app->formatter->asMarkdown($changelog) ?>
</div>