<?php

include_once(dirname(__FILE__) . "/../functions/users.php");
include_once(dirname(__FILE__) . "/../functions/contests.php");
$runid = convert_str($_POST['runid']);
if ($runid == "") {
    echo "Unable to rejudge.";
    die();
}

$ret = array();
$ret["code"] = 1;

$sql = "select pid,result,contest_belong,jnum from status where runid='$runid'";
list($pid, $result, $cid, $jnum) = $db->get_row($sql, ARRAY_N);

if (!$current_user->is_root() && $jnum + 1 >= $config["limits"]["max_error_rejudge_times"]) {
    $ret["msg"] = "Unable to rejudge. Already tried " . ($jnum + 1) . " times.";
    echo json_encode($ret);
    die();
}
if ($result != "Judge Error" && $result != "Judge Error (Vjudge Failed)" && $result != "") {
    $ret["msg"] = "Unable to rejudge.";
    echo json_encode($ret);
    die();
}

$ispretest = true;

if ($cid == "0" || !contest_has_challenge($cid) || contest_passed($cid)) $ispretest = false;


$host = $config["contact"]["server"];
$port = $config["contact"]["port"];
list($vname) = $db->get_row("select vname from problem where pid='$pid'", ARRAY_N);
$db->query("update status set result='Rejudging',jnum=jnum+1 where runid='$runid'");

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
