<?php
include_once(dirname(__FILE__) . "/../functions/users.php");
include_once(dirname(__FILE__) . "/../functions/problems.php");
include_once(dirname(__FILE__) . "/../functions/contests.php");
$ret = array();
$ret["code"] = 1;
if ($current_user->is_root()) {
    $cid = convert_str($_GET['cid']);
    $pid = convert_str($_GET['pid']);
    $rac = convert_str($_GET['rac']);
    $type = convert_str($_GET['type']);
    if ($pid == "") {
        $ret["msg"] = "Invalid request.";
        echo json_encode($ret);
        exit;
    }
    if ($type == 2) {
        if (!contest_exist($cid)) {
            $ret["msg"] = "Invalid contest.";
            echo json_encode($ret);
            exit;
        }
        $pid = contest_get_pid_from_label($cid, $pid);
        if ($pid == null) {
            $ret["msg"] = "No such problem in this contest.";
            echo json_encode($ret);
            exit;
        }
    } else if (!problem_exist($pid)) {
        $ret["msg"] = "No such problem.";
        echo json_encode($ret);
        exit;
    }
    if ($cid != "") {
        if ($rac == 0) $sql_r = "update status set result='Rejudging' where pid='$pid' and contest_belong='$cid' and result!='Accepted' ";
        else $sql_r = "update status set result='Rejudging' where pid='$pid' and contest_belong='$cid' ";
    } else {
        $cid = 0;
        if ($rac == 0) $sql_r = "update status set result='Rejudging' where pid='$pid' and contest_belong='$cid' and result!='Accepted' ";
        else $sql_r = "update status set result='Rejudging' where pid='$pid' and contest_belong='$cid' ";

    }
    $db->query($sql_r);
    $host = $config["contact"]["server"];
    $port = $config["contact"]["port"];
    $fp = fsockopen($host, $port, $errno, $errstr);
    if (!$fp) {
        $ret["msg"] = "Socket open error!";
        echo json_encode($ret);
        die();
    } else {
        $msg = $config["contact"]["rejudge"] . "\n" . $pid . "\n" . $cid . "\n";
        if (fwrite($fp, $msg) === FALSE) {
            $ret["msg"] = "Socket send error!";
            echo json_encode($ret);
            die();
        }
        fclose($fp);
    }

    $ret["msg"] = "PID: $pid of CID: $cid has been sent to rejudge.";
    $ret["code"] = 0;
} else $ret["msg"] = "Please login as root!";
echo json_encode($ret);
?>
