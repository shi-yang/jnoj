<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $model app\models\Contest */
/* @var $solution app\models\Solution */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $data array */

$this->title = $model->title;
$this->params['model'] = $model;

$problems = $model->problems;
?>
<div class="contest-overview text-center center-block">
    <div class="table-responsive well">
        <table class="table table-overview">
            <tbody>
            <tr>
                <th><?= Yii::t('app', 'Start time') ?></th>
                <td><?= $model->start_time ?></td>
                <th><?= Yii::t('app', 'Type') ?></th>
                <td><?= $model->getType() ?></td>
            </tr>
            <tr>
                <th><?= Yii::t('app', 'End time') ?></th>
                <td><?= $model->end_time ?></td>
                <th><?= Yii::t('app', 'Status') ?></th>
                <td><?= $model->getRunStatus(true) ?></td>
            </tr>
            </tbody>
        </table>
    </div>
    <div class="table-responsive">
        <table class="table table-bordered table-problem-list">
            <thead>
            <tr>
                <th width="70px">#</th>
                <?php
                if ($model->getRunStatus() == $model::STATUS_ENDED) {
                    echo "<th width='100px'>Problem Id</th>";
                }
                ?>
                <th>Name</th>
                <!--                <th>Solved</th>-->
            </tr>
            </thead>
            <tbody>
            <?php foreach ($problems as $key => $p): ?>
                <tr>
                    <th><?= Html::a(chr(65 + $key), ['/contest/problem', 'id' => $model->id, 'pid' => $key, '#' => 'problem-anchor']) ?></th>
                    <?php
                    if ($model->getRunStatus() == $model::STATUS_ENDED) {
                        echo "<th>" . Html::a($p['problem_id'], ['/problem/view', 'id' => $p['problem_id']]) . "</th>";
                    }
                    ?>
                    <td><?= Html::a(Html::encode($p['title']), ['/contest/problem', 'id' => $model->id, 'pid' => $key, '#' => 'problem-anchor']) ?></td>
                    <!--                    <td></td>-->
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
