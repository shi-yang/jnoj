<?php
include_once(dirname(__FILE__) . "/global.php");
include_once(dirname(__FILE__) . "/contests.php");
include_once(dirname(__FILE__) . "/users.php");

function sidebar_item_top($haswell = true)
{
    if ($haswell) return "
        <div class='well'>\n";
    return "
        <div>\n";
}

function sidebar_item_bottom()
{
    return "
        </div>\n";
}

//display news
function sidebar_item_content_news($haswell = true)
{
    global $config, $db;
    $value = sidebar_item_top($haswell) . "            <h3>Latest News <span style='font-size:12px'><a href='news.php'>[more]</a></span></h3>\n";
    $sql = "select * from news order by time_added desc limit 0," . $config["limits"]["news_on_index"];
    $none = true;
    foreach ((array)$db->get_results($sql, ARRAY_A) as $row) {
        $none = false;
        $row['title'] = strip_tags($row['title']);
        if (strlen($row['title']) > $config["limits"]["news_on_index_title_len"])
            $row['title'] = mb_strcut($row['title'], 0, $config["limits"]["news_on_index_title_len"], 'UTF-8') . "<a name='" . $row['newsid'] . "' class='newslink' href='#'>[...]</a>";
        $row['content'] = strip_tags($row['content']);
        if (strlen($row['content']) > $config["limits"]["news_on_index_content_len"])
            $row['content'] = mb_strcut($row['content'], 0, $config["limits"]["news_on_index_content_len"], 'UTF-8');
        $row['content'] .= "<a name='" . $row['newsid'] . "' class='newslink' href='#'>[...]</a>";
        $value .= "            <h4>" . $row['title'] . "</h4>
            <p>" . $row['content'] . "</p>\n";
    }
    if ($none) {
        $value .= "            No News.\n";
    }
    $value .= sidebar_item_bottom();
    return $value;
}

//display virtual judge status
function sidebar_item_content_vjstatus()
{
    global $db;
    $value = sidebar_item_top() . "            <h3>VJudge Status</h3>
            By checking remote status page every 10 minutes.
            <table class='table table-hover table-striped table-condensed' width='100%'>
                <thead>
                    <tr>
                        <th width='70%'>OJ</th>
                        <th width='30%'>Status</th>
                    </tr>
                </thead>
                <tbody>\n";
    $sql = "select * from ojinfo where name not like 'JNU' order by name";
    foreach ((array)$db->get_results($sql, ARRAY_A) as $row) {
        $statinfo = "";
        $stitle = "Last Check: " . $row['lastcheck'] . ", " . $row['status'];
        if ($row['status'] == "Normal") {
            $sclass = "success";
            $statinfo = "<img src='assets/img/green_light.png' />";
        } else if (substr($row['status'], 0, 4) == "Down") {
            $sclass = "error";
            $statinfo = "<img src='assets/img/red_light.png' />";
        } else {
            $sclass = "warning";
            $statinfo = "<img src='assets/img/yellow_light.png' />";
        }
        $value .= "                    <tr title='$stitle' class='vjttip'>
                        <td>" . $row['name'] . "</td>
                        <td>" . $statinfo . "</td>
                    </tr>\n";
    }
    $value .= "                </tbody>
            </table>\n";
    $value .= sidebar_item_bottom();
    return $value;
}

function sidebar_item_content_vjstatus_index()
{
    global $db;
    $value = sidebar_item_top(false) . "            <h3>VJudge Status</h3>
            By checking remote status page every 10 minutes.
            <table class='table table-hover table-striped table-condensed' width='100%'>
                <thead>
                    <tr>
                        <th width='70%'>OJ</th>
                        <th width='30%'>Status</th>
                    </tr>
                </thead>
                <tbody>\n";
    $sql = "select * from ojinfo where name not like 'JNU' order by name";
    foreach ((array)$db->get_results($sql, ARRAY_A) as $row) {
        $statinfo = "";
        $stitle = "Last Check: " . $row['lastcheck'] . ", " . $row['status'];
        if ($row['status'] == "Normal") {
            $sclass = "success";
            $statinfo = "<img src='assets/img/green_light.png' />";
        } else if (substr($row['status'], 0, 4) == "Down") {
            $sclass = "error";
            $statinfo = "<img src='assets/img/red_light.png' />";
        } else {
            $sclass = "warning";
            $statinfo = "<img src='assets/img/yellow_light.png' />";
        }
        $value .= "                    <tr title='$stitle' class='vjttip'>
                        <td>" . $row['name'] . "</td>
                        <td>" . $statinfo . "</td>
                    </tr>\n";
    }
    $value .= "                </tbody>
            </table>\n";
    $value .= sidebar_item_bottom();
    return $value;
}

function sidebar_item_content_upcoming_stardard_contests()
{
    $res = contest_get_standard_scheduled_list();
    $value = sidebar_item_top() . "            <h3>Upcoming...</h3>\n";
    if (sizeof($res) == 0) {
        $value .= "            No upcoming contest.\n";
    } else {
        foreach ($res as $contest) {
            $value .= "            <a href='contest_show.php?cid=" . $contest["cid"] . "'>" . $contest["title"] . "</a> ends at " . $contest["end_time"] . "<br />\n";
        }
    }
    $value .= sidebar_item_bottom();
    return $value;
}

function sidebar_item_content_user_stat($user)
{
    if (!$user->is_valid()) return "";
    $stat = $user->get_stat();
    $name = $user->get_username();
    $value = sidebar_item_top() . "            <div id='userpie' class='highcharts-container'>
            </div>
            <table id='userstat' class='table table-striped'>
              <tbody>
                <tr>
                  <th class='col-md-9'>Total Submissions</th>
                  <td class='col-md-3'><a href='status.php?showname=$name'>" . $stat["num_total"] . "</a></td>
                </tr>
                <tr>
                  <th>Accepted</th>
                  <td><a href='status.php?showname=$name&showres=Accepted'>" . $stat["num_ac"] . "</a></td>
                </tr>
                <tr>
                  <th>Compile Error</th>
                  <td><a href='status.php?showname=$name&showres=Compile+Error'>" . $stat["num_ce"] . "</a></td>
                </tr>
                <tr>
                  <th>Wrong Answer</th>
                  <td><a href='status.php?showname=$name&showres=Wrong+Answer'>" . $stat["num_wa"] . "</a></td>
                </tr>
                <tr>
                  <th>Presentation Error</th>
                  <td><a href='status.php?showname=$name&showres=Presentation+Error'>" . $stat["num_pe"] . "</a></td>
                </tr>
                <tr>
                  <th>Runtime Error</th>
                  <td><a href='status.php?showname=$name&showres=Runtime+Error'>" . $stat["num_re"] . "</a></td>
                </tr>
                <tr>
                  <th>Time Limit Exceed</th>
                  <td><a href='status.php?showname=$name&showres=Time+Limit+Exceed'>" . $stat["num_tle"] . "</a></td>
                </tr>
                <tr>
                  <th>Memory Limit Exceed</th>
                  <td><a href='status.php?showname=$name&showres=Memory+Limit+Exceed'>" . $stat["num_mle"] . "</a></td>
                </tr>
                <tr>
                  <th>Output Limit Exceed</th>
                  <td><a href='status.php?showname=$name&showres=Output+Limit+Exceed'>" . $stat["num_ole"] . "</a></td>
                </tr>
                <tr>
                  <th>Restricted Function</th>
                  <td><a href='status.php?showname=$name&showres=Restricted+Function'>" . $stat["num_rf"] . "</a></td>
                </tr>
                <tr>
                  <th>Others</th>
                  <td>" . $stat["num_other"] . "</td>
                </tr>
              </tbody>
            </table>";
    $value .= sidebar_item_bottom();
    return $value;
}

function sidebar_item_content_problem_stat($problem)
{
    if (!$problem->is_valid()) return "";
    $stat = $problem->get_val("stat");
    $pid = $problem->get_val("pid");
    $value = sidebar_item_top() . "            <div id='probpie' class='highcharts-container'>
            </div>
            <table id='probstat' class='table table-striped'>
              <tbody>
                <tr>
                  <th class='col-md-9'>Total Submissions</th>
                  <td class='col-md-3'><a href='status.php?showpid=$pid'>" . $stat["num_total"] . "</a></td>
                </tr>
                <tr>
                  <th>Accepted</th>
                  <td><a href='status.php?showpid=$pid&showres=Accepted'>" . $stat["num_ac"] . "</a></td>
                </tr>
                <tr>
                  <th>Compile Error</th>
                  <td><a href='status.php?showpid=$pid&showres=Compile+Error'>" . $stat["num_ce"] . "</a></td>
                </tr>
                <tr>
                  <th>Wrong Answer</th>
                  <td><a href='status.php?showpid=$pid&showres=Wrong+Answer'>" . $stat["num_wa"] . "</a></td>
                </tr>
                <tr>
                  <th>Presentation Error</th>
                  <td><a href='status.php?showpid=$pid&showres=Presentation+Error'>" . $stat["num_pe"] . "</a></td>
                </tr>
                <tr>
                  <th>Runtime Error</th>
                  <td><a href='status.php?showpid=$pid&showres=Runtime+Error'>" . $stat["num_re"] . "</a></td>
                </tr>
                <tr>
                  <th>Time Limit Exceed</th>
                  <td><a href='status.php?showpid=$pid&showres=Time+Limit+Exceed'>" . $stat["num_tle"] . "</a></td>
                </tr>
                <tr>
                  <th>Memory Limit Exceed</th>
                  <td><a href='status.php?showpid=$pid&showres=Memory+Limit+Exceed'>" . $stat["num_mle"] . "</a></td>
                </tr>
                <tr>
                  <th>Output Limit Exceed</th>
                  <td><a href='status.php?showpid=$pid&showres=Output+Limit+Exceed'>" . $stat["num_ole"] . "</a></td>
                </tr>
                <tr>
                  <th>Restricted Function</th>
                  <td><a href='status.php?showpid=$pid&showres=Restricted+Function'>" . $stat["num_rf"] . "</a></td>
                </tr>
                <tr>
                  <th>Others</th>
                  <td>" . $stat["num_other"] . "</td>
                </tr>
              </tbody>
            </table>";
    $value .= sidebar_item_bottom();
    return $value;
}

function sidebar_item_content_contest_info($cid)
{
    $value = sidebar_item_top() . "            <h3>Notice</h3>
            <p>" . nl2br(contest_get_val($cid, "description")) . "</p>\n" . sidebar_item_bottom();
    return $value;
}

function sidebar_item_content_contest_points($cid)
{
    $value = sidebar_item_top() . "            <h3>Current Value</h3>
            <table style='width:100%' class='table table-striped table-hover table-condensed'>
                <thead>
                    <tr><th>Label</th><th>Value</th></tr>
                </thead>
                <tbody>\n";
    $nowt = time();
    foreach ((array)contest_get_problem_basic($cid) as $row) {
        $value .= "                    <tr><th>" . $row["lable"] . "</th><td>" . contest_get_problem_point_from_mixed($cid, $row["cpid"], $nowt) . "</td></tr>\n";
    }
    $value .= "                </tbody>
            </table>\n";
    $value .= sidebar_item_bottom();
    return $value;
}

function sidebar_index()
{
    return sidebar_item_content_news() . sidebar_item_content_vjstatus();
}

function sidebar_userinfo($user)
{
    return sidebar_item_content_user_stat($user);
}

function sidebar_problem_stat($problem)
{
    return sidebar_item_content_problem_stat($problem);
}

function sidebar_contest_show($cid)
{
    return sidebar_item_content_contest_info($cid) . (contest_get_val($cid, "type") == 1 ? sidebar_item_content_contest_points($cid) : "");
}

function sidebar_common()
{
    return sidebar_item_content_upcoming_stardard_contests() . sidebar_item_content_news();
}
