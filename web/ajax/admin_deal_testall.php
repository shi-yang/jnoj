<?php
include_once(dirname(__FILE__) . "/../functions/contests.php");
include_once(dirname(__FILE__) . "/../functions/users.php");
$ret = array();
$ret["code"] = 1;
if ($current_user->is_root()) {
    $cid = convert_str($_GET['cid']);
    if ($cid == "" || contest_get_val($cid, "has_cha") == 0) {
        $ret["msg"] = "Invalid Request.";
    } else {
        $sql_r = "update status set result='Testing' where contest_belong='$cid' and result like 'Pretest Passed'";
        $db->query($sql_r);
        $host = $config["contact"]["server"];
        $port = $config["contact"]["port"];
        $fp = fsockopen($host, $port, $errno, $errstr);
        if (!$fp) {
            $ret["msg"] = "Message sent.";
            $ret["code"] = 0;
        } else {
            $msg = $config["contact"]["test_all"] . "\n" . $cid . "\n";
            @fwrite($fp, $msg);
            $ret["msg"] = "Message sent.";
            $ret["code"] = 0;
            fclose($fp);
        }
    }
} else $ret["msg"] = "Please login as root!";
echo json_encode($ret);
?>
