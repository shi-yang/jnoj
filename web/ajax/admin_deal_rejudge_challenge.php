<?php
include_once(dirname(__FILE__) . "/../functions/users.php");
include_once(dirname(__FILE__) . "/../functions/problems.php");
include_once(dirname(__FILE__) . "/../functions/contests.php");
$ret = array();
$ret["code"] = 1;
if ($current_user->is_root()) {
    $cid = convert_str($_GET['cid']);
    $type = convert_str($_GET['type']);
    if ($cid != "" && contest_exist($cid)) {
        $sql_r = "update challenge set cha_result='Pending' where cid='$cid'";
    } else {
        $ret["msg"] = "Invalid request.";
        die(json_encode($ret));
    }

    if (contest_get_val($cid, "has_cha") == 0) {
        $ret["msg"] = "No challenge in this contest.";
        die(json_encode($ret));
    }

    $que_r = $db->query($sql_r);

    $sql = "select cha_id,runid from challenge where cha_result='Pending' and cid='$cid'";
    //$res=mysql_query($sql);
    //if (db_problem_isvirtual($pid)) $port=$vserver_port; else $port=$server_port;
    $host = $config["contact"]["server"];
    $port = $config["contact"]["port"];
    foreach ((array)$db->get_results($res, ARRAY_A) as $row) {
        $fp = fsockopen($host, $port, $errno, $errstr);
        if ($fp) {
            list($vname) = $db->get_row("select vname from problem,status where runid='" . $row['runid'] . "' and problem.pid=status.pid", ARRAY_N);
            $msg = $config["contact"]["challenge"] . "\n" . $row['cha_id'] . "\n" . $vname . "\n";
            fwrite($fp, $msg);
            fclose($fp);
        }
    }
    $ret["msg"] = "Message sent.";
    $ret["code"] = 0;
} else $ret["msg"] = "Please login as root!";
echo json_encode($ret);
?>
