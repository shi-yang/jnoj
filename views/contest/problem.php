<?php

use app\models\Solution;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\bootstrap\Modal;
use yii\bootstrap\Nav;

/* @var $this yii\web\View */
/* @var $model app\models\Contest */
/* @var $solution app\models\Solution */
/* @var $problem array */
/* @var $submissions array */

$this->title = Html::encode($model->title) . ' - ' . $problem['title'];
$this->params['model'] = $model;

if (!Yii::$app->user->isGuest) {
    $solution->language = Yii::$app->user->identity->language;
}
$problems = $model->problems;
if (empty($problems)) {
    echo 'Please add problem';
    return;
}

$nav = [];
foreach ($problems as $key => $p) {
    $nav[] = [
        'label' => chr(65 + $key),
        'url' => [
            'problem',
            'id' => $model->id,
            'pid' => $key,
        ]
    ];
}
$sample_input = unserialize($problem['sample_input']);
$sample_output = unserialize($problem['sample_output']);
$loadingImgUrl = Yii::getAlias('@web/images/loading.gif');
?>
<div class="problem-view">
    <div class="text-center">
        <?= Nav::widget([
            'items' => $nav,
            'options' => ['class' => 'pagination']
        ]) ?>
    </div>
    <div class="row">
        <div class="col-md-8 problem-view">
            <?php if ($this->beginCache('contest_problem_view' . $model->id . '_' . $problem['num'] . '_ '. $problem['id'])): ?>
                <h1><?= Html::encode(chr(65 + $problem['num']) . '. ' . $problem['title']) ?></h1>

                <h3><?= Yii::t('app', 'Description') ?></h3>
                <div class="content-wrapper">
                    <?= Yii::$app->formatter->asMarkdown($problem['description']) ?>
                </div>

                <h3><?= Yii::t('app', 'Input') ?></h3>
                <div class="content-wrapper">
                    <?= Yii::$app->formatter->asMarkdown($problem['input']) ?>
                </div>

                <h3><?= Yii::t('app', 'Output') ?></h3>
                <div class="content-wrapper">
                    <?= Yii::$app->formatter->asMarkdown($problem['output']) ?>
                </div>

                <h3><?= Yii::t('app', 'Examples') ?></h3>
                <div class="content-wrapper">
                    <div class="sample-test">
                        <div class="input">
                            <h4><?= Yii::t('app', 'Input') ?></h4>
                            <pre><?= $sample_input[0] ?></pre>
                        </div>
                        <div class="output">
                            <h4><?= Yii::t('app', 'Output') ?></h4>
                            <pre><?= $sample_output[0] ?></pre>
                        </div>

                        <?php if ($sample_input[1] != '' || $sample_output[1] != ''):?>
                            <div class="input">
                                <h4><?= Yii::t('app', 'Input') ?></h4>
                                <pre><?= $sample_input[1] ?></pre>
                            </div>
                            <div class="output">
                                <h4><?= Yii::t('app', 'Output') ?></h4>
                                <pre><?= $sample_output[1] ?></pre>
                            </div>
                        <?php endif; ?>

                        <?php if ($sample_input[2] != '' || $sample_output[2] != ''):?>
                            <div class="input">
                                <h4><?= Yii::t('app', 'Input') ?></h4>
                                <pre><?= $sample_input[2] ?></pre>
                            </div>
                            <div class="output">
                                <h4><?= Yii::t('app', 'Output') ?></h4>
                                <pre><?= $sample_output[2] ?></pre>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php if (!empty($problem['hint'])): ?>
                    <h3><?= Yii::t('app', 'Hint') ?></h3>
                    <div class="content-wrapper">
                        <?= Yii::$app->formatter->asMarkdown($problem['hint']) ?>
                    </div>
                <?php endif; ?>
                <?php $this->endCache(); ?>
            <?php endif; ?>
            <hr>
            <h3 id="submit-code">Submit</h3>
            <div class="content-wrapper">
                <?php if ($model->isContestEnd() && time() < strtotime($model->end_time) + 5 * 60): ?>
                    比赛已结束。比赛结束五分钟后开放提交。
                <?php else: ?>
                    <?php if (Yii::$app->user->isGuest): ?>
                        <?= app\widgets\login\Login::widget(); ?>
                    <?php else: ?>
                        <?php $form = ActiveForm::begin(); ?>

                        <?= $form->field($solution, 'language')->dropDownList($solution::getLanguageList()) ?>

                        <?= $form->field($solution, 'source')->widget('app\widgets\codemirror\CodeMirror'); ?>

                        <div class="form-group">
                            <?= Html::submitButton(Yii::t('app', 'Submit'), ['class' => 'btn btn-primary']) ?>
                        </div>
                        <?php ActiveForm::end(); ?>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
        <div class="col-md-4 problem-info">
            <div class="panel panel-default">
                <!-- Table -->
                <table class="table">
                    <tbody>
                    <tr>
                        <td><?= Yii::t('app', 'Time Limit') ?></td>
                        <td>
                            <?= Yii::t('app', '{t, plural, =1{# second} other{# seconds}}', ['t' => intval($problem['time_limit'])]); ?>
                        </td>
                    </tr>
                    <tr>
                        <td><?= Yii::t('app', 'Memory Limit') ?></td>
                        <td><?= $problem['memory_limit'] ?> MB</td>
                    </tr>
                    </tbody>
                </table>
            </div>

            <a class="btn btn-success" href="#submit-code">
                <span class="glyphicon glyphicon-plus"></span> <?= Yii::t('app', 'Submit') ?>
            </a>

            <?php if (!Yii::$app->user->isGuest && !empty($submissions)): ?>
            <div class="panel panel-default" style="margin-top: 40px">
                <div class="panel-heading"><?= Yii::t('app', 'Submissions') ?></div>
                <!-- Table -->
                <table class="table">
                    <tbody>
                    <?php foreach($submissions as $sub): ?>
                    <tr>
                        <td title="<?= $sub['created_at'] ?>">
                            <?= Yii::$app->formatter->asRelativeTime($sub['created_at']) ?>
                        </td>
                        <td>
                            <?php
                            if ($sub['result'] <= Solution::OJ_WAITING_STATUS) {
                                $waitingHtmlDom = 'waiting="true"';
                                $loadingImg = "<img src=\"{$loadingImgUrl}\">";
                            } else {
                                $waitingHtmlDom = 'waiting="false"';
                                $loadingImg = "";
                            }
                            // OI 比赛过程中结果不可见
                            if ($model->type == \app\models\Contest::TYPE_OI && !$model->isContestEnd()) {
                                $waitingHtmlDom = 'waiting="false"';
                                $loadingImg = "";
                                $sub['result'] = 0;
                            }
                            $innerHtml =  'data-verdict="' . $sub['result'] . '" data-submissionid="' . $sub['id'] . '" ' . $waitingHtmlDom;
                            if ($sub['result'] == Solution::OJ_AC) {
                                $span = '<strong class="text-success"' . $innerHtml . '>' . Solution::getResultList($sub['result']) . '</strong>';
                                echo Html::a($span,
                                    ['/solution/source', 'id' => $sub['id']],
                                    ['onclick' => 'return false', 'data-click' => "solution_info", 'data-pjax' => 0]
                                );
                            } else {
                                $span = '<strong class="text-danger" ' . $innerHtml . '>' . Solution::getResultList($sub['result']) . $loadingImg . '</strong>';
                                echo Html::a($span,
                                    ['/solution/result', 'id' => $sub['id']],
                                    ['onclick' => 'return false', 'data-click' => "solution_info", 'data-pjax' => 0]
                                );
                            }
                            ?>
                        </td>
                        <td>
                            <?= Html::a('<span class="glyphicon glyphicon-edit"></span>',
                                ['/solution/source', 'id' => $sub['id']],
                                ['title' => '查看源码', 'onclick' => 'return false', 'data-click' => "solution_info", 'data-pjax' => 0]) ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php Modal::begin([
    'options' => ['id' => 'solution-info']
]); ?>
<div id="solution-content">
</div>
<?php Modal::end(); ?>

<?php
$url = \yii\helpers\Url::toRoute(['/solution/verdict']);
$js = <<<EOF
$('[data-click=solution_info]').click(function() {
    $.ajax({
        url: $(this).attr('href'),
        type:'post',
        error: function(){alert('error');},
        success:function(html){
            $('#solution-content').html(html);
            $('#solution-info').modal('show');
        }
    });
});
function updateVerdictByKey(submission) {
    $.get({
        url: "{$url}?id=" + submission.attr('data-submissionid'),
        success: function(data) {
            var obj = JSON.parse(data);
            submission.attr("waiting", obj.waiting);
            submission.text(obj.result);
            if (obj.verdict === "4") {
                submission.attr("class", "text-success")
            }
            if (obj.waiting === "true") {
                submission.append('<img src="{$loadingImgUrl}" alt="loading">');
            }
        }
    });
}
var waitingCount = $("strong[waiting=true]").length;
if (waitingCount > 0) {
    console.log("There is waitingCount=" + waitingCount + ", starting submissionsEventCatcher...");
    var interval = null;
    var waitingQueue = [];
    $("strong[waiting=true]").each(function(){
        waitingQueue.push($(this));
    });
    waitingQueue.reverse();
    var testWaitingsDone = function () {
        updateVerdictByKey(waitingQueue[0]);
        var waitingCount = $("strong[waiting=true]").length;
        while (waitingCount < waitingQueue.length) {
            if (waitingCount < waitingQueue.length) {
                waitingQueue.shift();
            }
            if (waitingQueue.length === 0) {
                break;
            }
            updateVerdictByKey(waitingQueue[0]);
            waitingCount = $("strong[waiting=true]").length;
        }
        console.log("There is waitingCount=" + waitingCount + ", starting submissionsEventCatcher...");
        
        if (interval && waitingCount === 0) {
            console.log("Stopping submissionsEventCatcher.");
            clearInterval(interval);
            interval = null;
        }
    }
    interval = setInterval(testWaitingsDone, 200);
}
EOF;
$this->registerJs($js);
?>
