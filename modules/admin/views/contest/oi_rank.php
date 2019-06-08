<?php

use yii\helpers\Html;

/* @var $model app\models\Contest */

$this->title = $model->title;
$problems = $model->problems;
$rank_result = $model->getOIRankData(false);
$first_blood = $rank_result['first_blood'];
$result = $rank_result['rank_result'];
$submit_count = $rank_result['submit_count'];

$this->registerAssetBundle('yii\bootstrap\BootstrapPluginAsset');
?>
<div class="wrap">
    <div class="container">
        <div class="alert alert-warning alert-dismissible fade in hidden-print" role="alert">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
            <p>提示：</p>
            <ul>
                <li>可以使用浏览器自带的打印功能（Chrome 浏览器可在页面上鼠标“右键”-“打印”，其它浏览器请自行利用搜索引擎获取使用方法），
                    可以选择将此页面导出为 PDF 格式。此提示信息不会出现在浏览器的打印窗口中。</li>
                <li>比赛期间，若设了封榜，即使到了封榜期间，此榜单依然为实时榜单。</li>
                <li>比赛结束后，由于前台依然能够开放提交，此榜单为比赛期间的榜单。</li>
            </ul>
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
                <th width="80px">测评总分</th>
                <th width="80px">订正总分</th>
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
                    <th class="score-solved">
                        <?= $rank['total_score'] ?>
                    </th>
                    <th class="score-time">
                        <?= $rank['correction_score'] ?>
                    </th>
                    <?php
                    foreach($problems as $key => $p) {
                        $score = "";
                        $max_score = "";
                        if (isset($rank['score'][$p['problem_id']])) {
                            $score = $rank['score'][$p['problem_id']];
                            $max_score = $rank['max_score'][$p['problem_id']];
                        }
                        echo "<th class=\"table-problem-cell\">{$score}<br><small>{$max_score}</small></th>";
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
        <p class="pull-left">&copy; <?= Yii::$app->setting->get('ojName') ?> OJ <?= date('Y') ?></p>
    </div>
</footer>
