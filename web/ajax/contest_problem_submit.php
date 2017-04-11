<?php
include_once(dirname(__FILE__) . "/../functions/users.php");
include_once(dirname(__FILE__) . "/../functions/problems.php");
include_once(dirname(__FILE__) . "/../functions/contests.php");
$uname = convert_str($_POST['user_id']);
$lang = convert_str($_POST['language']);
$src = convert_str($_POST['source']);
$lab = convert_str($_POST['lable']);
$cid = convert_str($_POST['contest_id']);
$isshare = convert_str($_POST['isshare']);
if ($isshare != "0") $isshare = "1";
setcookie($config["cookie_prefix"] . "defaultshare", $isshare, time() + 7 * 24 * 60 * 60, $config["base_path"]);
$ip = get_ip();
$flag = $flag2 = true;
$pid = contest_get_pid_from_label($cid, $lab);

if (strlen($src) > $config["limits"]["max_source_code_len"]) {
    $ret["msg"] = "Source too long!";
    echo json_encode($ret);
    die();
}
if (strlen($src) == 0) {
    $ret["msg"] = "No source code!";
    echo json_encode($ret);
    die();
}
if (!$current_user->is_valid() || !$current_user->match($uname)) {
    clear_cookies();
    $ret["msg"] = "Invalid User.";
    echo json_encode($ret);
    die();
}
if (!contest_exist($cid)) {
    $ret["msg"] = "No Such Contest.";
    echo json_encode($ret);
    die();
}
if (contest_get_val($cid, "isprivate") == 1 && !$current_user->is_in_contest($cid)) {
    $ret["msg"] = "Not in this contest.";
    echo json_encode($ret);
    die();
}
if (contest_get_val($cid, "isprivate") == 2 && contest_get_val($cid, "password") != $_COOKIE[$config["cookie_prefix"] . "contest_pass_$cid"]) {
    $ret["msg"] = "Wrong Password.";
    echo json_encode($ret);
    die();
}
if ($pid == null) {
    $ret["msg"] = "No such problem in this contest.";
    echo json_encode($ret);
    die();
}
if (!problem_exist($pid)) {
    $ret["msg"] = "No Such Problem.";
    echo json_encode($ret);
    die();
}
if (contest_intermission($cid)) {
    $ret["msg"] = "You cannot submit in intermission phase.";
    echo json_encode($ret);
    die();
}
if (contest_challenging($cid)) {
    $ret["msg"] = "You cannot submit in challenge phase.";
    echo json_encode($ret);
    die();
}
if ($lang == 0) {
    $ret["msg"] = "Please Select Language.";
    echo json_encode($ret);
    die();
}
list($vname) = @$db->get_row("select vname from problem where pid='$pid'", ARRAY_N);
if (!in_array($lang, problem_support_lang($vname))) {
    $ret["msg"] = "Language Invalid.";
    echo json_encode($ret);
    die();
}
if (time() - strtotime($current_user->get_val("last_submit_time")) < 5) {
    $ret["msg"] = "Too Fast!";
    echo json_encode($ret);
    die();
}

if ($cid == "" || contest_passed($cid)) $cid = "0";

if ($lang < 4 && $lang > 0) setcookie($config["cookie_prefix"] . "lastlang", $lang, time() + 60 * 60 * 24 * 30, $config["base_path"]);


$query = "insert into status set pid='$pid' ,source='$src' ,contest_belong='$cid', result='Waiting', language='$lang', username='$uname', ipaddr='$ip', isshared='$isshare', time_submit='" . date("Y-m-d G:i:s", time()) . "' ";
$result = $db->query($query);
$nowid = $db->insert_id;
$query = "update problem set total_submit=total_submit+1 where pid='$pid' ";
$result = $db->query($query);
$query = "update user set total_submit=total_submit+1 where username='$uname' ";
$result = $db->query($query);

$ret = array(
    "runid" => $nowid
);
$host = $config["contact"]["server"];
$port = $config["contact"]["port"];
$fp = @fsockopen($host, $port, $errno, $errstr);
if (!$fp) {
    if ($cid == "0") $ret["msg"] = "Transmitted.";
    else $ret["msg"] = "Submitted.";
    $ret["code"] = 0;
    echo json_encode($ret);
    die();
} else {
    if (contest_get_val($cid, "has_cha") == "1") $msg = $config["contact"]["pretest"] . "\n" . $nowid;
    else $msg = $config["contact"]["submit"] . "\n" . $nowid;

    $msg = $msg . "\n" . $vname;
    if (@fwrite($fp, $msg) === FALSE) {
        if ($cid == "0") $ret["msg"] = "Transmitted.";
        else $ret["msg"] = "Submitted.";
        $ret["code"] = 0;
        echo json_encode($ret);
        die();
    }
    fclose($fp);
}
if ($cid == "0")
    $ret["msg"] = "Transmitted.";
else
    $ret["msg"] = "Submitted.";
$ret["code"] = 0;
echo json_encode($ret);
