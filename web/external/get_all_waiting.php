<?php
include_once(dirname(__FILE__) . "/../functions/users.php");
include_once(dirname(__FILE__) . "/../functions/runs.php");

die(); // please manually delete this line if you need this function

$res = $db->get_results("
        SELECT runid,vname 
        FROM   status,problem 
        WHERE 
               " . (isset($_POST["rejudge"]) ? "result='Rejudging'" : "(result='Waiting' OR result like 'Judg%' OR result='Rejudging')") . " AND 
               status.pid=problem.pid " .
    (isset($_POST["pid"]) ? " AND status.pid='" . $db->escape($_POST["pid"]) . "'" : "") .
    (isset($_POST["cid"]) ? " AND status.contest_belong='" . $db->escape($_POST["cid"]) . "'" : "") .
    "ORDER BY runid", ARRAY_N);

foreach ((array)$res as $row) {
    echo $row[0] . " " . $row[1] . "\n";
}
