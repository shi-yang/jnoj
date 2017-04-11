<?php
include_once(dirname(__FILE__) . "/../functions/problems.php");
include_once(dirname(__FILE__) . "/../functions/users.php");
$ret = array();
$ret["code"] = 1;
if ($current_user->is_root()) {
    $cid = convert_str($_GET['cid']);
    $hide = convert_str($_GET['hide']);
    if ($cid == "" || $hide == "") {
        $ret["msg"] = "Invalid request.";
    } else {
        $sql_r = "update problem set hide='$hide' where pid=any(select pid from contest_problem where cid='$cid')";
        $db->query($sql_r);
        if ($hide == 1) $ret["msg"] = "Lock";
        else $ret["msg"] = "Unlock";
        $ret["msg"] .= " succeed.";
        $ret["code"] = 0;
    }
} else $ret["msg"] = "Please login as root!";
echo json_encode($ret);
?>
