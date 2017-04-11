<?php
include_once(dirname(__FILE__) . "/../functions/users.php");
include_once(dirname(__FILE__) . "/../functions/contests.php");

$cid = convert_str($_GET["cid"]);

$ret["code"] = 1;
$ret["msg"] = "Permission denied.";
if ($current_user->is_root() || ($current_user->match(contest_get_val($cid, "owner")) && !contest_started($cid))) {
    $ret["code"] = 0;
    contest_delete($cid);
    $ret["msg"] = "Contest $cid has been successfully deleted.";
}

echo json_encode($ret);
