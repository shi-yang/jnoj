<?php
include_once(dirname(__FILE__) . "/../functions/users.php");
include_once(dirname(__FILE__) . "/../functions/problems.php");
include_once(dirname(__FILE__) . "/../functions/replays.php");
require_once(dirname(__FILE__) . "/../functions/simple_html_dom.php");

$oj = convert_str($_GET["oj"]);
$cid = convert_str($_GET["cid"]);

$ret = array();
if ($current_user->is_root()) {
    if ($oj == "ZJU") $ret = replay_crawl_zju($cid);
    else if ($oj == "HUSTV") $ret = replay_crawl_hustv($cid);
    else if ($oj == "UESTC") $ret = replay_crawl_uestc($cid);
    else if ($oj == "UVA") $ret = replay_crawl_uva($cid);
    else if ($oj == "OpenJudge") $ret = replay_crawl_openjudge($cid);
    else if ($oj == "SCU") $ret = replay_crawl_scu($cid);
    else if ($oj == "HUST") $ret = replay_crawl_hust($cid);
    else if ($oj == "CFGYM") $ret = replay_crawl_cfgym($cid);

    if ($ret["code"] == 1) {
        if ($ret["msg"] == "") $ret["msg"] = "Error occured!";
    } else $ret["msg"] = "Success!";
} else {
    $ret["code"] = 1;
    $ret["msg"] = "Please login as root!";
}

// JSON_FORCE_OBJECT is required to work with jquery.repopulate if array contained
echo json_encode($ret, JSON_FORCE_OBJECT);

?>
