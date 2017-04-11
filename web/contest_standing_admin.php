<?php
include_once("functions/users.php");
include_once("functions/contests.php");
$cid = convert_str($_GET["cid"]);
if (!$current_user->is_root() || !contest_exist($cid) || !(contest_get_val($cid, "isprivate") == 0 ||
        (contest_get_val($cid, "isprivate") == 1 && $current_user->is_in_contest($cid)) ||
        (contest_get_val($cid, "isprivate") == 2 && contest_get_val($cid, "password") == $_COOKIE[$config["cookie_prefix"] . "contest_pass_$cid"]))
) {
    ?>
    <div class="col-md-12">
        <p class="alert alert-error">Contest Unavailable!</p>
    </div>
    <?php
    die();
}

//var_dump($_GET);

function get_time($unix_time, $force = false)
{
    global $ctype;
    if ($ctype == 1 && !$force) return number_format($unix_time, 2, ".", "");
    $first = floor($unix_time / 3600);
    $mid = floor(($unix_time - $first * 3600) / 60);
    $last = $unix_time % 60;
    return $first . ":" . $mid . ":" . $last;
}

$cid = convert_str($_GET['cid']);
$maxrank = 1000000000;
if ($_GET['shownum'] != 0) $maxrank = $_GET['shownum'];
if ($_GET['anim'] == "on") $maxrank = $config["limits"]["max_rank_in_animation"];
$imerge = 1;
if ($_GET['cid_' . $cid] == 'on' && $maxrank == 1000000000) $csingle = 1;
else $csingle = 0;
$pagetitle = "Standing of Contest " . $cid . " for Admin";
$realnow = time();
$chaing = false;
$ccpassed = false;
$cidtype = array();

if (contest_exist($cid)) {
    $nowtime = time();
    $locktu = strtotime(contest_get_val($cid, "lock_board_time"));
    $sttimeu = strtotime(contest_get_val($cid, "start_time"));
    $fitimeu = strtotime(contest_get_val($cid, "end_time"));
    $cstarttimeu = strtotime(contest_get_val($cid, "challenge_start_time"));
    $cendtimeu = strtotime(contest_get_val($cid, "challenge_end_time"));
    $has_cha = contest_get_val($cid, "has_cha");
    $t = strtotime(contest_get_val($cid, "mboard_make"));
    $allp = contest_get_val($cid, "allp");
    $targ = "standings/contest_standing_" . $cid . ".html";
    if (isset($_GET['passtime']) && is_numeric($_GET['passtime']) && contest_passed($cid)) {
        $tmptime = intval($_GET['passtime']) + $sttimeu;
        if ($tmptime < $nowtime) $nowtime = $tmptime;
    }
    if ($has_cha == 1 && $nowtime > $cendtimeu) $nowtime = $cendtimeu;
    if ($has_cha == 0 && $nowtime > $fitimeu) $nowtime = $fitimeu;
    if ($nowtime > $realnow) $nowtime = $realnow;
    $pastsec = $nowtime - $t;
    $needtime = $nowtime - $sttimeu;
    ?>

    <?php
    if (contest_passed($cid)) {
        ?>
        <div class="slidediv" style="width:960px;margin:5px auto">
            <span class="passtime"><?= get_time($nowtime - $sttimeu, true) ?></span>
            <div class="timeslider noUiSlider" name="<?= $nowtime - $sttimeu ?>"></div>
            <span class="maxval" style="display:none"
                  name="<?= $has_cha == 0 ? $fitimeu - $sttimeu : $cendtimeu - $sttimeu ?>"></span>
        </div>
        <?php
    }
    ?>


    <div id="cstandingcontainer">
    <h3 class="pagetitle" style="display:none"><?= $pagetitle ?></h3>
    <div class="tcenter currentstat">
        <label class="radio inline"><input type="radio" id="stat_dis_nick">
            <p>Display Nickname</p></label>&nbsp;&nbsp;&nbsp;&nbsp;
        <label class="radio inline"><input type="radio" id="stat_dis_user" checked>
            <p>Display Username</p></label><br/>
        <b>

            <?php
            if ($realnow < $sttimeu) echo "Not Started";
            else if ($has_cha == 1 && $realnow > $fitimeu && $realnow < $cstarttimeu) {
                echo "Intermission Phase";
            } else if ($has_cha == 1 && $realnow > $cstarttimeu && $realnow < $cendtimeu) {
                echo "Challenge Phase!";
                $chaing = true;
            } else if ($has_cha == 1 && $realnow > $cendtimeu) {
                echo "Contest Finished";
                $ccpassed = true;
            } else if ($has_cha == 0 && $realnow > $fitimeu) echo "Contest Finished";
            else if ($realnow > $locktu) echo "Board Locked (Not for admin)";
            else echo "Contest Running";
            ?>
        </b>
    </div>
    <div class="rankcontainer">
    <?php
    if ($locktu == 0) $locktu = $fitimeu + 1;
    if ($nowtime >= $sttimeu + $srefresh) {
        if ($locktu < $sttimeu || $nowtime >= $fitimeu || contest_passed($cid)) $locktu = $fitimeu;
        $num_of_problem = 0;

        $map2 = array();
        $map3 = array();
        $titles = array();
        $usernum = contest_get_number_of_users($cid);
        foreach ((array)contest_get_problem_summaries($cid) as $row) {
            if ($row['type'] == 3) {
                $pacnum = $row["ac_user"];
                $row['type'] = 1;
                if ($usernum == 0) $rto = 0;
                else $rto = $pacnum / $usernum;
                if ($rto > 1 / 2) $mult = 1;
                else if ($rto > 1 / 4) $mult = 2;
                else if ($rto > 1 / 8) $mult = 3;
                else $mult = 4;
                $row['base'] = $mult * intval($row['base']) / 4;
                $row['minp'] = $mult * intval($row['minp']) / 4;
                $row['para_a'] = $mult * intval($row['para_a']) / 4;
            }
            $map[$row["pid"]] = $row["lable"];
            $map2[$row["lable"]] = $row["cpid"];
            $map3[$row["lable"]] = $row;
            $titles[$row["pid"]] = $row["title"];
            $num_of_problem++;
        }

        /*
         * 比赛信息
         * [0] =>[cid]
         * [1] =>[title]
         * [2] =>[description]
         * [3] =>[isprivate]
         * [4] =>[start_time]
         * [5] =>[end_time]
         * [6] =>[lock_board_time]
         * [7] =>[hide_others]
         * [8] => [board_make]
         * [9] =>[isvirtual]
         * [10] => [owner]
         * [11] => [report]
         * */
        $basetime = strtotime(contest_get_val($cid, "start_time"));
        $ctype = contest_get_val($cid, "type");


        // 查询 并存入二维表 OK
        //	联合查询，带出名字uu
        $ary = array();//初始化二维表

        if ($has_cha == "0") $show_contests = contest_get_comparable_list($cid);
        else $show_contests[] = $cid;
        foreach ($show_contests as $ccid) {
            if ($_GET['cid_' . $ccid] != "on") continue;

            $map4 = array();
            foreach ($map as $pid => $lable) {
                $mres = contest_get_problem_from_title($ccid, $titles[$pid]);
                if ($ccid != $cid) $map4[$mres["pid"]] = $pid;
                else $map4[$pid] = $pid;
            }
            $corrt = $needtime + strtotime(contest_get_val($ccid, "start_time"));
            $clocktu = strtotime(contest_get_val($ccid, "start_time")) + $locktu - $basetime;
            $cbase = strtotime(contest_get_val($ccid, "start_time"));
            /*if ($corrt>=$clocktu&&$corrt<strtotime(contest_get_val($ccid,"end_time"))) $corrt=$clocktu;
            else if($corrt>=$clocktu)  $corrt=strtotime(contest_get_val($ccid,"start_time"))+strtotime(contest_get_val($cid,"end_time"))-$basetime;*/
            $cidtype[$ccid] = contest_get_val($ccid, "type");

            foreach ((array)contest_get_status_before_time($ccid, $corrt) as $row) {
                $row["0"] = $row["pid"] = $map4[$row["pid"]];
                $row["username"] = trim(strtolower($row["username"]));
                $row[3] = $row['username'] = $row["username"] . "(" . $row["contest_belong"] . ")";
                $row[5] = $row["contest_belong"];
                $row[4] = $row["nickname"];
                $row[1] = $row["result"];
                $row[2] = $row['time_submit'] = strtotime($row["time_submit"]) - $cbase;
                $id = array_push($ary, $row); //$id 为行数
            }//将查询结果存入
        }
        // 扫描一遍查询结果 生成名称序二维表
        $Name_ary = array(); //初始化名称序二维表
        $tot_num = $tot_ac = 0;
        $namemap = array();

        $charessuc = array();
        $charesfal = array();

        for ($i = 0; $i < $id; $i++) {
            $totnum[$map[$ary[$i]['pid']]]++;
            $tot_num++;
            $lowername = strtolower($ary[$i]['username']);
            if ($ary[$i]['result'] == "Accepted" || $ary[$i]['result'] == "Pretest Passed") {
                $acnum[$map[$ary[$i]['pid']]]++;
                $tot_ac++;
                if ($fb[$map[$ary[$i]['pid']]] == "" || intval($fb[$map[$ary[$i]['pid']]]) > intval($ary[$i]['time_submit']))
                    $fb[$map[$ary[$i]['pid']]] = $ary[$i]['time_submit'];
            }
            $k = 0;
            $get = 0;

            if (isset($namemap[$lowername])) {
                $k = 1;
                $get = $namemap[$lowername];
            } else $k = 0;
            if ($k == 1) {
                //echo "Yes<br/>";
                if ($ary[$i]['result'] == "Accepted" || $ary[$i]['result'] == "Pretest Passed") {
                    if ($Name_ary[$get][$map[$ary[$i]['pid']]] != -1) {
                        if ($has_cha) {
                            $Name_ary[$get][$map[$ary[$i]['pid']]] = $ary[$i]['time_submit'];
                            $Name_ary[$get][$map[$ary[$i]['pid']] . '_ori'] = $ary[$i]['time_submit'];
                            $Name_ary[$get][$map[$ary[$i]['pid']] . _wci]--;
                        }
                        continue;
                    } else {
                        $Name_ary[$get][$map[$ary[$i]['pid']]] = $ary[$i]['time_submit'];
                        $Name_ary[$get][$map[$ary[$i]['pid']] . '_ori'] = $ary[$i]['time_submit'];
                    }
                } else {
                    if ($Name_ary[$get][$map[$ary[$i]['pid']]] == -1) {
                        $Name_ary[$get][$map[$ary[$i]['pid']] . _wci]--;
                    } else {
                        if ($has_cha) {
                            $Name_ary[$get][$map[$ary[$i]['pid']] . _wci] -= 2;
                            $Name_ary[$get][$map[$ary[$i]['pid']]] = -1;
                        }
                    }
                }
            } else {
                //echo "No<br/>";
                $iid = array_push($Name_ary, $ary[$i]);
                $namemap[$lowername] = $iid - 1;
                if ($ary[$i]['result'] == "Accepted" || $ary[$i]['result'] == "Pretest Passed") {
                    array_push($Name_ary[$iid - 1], $ary[$i]['time_submit']);
                    foreach ($map as $value) {
                        $Name_ary[$iid - 1][$value] = -1;
                        $Name_ary[$iid - 1][$value . _wci] = 0;
                    }
                    $Name_ary[$iid - 1][$map[$ary[$i]['pid']]] = $ary[$i]['time_submit'];
                    $Name_ary[$iid - 1][$map[$ary[$i]['pid']] . '_ori'] = $ary[$i]['time_submit'];
                    $Name_ary[$iid - 1][$map[$ary[$i]['pid']] . _wci] = 0;
                } else {
                    array_push($Name_ary[$iid - 1], 0);
                    foreach ($map as $value) {
                        $Name_ary[$iid - 1][$value] = -1;
                        $Name_ary[$iid - 1][$value . _wci] = 0;
                    }
                    $Name_ary[$iid - 1][$map[$ary[$i]['pid']]] = -1;
                    $Name_ary[$iid - 1][$map[$ary[$i]['pid']] . _wci] = -1;
                }
                $charessuc[$iid - 1] = 0;
                $charesfal[$iid - 1] = 0;
            }
        }

//            print_r($Name_ary[0]);

        $totsuc = 0;
        $totfal = 0;
        if ($has_cha) {
            foreach ((array)contest_get_challenge_before_time($cid, $nowtime) as $srow) {
                $lowername = strtolower($srow['username'] . '(' . $cid . ')');
                if (isset($namemap[$lowername])) $get = $namemap[$lowername];
                else {
                    $namemap[$lowername] = $iid;
                    $Name_ary[$iid] = array();
                    $Name_ary[$iid][3] = $lowername;
                    $Name_ary[$iid][4] = $srow["nickname"];
                    $Name_ary[$iid]['contest_belong'] = $cid;
                    $iid++;
                    for ($i = 0; $i < $id; $i++) {
                        array_push($Name_ary[$iid - 1], 0);
                        foreach ($map as $value) {
                            $Name_ary[$iid - 1][$value] = -1;
                            $Name_ary[$iid - 1][$value . _wci] = 0;
                        }
                    }
                    $get = $iid - 1;
                    $charessuc[$get] = 0;
                    $charesfal[$get] = 0;
                }
                if (strstr($srow['cha_result'], "Success")) {
                    if (!isset($chaed[$srow['runid']])) {
                        $chaed[$srow['runid']] = true;
                        $charessuc[$get]++;
                        $totsuc++;
                    }
                } else if (strstr($srow['cha_result'], "Failed")) {
                    $charesfal[$get]++;
                    $totfal++;
                }
            }
        }

// 扫描计算罚时与题数 然后排序

        for ($i = 0; $i < $iid; $i++) {
            $fs = 0;
            $Name_ary[$i]['sum'] = 0;
            $Name_ary[$i][6] = 0;
            foreach ($map as $value) {
                if ($Name_ary[$i][$value] != -1) {
                    $Name_ary[$i]['sum']++;
                    //$fs -= 20*60*$Name_ary[$i][$value._wci];
                    if ($ctype == 0 || $ctype == 99) $Name_ary[$i][6] += $Name_ary[$i][$value] - 20 * 60 * $Name_ary[$i][$value . _wci];
                    else if ($ctype == 1) {
                        $Name_ary[$i][$value] = contest_get_problem_point_from_mixed($cid, $value, $Name_ary[$i][$value]);
                        if ($map3[$value]['type'] == 1) $Name_ary[$i][$value] += $Name_ary[$i][$value . _wci] * $map3[$value]['para_b'];
                        else if ($map3[$value]['type'] == 2) $Name_ary[$i][$value] = intval($Name_ary[$i][$value] * pow(1.0 - doubleval($map3[$value]['para_e']) / 100.0, -$Name_ary[$i][$value . _wci]));
                        if ($Name_ary[$i][$value] < $map3[$value]['minp']) $Name_ary[$i][$value] = $map3[$value]['minp'];
                        $Name_ary[$i][6] += $Name_ary[$i][$value];
                    }
                }
            }
            if ($has_cha) {
                if ($ctype == 0 || $ctype == 99) $Name_ary[$i][6] += 20 * 60 * $charesfal[$i] - 40 * 60 * $charessuc[$i];
                else if ($ctype == 1) $Name_ary[$i][6] += -25 * $charesfal[$i] + 50 * $charessuc[$i];
            }
            //$Name_ary[$i][6] += $fs;
        }

        function cmp0($a, $b)
        {
//                print_r($a);
            if ($a['sum'] == $b['sum']) {
                if ($a[6] < $b[6]) return -1;
                if ($a[6] > $b[6]) return 1;
                return 0;
            } else {
                if ($a['sum'] > $b['sum']) return -1;
                return 1;
            }
        }

        function cmp99($a, $b)
        {
//                print_r($a);
            if ($a['sum'] == $b['sum']) {
                if ($a[6] < $b[6]) return -1;
                if ($a[6] > $b[6]) return 1;
                return 0;
            } else {
                if ($a['sum'] > $b['sum']) return -1;
                return 1;
            }
        }


        function cmp1($a, $b)
        {
//                print_r($a);
            if ($a[6] > $b[6]) return -1;
            if ($a[6] < $b[6]) return 1;
            return 0;
        }


        usort($Name_ary, "cmp" . $ctype);

        echo "<table class='table table-striped table-hover cstanding basetable'>";
        echo "\n" . '<thead><tr>';
        echo "<th class='trank anim:position'> Rank </th>";
        echo "<th class='anim:constant tname tnickname' style='display:none'> Nickname </th>";
        echo "<th class='anim:constant tname tusername'> Username </th>";
        echo "<th class='tac'> AC <br />";
        echo $tot_ac . "/" . $tot_num . "<br />";
        if (intvaL($tot_num) > 0) echo round(intval($tot_ac) / intvaL($tot_num) * 100, 2) . "%";
        else echo "0%";
        echo "</th>";

        foreach ($map as $value) {
            echo "<th class='tprob'>";
            echo "<a href='#problem/" . $value . "'>" . $value . "</a><br />";
            if ($acnum[$value] == "") $acnum[$value] = "0";
            if ($totnum[$value] == "") $totnum[$value] = "0";
            echo $acnum[$value] . "/" . $totnum[$value] . "<br />";
            if (intval($totnum[$value]) > 0) echo round(intval($acnum[$value]) / intval($totnum[$value]) * 100, 2) . "%";
            else echo "0%";
            echo "</th>";
        }
        if ($has_cha) echo "<th class='tpenal'>Sum<br />+$totsuc : -$totfal</th>";
        if ($ctype == 0 || $ctype == 99) echo "<th class='tpenal'>Penalty</th>";//Penalty
        else if ($ctype == 1) echo "<th class='tpenal'>Score</th>";//Penalty
        if ($imerge) echo "<th class='anim:constant tcid'>CID</th>";
        else echo "<th style='display:none' class='anim:constant tcid'>CID</th>";
        echo "<th class='tidentii anim:id' style='display:none'>ID</th>";
        echo "</tr></thead>\n<tbody>";
        if ($iid > $maxrank) $iid = $maxrank;
        for ($i = 0; $i < $iid; $i++) {
            //  print_r($Name_ary[$i]);

            $nick = change_out_nick($Name_ary[$i][4]);
            if ($nick == '') {
                $nick = "No nickname.";
            }
            $rnick = htmlentities($nick, ENT_QUOTES);
            $cduser = $cuser = substr($Name_ary[$i][3], 0, strrpos($Name_ary[$i][3], '('));
            echo "<tr>";
            if ($cidtype[$Name_ary[$i]['contest_belong']] == 0 || $cidtype[$Name_ary[$i]['contest_belong']] == 1)
                echo //"<th>".$Name_ary[$i][0]."</th>" . //pid
                    // "<th>".$Name_ary[$i][1]."</th>" . //result
                    // "<th>".$Name_ary[$i][2]."</th>".//time_submit
                    "<td>" . ($i + 1) . "</td>" .
                    "<td class='tnickname' style='display:none'><a href='userinfo.php?name=$cuser' title='$rnick'>" . $nick . "</a></td>" .//nickname
                    "<td class='tusername'><a target='_blank' href='userinfo.php?name=$cuser' title='$cuser'>" . $cduser . "</a></td>" .//username
                    "<td>" . $Name_ary[$i][sum] . "</td>";//ac_num
            else
                echo //"<th>".$Name_ary[$i][0]."</th>" . //pid
                    // "<th>".$Name_ary[$i][1]."</th>" . //result
                    // "<th>".$Name_ary[$i][2]."</th>".//time_submit
                    "<td>" . ($i + 1) . "</td>" .
                    "<td class='tnickname' style='display:none' title='$rnick'>" . $nick . "</td>" .//nickname
                    "<td class='tusername' title='$cuser'>" . $cduser . "</td>" .//username
                    "<td>" . $Name_ary[$i][sum] . "</td>";//ac_num


            foreach ($map as $value) {
                if ($Name_ary[$i][$value] != -1 && array_key_exists($value, $Name_ary[$i])) {
                    if ($ctype == 0 || $ctype == 99) $cont = get_time($Name_ary[$i][$value]) . "(" . (-$Name_ary[$i][$value . _wci]) . ")";
                    else if ($ctype == 1) $cont = get_time($Name_ary[$i][$value]) . "<br />" . get_time($Name_ary[$i][$value . '_ori'], true) . "(" . (-$Name_ary[$i][$value . _wci]) . ")";
                    if ($fb[$value] != $Name_ary[$i][$value . '_ori']) {
                        if ($chaing || $ccpassed) $cont = "<a class='cha_click' chauname='$cuser' chaprob='" . $map3[$value]['pid'] . "'>" . $cont . "</a>";
                        echo "<td class='ac_stat'>" . $cont . "</td>";
                    } else {
                        if ($chaing || $ccpassed) $cont = "<a class='cha_click' chauname='$cuser' chaprob='" . $map3[$value]['pid'] . "'>" . $cont . "</a>";
                        echo "<td class='acfb_stat'>" . $cont . "</td>";
                    }
                } else if ($Name_ary[$i][$value . _wci])
                    if ($ccpassed) echo "<td class='notac_stat'><a class='cha_click' chauname='$cuser' chaprob='" . $map3[$value]['pid'] . "'>(" . $Name_ary[$i][$value . _wci] . ")</a></td>";
                    else echo "<td class='notac_stat'>(" . $Name_ary[$i][$value . _wci] . ")</td>";
                else {
                    echo "<td>&nbsp;</td>";
                }
            }
            if ($has_cha) echo "<td><a class='user_cha' chauname='$cuser'>+" . $charessuc[$namemap[$Name_ary[$i][3]]] . " : -" . $charesfal[$namemap[$Name_ary[$i][3]]] . "</a></td>";
            echo "<td>" . get_time($Name_ary[$i][6]) . "</td>";//Penalty
            if ($imerge) echo "<td>";
            else  echo "<td style='display:none'>";
            echo "<a target='_blank' href='contest_show.php?cid=" . $Name_ary[$i]['contest_belong'] . "'>" . $Name_ary[$i]['contest_belong'] . "</a></td>";//Contest
            echo "<td style='display:none'>" . $Name_ary[$i][3] . "</td>";//ID
            echo "</tr>\n";
        }
        echo "</tbody></table>\n";
        ?>
        </div>
        </div>

        <?php
    }

} else {
    ?>
    <div class="col-md-12">
        <p class="alert alert-error">Contest Unavailable!</p>
    </div>
    <?php
}
?>




