<?php
include_once(dirname(__FILE__) . "/../functions/users.php");
include_once(dirname(__FILE__) . "/../functions/contests.php");
$ret = array();
$ret["code"] = 1;

if (!$current_user->is_root()) {
    $ret["msg"] = "Please login as root!";
    die(json_encode($ret));
}

$runid = convert_str($_GET['runid']);
if ($runid == "") {
    $ret["msg"] = "Invalid request.";
    die(json_encode($ret));
}
$sql = "select pid,result,contest_belong from status where runid='$runid'";
list($pid, $result, $cid) = $db->get_row($sql, ARRAY_N);

if ($pid == "") {
    $ret["msg"] = "Invalid runid.";
    die(json_encode($ret));
}

$ispretest = true;

if ($cid == "0" || contest_get_val($cid, "has_cha") == 0 || contest_passed($cid)) $ispretest = false;

$host = $config["contact"]["server"];
$port = $config["contact"]["port"];
list($vname) = $db->get_row("select vname from problem where pid='$pid'", ARRAY_N);

$sql_r = "update status set result='Rejudging' where runid='$runid' ";
$db->query($sql_r);
$host = $config["contact"]["server"];
$port = $config["contact"]["port"];
$fp = @fsockopen($host, $port, $errno, $errstr);
if (!$fp) {
    $ret["msg"] = "Socket open error!";
    echo json_encode($ret);
    die();
} else {
    if (!$ispretest) $msg = $config["contact"]["error_rejudge"] . "\n" . $runid . "\n" . $vname;
    else $msg = $config["contact"]["pretest"] . "\n" . $runid . "\n" . $vname;
    if (@fwrite($fp, $msg) === FALSE) {
        $ret["msg"] = "Socket send error!";
        echo json_encode($ret);
        die();
    }
    fclose($fp);
}


$ret["msg"] = $runid . " has been sent to Rejudge.";
$ret["code"] = 0;
echo json_encode($ret);

?>
