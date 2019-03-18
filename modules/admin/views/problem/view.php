<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Problem */

$this->title = $model->title;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Problems'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="problem-header">
    <?= \yii\bootstrap\Nav::widget([
        'options' => ['class' => 'nav nav-pills'],
        'items' => [
            ['label' => Yii::t('app', 'Preview'), 'url' => ['/admin/problem/view', 'id' => $model->id]],
            ['label' => Yii::t('app', 'Edit'), 'url' => ['/admin/problem/update', 'id' => $model->id]],
            ['label' => Yii::t('app', 'Tests Data'), 'url' => ['/admin/problem/test-data', 'id' => $model->id]],
            ['label' => Yii::t('app', 'Verify Data'), 'url' => ['/admin/problem/verify', 'id' => $model->id]],
            ['label' => Yii::t('app', 'SPJ'), 'url' => ['/admin/problem/spj', 'id' => $model->id]],
            ['label' => Yii::t('app', 'Subtask'), 'url' => ['/admin/problem/subtask', 'id' => $model->id]]
        ],
    ]) ?>
</div>
<hr>
<div class="row">
    <div class="col-md-9 problem-view">

        <h1><?= Html::encode($this->title) ?></h1>

        <div class="content-wrapper">
            <?= Yii::$app->formatter->asHtml($model->description) ?>
        </div>

        <h3><?= Yii::t('app', 'Input') ?></h3>
        <div class="content-wrapper">
            <?= Yii::$app->formatter->asHtml($model->input) ?>
        </div>

        <h3><?= Yii::t('app', 'Output') ?></h3>
        <div class="content-wrapper">
            <?= Yii::$app->formatter->asHtml($model->output) ?>
        </div>

        <h3><?= Yii::t('app', 'Examples') ?></h3>
        <div class="content-wrapper">
            <div class="sample-test">
                <div class="input">
                    <h4><?= Yii::t('app', 'Input') ?></h4>
                    <pre><?= $model->sample_input ?></pre>
                </div>
                <div class="output">
                    <h4><?= Yii::t('app', 'Output') ?></h4>
                    <pre><?= $model->sample_output ?></pre>
                </div>

                <?php if ($model->sample_input_2 != '' || $model->sample_output_2 != ''):?>
                    <div class="input">
                        <h4><?= Yii::t('app', 'Input') ?></h4>
                        <pre><?= $model->sample_input_2 ?></pre>
                    </div>
                    <div class="output">
                        <h4><?= Yii::t('app', 'Output') ?></h4>
                        <pre><?= $model->sample_output_2 ?></pre>
                    </div>
                <?php endif; ?>

                <?php if ($model->sample_input_3 != '' || $model->sample_output_3 != ''):?>
                    <div class="input">
                        <h4><?= Yii::t('app', 'Input') ?></h4>
                        <pre><?= $model->sample_input_3 ?></pre>
                    </div>
                    <div class="output">
                        <h4><?= Yii::t('app', 'Output') ?></h4>
                        <pre><?= $model->sample_output_3 ?></pre>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <?php if (!empty($model->hint)): ?>
            <h3><?= Yii::t('app', 'Hint') ?></h3>
            <div class="content-wrapper">
                <?= Yii::$app->formatter->asHtml($model->hint) ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($model->source)): ?>
            <h3><?= Yii::t('app', 'Source') ?></h3>
            <div class="content-wrapper">
                <?= Yii::$app->formatter->asHtml($model->source) ?>
            </div>
        <?php endif; ?>
    </div>
    <div class="col-md-3 problem-info">
        <div class="panel panel-default">
            <div class="panel-heading">Information</div>
            <!-- Table -->
            <table class="table">
                <tbody>
                <tr>
                    <td>Time limit</td>
                    <td><?= $model->time_limit ?> Second</td>
                </tr>
                <tr>
                    <td>Memory limit</td>
                    <td><?= $model->memory_limit ?> MB</td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
