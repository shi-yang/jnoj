<?php
include_once(dirname(__FILE__) . "/../functions/problems.php");
include_once(dirname(__FILE__) . "/../functions/users.php");
$res = array();
$res["code"] = 1;
if ($current_user->is_root()) {
    $pid = convert_str($_GET['pid']);
    $query = "select pid,title,description,input,output,sample_in,sample_out,number_of_testcase,special_judge_status,time_limit,case_time_limit,memory_limit,hint,source,hide,ignore_noc,tags,author from problem where pid='$pid'";
    $db->query($query);
    if ($db->num_rows > 0) {
        $res["code"] = 0;
        list($res["pid"], $res["title"], $res["desc"], $res["inp"], $res["oup"], $res["sinp"], $res["sout"], $res["noc"], $res["spj"], $res["tl"], $res["ctl"], $res["ml"], $res["hint"], $res["source"], $res["p_hide"], $res["p_ignore_noc"], $res["author"]) = $db->get_row(null, ARRAY_N);
    } else $res["msg"] = "No such problem.";
} else $res["msg"] = "Please login as root!";
echo json_encode($res);
