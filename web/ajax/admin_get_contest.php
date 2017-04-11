<?php
include_once(dirname(__FILE__) . "/../functions/contests.php");
include_once(dirname(__FILE__) . "/../functions/users.php");
$res = array();
$res["code"] = 1;
$cid = convert_str($_GET['cid']);
if ($current_user->is_root() && contest_get_val($cid, "type") != 99) {
    $query = "select cid,title,description,isprivate,start_time,end_time,lock_board_time,hide_others,report,type,has_cha,challenge_start_time,challenge_end_time from contest where cid='$cid'";
    $db->query($query);
    if ($db->num_rows > 0) {
        $res["code"] = 0;
        list($res["cid"], $res["title"], $res["description"], $res["isprivate"], $res["start_time"], $res["end_time"], $res["lock_board_time"], $res["hide_others"], $res["report"], $res["ctype"], $res["has_cha"], $res["challenge_start_time"], $res["challenge_end_time"]) = $db->get_row(null, ARRAY_N);
    } else $res["msg"] = "Invalid contest.";
} else $res["msg"] = "Please login as root!";
echo json_encode($res);

?>
