<?php

use yii\helpers\Html;
use app\models\Solution;

/* @var $this yii\web\View */
/* @var $model app\models\Solution */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Status'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
$json = NULL;
?>
<div class="solution-view">
    <h3>Run id: <?= Html::encode($this->title) ?></h3>
    <?php if ($model->solutionInfo != null): ?>
        <?php if ($model->result == Solution::OJ_CE): ?>
            <pre><?= \yii\helpers\HtmlPurifier::process($model->solutionInfo->run_info) ?></pre>
        <?php else: ?>
        <div id="run-info">

        </div>
        <?php
        $json = $model->solutionInfo->run_info;
        $json = str_replace(PHP_EOL,"",$json);
        ?>
        <?php endif; ?>
    <?php endif; ?>
</div>

<script>
    var verdict = <?= $model->result; ?>;
    var CE = <?= Solution::OJ_CE; ?>;
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
    var json = '<?= $json ?>';
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
</script>
