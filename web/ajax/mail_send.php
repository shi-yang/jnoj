<?php
include_once(dirname(__FILE__) . "/../functions/users.php");
$reciever = convert_str($_POST['reciever']);
$title = convert_str($_POST['title']);
$content = convert_str($_POST['content']);
$ret = array();
$ret["code"] = 1;
if (!$current_user->is_valid()) {
    $ret["msg"] = "Please Login!";
} else if (!user_exist($reciever)) {
    $ret["msg"] = "No Such Reciever.";
} else {
    if ($title == "") $title = "No Title";
    $query = "insert into mail set sender='$nowuser', reciever='$reciever', content='$content', title='$title', mail_time=now(), status=false";
    $res = $db->query($query);
    $ret["code"] = 0;
    $ret["msg"] = "Success.";
}
echo json_encode($ret);
?>
