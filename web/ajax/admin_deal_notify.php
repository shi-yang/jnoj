<?php
include_once(dirname(__FILE__) . "/../functions/problems.php");
include_once(dirname(__FILE__) . "/../functions/users.php");
$ret = array();
$ret["code"] = 1;
if ($current_user->is_root()) {
    $sub = convert_str($_POST['sub']);
    $sql_up_con = "update config set value='$sub' where name='substitle'";
    $db->query($sql_up_con);
    $ret["code"] = 0;
    $ret["msg"] = "Success!";
} else $ret["msg"] = "Please login as root!";
echo json_encode($ret);
?>
