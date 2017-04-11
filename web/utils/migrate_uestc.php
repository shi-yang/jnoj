<?php
/**
 * Short description for migrate_uestc.php
 *
 * @package migrate_uestc
 * @author Chen Ran <crccw@moonux.org>
 * @version 0.1
 * @copyright (C) 2015 Chen Ran <crccw@moonux.org>
 * @license CC-BY-SA 4.0
 */
include_once(dirname(__FILE__) . "/../functions/global.php");
include_once(dirname(__FILE__) . "/../functions/problems.php");
include_once(dirname(__FILE__) . "/../functions/pcrawlers.php");

$old = $db->get_results("select pid,vid,title from problem_uestc where vname = 'UESTC'", ARRAY_A);
$new = array();
for ($pid = 1; $pid <= 1064; $pid++) {
    $url = "http://acm.uestc.edu.cn/problem/data/$pid";
    $data = json_decode(file_get_contents($url), true);

    if ($data['result'] === "error") continue;
    $problem = $data['problem'];
    $title = $problem['title'];
    $new[$pid] = $title;
}
$t = array();
foreach ($old as $problem) {
    $newid = array_keys($new, $problem['title']);
    $t[$problem['vid']] = array(
        'pid' => $problem['pid'],
        'old_id' => $problem['vid'],
        'title' => $problem['title'],
    );
    if (sizeof($newid) == 1) {
        $t[$problem['vid']]['new_id'] = $newid[0];
    }
}
$t[1000]['new_id'] = 1;
$t[1011]['new_id'] = 32;
$t[1013]['new_id'] = 33;
$t[1151]['new_id'] = 73;
$t[1156]['new_id'] = 78;
$t[1189]['new_id'] = 95;
$t[1203]['new_id'] = 133;
$t[1204]['new_id'] = 134;
$t[1210]['new_id'] = 140;
$t[1214]['new_id'] = 144;
$t[1215]['new_id'] = 145;
$t[1217]['new_id'] = 147;
$t[1227]['new_id'] = 207;
$t[1293]['new_id'] = 236;
$t[1299]['new_id'] = 242;
$t[1334]['new_id'] = 277;
$t[1339]['new_id'] = 282;
$t[1401]['new_id'] = 336;
$t[1405]['new_id'] = 340;
$t[1406]['new_id'] = 341;
$t[1447]['new_id'] = 371;
$t[1451]['new_id'] = 375;
$t[1453]['new_id'] = 377;
$t[1465]['new_id'] = 389;
$t[1472]['new_id'] = 396;
$t[1502]['new_id'] = 426;
$t[1520]['new_id'] = 444;
$t[1525]['new_id'] = 449;
$t[1561]['new_id'] = 485;
$t[1588]['new_id'] = 512;
//$t[1623]['new_id']=1;
$t[1638]['new_id'] = 559;
$t[1639]['new_id'] = 560;
$t[1640]['new_id'] = 561;
$t[1652]['new_id'] = 573;
$t[1655]['new_id'] = 576;
$t[1656]['new_id'] = 42;
$t[1657]['new_id'] = 42;
$t[1658]['new_id'] = 43;
$t[1659]['new_id'] = 43;
$t[1660]['new_id'] = 44;
$t[1661]['new_id'] = 44;
$t[1662]['new_id'] = 45;
$t[1663]['new_id'] = 45;
$t[1664]['new_id'] = 46;
$t[1665]['new_id'] = 46;
$t[1666]['new_id'] = 47;
$t[1667]['new_id'] = 47;
$t[1668]['new_id'] = 577;
$t[1669]['new_id'] = 578;
$t[1670]['new_id'] = 579;
$t[1711]['new_id'] = 86;
$t[1717]['new_id'] = 92;
$t[1807]['new_id'] = 682;
$t[1810]['new_id'] = 685;

if ($com)
    echo "pid\told_id\tnew_id\ttitle\n";
foreach ($t as $x) {
    if (!isset($x['new_id'])) continue;
    echo implode("\t", $x) . "\n";
}
if ($_SERVER["argv"][1] === "commit") {
    foreach ($t as $x) {
        if (!isset($x['new_id'])) continue;
        $db->query("update problem set vid=" . $x['new_id'] . " where pid=" . $x['pid'] . ";");
    }
}

echo "FAILED:\n";
echo "pid\told_id\t\t\n";
foreach ($t as $x) {
    if (isset($x['new_id'])) continue;
    echo implode("\t", $x) . "\n";
}
