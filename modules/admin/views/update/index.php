<?php


/* @var $this yii\web\View */
/* @var $settings array */
/* @var $form yii\widgets\ActiveForm */
$this->title = Yii::t('app', 'Update');
?>
<h3>当前版本：<?= Yii::$app->setting->getVersion() ?></h3>
<h3>更新方法：<a href="https://github.com/shi-yang/jnoj/blob/master/docs/update.md" target="_blank">更新OJ教程</a></h3>
<hr>
<div>
    <?= Yii::$app->formatter->asMarkdown($changelog) ?>
</div>