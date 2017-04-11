<?php
include_once(dirname(__FILE__) . "/global.php");
function contest_get_standard_running_list($start = 0, $limit = 5)
{
    global $db;
    $sql = "select cid,title,end_time,has_cha,challenge_end_time from contest where start_time<now() and (end_time>now() or (has_cha=1 and challenge_end_time>now()) ) and isvirtual=0 order by start_time limit $start,$limit";
    $db->query($sql);
    foreach ((array)$db->get_results(null, ARRAY_A) as $row) {
        if ($row["has_cha"] == 1) $row["end_time"] = $row["challenge_end_time"];
        $ret[] = $row;
    }
    return $ret;
}

function contest_get_virtual_running_list($start = 0, $limit = 10)
{
    global $db;
    $sql = "select cid,title,end_time from contest where start_time<now() and end_time>now() and isvirtual=1 order by start_time desc limit $start,$limit";
    $ret = $db->get_results($sql, ARRAY_A);
    return $ret;
}

function contest_get_standard_scheduled_list($start = 0, $limit = 5)
{
    global $db;
    $sql = "select cid,title,start_time,type,end_time from contest where start_time>now() and isvirtual=0 order by start_time limit $start,$limit";
    $db->query($sql);
    foreach ((array)$db->get_results(null, ARRAY_A) as $row) {
        if ($row["type"] == 1) $row["title"] .= " [CF]";
        $ret[] = $row;
    }
    return $ret;
}

function contest_get_virtual_scheduled_list($start = 0, $limit = 5)
{
    global $db;
    $sql = "select cid,title,start_time,type from contest where start_time>now() and isvirtual=1 order by start_time limit $start,$limit";
    $db->query($sql);
    foreach ((array)$db->get_results(null, ARRAY_A) as $row) {
        if ($row["type"] == 1) $row["title"] .= " [CF]";
        $ret[] = $row;
    }
    return $ret;
}

function contest_get_pid_from_label($cid, $label)
{
    global $db;
    $sql = "select pid from contest_problem where cid='$cid' and lable='$label'";
    list($pid) = @$db->get_row($sql, ARRAY_N);
    return $pid;
}

function contest_get_label_from_pid($cid, $pid)
{
    global $db;
    $sql = "select lable from contest_problem where cid='$cid' and pid='$pid'";
    list($label) = @$db->get_row($sql, ARRAY_N);
    return $label;
}

function contest_get_status_before_time($cid, $time)
{
    global $db;
    if (!contest_exist($cid)) return null;
    if (contest_get_val($cid, "type") == 99) $sql = " SELECT pid,result,time_submit,username,username as nickname,contest_belong FROM replay_status WHERE contest_belong =" . $cid . " AND unix_timestamp(time_submit)<=$time  order by runid asc";
    else $sql = " SELECT pid,result,time_submit,status.username,nickname,contest_belong FROM status,user WHERE `status`.`contest_belong` =" . $cid . " AND status.username=user.username  AND unix_timestamp(status.time_submit)<=$time order by runid asc";
    return $db->get_results($sql, ARRAY_A);
}

function contest_get_challenge_before_time($cid, $time)
{
    global $db;
    if (!contest_exist($cid)) return null;
    $sql = "select user.username,nickname,cha_result,runid from challenge,user where cid='$cid' and user.username=challenge.username and unix_timestamp(cha_time)<=$time order by cha_id asc";
    return $db->get_results($sql, ARRAY_A);
}


function contest_get_problem_point_from_mixed($cid, $cpid, $t)
{
    if ($t < 0) $t = 0;
    if (!contest_exist($cid)) return null;
    $row = contest_get_problem_from_mixed($cid, $cpid);
    if ($row['type'] == 1) {
        $pt = $row['base'] - intval($t) / 60 * $row['para_a'];
        if ($pt < $row['minp']) $pt = $row['minp'];
    } else if ($row['type'] == 2) {
        //$t=intval(intval($t)/60);
        $t = intval($t);
        $pt = ($row['base'] * (
                doubleval($row['para_a']) +
                doubleval($row['para_b']) * doubleval($row['para_c']) * doubleval($row['para_c'])
                /
                (doubleval($row['para_d']) * $t * $t + doubleval($row['para_c']) * doubleval($row['para_c']))
            )
        );
        if ($pt < $row['minp']) $pt = $row['minp'];
    }
    return round($pt, 2);
}

function contest_get_all_clarify($cid)
{
    global $db;
    if (!contest_exist($cid)) return null;
    $query = "select * from contest_clarify where cid='$cid' order by ccid desc";
    return $db->get_results($query, ARRAY_A);
}

function contest_get_visible_clarify($cid, $user)
{
    global $db;
    if (!contest_exist($cid)) return null;
    $query = "select * from contest_clarify where cid='$cid' and (username='$user' or ispublic=1) order by ccid desc";
    return $db->get_results($query, ARRAY_A);
}


function contest_has_user($cid, $username)
{
    global $db;
    $result = $db->query("select * from contest_user where cid = '$cid' and username='" . $db->escape($username) . "'");
    return $db->num_rows;
}

$contest_infos = array();

function load_contest_infos($cid)
{
    global $db, $contest_infos;
    $sql = "select * from contest where cid='$cid'";
    $db->query($sql);
    if ($db->num_rows == 0) $contest_infos[$cid]["valid"] = false;
    else {
        //$contest_infos[$cid]=$db->get_row(null,ARRAY_A);
        $contest_infos[$cid]["cid"] = $cid;
        $contest_infos[$cid]["valid"] = true;
    }
}

function contest_has_challenge($cid)
{
    global $contest_infos;
    if (!isset($contest_infos[$cid])) load_contest_infos($cid);
    if (!$contest_infos[$cid]["valid"]) return false;
    return contest_get_val($cid, "has_cha") == 0 ? false : true;
}

function contest_exist($cid)
{
    global $contest_infos;
    if (!isset($contest_infos[$cid])) load_contest_infos($cid);
    return $contest_infos[$cid]["valid"];
}

function contest_started($cid)
{
    global $contest_infos;
    if (!isset($contest_infos[$cid])) load_contest_infos($cid);

    if (!$contest_infos[$cid]["valid"]) return false;
    return time() >= strtotime(contest_get_val($cid, "start_time"));
}

function contest_passed($cid)
{
    global $contest_infos;
    if (!isset($contest_infos[$cid])) load_contest_infos($cid);

    if (!$contest_infos[$cid]["valid"]) return false;
    if (contest_get_val($cid, "has_cha") == 0) return time() > strtotime(contest_get_val($cid, "end_time"));
    else return time() > strtotime(contest_get_val($cid, "challenge_end_time"));
}

function contest_challenging($cid)
{
    global $contest_infos;
    if (!isset($contest_infos[$cid])) load_contest_infos($cid);

    if (!$contest_infos[$cid]["valid"]) return false;
    if (contest_get_val($cid, "has_cha") == 0) return false;
    else return time() >= strtotime(contest_get_val($cid, "challenge_start_time")) && time() <= strtotime(contest_get_val($cid, "challenge_end_time"));
}

function contest_intermission($cid)
{
    global $contest_infos;
    if (!isset($contest_infos[$cid])) load_contest_infos($cid);

    if (!$contest_infos[$cid]["valid"]) return false;
    if (contest_get_val($cid, "has_cha") == 0) return false;
    else return time() > strtotime(contest_get_val($cid, "end_time")) && time() < strtotime(contest_get_val($cid, "challenge_start_time"));
}

function contest_coding($cid)
{
    global $contest_infos;
    if (!isset($contest_infos[$cid])) load_contest_infos($cid);

    if (!$contest_infos[$cid]["valid"]) return false;
    else return time() >= strtotime(contest_get_val($cid, "start_time")) && time() <= strtotime(contest_get_val($cid, "end_time"));
}

function contest_running($cid)
{
    global $contest_infos;
    if (!isset($contest_infos[$cid])) load_contest_infos($cid);

    if (!$contest_infos[$cid]["valid"]) return false;
    if (contest_get_val($cid, "has_cha") == 0) return contest_coding($cid);
    else return contest_challenging($cid) || contest_coding($cid);
}


function contest_is_private($cid)
{
    global $contest_infos;
    if (!isset($contest_infos[$cid])) load_contest_infos($cid);
    if (!$contest_infos[$cid]["valid"]) return false;
    return contest_get_val($cid, "isprivate") == 0 ? false : true;
}

function contest_get_problem_basic($cid)
{
    global $db, $contest_infos;

    if (!isset($contest_infos[$cid])) load_contest_infos($cid);
    if (!$contest_infos[$cid]["valid"]) return false;
    if (isset($contest_infos[$cid]["problems"])) return $contest_infos[$cid]["problems"];

    $ccsql = "select * from contest_problem where cid='$cid' order by lable asc";
    foreach ((array)$db->get_results($ccsql, ARRAY_A) as $row) {
        list($row["title"]) = $db->get_row("select title from problem where pid='" . $db->escape($row["pid"]) . "'", ARRAY_N);
        $contest_infos[$cid]["problems"][] = $row;
    }
    return $contest_infos[$cid]["problems"];
}

function contest_get_problem_summaries($cid)
{
    global $db, $contest_infos;

    if (!isset($contest_infos[$cid])) load_contest_infos($cid);
    if (!$contest_infos[$cid]["valid"]) return false;
    if (isset($contest_infos[$cid]["problems_got_summaries"])) return $contest_infos[$cid]["problems"];

    if (contest_get_val($cid, "type") == "99") $prefix = "replay_";
    else $prefix = "";

    foreach ((array)contest_get_problem_basic($cid) as $key => $row) {
        list($row["submit_run"]) = $db->get_row("select count(*) from " . $prefix . "status where contest_belong='$cid' and pid='" . $db->escape($row["pid"]) . "'", ARRAY_N);
        list($row["ac_run"]) = $db->get_row("select count(*) from " . $prefix . "status where contest_belong='$cid' and pid='" . $db->escape($row["pid"]) . "' and (result='Accepted' or result='Pretest Passed')", ARRAY_N);
        list($row["submit_user"]) = $db->get_row("select count(distinct username) from " . $prefix . "status where contest_belong='$cid' and pid='" . $db->escape($row["pid"]) . "'", ARRAY_N);
        list($row["ac_user"]) = $db->get_row("select count(distinct username) from " . $prefix . "status where contest_belong='$cid' and pid='" . $db->escape($row["pid"]) . "' and (result='Accepted' or result='Pretest Passed')", ARRAY_N);

        $contest_infos[$cid]["problems"][$key] = $row;
    }
    $contest_infos[$cid]["problems_got_summaries"] = true;
    return $contest_infos[$cid]["problems"];
}

function contest_get_number_of_users($cid)
{
    global $db, $contest_infos;
    if (!isset($contest_infos[$cid])) load_contest_infos($cid);
    if (!$contest_infos[$cid]["valid"]) return false;
    if (isset($contest_infos[$cid]["number_of_users"])) return $contest_infos[$cid]["number_of_users"];

    $db->query("select distinct(username) from status where contest_belong='$cid'");
    return $contest_infos[$cid]["number_of_users"] = $db->num_rows;

}

function contest_get_problem_from_mixed($cid, $cpid)
{
    global $db, $contest_infos;

    if (!isset($contest_infos[$cid])) load_contest_infos($cid);
    if (!$contest_infos[$cid]["valid"]) return false;
    foreach ((array)contest_get_problem_basic($cid) as $row) {
        if ($row["cpid"] == $cpid || $row["lable"] == $cpid) return $row;
    }
    return reset($contest_infos[$cid]["problems"]);
}

function contest_get_problem_from_title($cid, $title)
{
    global $db, $contest_infos;

    if (!isset($contest_infos[$cid])) load_contest_infos($cid);
    if (!$contest_infos[$cid]["valid"]) return false;
    foreach ((array)contest_get_problem_basic($cid) as $row) {
        if (strcasecmp($row["title"], $title) == 0) return $row;
    }
    return reset($contest_infos[$cid]["problems"]);
}

function contest_get_comparable_list($cid)
{
    global $db, $contest_infos;
    if (!isset($contest_infos[$cid])) load_contest_infos($cid);
    if (!$contest_infos[$cid]["valid"]) return false;

    if (isset($contest_infos[$cid]["comparable_list"])) return $contest_infos[$cid]["comparable_list"];

    $allp = $db->escape(contest_get_val($cid, "allp"));
    if (!contest_started($cid)) $csql = "select cid from contest where cid = '$cid'";
    else $csql = "select cid from contest where allp = '$allp' and start_time < NOW() order by start_time desc";
    foreach ((array)$db->get_results($csql, ARRAY_A) as $value) $contest_infos[$cid]["comparable_list"][] = $value["cid"];

    return $contest_infos[$cid]["comparable_list"];
}

function contest_get_col($cid, $str)
{
    global $db, $contest_infos;
    if (!isset($contest_infos[$cid])) load_contest_infos($cid);
    if (!$contest_infos[$cid]["valid"]) return null;
    if (isset($contest_infos[$cid][$str])) return $contest_infos[$cid][$str];
    $sql = "select $str from contest where cid='$cid'";
    $row = $db->get_row($sql, ARRAY_N);
    return $contest_infos[$cid][$str] = $row[0];
}

function contest_get_val($cid, $str)
{
    global $contest_infos;
    if (!isset($contest_infos[$cid])) load_contest_infos($cid);
    if (!$contest_infos[$cid]["valid"]) return null;
    if (isset($contest_infos[$cid][$str])) return $contest_infos[$cid][$str];
    $tstr = "contest_get_" . $str;
    if (function_exists($tstr)) return $str($cid);
    return contest_get_col($cid, $str);
}

function contest_delete($cid)
{
    global $db, $contest_infos;
    $db->query("delete from contest where cid='$cid'");
    $db->query("delete from contest_problem where cid='$cid'");
    $db->query("delete from replay_status where contest_belong='$cid'");
    unset($contest_infos[$cid]);
}
