<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\Modal;

/* @var $model app\models\Contest */

$problems = $model->problems;
$rank_result = $model->getRankData();
$first_blood = $rank_result['first_blood'];
$result = $rank_result['rank_result'];
$submit_count = $rank_result['submit_count'];
?>
<?php if ($model->isScoreboardFrozen()): ?>
    <p>现已是封榜状态，榜单将不再实时更新，待赛后再揭晓</p>
<?php endif; ?>
<table class="table table-bordered table-rank">
    <thead>
    <tr>
        <th width="60px">Rank</th>
        <th width="200px">Who</th>
        <th title="# solved / penalty time" colspan="2">Score</th>
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
    <?php for ($i = 0, $ranking = 1; $i < count($result); $i++): ?>
    <?php $rank = $result[$i]; ?>
        <tr>
            <th>
                <?php
                //线下赛，参加比赛但不参加排名的处理
                if ($model->scenario == \app\models\Contest::SCENARIO_OFFLINE && $rank['role'] != \app\models\User::ROLE_PLAYER) {
                    echo '*';
                } else {
                    echo $ranking;
                    $ranking++;
                }
                ?>
            </th>
            <th>
                <?= Html::a(Html::encode($rank['nickname']), ['/user/view', 'id' => $rank['user_id']]) ?>
            </th>
            <th class="score-solved">
                <?= $rank['solved'] ?>
            </th>
            <th class="score-time">
                <?= intval($rank['time'] / 60) ?>
            </th>
            <?php
            foreach($problems as $key => $p) {
                $css_class = '';
                $num = 0;
                $time = '';
                if (isset($rank['ac_time'][$p['problem_id']]) && $rank['ac_time'][$p['problem_id']] > 0) {
                    if ($first_blood[$p['problem_id']] == $rank['user_id']) {
                        $css_class = 'solved-first';
                    } else {
                        $css_class = 'solved';
                    }
                    $num = $rank['ce_count'][$p['problem_id']] + $rank['wa_count'][$p['problem_id']] + 1;
                    $time = intval($rank['ac_time'][$p['problem_id']]);
                } else if (isset($rank['pending'][$p['problem_id']]) && $rank['pending'][$p['problem_id']]) {
                    $num = $rank['ce_count'][$p['problem_id']] + $rank['wa_count'][$p['problem_id']] + $rank['pending'][$p['problem_id']];
                    $css_class = 'pending';
                    $time = '';
                } else if (isset($rank['wa_count'][$p['problem_id']])) {
                    $css_class = 'attempted';
                    $num = $rank['ce_count'][$p['problem_id']] + $rank['wa_count'][$p['problem_id']];
                    $time = '';
                }
                if ($num == 0) {
                    $num = '';
                    $span = '';
                } else if ($num == 1) {
                    $span = 'try';
                } else {
                    $span = 'tries';
                }
                // 封榜的显示
                if ($model->isScoreboardFrozen() && isset($rank['pending'][$p['problem_id']]) && $rank['pending'][$p['problem_id']]) {
                    $num = $rank['ce_count'][$p['problem_id']] + $rank['wa_count'][$p['problem_id']] . "+" .  $rank['pending'][$p['problem_id']];
                }
                if ((!Yii::$app->user->isGuest && $model->created_by == Yii::$app->user->id) || $model->getRunStatus() == \app\models\Contest::STATUS_ENDED) {
                    $url = Url::toRoute([
                        '/contest/submission',
                        'pid' => $p['problem_id'],
                        'cid' => $model->id,
                        'uid' => $rank['user_id']
                    ]);
                    echo "<th class=\"table-problem-cell {$css_class}\" style=\"cursor:pointer\" data-click='submission' data-href='{$url}'>{$time}<br><small>{$num} {$span}</small></th>";
                } else {
                    echo "<th class=\"table-problem-cell {$css_class}\">{$num}<br><small>{$time}</small></th>";
                }
            }
            ?>
        </tr>
    <?php endfor; ?>
    </tbody>
</table>

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

