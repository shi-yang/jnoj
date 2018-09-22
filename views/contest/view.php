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
                <th>Start time</th>
                <td><?= $model->start_time ?></td>
                <th>Type</th>
                <td><?= $model->getType() ?></td>
            </tr>
            <tr>
                <th>End time</th>
                <td><?= $model->end_time ?></td>
                <th>Status</th>
                <td><?= $model->getRunStatus(true) ?></td>
            </tr>
            </tbody>
        </table>
    </div>
    <div class="contest-desc">
        <?= Yii::$app->formatter->asMarkdown($model->description) ?>
    </div>
    <hr>
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
    <?php
    if ($dataProvider->count > 0) {
        echo '<hr>';
        echo GridView::widget([
            'layout' => '{items}{pager}',
            'dataProvider' => $dataProvider,
            'options' => ['class' => 'table-responsive', 'style' => 'margin:0 auto;width:50%;min-width:600px;text-align: left;'],
            'columns' => [
                'created_at:datetime',
                [
                    'attribute' => Yii::t('app', 'Announcement'),
                    'value' => function ($model, $key, $index, $column) {
                        return $model->content;
                    },
                    'format' => 'ntext',
                ],
            ],
        ]);
    }
    ?>
</div>
