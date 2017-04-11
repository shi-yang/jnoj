<?php

include_once(dirname(__FILE__) . "/../functions/users.php");
$runid = convert_str($_GET['runid']);
$isshare = convert_str($_GET['type']);
$query = "select username from status where runid='$runid'";
list($user) = $db->get_row($query, ARRAY_N);
if ($current_user->is_valid() && (strcasecmp($current_user->get_username(), $user) == 0 || $current_user->is_codeviewer())) {
    $sql = "update status set isshared='$isshare' where runid='$runid'";
    $result = $db->query($sql);
    $ret["code"] = 0;
    $ret["msg"] = "Success!";
} else {
    $ret["code"] = 1;
    $ret["msg"] = "Permission denied!";
}
echo json_encode($ret);

?>
