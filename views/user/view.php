<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\User */
/* @var $contests array */

$this->title = $model->nickname;
$solutionStats = $model->getSolutionStats();

$this->registerJsFile("https://cdn.bootcss.com/flot/0.8.3/jquery.flot.min.js", ['depends' => 'yii\web\JqueryAsset']);
$this->registerJsFile("https://cdn.bootcss.com/flot/0.8.3/jquery.flot.time.js", ['depends' => 'yii\web\JqueryAsset']);

$plotJS = <<<EOT
var contests_json = {$contests};
var data1 = new Array();
var data2 = new Array();
var min_score = 6000, max_score = 0;
for (var i in contests_json) {
    if (min_score > contests_json[i].total)
        min_score = contests_json[i].total;
    if (max_score < contests_json[i].total)
        max_score = contests_json[i].total;
    data1.push([
        contests_json[i].start_time * 1000,
        contests_json[i].total, //1
        988, //2
        contests_json[i].title,
        contests_json[i].title,
        contests_json[i].rating_change,  //5
        contests_json[i].rank, //6
        contests_json[i].url,
        contests_json[i].level, //8
        contests_json[i].level,
        "1571360",
        contests_json[i].title,
    ]);
    data2.push([
        contests_json[i].start_time * 1000, contests_json[i].total
    ]);
}
var datas = [
    {label: "{$model->username}", data: data1},
    {clickable: false, hoverable: false, color: "red", data: data2}
];

var markings = [
    { color: '#a00', lineWidth: 1, yaxis: { from: 3000 } },
    { color: '#f33', lineWidth: 1, yaxis: { from: 2600, to: 2999 } },
    { color: '#f77', lineWidth: 1, yaxis: { from: 2400, to: 2599 } },
    { color: '#ffcc88', lineWidth: 1, yaxis: { from: 2150, to: 2399 } },
    { color: '#f8f', lineWidth: 1, yaxis: { from: 1900, to: 2149 } },
    { color: '#aaf', lineWidth: 1, yaxis: { from: 1650, to: 1899 } },
    { color: '#77ddbb', lineWidth: 1, yaxis: { from: 1400, to: 1649 } },
    { color: '#7f7', lineWidth: 1, yaxis: { from: 1150, to: 1399 } },
    { color: '#ccc', lineWidth: 1, yaxis: { from: 0, to: 1149 } },
];

var options = {
    lines: { show: true },
    points: { show: true },
    xaxis: { mode: "time" },
    yaxis: { min: min_score - 400, max: max_score + 600, ticks: [1150, 1400, 1650, 1900, 2150, 2300, 2400, 2600, 3000] },
    grid: { hoverable: true, markings: markings }
};

var plot = $.plot($("#placeholder"), datas, options);

function showTooltip(x, y, contents) {
    $('<div id="tooltip">' + contents + '</div>').css( {
        position: 'absolute',
        display: 'none',
        top: y - 20,
        left: x + 10,
        border: '1px solid #fdd',
        padding: '2px',
        'font-size' : '11px',
        'background-color': '#fee',
        opacity: 0.80
    }).appendTo("body").fadeIn(200);
}

var ctx = plot.getCanvas().getContext("2d");

var prev = -1;
$("#placeholder").bind("plothover", function (event, pos, item) {
    if (item) {
        if (prev != item.dataIndex) {
            $("#tooltip").remove();
            var params = data1[item.dataIndex];
            var total = params[1];
            var change = params[5] > 0 ? "+" + params[5] : params[5];
            var contestName = params[11];
            var contestUrl = params[7];
            var rank = params[6];
            var title = params[8];
            var html = "= " + total + " (" + change + "), " + title + "<br/>"
                            + "Rank: " + rank + "<br/>"
                            + "<a href='" + contestUrl + "'>" + contestName + "</a>";
            showTooltip(item.pageX, item.pageY, html);
            setTimeout(function () {
                $("#tooltip").fadeOut(200);
                prev = -1;
            }, 4000);
            prev = item.dataIndex;
        }
    }
});
EOT;
$this->registerJs($plotJS);
?>
<div class="user-view">

    <h1><?= Html::encode($this->title) ?></h1>
    <hr>
    <div class="row">
        <div class="col-md-3">
            <?= DetailView::widget([
                'model' => $model,
                'attributes' => [
                    'username',
                    'nickname',
                    [
                        'attribute' => Yii::t('app', 'QQ'),
                        'value' => function ($model, $widget) {
                            return Html::encode($model->profile->qq_number);
                        },
                        'format' => 'raw'
                    ],
                    [
                        'attribute' => Yii::t('app', 'Major'),
                        'value' => function ($model, $widget) {
                            return Html::encode($model->profile->major);
                        },
                        'format' => 'raw'
                    ],
                    [
                        'attribute' => Yii::t('app', 'Student Number'),
                        'value' => function ($model, $widget) {
                            return $model->profile->student_number;
                        },
                        'format' => 'raw'
                    ]
                ],
            ]) ?>
        </div>
        <div class="col-md-9">
            <div id="placeholder" style="width:100%;height:300px;"></div>
        </div>
    </div>
    <hr>
    <h3><?= Yii::t('app', 'Solved Problem') ?> <small>(<?= count($solutionStats['solved_problem']) ?>)</small></h3>
    <ul>
        <?php foreach ($solutionStats['solved_problem'] as $p): ?>
            <li class="label label-default"><?= Html::a($p, ['/problem/view', 'id' => $p], ['style' => 'color:#fff']) ?></li>
        <?php endforeach; ?>
    </ul>
    <hr>
    <h3><?= Yii::t('app', 'Unsolved Problem') ?> <small>(<?= count($solutionStats['unsolved_problem']) ?>)</small></h3>
    <ul>
        <?php foreach ($solutionStats['unsolved_problem'] as $p): ?>
            <li class="label label-default"><?= Html::a($p, ['/problem/view', 'id' => $p], ['style' => 'color:#fff']) ?></li>
        <?php endforeach; ?>
    </ul>

    <hr>
    <h2>Statistics</h2>
    <div class="row">
        <div class="left-list col-md-6">
            <ul class="stat-list">
                <li>
                    <strong>Submissions</strong><span> <?= $solutionStats['all_count'] ?></span>
                </li>
                <li>
                    <strong>Accepted submissions</strong><span> <?= $solutionStats['ac_count'] ?></span>
                </li>
                <li>
                    <strong>Submission ratio</strong>
                    <span>
                        <?= $solutionStats['all_count'] == 0 ? 0 : number_format($solutionStats['ac_count'] / $solutionStats['all_count'] * 100, 2) ?> %
                    </span>
                </li>
            </ul>
        </div>
        <div class="right-list col-md-6">
            <ul class="stat-list">
                <li>
                    <strong>Wrong Answer</strong><span> <?= $solutionStats['wa_count'] ?></span>
                </li>
                <li>
                    <strong>Time Limit Exceeded</strong><span> <?= $solutionStats['tle_count'] ?></span>
                </li>
                <li>
                    <strong>Compile Error</strong><span> <?= $solutionStats['ce_count'] ?></span>
                </li>
            </ul>
        </div>
    </div>
</div>
