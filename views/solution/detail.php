<?php

use yii\helpers\Html;
use app\models\Solution;

/* @var $this yii\web\View */
/* @var $model app\models\Solution */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Status'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="table-responsive">
    <table class="table table-bordered table-rank">
        <thead>
        <tr>
            <th width="120px">Run ID</th>
            <th width="120px"><?= Yii::t('app', 'Author') ?></th>
            <th width="200px"><?= Yii::t('app', 'Problem') ?></th>
            <th width="80px"><?= Yii::t('app', 'Lang') ?></th>
            <th><?= Yii::t('app', 'Verdict') ?></th>
            <?php if (Yii::$app->setting->get('oiMode')): ?>
                <th width="80px"><?= Yii::t('app', 'Score') ?></th>
            <?php endif; ?>
            <th><?= Yii::t('app', 'Time') ?></th>
            <th><?= Yii::t('app', 'Memory') ?></th>
            <th><?= Yii::t('app', 'Code Length') ?></th>
            <th><?= Yii::t('app', 'Submit Time') ?></th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <th><?= $model->id ?></th>
            <th><?= Html::a(Html::encode($model->user->nickname), ['/user/view', 'id' => $model->created_by]) ?></th>
            <th><?= Html::a(Html::encode($model->problem->title), ['/problem/view', 'id' => $model->problem_id]) ?></th>
            <th><?= Solution::getLanguageList($model->language) ?></th>
            <th>
                <?php if ($model->canViewResult()) {
                    echo Solution::getResultList($model->result);
                } else {
                    echo Solution::getResultList(Solution::OJ_WT0);
                } ?>
            </th>
            <?php if (Yii::$app->setting->get('oiMode')): ?>
                <th width="80px">
                    <?php
                        if ($model->canViewResult()) {
                            echo $model->score;
                        } else {
                            echo '-';
                        }
                    ?>
                </th>
            <?php endif; ?>
            <th>
                <?php
                if ($model->canViewResult()) {
                    echo $model->time;
                } else {
                    echo '-';
                }
                ?> MS
            </th>
            <th>
                <?php
                if ($model->canViewResult()) {
                    echo $model->memory;
                } else {
                    echo '-';
                }
                ?> KB
            </th>
            <th><?= $model->code_length ?></th>
            <th><?= $model->created_at ?></th>
        </tr>
        </tbody>
    </table>
</div>
<?php if (!Yii::$app->setting->get('oiMode')): ?>
    <hr>
    <h3>Tests(<?= $model->getPassedTestCount() ?>/<?= $model->getTestCount() ?>):</h3>
    <h3>
        <?php for ($i = 1; $i <= $model->getPassedTestCount(); $i++): ?>
            <?php if ($i <= $model->getTestCount()) : ?>
                <span class="glyphicon glyphicon-ok-circle text-success"></span>
            <?php else: ?>
                <span class="glyphicon glyphicon-remove-circle text-danger"></span>
            <?php endif; ?>
        <?php endfor; ?>
        <?php if ($model->getPassedTestCount() < $model->getTestCount()) : ?>
            <span class="glyphicon glyphicon-remove-circle text-danger"></span>
        <?php endif; ?>
    </h3>
<?php endif; ?>

<?php if ($model->canViewSource()): ?>
    <hr>
    <div class="pre"><p><?= Html::encode($model->source) ?></p></div>
<?php endif; ?>

<?php if ($model->solutionInfo != null && $model->canViewErrorInfo()): ?>
    <hr>
    <h3><?= Yii::t('app', 'Judgement Protocol') ?>:</h3>
    <div id="run-info">

    </div>
<?php
$json = $model->solutionInfo->run_info;
$json = str_replace(PHP_EOL,"<br>",$json);
$json = str_replace("'","\'",$json);
$oiMode = Yii::$app->setting->get('oiMode');
$verdict = $model->result;
$CE = Solution::OJ_CE;
$js = <<<EOF

var oiMode = $oiMode;
var verdict = $verdict;
var CE = $CE;

var json = '$json';
if (verdict != CE) {
    json = eval('(' + json + ')');
    var subtasks = json.subtasks;
    var testId = 1;
    for (var i = 0; i < subtasks.length; i++) {
        var cases = subtasks[i].cases;
        var score = subtasks[i].score;
        var isSubtask = (subtasks.length != 1);
        if (isSubtask) {
            var verdict = cases[cases.length - 1].verdict;
            $("#run-info").append(subtaskHtml(i + 1, score, verdict));
            for (var j = 0; j < cases.length; j++) {
                var id = i + 1;
                $('#subtask-body-' + id).append(testHtml(testId, cases[j]));
                testId++;
            }
        } else {
            for (var j = 0; j < cases.length; j++) {
                $("#run-info").append(testHtml(testId, cases[j]));
                testId++;
            }
        }
    }
    json = "";
}
if (verdict == CE) {
    $("#run-info").append(json);
}
EOF;
$this->registerJs($js);
?>

<?php endif; ?>
