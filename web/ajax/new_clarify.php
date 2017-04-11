<?php
include_once(dirname(__FILE__) . "/../functions/users.php");
include_once(dirname(__FILE__) . "/../functions/contests.php");
$question = convert_str($_POST['question']);
$cid = convert_str($_GET['cid']);
if ($cid == "") $cid = "0";
$ret = array();
$ret["code"] = 1;
if (!$current_user->is_valid()) {
    $ret["msg"] = "Please login.";
    echo json_encode($ret);
    die();
} else if ($cid == "0" || !contest_running($cid)) {
    $ret["msg"] = "Contest is not running.";
    echo json_encode($ret);
    die();
} else if (!(contest_get_val($cid, "isprivate") == 0 ||
    (contest_get_val($cid, "isprivate") == 1 && $current_user->is_in_contest($cid)) ||
    (contest_get_val($cid, "isprivate") == 2 && contest_get_val($cid, "password") == $_COOKIE[$config["cookie_prefix"] . "contest_pass_$cid"]))
) {
    $ret["msg"] = "You are not in this contest.";
    echo json_encode($ret);
    die();
}
$query = "insert into contest_clarify set cid='$cid',question='$question',username='$nowuser',ispublic=0";
$db->query($query);
$ret["code"] = 0;
$ret["msg"] = "Success!";
echo json_encode($ret);
?>
