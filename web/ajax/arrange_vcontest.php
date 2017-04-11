<?php
include_once(dirname(__FILE__) . "/../functions/users.php");
include_once(dirname(__FILE__) . "/../functions/problems.php");
include_once(dirname(__FILE__) . "/../functions/contests.php");

$ret = array();
$ret["code"] = 1;
if ($current_user->is_valid()) {
    $title = htmlspecialchars(convert_str($_POST['title']));
    $isprivate = 0;
    $description = htmlspecialchars(convert_str($_POST['description']));
    $lock_board_time = convert_str($_POST['lock_board_time']);
    $start_time = convert_str($_POST['start_time']);
    $end_time = convert_str($_POST['end_time']);

    if ($_POST["localtime"] == 1) {
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
    $pass = pwd(convert_str($_POST['password']));
    if ($_POST['password'] != "") $isprivate = 2;
    if ($ctype == 0) $n = $config["limits"]["problems_on_contest_add"];
    else $n = $paratypemax;
    for ($i = 0; $i < $n; $i++) {
        $pid[$i] = convert_str($_POST['pid' . $i]);
        $lable[$i] = convert_str($_POST['lable' . $i]);
        $ptype[$i] = convert_str($_POST['ptype' . $i]);
        $base[$i] = convert_str($_POST['base' . $i]);
        $minp[$i] = convert_str($_POST['minp' . $i]);
        $paraa[$i] = convert_str($_POST['paraa' . $i]);
        $parab[$i] = convert_str($_POST['parab' . $i]);
        $parac[$i] = convert_str($_POST['parac' . $i]);
        $parad[$i] = convert_str($_POST['parad' . $i]);
        $parae[$i] = convert_str($_POST['parae' . $i]);
    }


    $stt = strtotime($start_time);
    $edt = strtotime($end_time);
    $lbt = strtotime($lock_board_time);
    $nt = time();

    //echo "$title $start_time $end_time $pid[0] $stt $edt $lbt $nt ";
    //echo $_POST['submit'];

    if ($title == "") {
        $ret["msg"] = "Please input title.";
    } else if ($start_time == "" || $end_time == "" || $stt == 0 || $edt == 0 || $stt < $nt - 10 * 60) {
        $ret["msg"] = "Start/end time not correctly filled.";
    } else if (!problem_exist($pid[0]) || problem_hidden($pid[0])) {
        $ret["msg"] = "First problem doesn't exist.";
    } else if ($edt - $stt < 30 * 60 || $edt - $stt > 15 * 24 * 60 * 60 || ($lbt != 0 && ($lbt < $stt && $lbt > $edt))) {
        $ret["msg"] = "Invalid contest length.";
    } else {

        $sql_add_con = "insert into contest (title,description,isprivate,lock_board_time,start_time,end_time,hide_others,owner,isvirtual,type,password) values ('$title'" .
            ",'$description','$isprivate','$lock_board_time','$start_time','$end_time','$hide_others','$nowuser',1,'$ctype','$pass')";
        //$sql_add_con = change_in($sql_add_con);
        //echo "<br/>".$sql_add_con."<br/>";
        $pd = false;
        for ($i = 0; $i < $n; $i++) {
            if ($pid[$i] == "") continue;
            if (!problem_exist($pid[$i]) || problem_hidden($pid[$i])) $pd = true;
            else {
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
            echo json_encode($ret);
            die();
        }

        $que_add_con = $db->query($sql_add_con);
        $cid = $db->insert_id;
        if (!$que_add_con) {
            $ret["msg"] = "Insert error!";
            echo json_encode($ret);
            die();
        }

        for ($i = 0; $i < $n; $i++) {
            if ($pid[$i] == "") continue;
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
        $ret["msg"] = "Success!";
        $ret["code"] = 0;
    }

} else {
    $ret["msg"] = "Please login.";
}

echo json_encode($ret);
