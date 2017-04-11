<?php

include_once(dirname(__FILE__) . "/global.php");

class User
{
    private $info = array();
    private $valid = false;

    function load_info($username)
    {
        global $db;
        $sql = "select * from user where username='$username'";
        $db->query($sql);
        if ($db->num_rows == 0) return false;
        $this->valid = true;
        unset($this->info);
        //$this->info["username"]=$username;
        $this->info = $db->get_row(null, ARRAY_A);
        return true;
    }

    function update_info($infos)
    {
        global $db;
        $infos["password"] = hash_password($infos["password"]);
        $infos["email"] = htmlspecialchars($infos["email"]);
        $infos["nickname"] = htmlspecialchars($infos["nickname"]);
        $infos["school"] = htmlspecialchars($infos["school"]);
        $sql_update = "update user set password='" . $infos["password"] . "',email='" . $infos["email"] . "',school ='" . $infos["school"] . "',nickname='" . $infos["nickname"] . "' where username='" . $db->escape($this->info['username']) . "'";
        $db->query($sql_update);
        $this->info["password"] = $infos["password"];
        $this->info["email"] = $infos["email"];
        $this->info["school"] = $infos["school"];
        $this->info["nickname"] = $infos["nickname"];
    }

    function set_user($username, $password)
    {
        global $db;
        $this->valid = false;
        if ($username == "" || $password == "") return false;
        $sql = "select * from user where username='$username' and password='$password'";
        $db->query($sql);
        if ($db->num_rows == 0) return false;
        $this->load_info($username);
        return true;
    }

    function update_last_login()
    {
        global $db;
        if (!$this->valid) return;
        $now = time();
        $today = date("Y-m-d G:i:s", $now);
        $ip = get_ip();
        $db->query("update user set last_login_time='$today', ipaddr='$ip' where username='" . $db->escape($this->info['username']) . "' ");
        $this->info["ipaddr"] = $ip;
        $this->info["last_login_time"] = $today;
    }

    function get_rank()
    {
        global $db;
        if (!$this->valid) return 0;
        if (isset($this->info["rank"])) return $this->info["rank"];
        list($this->info["rank"]) = $db->get_row("select count(*)+1 from user where local_ac>" . $this->get_val("local_ac") . " or
            (local_ac=" . $this->get_val("local_ac") . " and total_ac>" . $this->get_val("total_ac") . ") or 
            (local_ac=" . $this->get_val("local_ac") . " and total_ac=" . $this->get_val("total_ac") . " and total_submit<" . $this->get_val("total_submit") . ") or 
            (local_ac=" . $this->get_val("local_ac") . " and total_ac=" . $this->get_val("total_ac") . " and total_submit=" . $this->get_val("total_submit") . " and username<'" . $db->escape($this->info['username']) . "' )", ARRAY_N);
        // $sql="SELECT rownum FROM ( SELECT @rownum := @rownum +1 rownum, ranklist . * FROM (SELECT @rownum :=0) r, ranklist) AS t where username='".$db->escape($this->info['username'])."'";
        // list($this->info["rank"]) = $db->get_row($sql,ARRAY_N);
        return $this->info["rank"];
    }

    function get_accepted_pid()
    {
        global $db;
        if (!$this->valid) return null;
        if (isset($this->info["ac_pid"])) return $this->info["ac_pid"];
        foreach ((array)$db->get_results("select distinct pid from status where username='" . $db->escape($this->info['username']) . "' and result='Accepted' order by pid", ARRAY_N) as $temp) $this->info["ac_pid"][] = $temp[0];
        return $this->info["ac_pid"];
    }

    function get_unread_mail_count()
    {
        global $db;
        if (!$this->valid) return 0;
        if (isset($this->info["unread_mail_count"])) return $this->info["unread_mail_count"];
        list($this->info["unread_mail_count"]) = $db->get_row("select count(*) from mail where status=false and reciever='" . $db->escape($this->info['username']) . "'", ARRAY_N);
        $this->info["unread_mail_count"] = intval($this->info["unread_mail_count"]);
        return $this->info["unread_mail_count"];
    }

    function get_username()
    {
        if (!$this->valid) return "";
        return $this->info["username"];
    }

    function get_stat()
    {
        global $db;
        if (!$this->valid) return null;
        if (isset($this->info["stat"])) return $this->info["stat"];
        $this->info["stat"] = array();
        $name = $db->escape($this->info['username']);
        list($this->info["stat"]["num_ac"]) = $db->get_row("select count(*) from status where username='$name' and result='Accepted'", ARRAY_N);
        list($this->info["stat"]["num_ce"]) = $db->get_row("select count(*) from status where username='$name' and result='Compile Error'", ARRAY_N);
        list($this->info["stat"]["num_wa"]) = $db->get_row("select count(*) from status where username='$name' and result='Wrong Answer'", ARRAY_N);
        list($this->info["stat"]["num_pe"]) = $db->get_row("select count(*) from status where username='$name' and result='Presentation Error'", ARRAY_N);
        list($this->info["stat"]["num_re"]) = $db->get_row("select count(*) from status where username='$name' and result='Runtime Error'", ARRAY_N);
        list($this->info["stat"]["num_tle"]) = $db->get_row("select count(*) from status where username='$name' and result='Time Limit Exceed'", ARRAY_N);
        list($this->info["stat"]["num_mle"]) = $db->get_row("select count(*) from status where username='$name' and result='Memory Limit Exceed'", ARRAY_N);
        list($this->info["stat"]["num_ole"]) = $db->get_row("select count(*) from status where username='$name' and result='Output Limit Exceed'", ARRAY_N);
        list($this->info["stat"]["num_rf"]) = $db->get_row("select count(*) from status where username='$name' and result='Restricted Function'", ARRAY_N);
        list($this->info["stat"]["num_total"]) = $db->get_row("select count(*) from status where username='$name'", ARRAY_N);
        $this->info["stat"]["num_other"] = $this->info["stat"]["num_total"] -
            $this->info["stat"]["num_rf"] -
            $this->info["stat"]["num_ole"] -
            $this->info["stat"]["num_mle"] -
            $this->info["stat"]["num_tle"] -
            $this->info["stat"]["num_re"] -
            $this->info["stat"]["num_pe"] -
            $this->info["stat"]["num_wa"] -
            $this->info["stat"]["num_ce"] -
            $this->info["stat"]["num_ac"];
        return $this->info["stat"];
    }

    function get_last_submit_time()
    {
        global $db;
        if (!$this->valid) return null;
        if (isset($this->info["last_submit_time"])) return $this->info["last_submit_time"];
        $query = "select time_submit from status where username='" . $db->escape($this->info['username']) . "' order by time_submit desc limit 0,1";
        list($this->info["last_submit_time"]) = $db->get_row($query, ARRAY_N);
        return $this->info["last_submit_time"];
    }

    function get_col($str)
    {
        global $db;
        if (!$this->valid) return null;
        if (isset($this->info[$str])) return $this->info[$str];
        $row = $db->get_row("select $str from user where username='" . $db->escape($this->info["username"]) . "'", ARRAY_N);
        return $this->info[$str] = $row[0];
    }

    function get_val($str)
    {
        global $db;
        if (!$this->valid) return null;
        if (isset($this->info[$str])) return $this->info[$str];
        $tstr = "get_" . $str;
        if (method_exists($this, $tstr)) return $this->$tstr();
        else return $this->get_col($str);
    }

    function is_valid()
    {
        return $this->valid;
    }

    function is_root()
    {
        if (!$this->valid) return false;
        return $this->info["isroot"] >= '1' ? true : false;
    }

    function is_codeviewer()
    {
        if (!$this->valid) return false;
        return intval($this->info["isroot"]) > 0 ? true : false;
    }

    function aced_problem($pid)
    {
        global $db;
        if (!$this->valid) return false;
        if (isset($this->info["aced_" . $pid])) return $this->info["aced_" . $pid];
        $db->query("select runid from status where username='" . $db->escape($this->info['username']) . "' and pid='$pid' and result='Accepted' limit 0,1");
        return $this->info["aced_" . $pid] = $db->num_rows;
    }

    function aced_problem_in_contest($pid, $cid)
    {
        global $db;
        if (!$this->valid) return false;
        if (isset($this->info["aced_" . $pid . "_in_" . $cid])) return $this->info["aced_" . $pid . "_in_" . $cid];
        $db->query("select runid from status where username='" . $db->escape($this->info['username']) . "' and pid='$pid' and contest_belong='$cid' and (result='Accepted' or result='Pretest Passed') limit 0,1");
        return $this->info["aced_" . $pid . "_in_" . $cid] = $db->num_rows;
    }

    function tried_problem_in_contest($pid, $cid)
    {
        global $db;
        if (!$this->valid) return false;
        if (isset($this->info["tried_" . $pid . "_in_" . $cid])) return $this->info["tried_" . $pid . "_in_" . $cid];
        $db->query("select runid from status where username='" . $db->escape($this->info['username']) . "' and pid='$pid' and contest_belong='$cid' limit 0,1");
        return $this->info["tried_" . $pid . "_in_" . $cid] = $db->num_rows;
    }

    function is_in_contest($cid)
    {
        global $db;
        if (!$this->valid) return false;
        if (isset($this->info["is_in_contest_" . $cid])) return $this->info["is_in_contest_" . $cid];
        $result = $db->query("select * from contest_user where cid = '$cid' and username='" . $db->escape($this->info['username']) . "'");
        return $this->info["is_in_contest_" . $cid] = $db->num_rows;
    }

    function match($name)
    {
        if (!$this->valid) return false;
        return strcasecmp($name, $this->info["username"]) == 0 ? true : false;
    }

    function tagged($pid, $tagid)
    {
        global $db;
        if (!$this->valid) return false;
        if (isset($this->info["tagged_" . $pid . "_as_" . $tagid])) return $this->info["tagged_" . $pid . "_as_" . $tagid];

        $db->query("select id from usertag where username='" . $db->escape($this->info['username']) . "' and pid='$pid' and catid='$tagid' limit 0,1");
        return $this->info["tagged_" . $pid . "_as_" . $tagid] = $db->num_rows;
    }

    function tag_problem_as_category($pid, $tagid, $weight = 10, $force = 0)
    {
        global $db;
        if (!$this->valid) return false;

        if ($this->is_root() && $force != 1) {
            $db->query("insert into usertag set username='" . $db->escape($this->info['username']) . "', pid='$pid', catid='$tagid'");
        }

        $db->query("select pcid from problem_category where pid='$pid' and catid='$tagid'");
        if ($db->num_rows == 0) $db->query("insert into problem_category set pid='$pid', catid='$tagid', weight='$weight'");
        else $db->query("update problem_category set weight=weight+$weight where pid='$pid' and catid='$tagid'");
    }
}

$current_user = new User;
$current_user->set_user($nowuser, $nowpass);


function user_create($infos)
{
    global $db, $EZSQL_ERROR;
    if (!is_array($infos[0])) $infos = array($infos);
    $now = time();
    $today = date("Y-m-d G:i:s", $now);
    $sql = "insert into user (username,password,nickname,school,email,register_time) values ";
    $values = array();
    foreach ($infos as $one) {
        $one[1] = hash_password($one[1]);
        $one[2] = htmlspecialchars($one[2]);
        $one[3] = htmlspecialchars($one[3]);
        $one[4] = htmlspecialchars($one[4]);
        $values[] = "('$one[0]','$one[1]','$one[2]','$one[3]','$one[4]','$today')";
    }
    $sql .= implode(",", $values);
    $db->query($sql);
    if ($EZSQL_ERROR) return false;
    else return true;
}

function user_exist($username)
{
    global $db;
    if (!is_array($username)) $username = array($username);
    $sql = "select * from user where ";
    $where = array();
    foreach ($username as $one) {
        $where[] = "username='$one'";
    }
    $sql .= implode(" OR ", $where);
    $db->query($sql);
    if ($db->num_rows > 0) return true;
    return false;
}

function add_user_to_contest($cid, $username)
{
    global $db, $EZSQL_ERROR;
    if (!is_array($username)) $username = array($username);
    $sql = "insert into contest_user (cid,username) values ";
    $values = array();
    foreach ($username as $one) {
        $values[] = "('$cid','$one')";
    }
    $sql .= implode(",", $values);
    $db->query($sql);
    if ($EZSQL_ERROR) return false;
    else return true;
}

function reset_password($infos)
{
    global $db, $EZSQL_ERROR;
    if (!is_array($infos[0])) $infos = array($infos);
    $sql = "";
    foreach ($infos as $one) {
        $one[0] = $db->escape($one[0]);
        $one[1] = hash_password($one[1]);
        $sql .= "update user set password='$one[1]' where username='$one[0]';";
    }
    if ($EZSQL_ERROR) return false;
    else return true;
}
