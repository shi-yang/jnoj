<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\User */

$this->title = $model->nickname;
$this->params['breadcrumbs'][] = $this->title;
$solutionStats = $model->getSolutionStats();
?>
<div class="user-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'username',
            'nickname',
            'email',
            [
                'attribute' => Yii::t('app', 'QQ'),
                'value' => function ($model, $widget) {
                    return Html::encode($model->profile->qq_number);
                },
                'format' => 'raw'
            ],
            [
                'attribute' => Yii::t('app', 'Major'),
                'value' => function ($model, $widget) {
                    return Html::encode($model->profile->major);
                },
                'format' => 'raw'
            ],
            [
                'attribute' => Yii::t('app', 'Student Number'),
                'value' => function ($model, $widget) {
                    return $model->profile->student_number;
                },
                'format' => 'raw'
            ]
        ],
    ]) ?>
    <hr>
    <h3><?= Yii::t('app', 'Solved Problem') ?> <small>(<?= count($solutionStats['solved_problem']) ?>)</small></h3>
    <ul>
        <?php foreach ($solutionStats['solved_problem'] as $p): ?>
            <li class="label label-default"><?= Html::a($p, ['/problem/view', 'id' => $p], ['style' => 'color:#fff']) ?></li>
        <?php endforeach; ?>
    </ul>
    <hr>
    <h3><?= Yii::t('app', 'Unsolved Problem') ?> <small>(<?= count($solutionStats['unsolved_problem']) ?>)</small></h3>
    <ul>
        <?php foreach ($solutionStats['unsolved_problem'] as $p): ?>
            <li class="label label-default"><?= Html::a($p, ['/problem/view', 'id' => $p], ['style' => 'color:#fff']) ?></li>
        <?php endforeach; ?>
    </ul>

    <hr>
    <h2>Statistics</h2>
    <div class="row">
        <div class="left-list col-md-6">
            <ul class="stat-list">
                <li>
                    <strong>Submissions</strong><span> <?= $solutionStats['all_count'] ?></span>
                </li>
                <li>
                    <strong>Accepted submissions</strong><span> <?= $solutionStats['ac_count'] ?></span>
                </li>
                <li>
                    <strong>Submission ratio</strong>
                    <span>
                        <?= $solutionStats['all_count'] == 0 ? 0 : number_format($solutionStats['ac_count'] / $solutionStats['all_count'] * 100, 2) ?> %
                    </span>
                </li>
            </ul>
        </div>
        <div class="right-list col-md-6">
            <ul class="stat-list">
                <li>
                    <strong>Wrong Answer</strong><span> <?= $solutionStats['wa_count'] ?></span>
                </li>
                <li>
                    <strong>Time Limit Exceeded</strong><span> <?= $solutionStats['tle_count'] ?></span>
                </li>
                <li>
                    <strong>Compile Error</strong><span> <?= $solutionStats['ce_count'] ?></span>
                </li>
            </ul>
        </div>
    </div>
</div>
