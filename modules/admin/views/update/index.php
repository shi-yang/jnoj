<?php


/* @var $this yii\web\View */
/* @var $settings array */
/* @var $form yii\widgets\ActiveForm */
$this->title = Yii::t('app', 'Update');
?>
<h3>当前版本：<?= Yii::$app->setting->getVersion() ?> (<?= date("Y-m-d", filemtime(Yii::getAlias('@app/CHANGELOG.md'))) ?>)</h3>
<p>更新方法：<a href="https://github.com/shi-yang/jnoj/blob/master/docs/update.md" target="_blank">更新OJ教程</a></p>
<p>
    如果你在使用过程中发现 Bug，或者希望增加一些额外的功能，欢迎使用
    <a href="https://github.com/shi-yang/jnoj/issues" target="_blank">GitHub Issues</a> 来报告．
</p>
<p>项目主页：<a href="https://github.com/shi-yang/jnoj" target="_blank">JNOJ GitHub Project</a></p>
<hr>
<div>
    <?= Yii::$app->formatter->asMarkdown($changelog) ?>
</div>