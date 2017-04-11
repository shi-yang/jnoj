<?php
include_once(dirname(__FILE__) . "/../functions/users.php");
include_once(dirname(__FILE__) . "/../functions/contests.php");

$fid = intval(convert_str($_GET["fcid"]));
$tid = intval(convert_str($_GET["tcid"]));

$ret["code"] = 1;

if ($current_user->is_root()) {
    if ($fid > $tid) {
        $ret["msg"] = "CID $fid to $tid is invalid.";
        die(json_encode($ret));
    }
    $ret["code"] = 0;
    $ret["msg"] = "Contest [ ";
    for ($cid = $fid; $cid <= $tid; $cid++) {
        if (contest_get_val($cid, "isvirtual") == 1 && contest_get_val($cid, "type") == 99) {
            $ret["msg"] .= $cid . " ";
            contest_delete($cid);
        }
    }
    $ret["msg"] .= "] have been successfully deleted.";
} else {
    $ret["msg"] = "Permission denied.";
}
echo json_encode($ret);

?>