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
    $pid = intval($_GET['pid']);
    if ($pid != 0 && !problem_exist($pid)) {
        $ret["msg"] = "No Such Problem!";
        echo json_encode($ret);
        die();
    }
    $uname = $nowuser;
    $sql = "INSERT INTO discuss (`fid`  ,`time` ,`title` ,`content` ,`uname` ,`pid`)VALUES ('0',  NOW( ) ,  '$title',  '$con',  '$uname',  '$pid')";
    $db->query($sql);
    $num = $db->insert_id;
    $sql = "update discuss set rid=id where id='$num'";
    $db->query($sql);
    $sql = "insert into time_bbs (`rid` ,`time`,`pid`) values ('$num', NOW(),'$pid')";
    $db->query($sql);
    $ret["code"] = 0;
    $ret["msg"] = "Success!";
}
echo json_encode($ret);
