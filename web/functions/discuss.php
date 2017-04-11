<?php
include_once(dirname(__FILE__) . "/global.php");

function discuss_load_detail($id)
{
    global $db;
    $sql = " select * from discuss where id= '" . $id . "'";
    $res = $db->get_row($sql, ARRAY_A);
    $res["content_length"] = strlen($res["content"]);
    return $res;
}

function discuss_load_subject_list(&$res)
{
    global $db;
    $sql = " select * from discuss where fid=" . $res["id"];
    $db->query($sql);
    $res["child_num"] = $db->num_rows;
    $res["child"] = $db->get_results(null, ARRAY_A);
    foreach ((array)$res["child"] as $key => $value) {
        $res["child"][$key]["content_length"] = strlen($res["child"][$key]["content"]);
        $res["child"][$key]["content"] = "";
        discuss_load_subject_list($res["child"][$key]);
    }
}

function discuss_load_list($page = 1, $pid = 0)
{
    global $db, $config;

    $discussperpage = $config["limits"]["discuss_per_page"];
    $start = (intval($page) - 1) * $discussperpage;
    if ($pid > 0) $sql = "select distinct(rid) from time_bbs where pid='$pid' order by time desc limit $start,$discussperpage";
    else $sql = "select distinct(rid) from time_bbs order by time desc limit $start,$discussperpage";
    //$db->debug_all=true;
    $res = $db->get_results($sql, ARRAY_A);
    foreach ((array)$res as $key => $value) {
        $sql = " select * from discuss where rid= " . $value["rid"] . " and fid=0";
        $res[$key] = $db->get_row($sql, ARRAY_A);
        $res[$key]["content_length"] = strlen($res[$key]["content"]);
        $res[$key]["content"] = "";
        discuss_load_subject_list($res[$key]);
    }
    return $res;
}
