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
<?php if (!empty($model->lock_board_time) && strtotime($model->lock_board_time) <= time() && strtotime($model->lock_board_time) >= time() - 120 * 60) :?>
    <p>现已是封榜状态，榜单将不再实时更新，待赛后再揭晓</p>
<?php endif; ?>
<table class="table table-bordered table-rank">
    <thead>
    <tr>
        <th width="60px">Rank</th>
        <th width="150px">Who</th>
        <th width="70px">Solved</th>
        <th width="80px">Scores</th>
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
                <?= Html::a(Html::encode($rank['nickname']), ['/user/view', 'id' => $rank['user_id']]) ?>
            </th>
            <th>
                <?= $rank['solved'] ?>
            </th>
            <th>
                <?= round($rank['time']) ?>
            </th>
            <?php
            foreach($problems as $key => $p) {
                $css_class = "";
                $num = 0;
                $time = "";
                if (isset($rank['ac_time'][$p['problem_id']]) && $rank['ac_time'][$p['problem_id']] > 0) {
                    if ($first_blood[$p['problem_id']] == $rank['user_id']) {
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
                if ((!Yii::$app->user->isGuest && $model->created_by == Yii::$app->user->id) || $model->getRunStatus() == \app\models\Contest::STATUS_ENDED) {
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
