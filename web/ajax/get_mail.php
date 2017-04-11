<?php
include_once(dirname(__FILE__) . "/../functions/users.php");
$mailid = convert_str($_GET['mailid']);
$query = "select * from mail where mailid='$mailid'";
$row = $db->get_row($query, ARRAY_A);

$ret = array();
$ret["code"] = 1;
if (!$current_user->match($row["reciever"]) && !$current_user->match($row["sender"])) {
    $ret["msg"] = "Invalid Mail Request.";
} else {
    $ret = $row;
    $ret["code"] = 0;
    $query = "update mail set status=true where mailid='$mailid'";
    if ($current_user->match($row["reciever"])) $res = $db->query($query);
}

echo json_encode($ret);

?>

