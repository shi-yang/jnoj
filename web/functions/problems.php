<?php
include_once(dirname(__FILE__) . "/global.php");

class Problem
{
    private $info = array();
    private $valid = false;

    private function get_to_page($problemperpage)
    {
        global $db;
        $querypage = "select count(*) from problem where pid<'" . $db->escape($this->info["pid"]) . "' and hide=0";
        list($ppage) = $db->get_row($querypage, ARRAY_A);
        return $this->info["to_page"] = intval($ppage / $problemperpage) + 1;
    }

    private function get_to_url()
    {
        global $db;
        if (!isset($this->info["vname"])) $this->get_val("vname");
        if (!isset($this->info["vid"])) $this->get_val("vid");

        $vname = $db->escape($this->info["vname"]);
        $vid = $db->escape($this->info["vid"]);

        if ($vname == "PKU") $this->info["to_url"] = "<a href='http://acm.pku.edu.cn/JudgeOnline/problem?id=$vid' target='_blank'>$vid</a>";
        else if ($vname == "OpenJudge") $this->info["to_url"] = "<a href='http://poj.openjudge.cn/practice/$vid' target='_blank'>$vid</a>";
        else if ($vname == "CodeForces") {
            $contest = substr($vid, 0, -1);
            $label = substr($vid, -1);
            $this->info["to_url"] = "<a href='http://codeforces.com/problemset/problem/$contest/$label' target='_blank'>$vid</a>";
        } else if ($vname == "CodeForcesGym") {
            $contest = substr($vid, 0, -1);
            $label = substr($vid, -1);
            $this->info["to_url"] = "<a href='http://codeforces.com/gym/$contest/problem/$label' target='_blank'>$vid</a>";
        } else if ($vname == "HDU") $this->info["to_url"] = "<a href='http://acm.hdu.edu.cn/showproblem.php?pid=$vid' target='_blank'>$vid</a>";
        else if ($vname == "SGU") $this->info["to_url"] = "<a href='http://acm.sgu.ru/problem.php?contest=0&problem=$vid' target='_blank'>$vid</a>";
        else if ($vname == "LightOJ") $this->info["to_url"] = "<a href='http://www.lightoj.com/volume_showproblem.php?problem=$vid' target='_blank'>$vid</a>";
        else if ($vname == "Ural") $this->info["to_url"] = "<a href='http://acm.timus.ru/problem.aspx?num=$vid' target='_blank'>$vid</a>";
        else if ($vname == "ZJU") $this->info["to_url"] = "<a href='http://acm.zju.edu.cn/onlinejudge/showProblem.do?problemCode=$vid' target='_blank'>$vid</a>";
        else if ($vname == "SPOJ") $this->info["to_url"] = "<a href='http://www.spoj.pl/problems/$vid/' target='_blank'>$vid</a>";
        else if ($vname == "UESTC") $this->info["to_url"] = "<a href='http://acm.uestc.edu.cn/#/problem/show/$vid' target='_blank'>$vid</a>";
        else if ($vname == "FZU") $this->info["to_url"] = "<a href='http://acm.fzu.edu.cn/problem.php?pid=$vid' target='_blank'>$vid</a>";
        else if ($vname == "NBUT") $this->info["to_url"] = "<a href='https://ac.2333.moe/Problem/view.xhtml?id=$vid' target='_blank'>$vid</a>";
        else if ($vname == "WHU") $this->info["to_url"] = "<a href='http://acm.whu.edu.cn/land/problem/detail?problem_id=$vid' target='_blank'>$vid</a>";
        else if ($vname == "SYSU") $this->info["to_url"] = "<a href='http://soj.sysu.edu.cn/$vid' target='_blank'>$vid</a>";
        else if ($vname == "SCU") $this->info["to_url"] = "<a href='http://acm.scu.edu.cn/soj/problem.action?id=$vid' target='_blank'>$vid</a>";
        else if ($vname == "HUST") $this->info["to_url"] = "<a href='http://acm.hust.edu.cn/problem/show/$vid' target='_blank'>$vid</a>";
        else if ($vname == "NJUPT") $this->info["to_url"] = "<a href='http://acm.njupt.edu.cn/acmhome/problemdetail.do?&method=showdetail&id=$vid' target='_blank'>$vid</a>";
        else if ($vname == "Aizu") $this->info["to_url"] = "<a href='http://judge.u-aizu.ac.jp/onlinejudge/description.jsp?id=$vid' target='_blank'>$vid</a>";
        else if ($vname == "ACdream") $this->info["to_url"] = "<a href='http://acdream.info/problem?pid=$vid' target='_blank'>$vid</a>";
        else if ($vname == "CodeChef") $this->info["to_url"] = "<a href='http://www.codechef.com/problems/$vid' target='_blank'>$vid</a>";
        else if ($vname == "HRBUST") $this->info["to_url"] = "<a href='http://acm.hrbust.edu.cn/index.php?m=ProblemSet&a=showProblem&problem_id=$vid' target='_blank'>$vid</a>";
        else if ($vname == "UVALive") {
            list($url) = $db->get_row("select url from vurl where voj='$vname' and vid='$vid'", ARRAY_N);
            $this->info["to_url"] = "<a href='$url' target='_blank'>$vid</a>";
        } else {
            list($url) = $db->get_row("select url from vurl where voj='$vname' and vid='$vid'", ARRAY_N);
            $this->info["to_url"] = "<a href='$url' target='_blank'>$vid</a>";
        }
        return $this->info["to_url"];
    }

    private function load_ojinfo()
    {
        global $db;
        if (!isset($this->info["vname"])) $this->get_val("vname");
        $vname = $db->escape($this->info["vname"]);
        $ojrow = $db->get_row("select * from ojinfo where name='$vname'", ARRAY_A);
        $this->info["i64io_info"] = $ojrow['int64io'];
        $this->info["java_class"] = $ojrow['javaclass'];
        $this->info["support_lang"] = explode(',', $ojrow['supportlang']);
    }

    private function get_i64io_info()
    {
        $this->load_ojinfo();
        return $this->info["i64io_info"];
    }

    private function get_java_class()
    {
        $this->load_ojinfo();
        return $this->info["java_class"];
    }

    private function get_support_lang()
    {
        $this->load_ojinfo();
        return $this->info["support_lang"];
    }

    private function get_tagged_category()
    {
        global $db;
        $this->info["tagged_category"] = $db->get_results("select name,catid,weight from category, problem_category where pid='" . $db->escape($this->info["pid"]) . "' and category.id=problem_category.catid and weight>0", ARRAY_A);
        return $this->info["tagged_category"];
    }

    private function get_stat()
    {
        global $db;
        $this->info["stat"] = array();
        $pid = $db->escape($this->info['pid']);
        list($this->info["stat"]["num_ac"]) = $db->get_row("select count(*) from status where pid='$pid' and result='Accepted'", ARRAY_N);
        list($this->info["stat"]["num_ce"]) = $db->get_row("select count(*) from status where pid='$pid' and result='Compile Error'", ARRAY_N);
        list($this->info["stat"]["num_wa"]) = $db->get_row("select count(*) from status where pid='$pid' and result='Wrong Answer'", ARRAY_N);
        list($this->info["stat"]["num_pe"]) = $db->get_row("select count(*) from status where pid='$pid' and result='Presentation Error'", ARRAY_N);
        list($this->info["stat"]["num_re"]) = $db->get_row("select count(*) from status where pid='$pid' and result='Runtime Error'", ARRAY_N);
        list($this->info["stat"]["num_tle"]) = $db->get_row("select count(*) from status where pid='$pid' and result='Time Limit Exceed'", ARRAY_N);
        list($this->info["stat"]["num_mle"]) = $db->get_row("select count(*) from status where pid='$pid' and result='Memory Limit Exceed'", ARRAY_N);
        list($this->info["stat"]["num_ole"]) = $db->get_row("select count(*) from status where pid='$pid' and result='Output Limit Exceed'", ARRAY_N);
        list($this->info["stat"]["num_rf"]) = $db->get_row("select count(*) from status where pid='$pid' and result='Restricted Function'", ARRAY_N);
        list($this->info["stat"]["num_total"]) = $db->get_row("select count(*) from status where pid='$pid'", ARRAY_N);
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

    private function get_time_limit()
    {
        $this->get_col("time_limit");
        if ($this->info["time_limit"] == "0") $this->info["time_limit"] = "Unknown ";
        return $this->info["time_limit"];
    }

    private function get_case_time_limit()
    {
        $this->get_col("case_time_limit");
        if ($this->info["case_time_limit"] == "0") $this->info["case_time_limit"] = "Unknown ";
        return $this->info["case_time_limit"];
    }

    private function get_memory_limit()
    {
        $this->get_col("memory_limit");
        if ($this->info["memory_limit"] == "0") $this->info["memory_limit"] = "Unknown ";
        return $this->info["memory_limit"];
    }

    private function get_description()
    {
        $this->get_col("description");
        $this->info["description"] = preg_replace('/<head[\s\S]*\/head>/', "", $this->info["description"]);
        return $this->info["description"];
    }

    private function get_col($str)
    {
        global $db;
        $row = $db->get_row("select $str from problem where pid='" . $db->escape($this->info["pid"]) . "'", ARRAY_N);
        return $this->info[$str] = $row[0];
    }

    public function get_val($str)
    {
        if (!$this->valid) return null;
        if (isset($this->info[$str])) return $this->info[$str];
        $tstr = "get_" . $str;
        if (method_exists($this, $tstr)) return $this->$tstr();
        else return $this->get_col($str);
    }

    public function set_problem($pid)
    {
        global $db;
        $sql = "select * from problem where pid='$pid'";
        $db->query($sql);
        $num = $db->num_rows;
        if ($num == 0) return false;
        $this->valid = true;
        unset($this->info);
        $this->info["pid"] = $pid;
        return true;
    }

    public function is_valid()
    {
        return $this->valid;
    }
}

function problem_exist($pid)
{
    global $db;
    $db->query("select * from problem where pid = '$pid'");
    if ($db->num_rows == 0) return false;
    else return true;
}

function problem_hidden($pid)
{
    global $db;
    $row = $db->get_row("select hide from problem where pid = '$pid'", ARRAY_N);
    if ($row[0] == '0') return false;
    else return true;
}

function problem_get_title($pid)
{
    global $db;
    $row = $db->get_row("select title from problem where pid = '$pid'", ARRAY_N);
    return $row[0];
}

function problem_get_id_from_virtual($vname, $vid)
{
    global $db;
    $row = $db->get_row("select pid from problem where vname='$vname' and vid = '$vid'", ARRAY_N);
    return $row[0];
}

function problem_get_id_from_oj_and_title($vname, $title)
{
    global $db;
    $pid = null;
    $title2 = strtr($title, array("'" => "%", "\"" => "%"));
    $title = convert_str($title);
    $row = $db->get_row("select pid from problem where vname='$vname' and title='$title'", ARRAY_N);
    if ($row == null) {
        $row = $db->get_row("select pid from problem where vname='$vname' and title like '$title2'", ARRAY_N);
    }
    return $row[0];
}

$problem_categories = null;
function problem_search_category($row, $depth)
{
    global $problem_categories, $db;
    $trow["id"] = $row["id"];
    $trow["depth"] = $depth;
    $trow["name"] = $row["name"];
    $problem_categories[] = $trow;

    foreach ((array)$db->get_results("select * from category where parent='" . $row['id'] . "'", ARRAY_A) as $row) problem_search_category($row, $depth + 1);
}

function problem_get_category()
{
    global $problem_categories, $db;
    if (isset($problem_categories)) return $problem_categories;
    foreach ((array)$db->get_results("select * from category where parent='-1'", ARRAY_A) as $row) problem_search_category($row, 0);
    return $problem_categories;
}

function problem_get_category_name_from_id($id)
{
    global $db;
    list($ctname) = $db->get_row("select name from category where id='$id'", ARRAY_N);
    return $ctname;
}

function problem_get_category_parent_from_id($id)
{
    global $db;
    list($parent) = $db->get_row("select parent from category where id='$id'", ARRAY_N);
    return $parent;
}

function problem_support_lang($vname)
{
    global $db;
    return explode(',', $db->get_row("select supportlang from ojinfo where name='$vname'", ARRAY_N)[0]);
}