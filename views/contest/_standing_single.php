<?php

use yii\helpers\Html;

$problems = $model->problems;
$rank_result = $model->getRankSingleData();
$first_blood = $rank_result['first_blood'];
$result = $rank_result['rank_result'];
?>
<table class="table table-bordered table-rank">
    <thead>
    <tr>
        <th width="60px">Rank</th>
        <th width="150px">Username</th>
        <th width="70px">Solved</th>
        <th width="80px">Score</th>
        <?php foreach($problems as $key => $p): ?>
            <th><?= Html::a(chr(65 + $key), ['view', 'id' => $model->id, 'action' => 'problem', 'problem_id' => $key]) ?></th>
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
                <?= Html::a(Html::encode($rank['username']), ['/user/view', 'id' => $rank['username']]) ?>
            </th>
            <th>
                <?= $rank['solved'] ?>
            </th>
            <th>
                <?= round($rank['score']) ?>
            </th>
            <?php
            foreach($problems as $key => $p) {
                $css_class = "";
                $num = 0;
                $time = "";
                if ($rank['ac_time'][$p->problem_id] > 0) {
                    if ($first_blood[$p->problem_id] == $rank['username']) {
                        $css_class = 'solved-first';
                    } else {
                        $css_class = 'solved';
                    }
                    $num = $rank['wa_count'][$p->problem_id] + 1;
                    $time = intval($rank['ac_time'][$p->problem_id] / 60);
                } else if ($rank['wa_count'][$p->problem_id] > 0) {
                    $css_class = 'attempted';
                    $num = $rank['wa_count'][$p->problem_id];
                    $time = '--';
                }
                echo "<th class=\"table-problem-cell {$css_class}\">{$num}<br><small>{$time}</small></th>";
            }
            ?>
        </tr>
    <?php endfor; ?>
    </tbody>
</table>