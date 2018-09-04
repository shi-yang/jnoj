<?php

use yii\helpers\Html;

/* @var $model app\models\Contest */
/* @var $who integer */

$this->title = $model->title;
$problems = $model->problems;
$rank_result = $model->getRankData(false);
$first_blood = $rank_result['first_blood'];
$result = $rank_result['rank_result'];
$submit_count = $rank_result['submit_count'];
?>

<div class="wrap">
    <div class="container">
        <div class="row">
            <div class="col-md-3 text-left">
                <strong>Start </strong>
                <?= $model->start_time ?>
            </div>
            <div class="col-md-6 text-center">
                <h2 class="contest-title"><?= Html::encode($model->title) ?></h2>
            </div>
            <div class="col-md-3 text-right">
                <strong>End </strong>
                <?= $model->end_time ?>
            </div>
        </div>
        <table class="table table-bordered table-rank">
            <thead>
            <tr>
                <th width="60px">Rank</th>
                <th width="150px">Who</th>
                <th width="70px">Solved</th>
                <th width="80px">Time</th>
                <?php foreach($problems as $key => $p): ?>
                    <th>
                        <?= chr(65 + $key) ?>
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
                        <?php
                        if ($who == 0) {
                            echo Html::encode($rank['username']);
                        } else if ($who == 1) {
                            echo Html::encode($rank['nickname']);
                        } else {
                            echo Html::encode($rank['nickname'] . '[' . $rank['username'] . ']');
                        }
                        ?>
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
                        } else if (isset($rank['wa_count'][$p['problem_id']])) {
                            $css_class = 'attempted';
                            $num = $rank['wa_count'][$p['problem_id']];
                            $time = '--';
                        }
                        echo "<th class=\"table-problem-cell {$css_class}\">{$num}<br><small>{$time}</small></th>";
                    }
                    ?>
                </tr>
            <?php endfor; ?>
            </tbody>
        </table>
    </div>
</div>

<footer class="footer">
    <div class="container">
        <p class="pull-left">&copy; <?= Yii::$app->params['ojName'] ?> OJ <?= date('Y') ?></p>
    </div>
</footer>
