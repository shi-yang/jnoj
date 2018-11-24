<?php

use yii\helpers\Html;

/* @var $model app\models\Contest */

$this->title = $model->title;
$problems = $model->problems;
$rank_result = $model->getRankData(false);
$first_blood = $rank_result['first_blood'];
$result = $rank_result['rank_result'];
$submit_count = $rank_result['submit_count'];

$this->registerAssetBundle('yii\bootstrap\BootstrapPluginAsset');
?>

<div class="wrap">
    <div class="container">
        <div class="alert alert-warning alert-dismissible fade in hidden-print" role="alert">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
            <p>提示：如需打印该榜单或将榜单导出为PDF文件，可以使用浏览器自带的打印功能
                （Chrome 浏览器可在页面上鼠标“右键”-“打印”，其它浏览器请自行利用搜索引擎获取使用方法），从中选择将此页面导出为 PDF 格式后再打印。</p>
            <p>此提示信息不会出现在浏览器的打印窗口中。</p>
        </div>
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
                <th width="120px">Username</th>
                <th width="120px">Nickname</th>
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
                        <?= Html::encode($rank['username']); ?>
                    </th>
                    <th>
                        <?= Html::encode($rank['nickname']); ?>
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
                            if ($first_blood[$p['problem_id']] == $rank['user_id']) {
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
