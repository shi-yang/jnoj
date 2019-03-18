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
            <th>Time</th>
            <th>Memory</th>
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
                <th width="80px"><?= $model->score ?></th>
            <?php endif; ?>
            <th><?= $model->time ?> MS</th>
            <th><?= $model->memory ?> KB</th>
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
    <h3>Code:</h3>
    <div class="pre"><p><?= Html::encode($model->source) ?></p></div>
<?php endif; ?>

<?php if ($model->solutionInfo != null && $model->canViewErrorInfo()): ?>
    <hr>
    <h3>Run Info:</h3>
    <div id="run-info">

    </div>
<?php
$json = $model->solutionInfo->run_info;
$json = str_replace(PHP_EOL,"",$json);
$oiMode = Yii::$app->setting->get('oiMode');
$verdict = $model->result;
$CE = Solution::OJ_CE;
$js = <<<EOF

var oiMode = $oiMode;
var verdict = $verdict;
var CE = $CE;
var OJ_VERDICT = new Array(
    "Pending",
    "Pending Rejudge",
    "Compiling",
    "Running & Judging",
    "Accepted",
    "Presentation Error",
    "Wrong Answer",
    "Time Limit Exceeded",
    "Memory Limit Exceeded",
    "Output Limit Exceeded",
    "Runtime Error",
    "Compile Error",
    "System Error",
    "No Test Data"
);
function testHtml(id, caseJsonObject)
{
    return '<div class="panel panel-default test-for-popup"> \
        <div class="panel-heading" role="tab" id="heading' + id + '"> \
            <h4 class="panel-title"> \
                <a class="collapsed" role="button" data-toggle="collapse" data-parent="#accordion" \
                   href="#test-' + id + '" aria-expanded="false" aria-controls="test-' + id + '"> \
                    Test: #<span class="test">' + id + '</span>, \
                    verdict: <span class="verdict">' + OJ_VERDICT[caseJsonObject.verdict] + '</span>, \
                    time: <span class="time">' + caseJsonObject.time + '</span> ms, \
                    memory: <span class="memory">' + caseJsonObject.memory + '</span> KB \
                </a> \
            </h4> \
        </div> \
        <div id="test-' + id + '" class="panel-collapse collapse" role="tabpanel" aria-labelledby="heading' + id + '"> \
            <div class="panel-body">\
                <div class="sample-test">\
                    <div class="input">\
                        <h4>Input</h4>\
                        <pre>' + caseJsonObject.input + '</pre>\
                    </div>\
                    <div class="output">\
                        <h4>Output</h4>\
                        <pre>' + caseJsonObject.user_output + '</pre>\
                    </div>\
                    <div class="output">\
                        <h4>Answer</h4>\
                        <pre>' + caseJsonObject.output + '</pre>\
                    </div>\
                    <div class="output">\
                        <h4>Checker Log</h4>\
                        <pre>' + caseJsonObject.checker_log + '</pre>\
                    </div>\
                    <div class="output">\
                        <h4>System info</h4>\
                        <pre>exit code: ' + caseJsonObject.exit_code + ', checker exit code: ' + caseJsonObject.checker_exit_code + '</pre>\
                    </div>\
                </div>\
            </div>\
        </div>\
    </div>';
}
function subtaskHtml(id, score)
{
    return '<div class="panel panel-default test-for-popup"> \
        <div class="panel-heading" role="tab" id="subtask-heading-' + id + '"> \
            <h4 class="panel-title"> \
                <a role="button" data-toggle="collapse" data-parent="#accordion" \
                    href="#subtask-' + id + '" aria-expanded="false" aria-controls="subtask-' + id + '"> \
                    Subtask #' + id + ', score: ' + score + ' \
                </a> \
            </h4> \
        </div> \
        <div id="subtask-' + id + '" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="subtask-heading-' + id + '"> \
            <div id="subtask-body-' + id + '" class="panel-body"> \
            </div> \
        </div> \
    </div>';
}
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
            $("#run-info").append(subtaskHtml(i + 1, score));
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
