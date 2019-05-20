<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use yii\bootstrap\Modal;

/* @var $this yii\web\View */
/* @var $model app\models\Contest */
/* @var $searchModel app\models\SolutionSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $data array */

$this->title = $model->title;
$this->params['model'] = $model;
$problems = $model->problems;

$nav = [];
$nav[''] = 'All';
foreach ($problems as $key => $p) {
    $nav[$p['problem_id']] = chr(65 + $key) . '-' . $p['title'];
}
?>
<div class="wrap">
    <div class="container">
        <h1>
            <?= Html::a(Html::encode($model->title), ['view', 'id' => $model->id]) ?>
        </h1>
        <?php Modal::begin([
            'header' => '<h3>'.Yii::t('app','Attention!').'</h3>',
            'toggleButton' => ['label' => Yii::t('app', 'Show the submissions in frontend'), 'class' => 'btn btn-success'],
        ]); ?>
        <h3>继续该操作前，请详细阅读以下内容：</h3>
        <div class="well">该功能是为了让比赛结束后的提交记录显示在前台的提交状态列表页面，不使用该功能提交记录将不会主动显示在前台状态列表页面</div>
        <p><strong>1. 此操作将会使目前为止该场比赛所有提交记录显示在前台提交状态页面[<?= Html::a(Yii::$app->request->hostInfo . '/status', Yii::$app->request->hostInfo . '/status') ?>]</strong></p>
        <p><strong>2. 这意味着以下所有提交的代码及出错数据等信息可以被任何用户查看</strong></p>
        <p><strong>3. 请在比赛结束后进行</strong></p>
        <p>继续就点下面红色按钮，否则请关闭该窗口</p>
        <?= Html::a('已阅读上述内容，并将提交记录展示在前台', ['/admin/contest/status', 'id' => $model->id, 'active' => 1], ['class' => 'btn btn-danger']) ?>
        <?php Modal::end(); ?>

        <?= Html::a('在前台隐藏提交记录', ['/admin/contest/status', 'id' => $model->id, 'active' => 2], ['class' => 'btn btn-default']) ?>

        <?php Pjax::begin() ?>
        <div class="solution-index" style="margin-top: 20px">
            <?= $this->render('_status_search', ['model' => $searchModel, 'nav' => $nav, 'contest_id' => $model->id]); ?>

            <?= GridView::widget([
                'layout' => '{items}{pager}',
                'dataProvider' => $dataProvider,
                'options' => ['class' => 'table-responsive'],
                'columns' => [
                    [
                        'attribute' => 'id',
                        'value' => function ($model, $key, $index, $column) {
                            return Html::a($model->id, ['/solution/detail', 'id' => $model->id], ['target' => '_blank']);
                        },
                        'format' => 'raw'
                    ],
                    [
                        'attribute' => 'who',
                        'value' => function ($model, $key, $index, $column) {
                            return Html::a(Html::encode($model->username) . '[' . Html::encode($model->user->nickname) . ']', ['/user/view', 'id' => $model->created_by]);
                        },
                        'format' => 'raw'
                    ],
                    [
                        'label' => Yii::t('app', 'Problem'),
                        'value' => function ($model, $key, $index, $column) {
                            $res = $model->getProblemInContest();
                            if (!isset($model->problem)) {
                                return null;
                            }
                            if (!isset($res->num)) {
                                return $model->problem->title;
                            }
                            return Html::a(chr(65 + $res->num) . ' - ' . $model->problem->title,
                                ['/contest/problem', 'id' => $res->contest_id, 'pid' => $res->num]);
                        },
                        'format' => 'raw'
                    ],
                    [
                        'attribute' => 'result',
                        'value' => function ($model, $key, $index, $column) {
                            if ($model->result == $model::OJ_CE || $model->result == $model::OJ_WA
                                || $model->result == $model::OJ_RE) {
                                return Html::a($model->getResult(),
                                    ['/solution/result', 'id' => $model->id],
                                    ['onclick' => 'return false', 'data-click' => "solution_info"]
                                );
                            } else {
                                return $model->getResult();
                            }
                        },
                        'format' => 'raw'
                    ],
                    [
                        'attribute' => 'score',
                        'visible' => Yii::$app->setting->get('oiMode')
                    ],
                    [
                        'attribute' => 'time',
                        'value' => function ($model, $key, $index, $column) {
                            return $model->time . ' MS';
                        },
                        'format' => 'raw'
                    ],
                    [
                        'attribute' => 'memory',
                        'value' => function ($model, $key, $index, $column) {
                            return $model->memory . ' KB';
                        },
                        'format' => 'raw'
                    ],
                    [
                        'attribute' => 'language',
                        'value' => function ($model, $key, $index, $column) {
                            return Html::a($model->getLang(),
                                ['/solution/source', 'id' => $model->id],
                                ['onclick' => 'return false', 'data-click' => "solution_info", 'data-pjax' => 0]
                            );
                        },
                        'format' => 'raw'
                    ],
                    'code_length',
                    'created_at:datetime',
                ],
            ]); ?>
        </div>
        <?php
$url = \yii\helpers\Url::toRoute(['/solution/verdict']);
$loadingImgUrl = Yii::getAlias('@web/images/loading.gif');
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
            if (obj.result === "Accepted") {
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
    var testWaitingsDone = function () {
        var waitingCount = $("strong[waiting=true]").length;
        console.log("There is waitingCount=" + waitingCount + ", starting submissionsEventCatcher...");
        $("strong[waiting=true]").each(function(){
            updateVerdictByKey($(this));
        });
        if (interval && waitingCount === 0) {
            console.log("Stopping submissionsEventCatcher.");
            clearInterval(interval);
            interval = null;
        }
    }
    interval = setInterval(testWaitingsDone, 1000);
}
EOF;
$this->registerJs($js);
?>
        <?php Pjax::end() ?>
    </div>
</div>

<?php Modal::begin([
    'header' => '<h3>'.Yii::t('app','Information').'</h3>',
    'options' => ['id' => 'solution-info']
]); ?>
    <div id="solution-content">
    </div>
<?php Modal::end(); ?>

