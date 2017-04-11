<?php

include_once(dirname(__FILE__) . "/../functions/global.php");
include_once(dirname(__FILE__) . "/../functions/users.php");
include_once(dirname(__FILE__) . "/../functions/problems.php");
$con = convert_str($_POST['content']);
$title = convert_str($_POST['title']);
$ret = array();
$ret["code"] = 1;
if (!$current_user->is_valid()) {
    $ret["msg"] = "Not logged in.";
} else if ($title == "") {
    $ret["msg"] = "No Title.";
} else {
    $pid = convert_str($_GET['pid']);
    if ($pid != "0" && $pid != "" && !problem_exist($pid)) {
        $ret["msg"] = "No Such Problem!";
        echo json_encode($ret);
        die();
    }
    $fid = convert_str($_GET['id']);
    $rid = convert_str($_GET['rid']);
    $uname = $nowuser;
    $sql = "INSERT INTO discuss (`id` ,`fid` ,`rid` ,`time` ,`title` ,`content` ,`uname` ,`pid`)VALUES (NULL ,'$fid',  '$rid', NOW( ) ,  '$title',  '$con',  '$uname',  '$pid')";
    $db->query($sql);
    $sql = "update time_bbs set time=NOW() where rid = $rid";
    $db->query($sql);
    $ret["code"] = 0;
    $ret["msg"] = "Success!";
}
echo json_encode($ret);
