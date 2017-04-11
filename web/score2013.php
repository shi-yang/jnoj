<?php
$pagetitle = "2013年暑期训练积分榜";
include_once("header.php");

$contests = array(1892, 2007, 2013, 1895, 1896, 1897, 1898, 1899, 1900, 1901, 1902, 1903, 1904, 1905, 1906, 1907, 1908, 1909, 1910, 1911, 1912, 1913, 1914, 1915, 1916, 1917, 1918, 1919);

$team = array();
$team[] = array("latte", "李思源 张伯威 赵力", "team100   北京师范大学");
$team[] = array("crazier", "吴浪 陈辉 董自鸣", "team099   北京师范大学");
$team[] = array("cappu", "周奕洋 刘芳 盛乔一", "team097   北京师范大学");
$team[] = array("idonotknow", "王梦非 郑培凯 陈高翔", "team091   北京师范大学");
$team[] = array("xiaohai", "李奕 李安然 马凌霄", "team095   北京师范大学");
$team[] = array("hwd", "何伟强 吴雷 段兰君", "team093   北京师范大学");

for ($i = 0; $i < 6; $i++) $team[$i]["punish"] = 0.0;
foreach ($team as $j => $v) {
    $team[$j]["tval"] = array();
    $team[$j]["csum"] = 0;
    $team[$j]["psum"] = array();
    foreach ($contests as $i) {
        list($team[$j]["cac" . $i]) = mysql_fetch_array(mysql_query("
            select count(distinct(pid)) from (
                select pid from status
                where result='Accepted' and username='" . $v[0] . "' and contest_belong='$i'
                union
                select pid from replay_status
                where result='Accepted' and username='" . $v[2] . "' and contest_belong='$i'
            ) as a
        "));
        list($fitime) = mysql_fetch_array(mysql_query("select unix_timestamp(end_time) from contest where cid='$i'"));
        list($team[$j]["aac" . $i]) = mysql_fetch_array(mysql_query("
            select count(distinct(pid)) from (
                select pid from status
                where result='Accepted' and username='" . $v[0] . "'
                and unix_timestamp(time_submit) <= " . ($fitime + (2 * 24 + 7) * 60 * 60) . "
                and pid=any(select pid from contest_problem where cid='$i')
                union
                select pid from replay_status
                where result='Accepted' and username='" . $v[2] . "' and contest_belong='$i'
            ) as a
        "));
        $team[$j]["aac" . $i] = ($team[$j]["aac" . $i] - $team[$j]["cac" . $i]) * 0.3;
        $team[$j]["tval"][] = $team[$j]["aac" . $i] + $team[$j]["cac" . $i];
        $team[$j]["csum"] += $team[$j]["cac" . $i];
        $team[$j]["rsum"] += $team[$j]["aac" . $i] + $team[$j]["cac" . $i];
        $team[$j]["psum"][$i] = $team[$j]["rsum"];
    }
    sort($team[$j]["tval"]);
    $team[$j]["sum"] = 0;
    for ($i = sizeof($team[$j]["tval"]) - 1; $i >= sizeof($team[$j]["tval"]) - 24; $i--) {
        $team[$j]["sum"] += $team[$j]["tval"][$i];
    }
    $team[$j]["sum"] += $team[$j]["punish"];
}
?>
<div class="span12">
    <h1><?= $pagetitle ?></h1>
    <table class="table table-condensed table-striped table-hover">
        <thead>
        <tr>
            <th style="min-width:140px">队伍/比赛</th>
            <th>积分<sup>1</sup></th>
            <th>总分</th>
            <th>赛中</th>
            <th>罚分</th>
            <?php foreach ($contests as $i) { ?>
                <th><?= "<a href='contest_show.php?cid=$i' target=_blank>" . $i . "</a>" ?></th>
                <th><?= $i ?>*</th>
            <?php } ?>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($team as $j => $v) { ?>
        <tr>
            <td><?= $v[0] . "<br />" . $v[1] ?></td>
            <td><?= $v["sum"] ?></td>
            <td><?= $v["rsum"] ?></td>
            <td><?= $v["csum"] ?></td>
            <td><?= $v["punish"] ?></td>
            <?php foreach ($contests as $i) { ?>
                <td><?= $v["cac" . $i] ?></td>
                <td><?= $v["aac" . $i] ?></td>
            <?php } ?>
            <?php } ?>
        </tr>
        </tbody>
    </table>
    <p align="center">1. 积分为28取24。</p>
    <div id="rank_chart" style="min-width: 400px; height: 400px; margin: 0 auto">
    </div>
    <div id="score_sum_chart" style="min-width: 400px; height: 400px; margin: 0 auto">
    </div>
</div>

<script type="text/javascript" src="js/highcharts.js"></script>
<script type="text/javascript">
    $("table").tablesorter({sortList: [[1, 1]]});

    var chart = new Highcharts.Chart({
        chart: {
            renderTo: 'rank_chart',
            backgroundColor: null,
            type: 'line'
        },
        title: {
            text: '排名曲线',
            x: -20 //center
        },
        xAxis: {
            categories: [<?= implode($contests, ',') ?>]
        },
        yAxis: {
            title: {
                text: '排名'
            },
            plotLines: [{
                value: 0,
                width: 1,
                color: '#808080'
            }],
            min: 1,
            max: 9,
            reversed: true
        },
        tooltip: {
            formatter: function () {
                return '<b>' + this.series.name + '</b><br/>' +
                    this.x + ': 第' + this.y + '名';
            }
        },
        legend: {
            layout: 'vertical',
            align: 'right',
            verticalAlign: 'top',
            x: -10,
            y: 100,
            borderWidth: 0
        },
        series: [{
            name: 'latte',
            data: [1, 1, 1, 1]
        }, {
            name: 'cappu',
            data: [5, 4, 5, 6]
        }, {
            name: 'crazier',
            data: [2, 2, 2, 3]
        }, {
            name: 'hwd',
            data: [6, 6, 4, 4]
        }, {
            name: 'idontknow',
            data: [3, 3, 3, 2]
        }, {
            name: 'xiaohai',
            data: [4, 5, 6, 5]
        }]
    });
    var sumchart = new Highcharts.Chart({
        chart: {
            renderTo: 'score_sum_chart',
            backgroundColor: null,
            type: 'line'
        },
        title: {
            text: '累计分数曲线（全取，不算罚分）',
            x: -20 //center
        },
        xAxis: {
            categories: [<?=implode($contests, ",") ?>]
        },
        yAxis: {
            title: {
                text: '累计分数'
            },
            plotLines: [{
                value: 0,
                width: 1,
                color: '#808080'
            }],
            min: 0
        },
        tooltip: {
            formatter: function () {
                return '<b>' + this.series.name + '</b><br/>' +
                    '累积到CID ' + this.x + ': ' + this.y + '分';
            }
        },
        legend: {
            layout: 'vertical',
            align: 'right',
            verticalAlign: 'top',
            x: -10,
            y: 100,
            borderWidth: 0
        },
        series: [
            <?php
            $fir = false;
            foreach ($team as $j => $v) {
            if ($fir) echo ",";
            $fir = true;
            ?>
            {
                name: '<?= $v[0] ?>',
                data: [<?= implode($v["psum"], ",") ?>]
            }
            <?php
            }
            ?>
        ]
    });
</script>
<?php
include("footer.php");
?>
