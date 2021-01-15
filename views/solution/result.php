<?php

use yii\helpers\Html;
use app\models\Solution;

/* @var $this yii\web\View */
/* @var $model app\models\Solution */

$this->title = $model->id;
$json = NULL;

if (!$model->canViewErrorInfo()) {
    return '暂无权限查看出错信息';
}
?>
<div class="solution-view">
    <h3><?= Yii::t('app', 'Run ID') ?>: <?= Html::a($model->id, ['/solution/detail', 'id' => $model->id]) ?></h3>
    <?php if ($model->solutionInfo != null): ?>
        <?php if ($model->result == Solution::OJ_CE): ?>
            <pre><?= \yii\helpers\HtmlPurifier::process($model->solutionInfo->run_info) ?></pre>
        <?php else: ?>
        <div id="run-info">

        </div>
        <?php
        $json = $model->solutionInfo->run_info;
        $json = str_replace("<", "&lt;", $json);
        $json = str_replace(">", "&gt;", $json);
        $json = str_replace(PHP_EOL,"<br>",$json);
        $json = str_replace("\\n","<br>",$json);
        $json = str_replace("'","\'",$json);
        $json = str_replace("\\r", "", $json);
        ?>
        <?php endif; ?>
    <?php endif; ?>
</div>

<script>
    var verdict = <?= $model->result; ?>;
    var CE = <?= Solution::OJ_CE; ?>;

    var json = '<?= $json ?>';
    if (verdict != CE) {
        json = JSON.parse(json);
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
</script>
