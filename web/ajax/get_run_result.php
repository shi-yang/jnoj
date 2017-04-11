<?php

include_once(dirname(__FILE__) . "/../functions/users.php");
include_once(dirname(__FILE__) . "/../functions/contests.php");
include_once(dirname(__FILE__) . "/../functions/runs.php");

$runid = convert_str($_GET['runid']);
$cid = run_get_val($runid, "contest_belong");
$uname = run_get_val($runid, "username");

$ret["code"] = 1;
if (!(($cid == "0" || contest_get_val($cid, "hide_others") == 0 || contest_passed($cid)) || $current_user->match($uname) || $current_user->is_root())) {
    $ret["msg"] = "Permission denined.";
} else {
    $query = "select runid,result,memory_used,time_used from status where runid='$runid'";
    $ret = $db->get_row($query, ARRAY_A);
    $ret["code"] = 0;
}
echo json_encode($ret);

