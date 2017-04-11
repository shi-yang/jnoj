<?php
include_once(dirname(__FILE__) . "/../functions/users.php");
include_once(dirname(__FILE__) . "/../functions/contests.php");

if ($current_user->is_root()) {
    $ccid = convert_str($_POST['ccid']);
    $reply = convert_str($_POST['answer']);
    $ispublic = convert_str($_POST["ispublic" . $ccid]);
    $sql_reply = "update contest_clarify set reply='" . $reply . "',ispublic='" . $ispublic . "' where ccid='" . $ccid . "'";
    $que_reply = $db->query($sql_reply);
    $ret["msg"] = "Reply Success.";
    $ret["code"] = 0;
} else {
    $ret["code"] = 1;
    $ret["msg"] = "Invalid Request!";
}
echo json_encode($ret);
