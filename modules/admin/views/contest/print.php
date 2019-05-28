<?php

use yii\helpers\Html;

/* @var $model app\models\Contest */
/* @var $this \yii\web\View */

$this->title = $model->title;
$this->registerAssetBundle('yii\bootstrap\BootstrapPluginAsset');
?>
<style>
    html, body {
        background-color: #fff !important;
        padding: 0 20px;
    }
    pre {
        padding: 0;
        background-color: #fff;
        border: none;
    }
    .limit {
        text-align: center;
    }
</style>
<div class="row">
    <div class="col-md-8 problem-view">
        <div class="alert alert-warning alert-dismissible fade in hidden-print" role="alert">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
            <p>提示：建议使用浏览器自带的打印功能（Chrome 浏览器可在页面上鼠标“右键”-“打印”，其它浏览器请自行利用搜索引擎获取使用方法），从中选择将此页面导出为 PDF 格式后再打印。</p>
            <p>此提示信息不会出现在浏览器的打印窗口中。</p>
        </div>
        <?php foreach ($problems as $key => $problem): ?>
        <h3><?= Html::encode(chr(65 + $problem['num']) . '. ' . $problem['title']) ?></h3>
        <p class="limit">
            Time limit: <?= Yii::t('app', '{t, plural, =1{# second} other{# seconds}}', ['t' => intval($problem['time_limit'])]); ?>
        </p>
        <p class="limit">
            Memory limit: <?= $problem['memory_limit'] ?> MB
        </p>
        <div class="content-wrapper">
            <?= Yii::$app->formatter->asHtml($problem['description']) ?>
        </div>
        <h4><?= Yii::t('app', 'Input') ?></h4>
        <div class="content-wrapper">
            <?= Yii::$app->formatter->asHtml($problem['input']) ?>
        </div>
        <h4><?= Yii::t('app', 'Output') ?></h4>
        <div class="content-wrapper">
            <?= Yii::$app->formatter->asHtml($problem['output']) ?>
        </div>
        <h4><?= Yii::t('app', 'Sample') ?></h4>
        <?php
        $sample_input = unserialize($problem['sample_input']);
        $sample_output = unserialize($problem['sample_output']);
        ?>
        <table class="table table-bordered">
            <thead>
            <tr>
                <th><?= Yii::t('app', 'Sample Input') ?></th>
                <th><?= Yii::t('app', 'Sample Output') ?></th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td><pre><?= $sample_input[0] ?></pre></td>
                <td><pre><?= $sample_output[0] ?></pre></td>
            </tr>
            </tbody>
        </table>
        <?php if ($sample_input[1] != '' || $sample_output[1] != ''):?>
            <table class="table table-bordered">
                <thead>
                <tr>
                    <th><?= Yii::t('app', 'Sample Input 2') ?></th>
                    <th><?= Yii::t('app', 'Sample Output 2') ?></th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td><pre><?= $sample_input[1] ?></pre></td>
                    <td><pre><?= $sample_output[1] ?></pre></td>
                </tr>
                </tbody>
            </table>
        <?php endif; ?>
        <?php if ($sample_input[2] != '' || $sample_output[2] != ''):?>
            <table class="table table-bordered">
                <thead>
                <tr>
                    <th><?= Yii::t('app', 'Sample Input 3') ?></th>
                    <th><?= Yii::t('app', 'Sample Output 3') ?></th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td><pre><?= $sample_input[2] ?></pre></td>
                    <td><pre><?= $sample_output[2] ?></pre></td>
                </tr>
                </tbody>
            </table>
        <?php endif; ?>
        <?php if (!empty($problem['hint'])): ?>
            <h4><?= Yii::t('app', 'Hint') ?></h4>
            <div class="content-wrapper">
                <?= Yii::$app->formatter->asHtml($problem['hint']) ?>
            </div>
        <?php endif; ?>
        <div style="page-break-after: always"></div>
    <?php endforeach; ?>
    </div>
</div>
