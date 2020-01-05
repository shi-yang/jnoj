<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\bootstrap\Modal;
use app\models\Solution;

/* @var $this yii\web\View */
/* @var $model app\models\Problem */
/* @var $solution app\models\Solution */
/* @var $submissions array */

$this->title = $model->id . ' - ' . $model->title;

$this->registerJsFile(Yii::getAlias('@web/js/splitter.min.js'));
$this->registerJs("
Split(['.problem-left', '.problem-right'], {
    sizes: [50, 50],
});
");
$this->registerCss("
    body {
        overflow: hidden;
    }
    .wrap {
        display: flex;
        flex-direction: column;
        padding: 0;
        margin: 0;
        height: 100%;
    }
    .wrap > .navbar {
        margin-bottom: 0;
    }
    .wrap > .container {
        padding: 0;
        display: flex;
        flex-direction: column;
        flex: 1 1 0;
        overflow: hidden;
    }
    .main-container {
        height: 100%;
    }
    .problem-container {
        padding: 20px 20px 4px 20px;
        display: flex;
        flex-direction: column;
        height: 100%;
        background: #fff;
    }
    .problem-splitter {
        display: flex;
        flex-direction: row;
        flex: 1 1 0;
        overflow: hidden;
    }
    .problem-left {
        overflow: hidden;
        display: flex;
        flex-direction: column;
        flex: 1 0 0;
    }
    .problem-left, .problem-right {
        display: -webkit-box;
        display: -webkit-flex;
        display: -ms-flexbox;
        display: flex;
        -webkit-box-orient: vertical;
        -webkit-flex-direction: column;
        -ms-flex-direction: column;
        flex-direction: column;
        height: 100%;
    }
    .problem-description {
        overflow-x: hidden;
        height: 100%;
    }
    .problem-header {
        display: flex;
        justify-content: space-between;
        border-bottom: 1px solid rgb(225, 228, 232);
    }
    .problem-header .problem-meta {
        display: flex;
        text-align: center;
        color: #666;
        font-size: 12px;
        margin: 0px;
    }
    .problem-header .separator {
        width: 1px;
        height: 100%;
        margin: 0px 20px;
        background: rgb(238, 238, 238);
    }
    .problem-right > .problem-editor {
        display: flex;
        flex-direction: column;
        height: 100%;
    }
    .problem-right .problem-editor .code-input {
        height: 100%;
    }
    .problem-wrap {
        display: flex;
        flex-direction: column;
        flex: 1 1 auto;
        overflow: hidden;
    }
    .problem-footer {
        display: flex;
        padding: 5px;
        border-top: 1px solid #eee;
    }
    .problem-left .problem-footer {
        justify-content: flex-end;
    }
    .problem-right .problem-footer {
        justify-content: space-between;
    }
    .CodeMirror {
        height: 100%;
    }
    .gutter {
        background-color: #eee;
        background-repeat: no-repeat;
        background-position: 50%;
    }
    .gutter.gutter-vertical {
        background-image: url('data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAB4AAAAFAQMAAABo7865AAAABlBMVEVHcEzMzMzyAv2sAAAAAXRSTlMAQObYZgAAABBJREFUeF5jOAMEEAIEEFwAn3kMwcB6I2AAAAAASUVORK5CYII=');
    }

    .gutter.gutter-horizontal {
        background-image: url('data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAUAAAAeCAYAAADkftS9AAAAIklEQVQoU2M4c+bMfxAGAgYYmwGrIIiDjrELjpo5aiZeMwF+yNnOs5KSvgAAAABJRU5ErkJggg==');
    }
");
if (!Yii::$app->user->isGuest) {
    $solution->language = Yii::$app->user->identity->language;
}

$model->setSamples();

$loadingImgUrl = Yii::getAlias('@web/images/loading.gif');
$previousProblemID = $model->getPreviousProblemID();
$nextProblemID = $model->getNextProblemID();
?>

<div class="main-container">
    <div class="problem-container">
        <div class="problem-header">
            <div class="problem-title">
                <h2><?= Html::encode($this->title) ?></h2>
            </div>
            <div class="problem-meta">
                <div class="problem-submit-count">
                    <p>通过次数</p>
                    <p><?= $model->accepted ?></p>
                </div>
                <div class="separator"></div>
                <div class="problem-accepted-count">
                    <p>提交次数</p>
                    <p><?= $model->submit ?></p>
                </div>
                <div class="separator"></div>
                <div>
                    <?= Html::a('旧版界面', ['/problem/view', 'id' => $model->id, 'view' => 'classic']) ?>
                </div>
            </div>
        </div>
        <div class="problem-splitter">
            <div class="problem-left">
                <div class="problem-description">
                    <div class="problem-limit">
                        <div class="time-limit">
                            <?= Yii::t('app', 'Time Limit') ?> : <?= intval($model->time_limit) ?> 秒
                        </div>
                        <div class="memory-limit">
                            <?= Yii::t('app', 'Memory Limit') ?> : <?= $model->memory_limit ?> MB
                        </div>
                    </div>
                    <div class="content-wrapper">
                        <?= Yii::$app->formatter->asHtml($model->description) ?>
                    </div>

                    <h4><?= Yii::t('app', 'Input') ?></h4>
                    <div class="content-wrapper">
                        <?= Yii::$app->formatter->asHtml($model->input) ?>
                    </div>

                    <h4><?= Yii::t('app', 'Output') ?></h4>
                    <div class="content-wrapper">
                        <?= Yii::$app->formatter->asHtml($model->output) ?>
                    </div>

                    <h4><?= Yii::t('app', 'Examples') ?></h4>
                    <div class="content-wrapper">
                        <div class="sample-test">
                            <div class="input">
                                <h4><?= Yii::t('app', 'Input') ?></h4>
                                <pre><?= Html::encode($model->sample_input) ?></pre>
                            </div>
                            <div class="output">
                                <h4><?= Yii::t('app', 'Output') ?></h4>
                                <pre><?= Html::encode($model->sample_output) ?></pre>
                            </div>

                            <?php if ($model->sample_input_2 != '' || $model->sample_output_2 != ''):?>
                                <div class="input">
                                    <h4><?= Yii::t('app', 'Input') ?></h4>
                                    <pre><?= Html::encode($model->sample_input_2) ?></pre>
                                </div>
                                <div class="output">
                                    <h4><?= Yii::t('app', 'Output') ?></h4>
                                    <pre><?= Html::encode($model->sample_output_2) ?></pre>
                                </div>
                            <?php endif; ?>

                            <?php if ($model->sample_input_3 != '' || $model->sample_output_3 != ''):?>
                                <div class="input">
                                    <h4><?= Yii::t('app', 'Input') ?></h4>
                                    <pre><?= Html::encode($model->sample_input_3) ?></pre>
                                </div>
                                <div class="output">
                                    <h4><?= Yii::t('app', 'Output') ?></h4>
                                    <pre><?= Html::encode($model->sample_output_3) ?></pre>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php if (!empty($model->hint)): ?>
                        <h4><?= Yii::t('app', 'Hint') ?></h4>
                        <div class="content-wrapper">
                            <?= Yii::$app->formatter->asHtml($model->hint) ?>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($model->source)): ?>
                        <h4><?= Yii::t('app', 'Source') ?></h4>
                        <div class="content-wrapper">
                            <?= Yii::$app->formatter->asHtml($model->source) ?>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="problem-footer">
                    <?= Html::a('<span class="glyphicon glyphicon-arrow-left"></span> 上一题',
                        $previousProblemID ? ['/problem/view', 'id' => $previousProblemID] : 'javascript:void(0);',
                        ['class' => 'btn btn-default', 'disabled' => !$previousProblemID]
                    )?>

                    <?= Html::a('下一题 <span class="glyphicon glyphicon-arrow-right"></span>',
                        $nextProblemID ? ['/problem/view', 'id' => $nextProblemID] : 'javascript:void(0);',
                        ['class' => 'btn btn-default', 'disabled' => !$nextProblemID]
                    )?>
                </div>
            </div>
            <div class="problem-right">
                <?php $form = ActiveForm::begin(['options' => ['class' => 'problem-editor']]); ?>

                <?= $form->field($solution, 'language', ['options' => ['style' => 'margin: 0']])
                    ->dropDownList($solution::getLanguageList(), ['style' => 'width: auto'])->label(false) ?>

                <?= $form->field($solution, 'source', ['options' => ['class' => 'code-input']])
                    ->widget('app\widgets\codemirror\CodeMirror')->label(false); ?>

                <div class="problem-footer">
                    <?php
                    if (Yii::$app->user->isGuest) {
                        echo '<span>请先登陆</span>';
                    } else {
                        echo Html::submitButton(Yii::t('app', 'Submit'), ['class' => 'btn btn-primary']);
                    }
                    ?>
                    <div>
                        <?php if (!Yii::$app->user->isGuest && !empty($submissions)): ?>
                            <?php Modal::begin([
                                'header' => '<h3>'.Yii::t('app','Submit') . '：' . Html::encode($model->id . '. ' . $model->title) . '</h3>',
                                'toggleButton' => [
                                    'label' => '我的提交',
                                    'class' => 'btn btn-default'
                                ]
                            ]); ?>
                                <table class="table">
                                    <tbody>
                                    <?php foreach ($submissions as $sub): ?>
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
                            <?php Modal::end(); ?>
                            <?php $sub = $submissions[0]; ?>
                            <span><?= Yii::$app->formatter->asRelativeTime($sub['created_at']) ?></span>
                            <span>
                                <?php
                                if ($sub['result'] <= Solution::OJ_WAITING_STATUS) {
                                    $waitingHtmlDom = 'waiting="true"';
                                    $loadingImg = "<img src=\"{$loadingImgUrl}\">";
                                } else {
                                    $waitingHtmlDom = 'waiting="false"';
                                    $loadingImg = "";
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
                            </span>
                            <span>
                                <?= Html::a('<span class="glyphicon glyphicon-edit"></span>',
                                    ['/solution/source', 'id' => $sub['id']],
                                    ['title' => '查看源码', 'onclick' => 'return false', 'data-click' => "solution_info", 'data-pjax' => 0])
                                ?>
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
                <?php ActiveForm::end(); ?>
            </div>
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
    interval = setInterval(testWaitingsDone, 20000);
}
EOF;
$this->registerJs($js);
?>
