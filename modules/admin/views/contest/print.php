<?php

use yii\helpers\Html;
use yii\helpers\Markdown;

/* @var $model app\models\Contest */

$this->title = $model->title;

?>
<style>
    html, body {
        background-color: #fff !important;
        padding: 0 20px;
    }
    @media print {
        .next-page {page-break-after:always;}
    }
    pre {
        padding: 0;
        background-color: #fff;
        border: none;
    }
</style>
<div class="row">
    <div class="col-md-8 problem-view">
    <?php foreach ($problems as $key => $problem): ?>
        <h2>Problem.<?= Html::encode(chr(65 + $problem['num']) . ' ' . $problem['title']) ?></h2>

        <div class="content-wrapper">
            <?= Markdown::process($problem['description'], 'gfm') ?>
        </div>
        <h3><?= Yii::t('app', 'Input') ?></h3>
        <div class="content-wrapper">
            <?= Markdown::process($problem['input'], 'gfm') ?>
        </div>
        <h3><?= Yii::t('app', 'Output') ?></h3>
        <div class="content-wrapper">
            <?= Markdown::process($problem['output'], 'gfm') ?>
        </div>
        <h3><?= Yii::t('app', 'Sample') ?></h3>
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
        <?php if (!empty($sample_input[1])): ?>
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
        <?php if (!empty($sample_input[2])): ?>
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
            <h3><?= Yii::t('app', 'Hint') ?></h3>
            <div class="content-wrapper">
                <?= Markdown::process($problem['hint'], 'gfm') ?>
            </div>
        <?php endif; ?>
        <div class="next-page"></div>
    <?php endforeach; ?>
    </div>
</div>
<script type="text/x-mathjax-config">
MathJax.Hub.Config({
    showProcessingMessages: false,
    messageStyle: "none",
    extensions: ["tex2jax.js"],
    jax: ["input/TeX", "output/HTML-CSS"],
    tex2jax: {
        inlineMath:  [ ["$", "$"] ],
        displayMath: [ ["$$","$$"] ],
        skipTags: ['script', 'noscript', 'style', 'textarea', 'pre','code','a'],
    },
    "HTML-CSS": {
        showMathMenu: false
    }
});
MathJax.Hub.Queue(["Typeset",MathJax.Hub]);
</script>
