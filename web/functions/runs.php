<?php
include_once(dirname(__FILE__) . "/global.php");

$run_infos = array();

function run_load_info($runid)
{
    global $db, $run_infos;
    $sql = "select * from status where runid='$runid'";
    $db->query($sql);
    if ($db->num_rows == 0) $run_infos[$runid]["valid"] = false;
    else {
        //$run_infos[$runid]=$db->get_row(null,ARRAY_A);
        $run_infos[$runid]["valid"] = true;
    }
}

function run_load_all($runid)
{
    global $db, $run_infos;
    $sql = "select * from status where runid='$runid'";
    $db->query($sql);
    if ($db->num_rows == 0) $run_infos[$runid]["valid"] = false;
    else {
        $run_infos[$runid] = $db->get_row(null, ARRAY_A);
        $run_infos[$runid]["valid"] = true;
    }
}

function run_get_col($runid, $str)
{
    global $db, $run_infos;
    if (!isset($run_infos[$runid])) run_load_info($runid);
    if (!$run_infos[$runid]["valid"]) return null;
    if (isset($run_infos[$runid][$str])) return $run_infos[$runid][$str];
    $sql = "select $str from status where runid='$runid'";
    $row = $db->get_row($sql, ARRAY_N);
    return $run_infos[$runid][$str] = $row[0];
}

function run_get_val($runid, $str)
{
    global $run_infos;
    if (!isset($run_infos[$runid])) run_load_info($runid);
    if (!$run_infos[$runid]["valid"]) return null;
    if (isset($run_infos[$runid][$str])) return $run_infos[$runid][$str];
    $tstr = "run_get_" . $str;
    if (function_exists($tstr)) return $str($runid);
    return run_get_col($runid, $str);
}
