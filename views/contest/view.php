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
$loginUserProblemSolvingStatus = $model->getLoginUserProblemSolvingStatus();
$submissionStatistics = $model->getSubmissionStatistics();
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
    <div class="contest-desc">
        <?= Yii::$app->formatter->asHtml($model->description) ?>
    </div>
    <hr>
    <div class="table-responsive">
        <table class="table table-bordered table-problem-list">
            <thead>
            <tr>
                <th width="70px">#</th>
                <?php
                if ($model->getRunStatus() == $model::STATUS_ENDED) {
                    echo "<th width='100px'>题号</th>";
                }
                ?>
                <th><?= Yii::t('app', 'Problem Name') ?></th>
                <th width="100px">正确 / 提交</th>
                <th width="80px">解答状态</th>
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
                    <th>
                        <?= $submissionStatistics[$p['problem_id']]['solved'] ?> / <?= $submissionStatistics[$p['problem_id']]['submit'] ?>
                    </th>
                    <th>
                        <?php if (!isset($loginUserProblemSolvingStatus[$p['problem_id']])): ?>

                        <?php elseif ($model->type == \app\models\Contest::TYPE_OI && $model->getRunStatus() == \app\models\Contest::STATUS_RUNNING): ?>
                            <span class="glyphicon glyphicon-question-sign"></span>
                        <?php elseif ($loginUserProblemSolvingStatus[$p['problem_id']] == \app\models\Solution::OJ_AC): ?>
                            <span class="glyphicon glyphicon-ok text-success" title="正确解答"></span>
                        <?php elseif ($loginUserProblemSolvingStatus[$p['problem_id']] < 4): ?>
                            <span class="glyphicon glyphicon-question-sign text-muted" title="等待测评"></span>
                        <?php else: ?>
                            <span class="glyphicon glyphicon-remove text-danger" title="未正确解答"></span>
                        <?php endif; ?>
                    </th>
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
                [
                    'attribute' => 'created_at',
                    'options' => ['width' => '150px'],
                    'format' => 'datetime'
                ],
                [
                    'attribute' => Yii::t('app', 'Announcement'),
                    'value' => function ($model, $key, $index, $column) {
                        return Yii::$app->formatter->asHtml($model->content);
                    },
                    'format' => 'html',
                ],
            ],
        ]);
    }
    ?>
</div>
