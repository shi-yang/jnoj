<?php
include_once(dirname(__FILE__) . "/../functions/problems.php");
include_once(dirname(__FILE__) . "/../functions/users.php");
$ret = array();
$ret["code"] = 1;
if ($current_user->is_root()) {
    $cid = convert_str($_GET['cid']);
    $share = convert_str($_GET['share']);
    if ($cid == "" || $share == "") {
        $ret["msg"] = "Invalid request.";
    } else {
        $sql_r = "update status set isshared='$share' where contest_belong='$cid'";
        $db->query($sql_r);
        if ($share == 1) $ret["msg"] = "Share";
        else $ret["msg"] = "Unshare";
        $ret["msg"] .= " succeed.";
        $ret["code"] = 0;
    }
} else $ret["msg"] = "Please login as root!";
echo json_encode($ret);
?>

