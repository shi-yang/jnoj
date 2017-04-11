<?php
include_once(dirname(__FILE__) . "/../functions/users.php");
include_once(dirname(__FILE__) . "/../functions/problems.php");
include_once(dirname(__FILE__) . "/../functions/contests.php");
$cid = convert_str($_POST['cid']);
$ret = array();
$ret["code"] = 1;
if (contest_exist($cid) && !contest_passed($cid) && ($current_user->is_root() || $current_user->match(contest_get_val($cid, "owner")))) {
    $title = htmlspecialchars(convert_str($_POST['title']));
    $isprivate = 0;
    $description = htmlspecialchars(convert_str($_POST['description']));
    $lock_board_time = convert_str($_POST['lock_board_time']);
    $start_time = convert_str($_POST['start_time']);
    $end_time = convert_str($_POST['end_time']);

    if (!contest_started($cid) && $_POST["localtime"] == 1) {
        $dt = new DateTime($start_time, new DateTimeZone($_POST['localtz']));
        $dt->setTimezone(new DateTimeZone($mytimezone));
        $start_time = $dt->format('Y-m-d H:i:s');
        $dt = new DateTime($lock_board_time, new DateTimeZone($_POST['localtz']));
        $dt->setTimezone(new DateTimeZone($mytimezone));
        $lock_board_time = $dt->format('Y-m-d H:i:s');
        $dt = new DateTime($end_time, new DateTimeZone($_POST['localtz']));
        $dt->setTimezone(new DateTimeZone($mytimezone));
        $end_time = $dt->format('Y-m-d H:i:s');
    }

    $ctype = convert_str($_POST['ctype']);
    $hide_others = convert_str($_POST['hide_others']);
    if (!contest_started($cid)) $owner_viewable = $_POST['owner_viewable'] == "on" ? 1 : 0;
    $pass = pwd(convert_str($_POST['password']));
    if ($_POST['password'] != "") $isprivate = 2;
    if ($ctype == 0) $n = $config["limits"]["problems_on_contest_add"];
    else $n = $config["limits"]["problems_on_contest_add_cf"];
    foreach ($_POST['prob'] as $prob) {
        if (convert_str($prob['pid']) == "") continue;
        $pid[] = convert_str($prob['pid']);
        $lable[] = convert_str($prob['lable']);
        $ptype[] = convert_str($prob['ptype']);
        $base[] = convert_str($prob['base']);
        $minp[] = convert_str($prob['minp']);
        $paraa[] = convert_str($prob['para_a']);
        $parab[] = convert_str($prob['para_b']);
        $parac[] = convert_str($prob['para_c']);
        $parad[] = convert_str($prob['para_d']);
        $parae[] = convert_str($prob['para_e']);
    }
    $n = min($n, sizeof($pid));

    $stt = strtotime($start_time);
    $edt = strtotime($end_time);
    $lbt = strtotime($lock_board_time);
    $nt = time();

    //echo "$title $start_time $end_time $pid[0] $stt $edt $lbt $nt ";
    //echo $_POST['submit'];

    if ($title == "") {
        $ret["msg"] = "Please input title.";
    } else if (!contest_started($cid) && ($start_time == "" || $end_time == "" || $stt == 0 || $edt == 0 || $stt < $nt - 10 * 60)) {
        $ret["msg"] = "Start/end time not correctly filled.";
    } else if (!contest_started($cid) && (!problem_exist($pid[0]) || problem_hidden($pid[0]))) {
        $ret["msg"] = "First problem doesn't exist.";
    } else if (!contest_started($cid) && ($edt - $stt < 30 * 60 || $edt - $stt > 15 * 24 * 60 * 60 || ($lbt != 0 && ($lbt < $stt && $lbt > $edt)))) {
        $ret["msg"] = "Invalid contest length.";
    } else {

        if (!contest_started($cid)) $sql_add_con = "update contest set
            title='$title',
            description='$description',
            lock_board_time='$lock_board_time',
            start_time='$start_time',
            end_time='$end_time',
            hide_others='$hide_others',
            type='$ctype',
            password='$pass',
            isprivate='$isprivate',
            owner_viewable='$owner_viewable'
            where cid='$cid'";
        else $sql_add_con = "update contest set
            title='$title',
            description='$description',
            password='$pass',
            isprivate='$isprivate'
            where cid='$cid'";

        //$sql_add_con = change_in($sql_add_con);
        if (!contest_started($cid)) {
            $pd = false;
            for ($i = 0; $i < $n; $i++) {
                if (!problem_exist($pid[$i]) || problem_hidden($pid[$i])) $pd = true;
                else if ($ctype != 0) {
                    if ($ptype[$i] == 1 || $ptype[$i] == 3) {
                        if (!is_numeric($base[$i]) || !is_numeric($minp[$i]) || !is_numeric($paraa[$i]) || !is_numeric($parab[$i])) {
                            //                        echo $base[$i].is_numeric($base[$i]);
                            $pd = true;
                        }
                    } else if ($ptype[$i] == 2) {
                        if (!is_numeric($base[$i]) || !is_numeric($minp[$i]) || !is_numeric($paraa[$i]) || !is_numeric($parab[$i]) || !is_numeric($parac[$i]) || !is_numeric($parad[$i]) || !is_numeric($parae[$i])) {
                            //echo $i;
                            $pd = true;
                        }
                        if (abs(doubleval($paraa[$i]) + doubleval($parab[$i]) - 1.0) > 0.001 || doubleval($parad[$i]) < 0 || doubleval($parac[$i]) < 0.0001) {
                            $pd = true;
                        }
                    } else if ($ptype[$i] != 0) {
                        $pd = true;
                        break;
                    }
                }
            }
            if ($pd) {
                $ret["msg"] = "Invalid problem!";
                die(json_encode($ret));
            }
        }
        $db->query($sql_add_con);
        if (!contest_started($cid)) {
            $ssql = "delete from contest_problem where cid='$cid'";
            $db->query($ssql);
            for ($i = 0; $i < $n; $i++) {
                $que = false;
                if ($ctype == 0) $sql = "insert into contest_problem (cid ,pid,lable) values ('" . $cid . "','" . $pid[$i] . "','" . $lable[$i] . "')";
                else $sql = "insert into contest_problem (cid ,pid,lable,type,base,minp,para_a,para_b,para_c,para_d,para_e) values
                    ('" . $cid . "','" . $pid[$i] . "','" . $lable[$i] . "','" . $ptype[$i] . "','" . $base[$i] . "','" . $minp[$i] . "','" . $paraa[$i] . "','" . $parab[$i] . "','" . $parac[$i] . "','" . $parad[$i] . "','" . $parae[$i] . "')";

                $que = $db->query($sql);
            }
            $str = array();
            foreach ((array)$db->get_results("select problem.title from contest_problem,problem where cid=" . $cid . " and contest_problem.pid=problem.pid", ARRAY_N) as $value) {
                $str[] = trim(strtolower($value[0]));
            }
            sort($str);
            $db->query("update contest set allp='" . md5(implode($str, $config["salt_problem_in_contest"])) . "' where cid=" . $cid);
        }
        $ret["msg"] = "Success!";
        $ret["code"] = 0;
    }

} else {
    $ret["msg"] = "You cannot modify this contest.";
}

echo json_encode($ret);
