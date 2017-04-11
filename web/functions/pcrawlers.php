<?php
include_once(dirname(__FILE__) . "/global.php");
include_once(dirname(__FILE__) . "/simple_html_dom.php");
include_once(dirname(__FILE__) . "/normalize_url.php");


$timeoutopts = stream_context_create(array('http' =>
    array(
        'timeout' => 120
    )
));
$crawled = array();
function process_and_get_image($ori, $path, $baseurl, $space_deli, $cookie)
{
    $para["path"] = $path;
    $para["base"] = $baseurl;
    $para["trans"] = !$space_deli;
    $para["cookie"] = $cookie;
    if ($space_deli) $reg = "/< *im[a]?g[^>]*src *= *[\"\\']?([^\"\\' >]*)[^>]*>/si";
    else $reg = "/< *im[a]?g[^>]*src *= *[\"\\']?([^\"\\'>]*)[^>]*>/si";
    return preg_replace_callback($reg,
        function ($matches) use ($para) {
            global $config, $crawled;
            $url = trim($matches[1]);
            if (stripos($url, "http://") === false && stripos($url, "https://") === false) {
                if ($para["trans"]) $url = str_replace(" ", "%20", $url);
                $url = $para["base"] . $url;
            }
            $url = normalizeURL($url);
            if ($crawled[$url]) return $result;
            $crawled[$url] = true;
            $name = basename($url);
            $name = "images/" . $para["path"] . "/" . strtr($name, ":", "_");

            $result = str_replace(trim($matches[1]), $name, $matches[0]);

            if (file_exists($config["base_local_path"] . $name)) return $result;
            mkdirs($config["base_local_path"] . $name);

            //echo $url;
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            if ($para["cookie"] != "") curl_setopt($ch, CURLOPT_COOKIEFILE, $para["cookie"]);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $content = curl_exec($ch);
            curl_close($ch);
            //echo $content;

            $fp = fopen($config["base_local_path"] . $name, "wb");
            fwrite($fp, $content);
            fclose($fp);
            return $result;
        },
        $ori);
}

function pcrawler_process_info($ret, $path, $baseurl, $space_deli = true, $cookie = "")
{
    $ret["description"] = process_and_get_image($ret["description"], $path, $baseurl, $space_deli, $cookie);
    $ret["input"] = process_and_get_image($ret["input"], $path, $baseurl, $space_deli, $cookie);
    $ret["output"] = process_and_get_image($ret["output"], $path, $baseurl, $space_deli, $cookie);
    $ret["hint"] = process_and_get_image($ret["hint"], $path, $baseurl, $space_deli, $cookie);
    return $ret;
}

function pcrawler_insert_problem($ret, $vname, $vid)
{
    global $db;
    $vname = $db->escape($vname);
    $vid = $db->escape($vid);
    $db->query("select pid from problem where vname like '$vname' and vid like '$vid'");
    if ($db->num_rows == 0) {
        $sql_add_pro = "insert into problem
        (title,description,input,output,sample_in,sample_out,hint,source,author,hide,memory_limit,time_limit,special_judge_status,case_time_limit,basic_solver_value,number_of_testcase,isvirtual,vname,vid,vacnum,vtotalnum) values
        ('" . $db->escape($ret["title"]) . "','" . $db->escape($ret["description"]) . "','" . $db->escape($ret["input"]) . "','" . $db->escape($ret["output"]) . "','" . $db->escape($ret["sample_in"]) . "','" . $db->escape($ret["sample_out"]) . "','" . $db->escape($ret["hint"]) . "','" . $db->escape($ret["source"]) . "','" . $db->escape($ret["author"]) . "','0','" . $ret["memory_limit"] . "','" . $ret["time_limit"] . "','" . $ret["special_judge_status"] . "','" . $ret["case_time_limit"] . "','0','0',1,'$vname','$vid'," . intval($ret['vacnum']) . "," . intval($ret['vtotalnum']) . ")";
        $db->query($sql_add_pro);
        $gnum = $db->insert_id;
    } else {
        list($gnum) = $db->get_row(null, ARRAY_N);
        $sql_add_pro = "update problem set
                            title='" . $db->escape($ret["title"]) . "',
                            description='" . $db->escape($ret["description"]) . "',
                            input='" . $db->escape($ret["input"]) . "',
                            output='" . $db->escape($ret["output"]) . "',
                            sample_in='" . $db->escape($ret["sample_in"]) . "',
                            sample_out='" . $db->escape($ret["sample_out"]) . "',
                            hint='" . $db->escape($ret["hint"]) . "',
                            source='" . $db->escape($ret["source"]) . "',
                            author='" . $db->escape($ret["author"]) . "',
                            hide='0',
                            memory_limit='" . $ret["memory_limit"] . "',
                            time_limit='" . $ret["time_limit"] . "',
                            special_judge_status='" . $ret["special_judge_status"] . "',
                            case_time_limit='" . $ret["case_time_limit"] . "',
                            vname='$vname',
                            vid='$vid',
                            vacnum=" . intval($ret['vacnum']) . ",
                            vtotalnum=" . intval($ret['vtotalnum']) . "
                            where pid=$gnum";
        $db->query($sql_add_pro);
    }
    return $gnum;
}

function init_result()
{
    return array(
        "title" => "",
        "description" => "",
        "input" => "",
        "output" => "",
        "sample_in" => "",
        "sample_out" => "",
        "hint" => "",
        "source" => "",
        "author" => "",
        "memory_limit" => "",
        "time_limit" => "",
        "special_judge_status" => "",
        "case_time_limit" => "",
        "vacnum" => "",
        "vtotalnum" => ""
    );
}

function pcrawler_cf_one($cid, $num, $url, $ret = array(), $default_desc = "")
{
    global $config;

    $ret = init_result();
    $pid = $cid . $num;
    $content = get_url($url);
    $content_type = get_headers($url, 1)["Content-Type"];
    if (stripos($content, "<title>Codeforces</title>") === false) {
        if (stripos($content, "<title>Attachments") !== false) {
            $ret["description"] .= $default_desc;
        } else {
            if (stripos($content_type, "text/html") !== false) {
                if (preg_match("/<div class=\"title\">$num\\. (.*)<\\/div>/sU", $content, $matches)) $ret["title"] = trim(html_entity_decode($matches[1]));
                if (preg_match("/time limit per test<\\/div>(.*) second/sU", $content, $matches)) $ret["time_limit"] = intval(trim($matches[1])) * 1000;
                $ret["case_time_limit"] = $ret["time_limit"];
                if (preg_match("/memory limit per test<\\/div>(.*) megabyte/sU", $content, $matches)) $ret["memory_limit"] = intval(trim($matches[1])) * 1024;
                if (preg_match("/output<\\/div>.*<div>(<p>.*)<\\/div>/sU", $content, $matches)) $ret["description"] .= trim(html_entity_decode($matches[1]));
                if (preg_match("/Input<\\/div>(.*)<\\/div>/sU", $content, $matches)) $ret["input"] = trim($matches[1]);
                if (preg_match("/Output<\\/div>(.*)<\\/div>/sU", $content, $matches)) $ret["output"] = trim($matches[1]);
                if (preg_match("/Examples<\\/div>(.*<\\/div><\\/div>)<\\/div>/sU", $content, $matches)) $ret["sample_in"] = trim($matches[1]);
                if (preg_match("/Note<\\/div>(.*)<\\/div><\\/div>/sU", $content, $matches)) $ret["hint"] = trim(html_entity_decode($matches[1]));
                if (preg_match("/<th class=\"left\" style=\"width:100%;\">(.*)<\\/th>/sU", $content, $matches)) $ret["source"] = trim(strip_tags($matches[1]));
                $ret["special_judge_status"] = 0;
            } else {
                if (stripos($content_type, "application/pdf") !== false) $ext = "pdf";
                else if (stripos($content_type, "application/msword") !== false) $ext = "doc";
                else if (stripos($content_type, "application/application/vnd.openxmlformats-officedocument.wordprocessingml.document") !== false) $ext = "docx";
                file_put_contents($config["base_local_path"] . "external/gym/$cid$num.$ext", $content);
                $ret["description"] .= "<a href=\"external/gym/$cid$num.$ext\">[Attachment Link]</a>";
            }
        }
        return $ret;
    } else return false;
}

function pcrawler_codeforces($cid)
{
    $msg = "";
    $num = 'A';
    while ($row = pcrawler_cf_one($cid, $num, "http://codeforces.com/problemset/problem/$cid/$num")) {
        $row = pcrawler_process_info($row, "cf", "http://codeforces.ru/");
        $id = pcrawler_insert_problem($row, "CodeForces", $cid . $num);
        $msg .= "CodeForces $cid$num has been crawled as $id.<br>";
        $num++;
    }
    $msg .= "No problem called CodeForces $cid$num.<br>";
    return $msg;
}

function pcrawler_codeforces_num()
{
    global $db;
    $i = 1;
    $one = 0;
    while (true) {
        if ($one) break;
        $html = str_get_html(get_url("http://www.codeforces.com/problemset/page/$i"));
        $table = $html->find("table.problems", 0);
        $rows = $table->find("tr");
        for ($j = 1; $j < sizeof($rows); $j++) {
            $row = $rows[$j];
            $pid = trim($row->find("td", 0)->find("a", 0)->innertext);
            $acnum = substr(trim($row->find("td", 3)->find("a", 0)->plaintext), 7);
            $totnum = 0;
            if ($pid == '1A') $one++;
            $db->query("update problem set vacnum='$acnum', vtotalnum='$totnum' where vname='CodeForces' and vid='$pid'");
        }
        $i++;
    }
    return "Done";
}

function pcrawler_codeforcesgym($cid)
{
    global $config;
    $msg = "";
    $html = str_get_html(get_url("http://codeforces.com/gym/$cid"));
    $table = $html->find("table.problems", 0);
    $rows = $table->find("tr");
    $probs = $prob = array();
    $prob["input"] = $prob["output"] = $prob["sample_in"] = $prob["sample_out"] = $prob["hint"] = $prob["source"] = "";
    if (preg_match("/Dashboard - (.*) - Codeforces/sU", $html->find("title", 0)->innertext, $matches)) $prob["source"] = trim($matches[1]);
    for ($i = 1; $i < sizeof($rows); $i++) {
        $row = $rows[$i];
        preg_match("/class=\"id\">.*<a href=\"\/gym.*\">.*([A-Za-z0-9]+)\s*<\/a>.*<!--.*-->(.*)<!--.*class=\"notice\">.*<div.*>(.*)<\/div>.*([0-9]*) s, ([0-9]*) MB/sU", $row->innertext, $matches);
        $prob["label"] = $matches[1];
        $prob["title"] = trim(html_entity_decode($matches[2]));
        $prob["time_limit"] = $prob["case_time_limit"] = intval($matches[4]) * 1000;
        $prob["memory_limit"] = intval($matches[5]) * 1024;
        $prob["description"] = "<p><strong>Input/Output: " . trim($matches[3]) . "</strong></p>";
        $prob["special_judge_status"] = 0;
        $prob["author"] = "";
        $probs[$prob["label"]] = $prob;
    }
    //Trying to get attchments
    $default_desc = "";
    if (stripos($att = get_url("http://codeforces.com/gym/$cid/attachments"), "<title>Attachments") !== false) {
        if (preg_match("/<a href=\"(\/gym\/$cid.*\.(pdf|doc|ps|zip))\"/sU", $att, $matches)) {
            $path = $matches[1];
            $ext = $matches[2];
            file_put_contents($config["base_local_path"] . "external/gym/$cid.$ext", get_url("http://codeforces.com/$path"));
            $default_desc = "<a href=\"external/gym/$cid.$ext\">[Attachment Link]</a>";
        } else {
            $msg = "Fetch attachments failed";
        }
    }
    foreach ($probs as $num => $prob) {
        $row = pcrawler_cf_one($cid, $num, "http://codeforces.com/gym/$cid/problem/$num", $prob, $default_desc);
        $row = pcrawler_process_info($row, "cf", "http://codeforces.ru/");
        $id = pcrawler_insert_problem($row, "CodeForcesGym", $cid . $num);
        $msg .= "CodeForces $cid$num has been crawled as $id.<br>";
        $num++;
    }
    return $msg;
}

function pcrawler_codeforcesgym_num()
{
    global $db;
    $html = str_get_html(get_url("http://codeforces.com/gyms"));
    $clist = $html->find(".contestList table", 0)->find("tr");
    for ($i = 1; $i < sizeof($clist); $i++) {
        $cid = substr($clist[$i]->find("td", 0)->find("a", 0)->href, 5);
        $html = str_get_html(get_url("http://codeforces.com/gym/$cid"));
        $table = $html->find("table.problems", 0);
        $rows = $table->find("tr");
        for ($j = 1; $j < sizeof($rows); $j++) {
            $row = $rows[$j];
            $pid = $cid . trim($row->find("td", 0)->find("a", 0)->innertext);
            $acnum = $row->find("td", 3)->find("a", 0) ? substr(trim($row->find("td", 3)->find("a", 0)->plaintext), 7) : "0";
            $totnum = 0;
            $db->query("update problem set vacnum='$acnum', vtotalnum='$totnum' where vname='CodeForcesGym' and vid='$pid'");
        }
    }
    return "Done";
}

function pcrawler_fzu($pid)
{
    $url = "http://acm.fzu.edu.cn/problem.php?pid=$pid";
    $content = get_url($url);
    $ret = array();

    if (stripos($content, "<font size=\"+3\">No Such Problem!</font>") === false) {
        if (preg_match("/<b> Problem $pid(.*)<\\/b>/sU", $content, $matches)) $ret["title"] = trim($matches[1]);
        if (preg_match("/<br \\/>Time Limit:(.*) mSec/sU", $content, $matches)) $ret["time_limit"] = intval(trim($matches[1]));
        $ret["case_time_limit"] = $ret["time_limit"];
        if (preg_match("/Memory Limit : (.*) KB/sU", $content, $matches)) $ret["memory_limit"] = intval(trim($matches[1]));
        if (preg_match("/Problem Description<\\/h2><\\/b>(.*)<h2>/sU", $content, $matches)) $ret["description"] = trim($matches[1]);
        if (preg_match("/> Input<\\/h2>(.*)<h2>/sU", $content, $matches)) $ret["input"] = trim($matches[1]);
        if (preg_match("/> Output<\\/h2>(.*)<h2>/sU", $content, $matches)) $ret["output"] = trim($matches[1]);
        if (preg_match("/<div class=\"data\">(.*)<\\/div>/sU", $content, $matches)) $ret["sample_in"] = trim($matches[1]);
        if ($ret["sample_in"] == "") {
            if (preg_match("/<div class=\"data\">(.*)<\\/div>/sU", $content, $matches)) $ret["sample_out"] = trim($matches[1]);
        } else if (preg_match("/<div class=\"data\">.*<div class=\"data\">(.*)<\\/div>/sU", $content, $matches)) $ret["sample_out"] = trim($matches[1]);
        if (preg_match("/Hint<\\/h2>(.*)<h2>/sU", $content, $matches)) $ret["hint"] = trim($matches[1]);
        if (preg_match("/Source<\\/h2>(.*)<\\/div>/sU", $content, $matches)) $ret["source"] = trim($matches[1]);
        if (strpos($content, "<font color=\"blue\">Special Judge</font>") !== false) $ret["special_judge_status"] = 1;
        else $ret["special_judge_status"] = 0;

        $ret = pcrawler_process_info($ret, "fzu", "http://acm.fzu.edu.cn/");
        $id = pcrawler_insert_problem($ret, "FZU", $pid);
        return "FZU $pid has been crawled as $id.<br>";
    } else return "No problem called FZU $pid.<br>";
}

function pcrawler_fzu_num()
{
    global $db;

    $i = 1;
    while (true) {
        $html = str_get_html(get_url("http://acm.fzu.edu.cn/list.php?vol=$i"));
        $table = $html->find("table", 0);
        $rows = $table->find("tr");
        if (sizeof($rows) < 2) break;
        for ($j = 1; $j < sizeof($rows); $j++) {
            $row = $rows[$j];
            //echo htmlspecialchars($row);
            $pid = $row->find("td", 1)->plaintext;
            $tstr = $row->find("td", 3)->plaintext;
            $acnum = substr(strstr(strstr($tstr, '('), '/', true), 1);
            $totnum = substr(strstr(strstr($tstr, '/'), ')', true), 1);
            //echo "$pid $acnum $totnum<br>";die();
            $db->query("update problem set vacnum='$acnum', vtotalnum='$totnum' where vname='FZU' and vid='$pid'");
        }
        $i++;
    }

    return "Done";
}

function pcrawler_hdu($pid)
{
    $url = "http://acm.hdu.edu.cn/showproblem.php?pid=$pid";
    $content = get_url($url);
    $content = iconv("gbk", "UTF-8//IGNORE", $content);
    $ret = array();

    if (stripos($content, "Invalid Parameter") === false && stripos($content, "No such problem - <strong>Problem") === false) {
        if (preg_match("/<h1 style='color:#1A5CC8'>(.*)<\\/h1>/sU", $content, $matches)) $ret["title"] = trim($matches[1]);
        if (preg_match("/Time Limit:.*\\/(.*) MS/sU", $content, $matches)) $ret["time_limit"] = intval(trim($matches[1]));
        $ret["case_time_limit"] = $ret["time_limit"];
        if (preg_match("/Memory Limit:.*\\/(.*) K/sU", $content, $matches)) $ret["memory_limit"] = intval(trim($matches[1]));
        if (preg_match("/Problem Description.*<div class=panel_content>(.*)<\\/div><div class=panel_bottom>/sU", $content, $matches)) $ret["description"] = trim($matches[1]);
        if (preg_match("/<div class=panel_title align=left>Input.*<div class=panel_content>(.*)<\\/div><div class=panel_bottom>/sU", $content, $matches)) $ret["input"] = trim($matches[1]);
        if (preg_match("/<div class=panel_title align=left>Output.*<div class=panel_content>(.*)<\\/div><div class=panel_bottom>/sU", $content, $matches)) $ret["output"] = trim($matches[1]);
        if (preg_match("/Sample Input.*<pre><div.*>(.*)<\/div>/sU", $content, $matches)) $ret["sample_in"] = trim($matches[1]);
        if (preg_match("/Sample Output.*<pre><div.*>(.*)<\/?div/sU", $content, $matches)) $ret["sample_out"] = trim($matches[1]);
        if (preg_match("/<i>Hint<\/i>.*<\/i>(.*)<\/div><\/pre>/sU", $content, $matches)) $ret["hint"] = trim($matches[1]);
        if (preg_match("/<div class=panel_title align=left>Source<\\/div> (.*)<div class=panel_bottom>/sU", $content, $matches)) $ret["source"] = trim(strip_tags($matches[1]));
        if (strpos($content, "<font color=red>Special Judge</font>") !== false) $ret["special_judge_status"] = 1;
        else $ret["special_judge_status"] = 0;

        $ret = pcrawler_process_info($ret, "hdu", "http://acm.hdu.edu.cn/");
        $id = pcrawler_insert_problem($ret, "HDU", $pid);
        return "HDU $pid has been crawled as $id.<br>";
    } else return "No problem called HDU $pid.<br>";
}

function pcrawler_hdu_num()
{
    global $db;
    $i = 1;
    while (true) {
        $html = str_get_html(get_url("http://acm.hdu.edu.cn/listproblem.php?vol=$i"));
        $table = $html->find("table", 4);
        $txt = explode(";", $table->find("script", 0)->innertext);
        if (sizeof($txt) < 2) break;
        foreach ($txt as $one) {
            $det = explode(",", $one);
            $pid = $det[1];
            $acnum = $det[sizeof($det) - 2];
            $totnum = substr($det[sizeof($det) - 1], 0, -1);
            $db->query("update problem set vacnum='$acnum', vtotalnum='$totnum' where vname='HDU' and vid='$pid'");
        }
        $i++;
    }

    return "Done";
}


function pcrawler_openjudge($pid)
{
    $url = "http://poj.openjudge.cn/practice/$pid";
    $content = get_url($url);
    $ret = array();

    if (stripos($content, "<div id=\"pageTitle\"><h2>") !== false) {
        if (preg_match('/<div id="pageTitle"><h2>.*:(.*)<\/h2>/sU', $content, $matches)) $ret["title"] = trim($matches[1]);
        if (preg_match('/<dt>总时间限制: <\/dt>.*<dd>(.*)ms/sU', $content, $matches)) $ret["time_limit"] = intval(trim($matches[1]));
        $ret["case_time_limit"] = $ret["time_limit"];
        if (preg_match('/<dt>内存限制: <\/dt>.*<dd>(.*)kB/sU', $content, $matches)) $ret["memory_limit"] = intval(trim($matches[1]));
        if (preg_match('/<dt>描述<\/dt>.*<dd>(.*)<\/dd>/sU', $content, $matches)) $ret["description"] = trim($matches[1]);
        if (preg_match('/<dt>输入<\/dt>.*<dd>(.*)<\/dd>/sU', $content, $matches)) $ret["input"] = trim($matches[1]);
        if (preg_match('/<dt>输出<\/dt>.*<dd>(.*)<\/dd>/sU', $content, $matches)) $ret["output"] = trim($matches[1]);
        if (preg_match('/<dt>样例输入<\/dt>.*<pre>(.*)<\/pre>/sU', $content, $matches)) $ret["sample_in"] = trim($matches[1]);
        if (preg_match('/<dt>样例输出<\/dt>.*<pre>(.*)<\/pre>/sU', $content, $matches)) $ret["sample_out"] = trim($matches[1]);
        $ret["hint"] = "";
        $ret["source"] = "";
        $ret["special_judge_status"] = 0;

        $ret = pcrawler_process_info($ret, "openjudge", "http://poj.openjudge.cn/practice/$pid/");
        $id = pcrawler_insert_problem($ret, "OpenJudge", $pid);
        return "OpenJudge $pid has been crawled as $id.<br>";
    } else return "No problem called OpenJudge $pid.<br>";
}

function pcrawler_openjudge_num()
{
    global $db;
    $got = array();
    $i = 1;
    while (true) {
        $html = str_get_html(get_url("http://poj.openjudge.cn/practice/?page=$i"));
        $table = $html->find("table", 0);
        $rows = $table->find("tr");
        if (isset($got[$rows[1]->find("td", 0)->plaintext])) break;
        for ($j = 1; $j < sizeof($rows); $j++) {
            $row = $rows[$j];
            //echo htmlspecialchars($row);
            $pid = $row->find("td", 0)->plaintext;
            $got[$pid] = true;
            $acnum = $row->find("td", 3)->plaintext;
            $totnum = $row->find("td", 4)->plaintext;
            //echo "$pid $acnum $totnum<br>";die();
            $db->query("update problem set vacpnum='$acnum', vtotalpnum='$totnum' where vname='OpenJudge' and vid='$pid'");
        }
        $i++;
    }

    return "Done";
}

function pcrawler_sysu($pid)
{
    $url = "http://soj.sysu.edu.cn/$pid";
    $content = get_url($url);
    $ret = array();

    if (stripos($content, "<div id=\"error_msg\">") === false) {
        if (preg_match('/<center><h1>.* (.*)<\/h1>/sU', $content, $matches)) $ret["title"] = trim($matches[1]);
        if (preg_match('/<p>Time Limit: (.*) secs/sU', $content, $matches)) $ret["time_limit"] = intval(trim($matches[1])) * 1000;
        $ret["case_time_limit"] = $ret["time_limit"];
        if (preg_match('/, Memory Limit: (.*) MB/sU', $content, $matches)) $ret["memory_limit"] = intval(trim($matches[1])) * 1024;
        if (preg_match('/<h1>Description<\/h1>(.*)<h1>/sU', $content, $matches)) $ret["description"] = trim($matches[1]);
        if (preg_match('/<h1>Input<\/h1>(.*)<h1>Input/sU', $content, $matches)) $ret["input"] = trim($matches[1]);
        if (preg_match('/<h1>Output<\/h1>(.*)<h1>Sample/sU', $content, $matches)) $ret["output"] = trim($matches[1]);
        if (preg_match('/<h1>Sample.*<pre>(.*)<\/pre>/sU', $content, $matches)) $ret["sample_in"] = trim($matches[1]);
        if (preg_match('/<h1>Sample.*<pre>.*<pre>(.*)<\/pre>/sU', $content, $matches)) $ret["sample_out"] = trim($matches[1]);
        $ret["hint"] = "";
        if (preg_match('/<h1>Problem Source<\/h1>.*<p>(.*)<\/p>/sU', $content, $matches)) $ret["source"] = trim($matches[1]);
        if (strpos($content, "<font color=\"blue\">Special Judge</font>") !== false) $ret["special_judge_status"] = 1;
        else $ret["special_judge_status"] = 0;

        $ret = pcrawler_process_info($ret, "sysu", "http://soj.me/");
        $id = pcrawler_insert_problem($ret, "SYSU", $pid);
        return "SYSU $pid has been crawled as $id.<br>";
    } else return "No problem called SYSU $pid.<br>";
}

function pcrawler_sysu_num()
{
    global $db;

    $html = str_get_html(get_url("http://soj.me/problem_tab.php?start=1000&end=999999"));
    $table = $html->find("table", 0);
    $rows = $table->find("tr");
    for ($j = 1; $j < sizeof($rows); $j++) {
        $row = $rows[$j];
        //echo htmlspecialchars($row);
        $pid = $row->find("td", 1)->plaintext;
        $acnum = $row->find("td", 3)->plaintext;
        $totnum = $row->find("td", 4)->plaintext;
        //echo "$pid $acnum $totnum<br>";die();
        $db->query("update problem set vacnum='$acnum', vtotalnum='$totnum' where vname='SYSU' and vid='$pid'");
    }

    return "Done";
}

function pcrawler_scu($pid)
{
    $url = "http://acm.scu.edu.cn/soj/problem.action?id=$pid";
    $content = get_url($url);
    $ret = array();
    $content = iconv("gbk", "UTF-8//IGNORE", $content);
    //$content=mb_convert_encoding($content,"UTF-8","GBK, GB2312, windows-1252");
    if (stripos($content, "<title>ERROR</title>") === false) {
        if (preg_match('/<h1 align="center">.*: (.*)<\/h1>/sU', $content, $matches)) $ret["title"] = trim($matches[1]);
        $ret["case_time_limit"] = $ret["time_limit"] = $ret["memory_limit"] = "0";

        $ret["description"] = get_url("http://acm.scu.edu.cn/soj/problem/$pid");
        $ret["description"] = mb_convert_encoding($ret["description"], "UTF-8", "GBK, GB2312, windows-1252");

        $ret["input"] = $ret["output"] = $ret["sample_in"] = $ret["sample_out"] = $ret["hint"] = $ret["source"] = "";
        $ret["special_judge_status"] = 0;

        $ret = pcrawler_process_info($ret, "scu/$pid", "http://acm.scu.edu.cn/soj/problem/$pid/", false);
        $id = pcrawler_insert_problem($ret, "SCU", $pid);
        return "SCU $pid has been crawled as $id.<br>";
    } else return "No problem called SCU $pid.<br>";
}

function pcrawler_scu_num()
{
    global $db;
    $i = 0;
    while (true) {
        $html = str_get_html(get_url("http://acm.scu.edu.cn/soj/problems.action?volume=$i"));
        $table = $html->find("table", 0);
        $rows = $table->find("tr");
        if (sizeof($rows) < 4) break;
        for ($j = 3; $j < sizeof($rows); $j++) {
            $row = $rows[$j];
            //echo htmlspecialchars($row);
            $pid = $row->find("td", 1)->plaintext;
            $acnum = strip_tags($row->find("td", 4)->innertext);
            $totnum = $row->find("td", 3)->plaintext;
            //echo "$pid $acnum $totnum<br>";
            $db->query("update problem set vacnum='$acnum', vtotalnum='$totnum' where vname='SCU' and vid='$pid'");
        }
        $i++;
    }

    return "Done";
}

function pcrawler_hust($pid)
{
    $url = "http://acm.hust.edu.cn/problem/show/$pid";
    $content = get_url($url);
    $ret = array();

    if (stripos($content, "<h2>Oops! Error.") === false) {
        if (preg_match('/<h1.*>.*- (.*)<\/h1>/sU', $content, $matches)) $ret["title"] = trim($matches[1]);
        if (preg_match('/Time Limit: .*>(\d*)s/sU', $content, $matches)) $ret["time_limit"] = intval(trim($matches[1])) * 1000;
        $ret["case_time_limit"] = $ret["time_limit"];
        if (preg_match('/Memory Limit: .*>(\d*)M/sU', $content, $matches)) $ret["memory_limit"] = intval(trim($matches[1])) * 1024;
        if (preg_match('/<dt>Description.*<dd.*>(.*)<\/dd>/sU', $content, $matches)) $ret["description"] = trim($matches[1]);
        if (preg_match('/<dt>Input.*<dd.*>(.*)<\/dd>/sU', $content, $matches)) $ret["input"] = trim($matches[1]);
        if (preg_match('/<dt>Output.*<dd.*>(.*)<\/dd>/sU', $content, $matches)) $ret["output"] = trim($matches[1]);
        if (preg_match('/<dt>Sample Input.*<pre>(.*)<\/pre>/sU', $content, $matches)) $ret["sample_in"] = trim($matches[1]);
        if (preg_match('/<dt>Sample Output.*<pre>(.*)<\/pre>/sU', $content, $matches)) $ret["sample_out"] = trim($matches[1]);
        if (preg_match('/<dt>Hint.*<dd.*>(.*)<\/dd>/sU', $content, $matches)) $ret["hint"] = trim($matches[1]);
        if (preg_match('/<dt>Source.*<dd.*>(.*)<\/dd>/sU', $content, $matches)) $ret["source"] = trim($matches[1]);
        if (strpos($content, "<span class=\"label label-danger\">Special Judge</span>") !== false) $ret["special_judge_status"] = 1;
        else $ret["special_judge_status"] = 0;

        $ret = pcrawler_process_info($ret, "hust", "http://acm.hust.edu.cn/problem/show/");
        $id = pcrawler_insert_problem($ret, "HUST", $pid);
        return "HUST $pid has been crawled as $id.<br>";
    } else return "No problem called HUST $pid.<br>";
}

function pcrawler_hust_num()
{
    global $db;
    $got = array();
    $i = 1;
    while (true) {
        $html = str_get_html(get_url("http://acm.hust.edu.cn/problem/list/$i"));
        $table = $html->find("table", 0);
        $rows = $table->find("tr");
        if (isset($got[$rows[1]->find("td", 0)->plaintext])) break;
        for ($j = 1; $j < sizeof($rows); $j++) {
            $row = $rows[$j];
            //echo htmlspecialchars($row);
            $pid = $row->find("td", 0)->plaintext;
            $got[$pid] = true;
            $tstr = $row->find("td", 2)->plaintext;
            $acnum = strstr($tstr, '/', true);
            $totnum = substr(strstr($tstr, '/'), 1);
            //echo "$pid $acnum $totnum<br>";die();
            $db->query("update problem set vacnum='$acnum', vtotalnum='$totnum' where vname='HUST' and vid='$pid'");
        }
        $i++;
    }

    return "Done";
}

function pcrawler_pku($pid)
{
    $url = "http://poj.org/problem?id=$pid";
    $content = get_url($url);
    $ret = array();

    if (trim($content) == "") return "No problem called PKU $pid.<br>";
    if (stripos($content, "Can not find problem") === false) {
        if (preg_match('/<div class="ptt" lang="en-US">(.*)<\/div>/sU', $content, $matches)) $ret["title"] = trim($matches[1]);
        if (preg_match('/<td><b>Time Limit:<\/b> (.*)MS<\/td>/sU', $content, $matches)) $ret["time_limit"] = intval(trim($matches[1]));
        $ret["case_time_limit"] = $ret["time_limit"];
        if (preg_match('/<td><b>Memory Limit:<\/b> (.*)K<\/td>/sU', $content, $matches)) $ret["memory_limit"] = intval(trim($matches[1]));
        if (preg_match('/<p class="pst">Description<\/p><div class="ptx" lang="en-US">(.*)<\/div><p class="pst">Input<\/p>/sU', $content, $matches)) $ret["description"] = trim($matches[1]);
        if (preg_match('/<p class="pst">Input<\/p><div class="ptx" lang="en-US">(.*)<\/div><p class="pst">Output<\/p>/sU', $content, $matches)) $ret["input"] = trim($matches[1]);
        if (preg_match('/<p class="pst">Output<\/p><div class="ptx" lang="en-US">(.*)<\/div><p class="pst">Sample Input<\/p>/sU', $content, $matches)) $ret["output"] = trim($matches[1]);
        if (preg_match('/<p class="pst">Sample Input<\/p><pre class="sio">(.*)<\/pre><p class="pst">Sample Output<\/p>/sU', $content, $matches)) $ret["sample_in"] = trim($matches[1]);
        if (preg_match('/<p class="pst">Sample Output<\/p><pre class="sio">(.*)<\/pre><p class="pst">Source<\/p>/sU', $content, $matches)) $ret["sample_out"] = trim($matches[1]);
        if (preg_match('/<p class="pst">Source<\/p><div class="ptx" lang="en-US">(.*)<\/div>/sU', $content, $matches)) $ret["source"] = trim(strip_tags($matches[1]));
        if (preg_match('/<p class="pst">Hint<\/p><div class="ptx" lang="en-US">(.*)<\/div><p class="pst">Source/sU', $content, $matches)) $ret["hint"] = trim($matches[1]);
        if (strpos($content, '<td style="font-weight:bold; color:red;">Special Judge</td>') !== false) $ret["special_judge_status"] = 1;
        else $ret["special_judge_status"] = 0;

        $ret = pcrawler_process_info($ret, "pku", "http://poj.org/");
        $id = pcrawler_insert_problem($ret, "PKU", $pid);
        return "PKU $pid has been crawled as $id.<br>";
    } else {
        return "No problem called PKU $pid.<br>";
    }
}

function pcrawler_pku_num()
{
    global $db;

    $i = 1;
    while (true) {
        $html = str_get_html(get_url("http://poj.org/problemlist?volume=$i"));
        $table = $html->find("table", 4);
        $rows = $table->find("tr");
        if (sizeof($rows) < 2) break;
        for ($j = 1; $j < sizeof($rows); $j++) {
            $row = $rows[$j];
            //echo htmlspecialchars($row);
            $pid = $row->find("td", 0)->plaintext;
            $acnum = $row->find("td", 2)->find("a", 0)->innertext;
            $totnum = $row->find("td", 2)->find("a", 1)->innertext;
            // echo "$pid $acnum $totnum<br>";die();
            $db->query("update problem set vacnum='$acnum', vtotalnum='$totnum' where vname='PKU' and vid='$pid'");
        }
        $i++;
    }

    return "Done";
}

function pcrawler_sgu($pid)
{
    $url = "http://acm.sgu.ru/problem.php?contest=0&problem=$pid";
    $content = get_url($url);
    $ret = array();
    $content = iconv("windows-1251", "UTF-8//IGNORE", $content);
    //$content=mb_convert_encoding($content,"UTF-8","GBK, GB2312, windows-1252");
    if (stripos($content, "<h4>no such problem</h4>") === false) {
        if (preg_match('/<title.*' . $pid . '\.(.*)</sUi', $content, $matches)) $ret["title"] = trim($matches[1]);
        if (preg_match('/time limit.*: ([0-9\.]*?).*s/sUi', $content, $matches)) $ret["case_time_limit"] = $ret["time_limit"] = intval(floatval(trim($matches[1])) * 1000);
        if (preg_match('/memory limit.*: ([0-9\.]*?).*k/sUi', $content, $matches)) $ret["memory_limit"] = intval(trim($matches[1]));

        $ret["description"] = $content;

        $ret["input"] = $ret["output"] = $ret["sample_in"] = $ret["sample_out"] = $ret["hint"] = $ret["source"] = "";
        $ret["special_judge_status"] = 0;

        $ret = pcrawler_process_info($ret, "sgu", "http://acm.sgu.ru/", false);
        $id = pcrawler_insert_problem($ret, "SGU", $pid);
        return "SGU $pid has been crawled as $id.<br>";
    } else return "No problem called SGU $pid.<br>";
}

function pcrawler_sgu_num()
{
    global $db;
    $i = 1;
    while (true) {
        $html = str_get_html(get_url("http://acm.sgu.ru/problemset.php?contest=0&volume=$i"));
        $table = $html->find("table", 11);
        $rows = $table->find("tr");
        if (sizeof($rows) < 3) break;
        for ($j = 1; $j < sizeof($rows) - 1; $j++) {
            $row = $rows[$j];
            // echo htmlspecialchars($row);
            $pid = $row->find("td", 0)->plaintext;
            $acnum = $row->find("td", 2)->find("a", 0)->innertext;
            $totnum = 0;
            //echo "$pid $acnum $totnum<br>";
            $db->query("update problem set vacnum='$acnum', vtotalnum='$totnum' where vname='SGU' and vid='$pid'");
        }
        $i++;
    }

    return "Done";
}

function pcrawler_lightoj($pid)
{

    global $config;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "http://www.lightoj.com/login_check.php");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_COOKIEJAR, "/tmp/lightoj_crawl.cookie");
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, "myuserid=" . urlencode($config["accounts"]["lightoj"]["username"]) . "&mypassword=" . urlencode($config["accounts"]["lightoj"]["password"]) . "&Submit=Login");
    $content = curl_exec($ch);
    curl_close($ch);

    $url = "http://www.lightoj.com/volume_showproblem.php?problem=$pid";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_COOKIEFILE, "/tmp/lightoj_crawl.cookie");
    $content = curl_exec($ch);
    curl_close($ch);

    $ret = array();

    if (trim($content) == "<script>location.href='volume_problemset.php'</script>") return "No problem called LIGHTOJ $pid.<br>";
    if (stripos($content, "Can not find problem") === false) {
        if (preg_match('/<div id="problem_name">(.*) - (.*)<\/div>/sU', $content, $matches)) $ret["title"] = trim($matches[2]);
        if (preg_match('/Time Limit: <span style="color: #B45F04;">(.*) second/sU', $content, $matches)) $ret["time_limit"] = intval(trim($matches[1])) * 1000;
        $ret["case_time_limit"] = $ret["time_limit"];
        if (preg_match('/Memory Limit: <span style="color: #B45F04;">(.*) MB/sU', $content, $matches)) $ret["memory_limit"] = intval(trim($matches[1])) * 1024;
        if (preg_match('/<div class=Section1>(.*)<h1>Input<\/h1>/sU', $content, $matches)) $ret["description"] = trim($matches[1]);
        if (preg_match('/<h1>Input<\/h1>.*<p class=MsoNormal>(.*)<\/p>/sU', $content, $matches)) $ret["input"] = trim($matches[1]);
        if (preg_match('/<h1>Output<\/h1>.*<p class=MsoNormal>(.*)<\/p>/sU', $content, $matches)) $ret["output"] = trim($matches[1]);
        if (preg_match('/<table class=MsoTableGrid border=1 cellspacing=0 cellpadding=0.*>.*<h1>Sample Input<\/h1>.*<\/table>/sU', $content, $matches)) $ret["sample_in"] = trim($matches[0]);
//        if (preg_match('', $content, $matches)) $ret["sample_out"] = trim($matches[1]);
        $ret["sample_out"] = "";
        if (preg_match('/<div id="problem_setter">.*Problem Setter:(.*)<\/div>/sU', $content, $matches)) $ret["source"] = trim(strip_tags($matches[1]));
        $ret["special_judge_status"] = 0;

        $ret = pcrawler_process_info($ret, "lightoj/$pid", "http://www.lightoj.com/", true, "/tmp/lightoj_crawl.cookie");
        $id = pcrawler_insert_problem($ret, "LightOJ", $pid);
        return "LightOJ $pid has been crawled as $id.<br>";
    } else {
        return "No problem called LightOJ $pid.<br>";
    }
    unlink("/tmp/lightoj_crawl.cookie");
}

function pcrawler_lightoj_num()
{

    global $config, $db;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "http://www.lightoj.com/login_check.php");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_COOKIEJAR, "/tmp/lightoj_num.cookie");
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, "myuserid=" . urlencode($config["accounts"]["lightoj"]["username"]) . "&mypassword=" . urlencode($config["accounts"]["lightoj"]["password"]) . "&Submit=Login");
    $content = curl_exec($ch);
    curl_close($ch);

    $i = 10;
    while (true) {
        $url = "http://www.lightoj.com/volume_problemset.php?volume=$i";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_COOKIEFILE, "/tmp/lightoj_num.cookie");
        $content = curl_exec($ch);
        curl_close($ch);
        if (stripos($content, "<h1>Volume List") !== false) break;
        $html = str_get_html($content);
        $table = $html->find("table", 1);
        if ($table == null) break;
        $rows = $table->find("tr");
        for ($j = 1; $j < sizeof($rows); $j++) {
            $row = $rows[$j];
            //echo htmlspecialchars($row);
            $pid = trim($row->find("td", 1)->plaintext);
            $temp = trim($row->find("td", 4)->find("div.pertext", 0)->innertext);
            $acnum = trim(strstr($temp, "/", true));
            $totnum = trim(substr(strstr($temp, "/"), 1));

            $tempp = trim($row->find("td", 3)->find("div.pertext", 0)->innertext);
            $acpnum = trim(strstr($tempp, "/", true));
            $totpnum = trim(substr(strstr($tempp, "/"), 1));
            //echo "$pid $acnum $totnum<br>";
            $db->query("update problem set vacnum='$acnum', vtotalnum='$totnum', vacpnum='$acpnum', vtotalpnum='$totpnum' where vname='LightOJ' and vid='$pid'");
        }
        $i++;
    }

    unlink("/tmp/lightoj_num.cookie");
    return "Done";
}

use \Michelf\MarkdownExtra;

function pcrawler_uestc($pid)
{
    $url = "http://acm.uestc.edu.cn/problem/data/$pid";
    $data = json_decode(get_url($url), true);

    if ($data['result'] === "error") return "No problem called UESTC $pid.<br>";
    $problem = $data['problem'];
    $ret = array(
        'description' => $problem['description'],
        'input' => $problem['input'],
        'output' => $problem['output'],
        'hint' => $problem['hint']
    );
    foreach ($ret as &$one) {
        $one = MarkdownExtra::defaultTransform($one);
    }
    unset($one);
    $ret = array_merge($ret, array(
        'title' => $problem['title'],
        'time_limit' => $problem['timeLimit'],
        'case_time_limit' => $problem['timeLimit'],
        'memory_limit' => $problem['memoryLimit'],
        'source' => $problem['source'] ? $problem['source'] : "UESTC",
        'special_judge_status' => 0, //TODO(crccw): fetch info about spj
        'vacnum' => $problem['solved'],
        'vtotalnum' => $problem['tried']
    ));
    if (json_decode($problem['sampleInput'])) {
        $ret['sample_in'] = $ret['sample_out'] = "";
        $sample_in = json_decode($problem['sampleInput'], true);
        $sample_out = json_decode($problem['sampleOutput'], true);
        $sample_count = sizeof($sample_in);
        if ($sample_count == 1) {
            $ret['sample_in'] = $sample_in[0];
            $ret['sample_out'] = $sample_out[0];
        } else {
            for ($i = 0; $i < $sample_count; $i++) {
                $ret['sample_in'] .= '<p>Input</p>';
                $ret['sample_in'] .= '<pre>' . $sample_in[$i] . '</pre>';
                $ret['sample_in'] .= '<p>Output</p>';
                $ret['sample_in'] .= '<pre>' . $sample_out[$i] . '</pre>';
            }
        }
    } else {
        $ret['sample_in'] = $problem['sampleInput'];
        $ret['sample_out'] = $problem['sampleOutput'];
    }

    $ret = pcrawler_process_info($ret, "uestc", "http://acm.uestc.edu.cn/");
    $id = pcrawler_insert_problem($ret, "UESTC", $pid);
    return "UESTC $pid has been crawled as $id.<br>";
}

function pcrawler_uestc_num()
{
}

function pcrawler_ural($pid)
{
    $url = "http://acm.timus.ru/problem.aspx?space=1&num=$pid";
    $content = get_url($url);
    $ret = array();

    if (strpos($content, '<DIV STYLE="color:Red; text-align:center;">Problem not found</DIV>') !== false) return "No problem called ural $pid.<br>";
    if (stripos($content, "Can not find problem") === false) {
        if (preg_match('/<H2 class="problem_title">.*\. (.*)<\/H2>/sU', $content, $matches)) $ret["title"] = trim($matches[1]);
        if (preg_match('/<DIV class="problem_limits">Time limit: (.*) second<BR>/sU', $content, $matches)) $ret["time_limit"] = intval(doubleval(trim($matches[1])) * 1000);
        $ret["case_time_limit"] = $ret["time_limit"];
        if (preg_match('/<DIV class="problem_limits">.*<BR>Memory limit: (.*) MB<BR>/sU', $content, $matches)) $ret["memory_limit"] = intval(trim($matches[1])) * 1024;
        if (preg_match('/<DIV ID="problem_text">(.*)<H3 CLASS="problem_subtitle">Input<\/H3>/sU', $content, $matches)) $ret["description"] = trim($matches[1]);
        if (preg_match('/<H3 CLASS="problem_subtitle">Input<\/H3>(.*)<H3 CLASS="problem_subtitle">Output<\/H3>/sU', $content, $matches)) $ret["input"] = trim($matches[1]);
        if (preg_match('/<H3 CLASS="problem_subtitle">Output<\/H3>(.*)<H3 CLASS="problem_subtitle">Sample/sU', $content, $matches)) $ret["output"] = trim($matches[1]);
        if (preg_match('/<TABLE CLASS="sample">.*<\/TABLE>/sU', $content, $matches)) $ret["sample_in"] = trim($matches[0]);
        $ret["sample_out"] = "";
        if (preg_match('/<B>Problem Source: <\/B>(.*)<BR>/sU', $content, $matches)) $ret["source"] = trim(strip_tags($matches[1]));
        $ret["special_judge_status"] = 0;

        $ret = pcrawler_process_info($ret, "ural", "http://acm.ural.edu.cn/");
        $id = pcrawler_insert_problem($ret, "Ural", $pid);
        return "Ural $pid has been crawled as $id.<br>";
    } else {
        return "No problem called Ural $pid.<br>";
    }
}

function pcrawler_ural_num()
{
    global $db;

    $content = str_get_html(get_url("http://acm.timus.ru/problemset.aspx?space=1&page=all"));
    $table = $content->find("table", 4);
    // echo $table;
    $rows = $table->find("tr");
    for ($j = 3; $j < sizeof($rows) - 1; $j++) {
        $i = $rows[$j]->find("td", 1)->plaintext;
        $html = str_get_html(get_url("http://acm.timus.ru/problem.aspx?num=$i"));
        $pid = $i;
        if (preg_match('/All accepted submissions \((\d*)\)/s', $html, $matches)) $acnum = trim($matches[1]);
        if (preg_match('/All submissions \((\d*)\)/s', $html, $matches)) $totnum = trim($matches[1]);
        // echo "$pid $acnum $totnum<br>";
        $db->query("update problem set vacnum='$acnum', vtotalnum='$totnum' where vname='Ural' and vid='$pid'");
        $i++;
    }

    return "Done";
}

function pcrawler_uva_urls()
{
    global $db;
    for ($cate = 1; $cate <= 2; ++$cate) {
        $url = "http://uva.onlinejudge.org/index.php?option=com_onlinejudge&Itemid=8&category=$cate";
        $html = str_get_html(get_url($url));
        $main_a = $html->find("#col3_content_wrapper table a");
        foreach ($main_a as $lone_a) {
            $l2url = $lone_a->href;
            $l2url = "http://uva.onlinejudge.org/" . htmlspecialchars_decode($l2url);
            $html2 = str_get_html(get_url($l2url));
            $rows = $html2->find("#col3_content_wrapper table", 0)->find("tr");
            for ($i = 1; $i < sizeof($rows); $i++) {
                $row = $rows[$i];
                $pid = html_entity_decode(trim($row->find("td", 2)->plaintext));
                $pid = iconv("utf-8", "utf-8//ignore", trim(strstr($pid, '-', true)));
                $pid = substr($pid, 0, -2);
                $url = "http://uva.onlinejudge.org/" . htmlspecialchars_decode($row->find("td", 2)->find("a", 0)->href);
                $db->query("select * from vurl where voj='UVA' and vid='$pid'");
                if ($db->num_rows > 0) $db->query("update vurl set url='$url' where voj='UVA' and vid='$pid'");
                else $db->query("insert into vurl set voj='UVA', vid='$pid', url='$url'");
            }
        }
    }
    return "Updated UVA urls.<br>";
}

function pcrawler_uvalive_urls()
{
    global $db;
    $url = "https://icpcarchive.ecs.baylor.edu/index.php?option=com_onlinejudge&Itemid=8&category=1";
    $html = str_get_html(get_url($url));
    $main_a = $html->find(".maincontent table a");
    foreach ($main_a as $lone_a) {
        $l2url = $lone_a->href;
        $l2url = "https://icpcarchive.ecs.baylor.edu/" . htmlspecialchars_decode($l2url);
        $html2 = str_get_html(get_url($l2url));
        $rows = $html2->find(".maincontent table", 0)->find("tr");
        for ($i = 1; $i < sizeof($rows); $i++) {
            $row = $rows[$i];
            $pid = html_entity_decode(trim($row->find("td", 2)->plaintext));
            $pid = iconv("utf-8", "utf-8//ignore", trim(strstr($pid, '-', true)));
            $pid = substr($pid, 0, -2);
            $url = "https://icpcarchive.ecs.baylor.edu/" . htmlspecialchars_decode($row->find("td", 2)->find("a", 0)->href);
            $db->query("select * from vurl where voj='UVALive' and vid='$pid'");
            if ($db->num_rows > 0) $db->query("update vurl set url='$url' where voj='UVALive' and vid='$pid'");
            else $db->query("insert into vurl set voj='UVALive', vid='$pid', url='$url'");
        }
    }
    return "Updated UVALive urls.<br>";
}

function pcrawler_uva_sources()
{
    global $db;
    $url = "http://uva.onlinejudge.org/index.php?option=com_onlinejudge&Itemid=8";
    $html = str_get_html(get_url($url));
    $main_a = $html->find("#col3_content_wrapper table a");
    $fir = 0;
    $trans = array(" :: " => ", ");
    foreach ($main_a as $lone_a) {
        $l2url = $lone_a->href;
        $fir++;
        if ($fir < 4 || $fir > 6) continue;
        $l2url = "http://uva.onlinejudge.org/" . htmlspecialchars_decode($l2url);
        $html2 = str_get_html(get_url($l2url));
        $l2main_a = $html2->find("#col3_content_wrapper table a");
        foreach ($l2main_a as $ltow_a) {
            $l3url = $ltow_a->href;
            $l3url = "http://uva.onlinejudge.org/" . htmlspecialchars_decode($l3url) . "&limit=2000&limitstart=0";
            $html3 = str_get_html(get_url($l3url));
            $source = $html3->find(".contentheading", 0)->plaintext;
            $source = substr($source, 8);
            $source = trim(strtr($source, $trans));
            $probs = $html3->find("#col3_content_wrapper table a");
            foreach ($probs as $prob) {
                $pid = html_entity_decode(trim($prob->plaintext));
                $pid = iconv("utf-8", "utf-8//ignore", trim(strstr($pid, '-', true)));
                $sql = "update problem set source='$source' where vid='$pid' and vname='UVA'";
                $db->query($sql);
            }
        }
    }
    return "Updated UVA sources.<br>";
}

function pcrawler_uvalive_sources()
{
    global $db;
    $url = "https://icpcarchive.ecs.baylor.edu/index.php?option=com_onlinejudge&Itemid=8";
    $html = str_get_html(get_url($url));
    $main_a = $html->find(".maincontent table a");
    $fir = 1;
    $trans = array(" :: " => ", ");
    foreach ($main_a as $lone_a) {
        $l2url = $lone_a->href;
        if ($fir > 0) {
            $fir--;
            continue;
        }
        $l2url = "https://icpcarchive.ecs.baylor.edu/" . htmlspecialchars_decode($l2url);
        $html2 = str_get_html(get_url($l2url));
        $l2main_a = $html2->find(".maincontent table a");
        foreach ($l2main_a as $ltow_a) {
            $l3url = $ltow_a->href;
            $l3url = "https://icpcarchive.ecs.baylor.edu/" . htmlspecialchars_decode($l3url);
            $html3 = str_get_html(get_url($l3url));
            $source = $html3->find(".contentheading", 0)->plaintext;
            $source = substr($source, 8);
            $source = trim(strtr($source, $trans));
            $probs = $html3->find(".maincontent table a");
            foreach ($probs as $prob) {
                $pid = substr($prob->plaintext, 0, 4);
                $sql = "update problem set source='$source' where vid='$pid' and vname='UVALive'";
                $db->query($sql);
            }
        }
    }
    return "Updated UVALive sources.<br>";
}

function pcrawler_uva($pid)
{
    global $db;
    $ret = array();
    list($url) = $db->get_row("select url from vurl where voj='UVA' and vid='$pid'", ARRAY_N);
    $content = get_url($url);

    if ($url == "") return "No problem called UVA $pid.<br>";
    if (stripos($content, "<h3>") !== false) {
        if (preg_match('/<!-- #col3: Main Content.*?<h3>(.*)<\/h3>/sU', $content, $matches)) $ret["title"] = trim($matches[1]);
        $ret["title"] = trim(substr($ret["title"], strpos($ret["title"], '-') + 1));
        if (preg_match('/Time limit: ([\d\.]*) seconds/sU', $content, $matches)) $ret["time_limit"] = intval(trim($matches[1])) * 1000;
        $ret["case_time_limit"] = $ret["time_limit"];
        $ret["memory_limit"] = 0;
        preg_match('/<iframe src="(external\/([\d]*)\/.*)"/sU', $content, $matches);
        $cate = $matches[2];
        $purl = $matches[1];

        preg_match('/<a href="(external.*)"/sU', $content, $matches);
        $pdflink = $matches[1];

        if ($purl != "") {
            $content = get_url("http://uva.onlinejudge.org/" . $purl);
        } else {
            $content = "";
        }
        $content = iconv("UTF-8", "UTF-8//IGNORE", $content);
        $content = preg_replace('/<head[\s\S]*\/head>/', "", $content);
        $content = preg_replace('/<style[\s\S]*\/style>/', "", $content);

        file_put_contents("/var/www/contest/" . $pdflink, get_url("http://uva.onlinejudge.org/" . $pdflink));
        $ret["description"] = "<p><a href='$pdflink' class='bottom_link'>[PDF Link]</a></p>" . trim($content);

        $ret = pcrawler_process_info($ret, "uva/" . $cate, "http://uva.onlinejudge.org/external/" . $cate . "/");
        $id = pcrawler_insert_problem($ret, "UVA", $pid);
        return "UVA $pid has been crawled as $id.<br>";
    } else {
        return "No problem called UVA $pid.<br>";
    }
}

function pcrawler_uva_num()
{
    global $db;

    for ($cate = 1; $cate <= 2; ++$cate) {
        $url = "http://uva.onlinejudge.org/index.php?option=com_onlinejudge&Itemid=8&category=$cate";
        $html = str_get_html(get_url($url));
        $main_a = $html->find("#col3_content_wrapper table a");
        foreach ($main_a as $lone_a) {
            $l2url = $lone_a->href;
            $l2url = "http://uva.onlinejudge.org/" . htmlspecialchars_decode($l2url);
            $html2 = str_get_html(get_url($l2url));
            $rows = $html2->find("#col3_content_wrapper table", 0)->find("tr");
            for ($i = 1; $i < sizeof($rows); $i++) {
                $row = $rows[$i];
                $pid = html_entity_decode(trim($row->find("td", 1)->plaintext));
                $pid = iconv("utf-8", "utf-8//ignore", trim(strstr($pid, '-', true)));
                $pid = substr($pid, 0, -2);
                $totnum = $row->find("td", 2)->innertext;
                $acnum = $row->find("td", 3)->find("div", 0)->find("div", 1)->innertext;
                $acnum = substr($acnum, 0, -1);
                //echo $acnum;
                if ($acnum[0] == 'N') $acnum = 0;
                else {
                    $acnum = intval(doubleval($acnum) / 100 * intval($totnum) + 0.1);
                }

                $totpnum = $row->find("td", 4)->innertext;
                $acpnum = $row->find("td", 5)->find("div", 0)->find("div", 1)->innertext;
                $acpnum = substr($acpnum, 0, -1);
                //echo $acnum;
                if ($acpnum[0] == 'N') $acpnum = 0;
                else {
                    $acpnum = intval(doubleval($acpnum) / 100 * intval($totpnum) + 0.1);
                }

                // echo "$pid $acnum $totnum<br>";
                $db->query("update problem set vacnum='$acnum', vtotalnum='$totnum', vacpnum='$acpnum', vtotalpnum='$totpnum' where vname='UVA' and vid='$pid'");
            }
            // die();
        }
    }

    return "Done";
}

function pcrawler_uvalive($pid)
{
    global $db;
    $ret = array();
    list($url) = $db->get_row("select url from vurl where voj='UVALive' and vid='$pid'", ARRAY_N);
    $content = get_url($url);

    if ($url == "") return "No problem called UVALive $pid.<br>";
    if (stripos($content, "<h3>") !== false) {
        if (preg_match('/<h3>(.*)<\/h3>/sU', $content, $matches)) $ret["title"] = trim($matches[1]);
        $ret["title"] = trim(substr($ret["title"], strpos($ret["title"], '-') + 1));
        if (preg_match('/Time limit: ([\d\.]*) seconds/sU', $content, $matches)) $ret["time_limit"] = intval(trim($matches[1])) * 1000;
        $ret["case_time_limit"] = $ret["time_limit"];
        $ret["memory_limit"] = 0;
        preg_match('/<iframe src="(external\/([\d]*)\/.*)"/sU', $content, $matches);
        $cate = $matches[2];
        $purl = $matches[1];

        preg_match('/<a href="(external.*)"/sU', $content, $matches);
        $pdflink = $matches[1];

        if ($purl != "") {
            $content = get_url("https://icpcarchive.ecs.baylor.edu/" . $purl);
        } else {
            $content = "";
        }
        $content = iconv("UTF-8", "UTF-8//IGNORE", $content);
        $content = preg_replace('/<head[\s\S]*\/head>/', "", $content);
        $content = preg_replace('/<style[\s\S]*\/style>/', "", $content);

        file_put_contents("/var/www/contest/" . $pdflink, get_url("https://icpcarchive.ecs.baylor.edu/" . $pdflink));
        $ret["description"] = "<p><a href='$pdflink' class='bottom_link'>[PDF Link]</a></p>" . trim($content);

        $ret = pcrawler_process_info($ret, "uvalive/" . $cate, "https://icpcarchive.ecs.baylor.edu/external/" . $cate . "/");
        $id = pcrawler_insert_problem($ret, "UVALive", $pid);
        return "UVALive $pid has been crawled as $id.<br>";
    } else {
        return "No problem called UVALive $pid.<br>";
    }
}

function pcrawler_uvalive_num()
{
    global $db;

    $url = "http://livearchive.onlinejudge.org/index.php?option=com_onlinejudge&Itemid=8&category=1";
    $html = str_get_html(get_url($url));
    $main_a = $html->find(".maincontent table a");
    foreach ($main_a as $lone_a) {
        $l2url = $lone_a->href;
        $l2url = "http://livearchive.onlinejudge.org/" . htmlspecialchars_decode($l2url);
        $html2 = str_get_html(get_url($l2url));
        $rows = $html2->find(".maincontent table", 0)->find("tr");
        for ($i = 1; $i < sizeof($rows); $i++) {
            $row = $rows[$i];
            $pid = html_entity_decode(trim($row->find("td", 1)->plaintext));
            $pid = iconv("utf-8", "utf-8//ignore", trim(strstr($pid, '-', true)));
            $pid = substr($pid, 0, -2);
            $totnum = $row->find("td", 2)->innertext;
            $acnum = $row->find("td", 3)->find("div", 0)->find("div", 1)->innertext;
            $acnum = substr($acnum, 0, -1);
            //echo $acnum;
            if ($acnum[0] == 'N') $acnum = 0;
            else {
                $acnum = intval(doubleval($acnum) / 100 * intval($totnum) + 0.1);
            }

            $totpnum = $row->find("td", 4)->innertext;
            $acpnum = $row->find("td", 5)->find("div", 0)->find("div", 1)->innertext;
            $acpnum = substr($acpnum, 0, -1);
            //echo $acnum;
            if ($acpnum[0] == 'N') $acpnum = 0;
            else {
                $acpnum = intval(doubleval($acpnum) / 100 * intval($totpnum) + 0.1);
            }

            // echo "$pid $acnum $totnum<br>";
            $db->query("update problem set vacnum='$acnum', vtotalnum='$totnum', vacpnum='$acpnum', vtotalpnum='$totpnum' where vname='UVALive' and vid='$pid'");
        }
        // die();
    }

    return "Done";
}

function pcrawler_spoj($pid)
{
    $url = "http://www.spoj.com/problems/$pid/";
    $content = get_url($url);
    $ret = array();
    if (stripos($content, "Wrong problem code!") === false) {
        if (preg_match('/<h2 id="problem-name".* - (.*)<\/h2>/sU', $content, $matches)) $ret["title"] = trim($matches[1]);
        if (preg_match('/<td>Time limit:<\/td><td>(\d*)s/sU', $content, $matches)) $ret["case_time_limit"] = $ret["time_limit"] = intval(trim($matches[1])) * 1000;
        if (preg_match('/<td>Memory limit:<\/td><td>(\d*)MBs/sU', $content, $matches)) $ret["memory_limit"] = intval(trim($matches[1])) * 1024;

        if (preg_match('/<div id="problem-body">(.*)<h3>/sU', $content, $matches)) $ret["description"] = trim($matches[1]);
        if (preg_match('/<h3>Input<\/h3>(.*)<h3>/sU', $content, $matches)) $ret["input"] = trim($matches[1]);
        if (preg_match('/<h3>Output<\/h3>(.*)<h3>/sU', $content, $matches)) $ret["output"] = trim($matches[1]);
        if (preg_match('/<h3>Example<\/h3>(.*<\/(?:pre|PRE)>)/sU', $content, $matches)) $ret["sample_in"] = trim($matches[1]);

        $ret["sample_out"] = $ret["hint"] = $ret["source"] = "";
        $ret["special_judge_status"] = 0;

        $ret = pcrawler_process_info($ret, "spoj", "http://www.spoj.com/", false);
        $id = pcrawler_insert_problem($ret, "SPOJ", $pid);
        return "SPOJ $pid has been crawled as $id.<br>";
    } else return "No problem called SPOJ $pid.<br>";
}

function pcrawler_spoj_num()
{
    global $db;
    $used = array();
    foreach (array("tutorial", "classical") as $typec) {
        $i = 0;
        $pd = true;
        while ($pd) {
            $html = str_get_html(get_url("http://www.spoj.pl/problems/$typec/sort=0,start=" . ($i * 50)));
            if ($html == null) break;
            $table = $html->find("table.problems", 0);
            if ($table == null) break;
            $rows = $table->find("tr");
            for ($j = 1; $j < sizeof($rows); $j++) {
                $row = $rows[$j];
                $pid = trim($row->find("td", 2)->plaintext);
                if (isset($used[$pid])) {
                    $pd = false;
                    break;
                }
                $used[$pid] = true;

                $phtml = str_get_html(get_url("http://www.spoj.pl/ranks/$pid/"));
                if ($phtml == null) continue;
                $ptable = $phtml->find("table.problems", 0);
                if ($ptable == null) break;
                $acnum = $ptable->find("tr.lightrow td", 2)->plaintext;
                $totnum = $ptable->find("tr.lightrow td", 1)->plaintext;
                $acpnum = $ptable->find("tr.lightrow td", 0)->plaintext;

                $db->query("update problem set vacnum='$acnum', vtotalnum='$totnum', vacpnum='$acpnum' where vname='SPOJ' and vid='$pid'");
            }
            $i++;
        }
    }
    return "Done";
}

function pcrawler_zju($pid)
{
    $url = "http://acm.zju.edu.cn/onlinejudge/showProblem.do?problemCode=$pid";
    $content = get_url($url);
    $ret = array();
    if (stripos($content, "<div id=\"content_title\">Message</div>") === false) {
        if (preg_match('/<span class="bigProblemTitle">(.*)<\/span>/sU', $content, $matches)) $ret["title"] = trim($matches[1]);
        if (preg_match('/Time Limit: <\/font> (\d*) Sec/sU', $content, $matches)) $ret["case_time_limit"] = $ret["time_limit"] = intval(trim($matches[1])) * 1000;
        if (preg_match('/Memory Limit: <\/font> (\d*) KB/sU', $content, $matches)) $ret["memory_limit"] = intval(trim($matches[1]));
        if (preg_match('/<hr>.*<hr>(.*?)<hr>.*<\/table>/sU', $content, $matches)) $ret["description"] = trim($matches[1]);
        if (preg_match('/(Source|Contest): <strong>(.*)<\/strong>/sU', $content, $matches)) $ret["source"] = html_entity_decode(trim(strip_tags($matches[2])), ENT_QUOTES);
        if (preg_match('/Author: <strong>(.*)<\/strong>/sU', $content, $matches)) $ret["author"] = html_entity_decode(trim(strip_tags($matches[1])), ENT_QUOTES);

        if (stripos($content, "<font color=\"blue\">Special Judge</font>", 0) !== false) $ret["special_judge_status"] = 1;
        else $ret["special_judge_status"] = 0;

        $ret["input"] = $ret["output"] = $ret["sample_in"] = $ret["sample_out"] = $ret["hint"] = "";

        $ret = pcrawler_process_info($ret, "zju", "http://acm.zju.edu.cn/onlinejudge/", false);
        $id = pcrawler_insert_problem($ret, "ZJU", $pid);
        return "ZJU $pid has been crawled as $id.<br>";
    } else return "No problem called ZJU $pid.<br>";
}

function pcrawler_zju_num()
{
    global $db;
    $got = array();
    $i = 1;
    while (true) {
        $html = str_get_html(get_url("http://acm.zju.edu.cn/onlinejudge/showProblems.do?contestId=1&pageNumber=$i"));
        $table = $html->find("table.list", 0);
        $rows = $table->find("tr");
        if (isset($got[$rows[1]->find("td", 0)->plaintext])) break;
        for ($j = 1; $j < sizeof($rows); $j++) {
            $row = $rows[$j];
            //echo htmlspecialchars($row);
            $pid = $row->find("td", 0)->plaintext;
            $got[$pid] = true;
            $acnum = $row->find("td", 2)->find("a", 0)->innertext;
            $totnum = $row->find("td", 2)->find("a", 1)->innertext;
            //echo "$pid $acnum $totnum<br>";
            $db->query("update problem set vacnum='$acnum', vtotalnum='$totnum' where vname='ZJU' and vid='$pid'");
        }
        $i++;
    }
    return "Done";
}

function pcrawler_nbut($pid)
{
    $url = "https://ac.2333.moe/Problem/view.xhtml?id=$pid";
    $content = get_url($url);
    $ret = array();
    if (stripos($content, "<h3>[] </h3>") === false) {
        if (preg_match('/<li id="title"><h3>\[.*\] (.*)<\/h3>/sU', $content, $matches)) $ret["title"] = trim($matches[1]);
        if (preg_match('/<li id="limit">.*时间限制: (\d*) ms/sU', $content, $matches)) $ret["case_time_limit"] = $ret["time_limit"] = intval(trim($matches[1]));
        if (preg_match('/<li id="limit">.*内存限制: (\d*) K/sU', $content, $matches)) $ret["memory_limit"] = intval(trim($matches[1]));
        if (preg_match('/<li class="contents" id="description">(.*?)<\/li>.*<li class="titles" id="input-title">/sU', $content, $matches)) $ret["description"] = trim($matches[1]);
        if (preg_match('/<li class="contents" id="input">(.*?)<\/li>.*<li class="titles" id="output-title">/sU', $content, $matches)) $ret["input"] = trim($matches[1]);
        if (preg_match('/<li class="contents" id="output">(.*?)<\/li>.*<li class="titles" id="sampleinput-title">/sU', $content, $matches)) $ret["output"] = trim($matches[1]);
        if (preg_match('/<li class="contents" id="sampleinput">.*<pre>(.*)<\/pre>/sU', $content, $matches)) $ret["sample_in"] = trim($matches[1]);
        if (preg_match('/<li class="contents" id="sampleoutput">.*<pre>(.*)<\/pre>/sU', $content, $matches)) $ret["sample_out"] = trim($matches[1]);
        if (preg_match('/<li class="contents" id="hint">.*<pre>(.*)<\/pre>/sU', $content, $matches)) $ret["hint"] = trim($matches[1]);
        if ($ret["hint"] == "无") $ret["hint"] = "";
        if (preg_match('/<li class="contents" id="source">.*<pre>(.*)<\/pre>/sU', $content, $matches)) $ret["source"] = trim(strip_tags($matches[1]));
        if ($ret["source"] == "本站或者转载") $ret["source"] = "";

        $ret["special_judge_status"] = 0;

        $ret = pcrawler_process_info($ret, "nbut", "https://ac.2333.moe/Problem/", false);
        $id = pcrawler_insert_problem($ret, "NBUT", $pid);
        return "NBUT $pid has been crawled as $id.<br>";
    } else return "No problem called NBUT $pid.<br>";
}

function pcrawler_nbut_num()
{
    global $db;
    $got = array();
    $i = 1;
    while (true) {
        $html = str_get_html(get_url("https://ac.2333.moe/Problem.xhtml?page=$i"));
        //echo $html;
        $table = $html->find("table tbody", 0);
        $rows = $table->find("tr");
        if (isset($got[$rows[0]->find("td", 1)->plaintext])) break;
        for ($j = 0; $j < sizeof($rows); $j++) {
            $row = $rows[$j];
            $pid = $row->find("td", 1)->plaintext;
            $got[$pid] = true;
            $tstr = $row->find("td", 3)->plaintext;
            $acnum = trim(strstr($tstr, '/', true));
            $totnum = trim(substr(strstr(strstr($tstr, '(', true), '/'), 1));
            //        echo "$pid $acnum $totnum<br>";die();
            $db->query("update problem set vacnum='$acnum', vtotalnum='$totnum' where vname='NBUT' and vid='$pid'");
        }
        $i++;
    }
    return "Done";
}

function pcrawler_whu($pid)
{
    $url = "http://acm.whu.edu.cn/land/problem/detail?problem_id=$pid";
    $content = get_url($url);
    $ret = array();
    // If file_get_contents is replaced by curl, finding <div id="tt">Ooooops!</div> could be a fallback to dertermine whether the problem exists.
    if ($content !== false) {
        if (preg_match('/<div id="tt">.*- (.*)? <\/div>/sU', $content, $matches)) $ret["title"] = trim($matches[1]);
        if (preg_match('/<strong>Time Limit<\/strong>: ([0-9]*)MS/sU', $content, $matches)) $ret["case_time_limit"] = $ret["time_limit"] = intval(trim($matches[1]));
        if (preg_match('/<strong>Memory Limit<\/strong>: ([0-9]*)KB/sU', $content, $matches)) $ret["memory_limit"] = intval(trim($matches[1]));
        if (preg_match('/<div class="ptt">Description<\/div>(.*)<div class="ptt">Input<\/div>/sU', $content, $matches)) $ret["description"] = trim($matches[1]);
        if (preg_match('/<div class="ptt">Input<\/div>(.*)<div class="ptt">Output<\/div>/sU', $content, $matches)) $ret["input"] = trim($matches[1]);
        if (preg_match('/<div class="ptt">Output<\/div>(.*)<div class="ptt">Sample Input<\/div>/sU', $content, $matches)) $ret["output"] = trim($matches[1]);
        if (preg_match('/<div class="ptt">Sample Input<\/div>(.*)<div class="ptt">Sample Output<\/div>/sU', $content, $matches)) $ret["sample_in"] = trim($matches[1]);
        if (preg_match('/<div class="ptt">Sample Output<\/div>(.*)<div class="ptt">Hint<\/div>/sU', $content, $matches)) $ret["sample_out"] = trim($matches[1]);
        if (preg_match('/<div class="ptt">Hint<\/div>(.*)<div class="ptt">Source<\/div>/sU', $content, $matches)) $ret["hint"] = trim($matches[1]);
        if (preg_match('/<div class="ptt">Source<\/div>(.*)<br \/>/sU', $content, $matches)) $ret["source"] = trim(strip_tags($matches[1]));

        if (stripos($content, "<strong>Special Judge</strong>: Yes", 0) !== false) $ret["special_judge_status"] = 1;
        else $ret["special_judge_status"] = 0;

        $ret = pcrawler_process_info($ret, "whu", "http://acm.whu.edu.cn/land/problem/", false);
        $id = pcrawler_insert_problem($ret, "WHU", $pid);
        return "WHU $pid has been crawled as $id.<br>";
    } else return "No problem called WHU $pid.<br>";
}

function pcrawler_whu_num()
{
    global $db;
    $i = 1;
    while (true) {
        $html = get_url("http://acm.whu.edu.cn/land/problem/list?volume=$i");
        $chr = "problem_data = ";
        $pos1 = stripos($html, $chr) + strlen($chr);
        $pos2 = stripos($html, "var is_admin", $pos1);
        $html = substr(trim(substr($html, $pos1, $pos2 - $pos1)), 0, -1);
        //echo $html;die();
        $html = json_decode($html);
        if (sizeof($html) < 1) break;
        foreach ($html as $row) {
            $pid = $row->problem_id;
            $acnum = $row->accepted;
            $totnum = $row->submitted;
            //echo "$pid $acnum $totnum<br>";
            $db->query("update problem set vacnum='$acnum', vtotalnum='$totnum' where vname='WHU' and vid='$pid'");
        }
        $i++;
    }
    return "Done";
}

function pcrawler_njupt($pid)
{
    $url = "http://acm.njupt.edu.cn/acmhome/problemdetail.do?&method=showdetail&id=$pid";
    $content = get_url($url);
    $content = iconv("gbk", "utf-8//ignore", $content);
    $ret = array();
    if (stripos($content, "doesn't exit or has been deleted.</LI></UL>") === false &&
        stripos($content, "<strong>500</strong> <span class=\"style1\">System Error.Please Wait...</span>") === false
    ) {
        if (preg_match("/<h2.*>\s*<strong>(.*)<\/strong>/sU", $content, $matches)) $ret["title"] = trim($matches[1]);
        if (preg_match('/<strong>时间限制.*<strong>(\d*)\s*MS/sU', $content, $matches)) $ret["case_time_limit"] = $ret["time_limit"] = intval(trim($matches[1]));
        if (preg_match('/<strong>运行内存限制\s*:\s*(\d*)\s*KB/sU', $content, $matches)) $ret["memory_limit"] = intval(trim($matches[1]));
        if (preg_match('/<b crawl="description"><\/b>(.*)<b\s*crawl="description">/sU', $content, $matches)) $ret["description"] = trim($matches[1]);
        if (preg_match('/<b crawl="input"><\/b>(.*)<b\s*crawl="input">/sU', $content, $matches)) $ret["input"] = trim($matches[1]);
        if (preg_match('/<b crawl="output"><\/b>(.*)<b\s*crawl="output">/sU', $content, $matches)) $ret["output"] = trim($matches[1]);
        if (preg_match('/class="pst">\s*样例输入.*<div class="textBG">(.*)<\/div>/sU', $content, $matches)) $ret["sample_in"] = trim($matches[1]);
        if (preg_match('/class="pst">\s*样例输出.*<div class="textBG">(.*)<\/div>/sU', $content, $matches)) $ret["sample_out"] = trim($matches[1]);
        if (preg_match('/<b crawl="hint"><\/b>(.*)<b\s*crawl="hint">/sU', $content, $matches)) $ret["hint"] = trim($matches[1]);
        if (preg_match('/<b crawl="source"><\/b>(.*)<b\s*crawl="source">/sU', $content, $matches)) $ret["source"] = trim(strip_tags($matches[1]));

        $ret["special_judge_status"] = 0;

        $ret = pcrawler_process_info($ret, "njupt", "http://acm.njupt.edu.cn/acmhome/", false);
        $id = pcrawler_insert_problem($ret, "NJUPT", $pid);
        return "NJUPT $pid has been crawled as $id.<br>";
    } else return "No problem called NJUPT $pid.<br>";
}

function pcrawler_njupt_num()
{
    global $db;
    $i = 1;
    while (true) {
        $html = str_get_html(get_url("http://acm.njupt.edu.cn/acmhome/problemList.do?method=show&page=$i"));
        $table = $html->find("table", 1);
        $rows = $table->find("tr");
        if (sizeof($rows) < 2) break;
        for ($j = 1; $j < sizeof($rows); $j++) {
            $row = $rows[$j];
            // echo htmlspecialchars($row);
            $pid = $row->find("td", 0)->plaintext;
            $acnum = $row->find("td", 2)->find("a", 0)->plaintext;
            $totnum = $row->find("td", 2)->find("a", 1)->plaintext;
            // echo "$pid $acnum $totnum<br>";
            $db->query("update problem set vacnum='$acnum', vtotalnum='$totnum' where vname='NJUPT' and vid='$pid'");
        }
        $i++;
    }

    return "Done";
}

function pcrawler_aizu($pid)
{
    $url = "http://judge.u-aizu.ac.jp/onlinejudge/description.jsp?id=$pid";
    $content = get_url($url);
    $content = iconv("SHIFT_JIS", "UTF-8//IGNORE", $content);
    $ret = array();
    if (stripos($content, "<h1 class=\"title\">") !== false) {
        if (preg_match('/<h1 class="title">(.*)<\/h1>/sU', $content, $matches)) $ret["title"] = trim($matches[1]);
        if (preg_match('/Time Limit : (\d*) sec/sU', $content, $matches)) $ret["case_time_limit"] = $ret["time_limit"] = intval(trim($matches[1])) * 1000;
        if (preg_match('/Memory Limit : (\d*) KB/sU', $content, $matches)) $ret["memory_limit"] = intval(trim($matches[1]));
        if (preg_match('/<div class="description">(.*)<hr>/sU', $content, $matches)) $ret["description"] = trim($matches[1]);
        $ret["description"] = str_replace("./IMAGE/varmath.js", "js/varmath.js", $ret["description"]);
        if (preg_match('/<div class="subinfo">.*<div class="dat">(.*)<\/div>/sU', $content, $matches)) $ret["source"] = html_entity_decode(trim(strip_tags($matches[1])), ENT_QUOTES);

        $ret["special_judge_status"] = 0;
        $ret["input"] = $ret["output"] = $ret["sample_in"] = $ret["sample_out"] = $ret["hint"] = $ret["author"] = "";

        $ret = pcrawler_process_info($ret, "aizu", "http://judge.u-aizu.ac.jp/onlinejudge/", false);
        $id = pcrawler_insert_problem($ret, "Aizu", $pid);
        return "Aizu $pid has been crawled as $id.<br>";
    } else return "No problem called Aizu $pid.<br>";
}

function pcrawler_aizu_num()
{
    global $db;
    for ($i = 0; $i <= 100; ++$i) {
        $html = str_get_html(get_url("http://judge.u-aizu.ac.jp/onlinejudge/finder.jsp?volumeNo=$i"));
        $table = $html->find("table", 0);
        $rows = $table->find("tr");
        for ($j = 1; $j < sizeof($rows); $j++) {
            $row = $rows[$j];
            // echo htmlspecialchars($row);
            preg_match('/<td class="text-left">#(\d*)<.*<!--<td>(\d*)\/(\d*)<.*> x (\d*)<\/a>/sU', $row, $matches);
            $pid = $matches[1];
            $acnum = $matches[2];
            $totnum = $matches[3];
            $acpnum = $matches[4];
            // echo "$pid $acnum $totnum $acpnum<br>";
            $db->query("update problem set vacnum='$acnum', vtotalnum='$totnum', acpnum='$acpnum' where vname='Aizu' and vid='$pid'");
        }
    }

    return "Done";
}

function pcrawler_acdream($pid)
{
    $url = "http://acdream.info/problem?pid=$pid";
    $content = get_url($url);
    $ret = array();
    if (stripos($content, "<h1 align=\"center\">The Problem is not Available!!</h1>") === false) {
        if (preg_match('/<h3 class="problem-header">(.*)<\/h3>/sU', $content, $matches)) $ret["title"] = trim($matches[1]);
        if (preg_match('/Time Limit:&nbsp;.*\/(\d*)MS/sU', $content, $matches)) $ret["case_time_limit"] = $ret["time_limit"] = intval(trim($matches[1]));
        if (preg_match('/Memory Limit:&nbsp;.*\/(\d*)KB/sU', $content, $matches)) $ret["memory_limit"] = intval(trim($matches[1]));
        if (preg_match('/<h4>Problem Description<\/h4><div class="prob-content">(.*)<\/div><h4>/sU', $content, $matches)) $ret["description"] = trim($matches[1]);
        if (preg_match('/<h4>Input<\/h4><div class="prob-content">(.*)<\/div><h4>/sU', $content, $matches)) $ret["input"] = trim($matches[1]);
        if (preg_match('/<h4>Output<\/h4><div class="prob-content">(.*)<\/div><h4>/sU', $content, $matches)) $ret["output"] = trim($matches[1]);
        if (preg_match('/<h4>Sample Input<\/h4><div class="prob-content"><pre.*>(.*)<\/pre><\/div><h4>/sU', $content, $matches)) $ret["sample_in"] = trim($matches[1]);
        if (preg_match('/<h4>Sample Output<\/h4><div class="prob-content"><pre.*>(.*)<\/pre><\/div><h4>/sU', $content, $matches)) $ret["sample_out"] = trim($matches[1]);
        if (preg_match('/<h4>Hint<\/h4><div class="prob-content">(.*)<\/div><h4>/sU', $content, $matches)) $ret["hint"] = trim($matches[1]);
        if (preg_match('/<h4>Source<\/h4><div class="prob-content">(.*)<\/div>/sU', $content, $matches)) $ret["source"] = trim(strip_tags($matches[1]));

        if (stripos($content, "class=\"user user-red\">Special Judge</span>", 0) !== false) $ret["special_judge_status"] = 1;
        else $ret["special_judge_status"] = 0;

        $ret = pcrawler_process_info($ret, "acdream", "http://acdream.info/", false);
        $id = pcrawler_insert_problem($ret, "ACdream", $pid);
        error_log(json_encode($ret));
        return "ACdream $pid has been crawled as $id.<br>";
    } else return "No problem called ACdream $pid.<br>";
}

function pcrawler_acdream_num()
{
    global $db;
    $got = array();
    $i = 1;
    while (true) {
        $html = str_get_html(get_url("http://acdream.info/problem/list?page=$i"));
        $table = $html->find("table", 0);
        $rows = $table->find("tr");
        if ($rows[1]->find("td", 0)->innertext === "No Problems are matched.") break;
        for ($j = 1; $j < sizeof($rows) - 1; $j++) {
            $row = $rows[$j];
            //echo htmlspecialchars($row);
            $pid = $row->find("td", 0)->plaintext;
            $got[$pid] = true;
            $acnum = $row->find("td", 4)->find("a", 0)->innertext;
            $totnum = $row->find("td", 4)->find("a", 1)->innertext;
            // echo "$pid $acnum $totnum<br>";
            $db->query("update problem set vacnum='$acnum', vtotalnum='$totnum' where vname='ACdream' and vid='$pid'");
        }
        $i++;
    }
    return "Done";
}

function pcrawler_codechef($pid)
{
    $url = "http://www.codechef.com/api/contests/PRACTICE/problems/$pid";
    $data = json_decode(get_url($url), true);
    if ($data['status'] !== 'success') return $data['message'];
    $ret = array();
    $ret['title'] = $data['problem_name'];
    $ret['time_limit'] = $ret['case_time_limit'] = intval($data['max_timelimit']) * 1000;
    $ret['memory_limit'] = 0;
    $ret['special_judge_status'] = 0;
    $ret['author'] = $data['problem_author'];
    if (preg_match('/<\/h3>\n?(<p>.*)(?:<h3>Input|$)/sU', $data['body'], $matches)) $ret['description'] = $matches[1];
    if (preg_match('/<h3>Input<\/h3>(.*)(?:<h3>|$)/sU', $data['body'], $matches)) $ret['input'] = $matches[1];
    if (preg_match('/<h3>Output<\/h3>(.*)(?:<h3>|$)/sU', $data['body'], $matches)) $ret['output'] = $matches[1];
    if (preg_match('/<h3>Constraints<\/h3>(.*)(?:<h3>|$)/sU', $data['body'], $matches)) $ret['input'] .= $matches[1];
    if (preg_match('/<h3>Sub tasks<\/h3>(.*)(?:<h3>|$)/sU', $data['body'], $matches)) $ret['input'] .= $matches[1];
    if (preg_match('/<h3>Example<\/h3>(.*)(?:<h3>|$)/sU', $data['body'], $matches)) $ret['sample_in'] = $matches[1];
    if (preg_match('/<h3>Explanation<\/h3>(.*)(?:<h3>|$)/sU', $data['body'], $matches)) $ret['hint'] = $matches[1];
    $ret["sample_out"] = $ret["source"] = "";
    $ret = pcrawler_process_info($ret, "codechef", "http://www.codechef.com/problems/", false);
    $id = pcrawler_insert_problem($ret, "CodeChef", $pid);
    return "CodeChef $pid has been crawled as $id.<br>";
}

function pcrawler_codechef_num()
{
    global $db;
    foreach (array("easy", "medium", "hard", "challenge", "extcontest", "school") as $typec) {
        $html = str_get_html(get_url("http://www.codechef.com/problems/$typec/"));
        if ($html == null) break;
        $table = $html->find("table.problems", 0);
        if ($table == null) break;
        $rows = $table->find("tr");
        for ($j = 1; $j < sizeof($rows); $j++) {
            $row = $rows[$j];
            $pid = trim($row->find("td", 1)->plaintext);
            $acpnum = trim($row->find("td", 2)->plaintext);
            if ($acpnum != "0") {
                $totpnum = intval(intval($acpnum) / (doubleval(trim($row->find("td", 3)->plaintext)) / 100));
            } else {
                $totpnum = 0;
            }
            // echo "$pid $acpnum $totpnum<br>";
            $db->query("update problem set vtotalpnum='$totpnum', vacpnum='$acpnum' where vname='CodeChef' and vid='$pid'");
        }
    }
    return "Done";
}

function pcrawler_codechef_sources()
{
    global $db;
    $url = "http://www.codechef.com/contests";
    $html = str_get_html(get_url($url));
    $main_a = $html->find("table", 3)->find("a");
    $fir = 4;
    foreach ($main_a as $lone_a) {
        if ($fir > 0) {
            $fir--;
            continue;
        }
        $source = $lone_a->plaintext;
        $l2url = $lone_a->href;
        $l2url = "https://www.codechef.com" . htmlspecialchars_decode($l2url);
        $html2 = str_get_html(get_url($l2url));
        $rows = $html2->find("tr.problemrow");
        foreach ($rows as $row) {
            $pid = trim($row->find("td", 1)->plaintext);
            $sql = "update problem set source='$source' where vid='$pid' and vname='CodeChef'";
            $db->query($sql);
        }
    }
    return "Updated CodeChef sources.<br>";
}

function pcrawler_hrbust($pid)
{
    $url = "http://acm.hrbust.edu.cn/index.php?m=ProblemSet&a=showProblem&problem_id=$pid";
    $content = get_url($url);
    $ret = array();

    if (stripos($content, "<td class=\"problem_mod_title\">") !== false) {
        if (preg_match('/<td class="problem_mod_name">(.*)<\/td>/sU', $content, $matches)) $ret["title"] = trim($matches[1]);
        if (preg_match('/>Time Limit: (\d*) MS</sU', $content, $matches)) $ret["time_limit"] = $ret["case_time_limit"] = intval(trim($matches[1]));
        if (preg_match('/>Memory Limit: (\d*) K</sU', $content, $matches)) $ret["memory_limit"] = intval(trim($matches[1]));
        if (preg_match('/<td class="problem_mod_title">Description<\/td>.*<td class="problem_mod_content">(.*)<\/td><\/tr>(<tr><td class="problem_mod_title">|<\/table>)/sU', $content, $matches)) $ret["description"] = trim($matches[1]);
        if (preg_match('/<td class="problem_mod_title">Input<\/td>.*<td class="problem_mod_content">(.*)<\/td><\/tr>(<tr><td class="problem_mod_title">|<\/table>)/sU', $content, $matches)) $ret["input"] = trim($matches[1]);
        if (preg_match('/<td class="problem_mod_title">Output<\/td>.*<td class="problem_mod_content">(.*)<\/td><\/tr>(<tr><td class="problem_mod_title">|<\/table>)/sU', $content, $matches)) $ret["output"] = trim($matches[1]);
        if (preg_match('/<td class="problem_mod_title">Sample Input<\/td>.*<td class="problem_mod_content">(.*)<\/td><\/tr>(<tr><td class="problem_mod_title">|<\/table>)/sU', $content, $matches)) $ret["sample_in"] = trim($matches[1]);
        if (preg_match('/<td class="problem_mod_title">Sample Output<\/td>.*<td class="problem_mod_content">(.*)<\/td><\/tr>(<tr><td class="problem_mod_title">|<\/table>)/sU', $content, $matches)) $ret["sample_out"] = trim($matches[1]);
        if (preg_match('/<td class="problem_mod_title">Hint<\/td>.*<td class="problem_mod_content">(.*)<\/td><\/tr>(<tr><td class="problem_mod_title">|<\/table>)/sU', $content, $matches)) $ret["hint"] = trim($matches[1]);
        if (preg_match('/<td class="problem_mod_title">Source<\/td>.*<td class="problem_mod_content">(.*)<\/td><\/tr>(<tr><td class="problem_mod_title">|<\/table>)/sU', $content, $matches)) $ret["source"] = trim(strip_tags($matches[1]));
        if (preg_match('/<td class="problem_mod_title">Author<\/td>.*<td class="problem_mod_content">(.*)<\/td><\/tr>(<tr><td class="problem_mod_title">|<\/table>)/sU', $content, $matches)) $ret["author"] = trim(strip_tags($matches[1]));

        if (strpos($content, 'Special Judge: <span class="problem_mod_info_sj_yes">Yes</span>') !== false) $ret["special_judge_status"] = 1;
        else $ret["special_judge_status"] = 0;

        $ret = pcrawler_process_info($ret, "hrbust", "http://acm.hrbust.edu.cn/");
        $id = pcrawler_insert_problem($ret, "HRBUST", $pid);
        return "HRBUST $pid has been crawled as $id.<br>";
    } else {
        return "No problem called HRBUST $pid.<br>";
    }
}

function pcrawler_hrbust_num()
{
    global $db;

    $i = 1;
    while (true) {
        $html = str_get_html(get_url("http://acm.hrbust.edu.cn/index.php?m=ProblemSet&a=showProblemVolume&vol=$i"));
        $table = $html->find("table.ojlist", 0);
        $rows = $table->find("tr");
        if (sizeof($rows) < 2) break;
        for ($j = 1; $j < sizeof($rows); $j++) {
            $row = $rows[$j];
            //echo htmlspecialchars($row);
            $pid = $row->find("td", 1)->plaintext;
            $acnum = $row->find("td", 4)->find("a", 0)->innertext;
            $totnum = $row->find("td", 4)->find("a", 1)->innertext;
            $tmp = $row->find("td", 5)->plaintext;
            preg_match('/\((\d*)\/(\d*)/', $tmp, $matches);
            $acpnum = $matches[1];
            $totpnum = $matches[2];
            $db->query("update problem set vacnum='$acnum', vtotalnum='$totnum', vacpnum='$acpnum', vtotalpnum='$totpnum' where vname='HRBUST' and vid='$pid'");
        }
        $i++;
    }

    return "Done";
}

?>
