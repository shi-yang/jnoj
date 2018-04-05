<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\Modal;

/* @var $this yii\web\View */
/* @var $model app\models\Contest */
/* @var $form yii\widgets\ActiveForm */
/* @var $data array */
$this->title = $model->title;
$this->params['model'] = $model;

$problems = $model->problems;
$rank_result = $model->getRankData();
$first_blood = $rank_result['first_blood'];
$result = $rank_result['rank_result'];
$submit_count = $rank_result['submit_count'];
?>
<div class="contest-overview text-center center-block">
    <div class="legend-strip">
        <div class="pull-right table-legend">
            <div>
                <span class="solved-first legend-status"></span>
                <p class="legend-label"> First to solve problem</p>
            </div>
            <div>
                <span class="solved legend-status"></span>
                <p class="legend-label"> Solved problem</p></div>
            <div>
                <span class="attempted legend-status"></span>
                <p class="legend-label"> Attempted problem</p>
            </div>
            <div>
                <span class="pending legend-status"></span>
                <p class="legend-label"> Pending judgement</p>
            </div>
        </div>
    </div>
    <div class="clearfix"></div>
    <div class="table-responsive">
        <table class="table table-bordered table-rank">
            <thead>
            <tr>
                <th width="60px">Rank</th>
                <th width="130px"><?= Yii::t('app', 'Student Number') ?></th>
                <th width="150px">Who</th>
                <th width="70px">Solved</th>
                <th width="80px">Time</th>
                <?php foreach($problems as $key => $p): ?>
                    <th>
                        <?= Html::a(chr(65 + $key), ['/contest/problem', 'id' => $model->id, 'pid' => $key]) ?>
                        <br>
                        <span style="color:#7a7a7a; font-size:12px">
                        <?php
                        if (isset($submit_count[$p['problem_id']]['solved']))
                            echo $submit_count[$p['problem_id']]['solved'];
                        else
                            echo 0;
                        ?>
                        /
                        <?php
                        if (isset($submit_count[$p['problem_id']]['submit']))
                            echo $submit_count[$p['problem_id']]['submit'];
                        else
                            echo 0;
                        ?>
                </span>
                    </th>
                <?php endforeach; ?>
            </tr>
            </thead>
            <tbody>
            <?php for ($i = 0; $i < count($result); $i++): $rank = $result[$i]; ?>
                <tr>
                    <th>
                        <?= $i + 1 ?>
                    </th>
                    <th>
                        <?= $rank['student_number'] ?>
                    </th>
                    <th>
                        <?= Html::a(Html::encode($rank['nickname']), ['/user/view', 'id' => $rank['username']]) ?>
                    </th>
                    <th>
                        <?= $rank['solved'] ?>
                    </th>
                    <th>
                        <?= round($rank['time'] / 60) ?>
                    </th>
                    <?php
                    foreach($problems as $key => $p) {
                        $css_class = "";
                        $num = 0;
                        $time = "";
                        if (isset($rank['ac_time'][$p['problem_id']]) && $rank['ac_time'][$p['problem_id']] > 0) {
                            if ($first_blood[$p['problem_id']] == $rank['username']) {
                                $css_class = 'solved-first';
                            } else {
                                $css_class = 'solved';
                            }
                            $num = $rank['wa_count'][$p['problem_id']] + 1;
                            $time = round($rank['ac_time'][$p['problem_id']] / 60);
                        } else if (isset($rank['pending'][$p['problem_id']]) && $rank['pending'][$p['problem_id']]) {
                            $css_class = 'pending';
                            $num = $rank['wa_count'][$p['problem_id']];
                            $time = '--';
                        } else if (isset($rank['wa_count'][$p['problem_id']])) {
                            $css_class = 'attempted';
                            $num = $rank['wa_count'][$p['problem_id']];
                            $time = '--';
                        }
                        if (!Yii::$app->user->isGuest && $model->created_by == Yii::$app->user->id || $model->getRunStatus() == \app\models\Contest::STATUS_ENDED) {
                            $url = Url::toRoute([
                                '/contest/submission',
                                'pid' => $p['problem_id'],
                                'cid' => $model->id,
                                'uid' => $rank['user_id']
                            ]);
                            echo "<th class=\"table-problem-cell {$css_class}\" style=\"cursor:pointer\" data-click='submission' data-href='{$url}'>{$num}<br><small>{$time}</small></th>";
                        } else {
                            echo "<th class=\"table-problem-cell {$css_class}\">{$num}<br><small>{$time}</small></th>";
                        }
                    }
                    ?>
                </tr>
            <?php endfor; ?>
            </tbody>
        </table>
    </div>
</div>
<?php
$js = "
$('[data-click=submission]').click(function() {
    $.ajax({
        url: $(this).attr('data-href'),
        type:'post',
        error: function(){alert('error');},
        success:function(html){
            $('#submission-content').html(html);
            $('#submission-info').modal('show');
        }
    });
});
";
$this->registerJs($js);
?>
<?php Modal::begin([
    'options' => ['id' => 'submission-info']
]); ?>
    <div id="submission-content">
    </div>
<?php Modal::end(); ?>

