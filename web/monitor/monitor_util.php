<?php
include_once(dirname(__FILE__) . "/../functions/global.php");
include_once(dirname(__FILE__) . "/../functions/simple_html_dom.php");
include_once(dirname(__FILE__) . "/../functions/problems.php");
include_once(dirname(__FILE__) . "/../functions/pcrawlers.php");

$timeoutopts = stream_context_create(array('http' =>
    array(
        'timeout' => 30
    )
));

function monitor_insert_url($oj, $id, $url)
{
    global $db;
    $db->query("select * from vurl where voj='$oj' and vid='$id'");
    if ($db->num_rows) $db->query("update vurl set url='" . $db->escape($url) . "' where voj='$oj' and vid='$id'");
    else $db->query("insert into vurl set url='" . $db->escape($url) . "', voj='$oj', vid='$id'");
}

function monitor_uva()
{
    global $timeoutopts;
    for ($i = 1; $i < 3; $i++) {
        $url = "http://uva.onlinejudge.org/index.php?option=com_onlinejudge&Itemid=8&category=$i";
        $html = str_get_html(get_url($url, false, $timeoutopts));
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
                $url = "http://uva.onlinejudge.org/" . htmlspecialchars_decode($row->find("td", 1)->find("a", 0)->href);
                monitor_insert_url("UVA", $pid, $url);
                if (trim($pid) == "" || problem_get_id_from_virtual("UVA", $pid)) continue;
                echo "UVA $pid\n";
                pcrawler_uva($pid);
            }
        }
    }
    pcrawler_uva_num();
    pcrawler_uva_sources();
}

function monitor_uvalive()
{
    global $timeoutopts;
    $url = "http://livearchive.onlinejudge.org/index.php?option=com_onlinejudge&Itemid=8&category=1";
    $html = str_get_html(get_url($url, false, $timeoutopts));
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
            $url = "https://icpcarchive.ecs.baylor.edu/" . htmlspecialchars_decode($row->find("td", 1)->find("a", 0)->href);
            monitor_insert_url("UVALive", $pid, $url);
            if (trim($pid) == "" || problem_get_id_from_virtual("UVALive", $pid)) continue;
            echo "UVALive $pid\n";
            pcrawler_uvalive($pid);
        }
    }
    pcrawler_uvalive_num();
    pcrawler_uvalive_sources();
}

function monitor_spoj()
{
    global $timeoutopts;
    $used = array();
    foreach (array("tutorial", "classical") as $typec) {
        $i = 0;
        $pd = true;
        while ($pd) {
            $html = str_get_html(get_url("http://www.spoj.pl/problems/$typec/sort=0,start=" . ($i * 50), false, $timeoutopts));
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
                if (trim($pid) == "" || problem_get_id_from_virtual("SPOJ", $pid)) continue;
                echo "SPOJ $pid\n";
                pcrawler_spoj($pid);
            }
            $i++;
        }
    }
    // pcrawler_spoj_num();
}

function monitor_hdu()
{
    global $db, $timeoutopts;
    $i = 1;
    while (true) {
        $html = str_get_html(get_url("http://acm.hdu.edu.cn/listproblem.php?vol=$i", false, $timeoutopts));
        $table = $html->find("table", 4);
        $txt = explode(";", $table->find("script", 0)->innertext);
        if (sizeof($txt) < 2) break;
        foreach ($txt as $one) {
            $det = explode(",", $one);
            $pid = $det[1];
            if (trim($pid) == "" || problem_get_id_from_virtual("HDU", $pid)) continue;
            echo "HDU $pid\n";
            pcrawler_hdu($pid);
        }
        $i++;
    }
    pcrawler_hdu_num();
}

function monitor_ural()
{
    $html = str_get_html(get_url("http://acm.timus.ru/problemset.aspx?space=1&page=all", false, $timeoutopts));
    $table = $html->find("table.problemset", 0);
    $rows = $table->find("tr");
    for ($j = 2; $j < sizeof($rows) - 2; $j++) {
        $row = $rows[$j];
        $pid = trim($row->find("td", 1)->plaintext);
        if (trim($pid) == "" || problem_get_id_from_virtual("Ural", $pid)) continue;
        echo "Ural $pid\n";
        pcrawler_ural($pid);
    }
    $i++;
    pcrawler_ural_num();
}

function monitor_pku()
{
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

            if (trim($pid) == "" || problem_get_id_from_virtual("PKU", $pid)) continue;
            echo "PKU $pid\n";
            pcrawler_pku($pid);
        }
        $i++;
    }
    pcrawler_pku_num();
}

function monitor_codeforces()
{
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
            if ($pid == '1A') $one++;
            if (preg_match("/(\d*)/", $pid, $matches)) $cid = trim($matches[1]);
            if ($cid == "" ||
                in_array($cid, array("177", "178", "207", "316", "331")) || // ABBYY Cup
                problem_get_id_from_virtual("CodeForces", $cid . "A")
            ) continue;
            echo "CodeForces $cid\n";
            pcrawler_codeforces($cid);
            // echo $cid;
        }
        $i++;
    }
    pcrawler_codeforces_num();
}

function monitor_codeforcesgym()
{
    global $db;
    $json = json_decode(file_get_contents("http://codeforces.com/api/contest.list?gym=true"));
    if ($json->status != "OK") return;
    foreach ($json->result as $contest) {
        $row = $db->get_row("select pid from problem where vname='CodeForcesGym' and vid like '$contest->id%'", ARRAY_N);
        if ($row[0]) continue;
        echo "CodeForcesGym " . $contest->id . "\n";
        pcrawler_codeforcesgym($contest->id);
    }
    pcrawler_codeforcesgym_num();
}

function monitor_sgu()
{
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

            if (trim($pid) == "" || problem_get_id_from_virtual("SGU", $pid)) continue;
            echo "SGU $pid\n";
            pcrawler_sgu($pid);
        }
        $i++;
    }
    pcrawler_sgu_num();
}

function monitor_lightoj()
{

    global $config;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "http://www.lightoj.com/login_check.php");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_COOKIEJAR, "/tmp/lightoj_monitor.cookie");
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
        curl_setopt($ch, CURLOPT_COOKIEFILE, "/tmp/lightoj_monitor.cookie");
        $content = curl_exec($ch);
        curl_close($ch);
        if (stripos($content, "<h1>Volume List") !== false) break;
        $html = str_get_html($content);
        $table = $html->find("table", 1);
        if ($table == null) break;
        $rows = $table->find("tr");
        for ($j = 1; $j < sizeof($rows); $j++) {
            $row = $rows[$j];
            // echo htmlspecialchars($row);
            $pid = trim($row->find("td", 1)->plaintext);

            if (trim($pid) == "" || problem_get_id_from_virtual("LightOJ", $pid)) continue;
            echo "LightOJ $pid\n";
            pcrawler_lightoj($pid);
        }
        $i++;
    }

    unlink("/tmp/lightoj_monitor.cookie");
    pcrawler_lightoj_num();
}

function monitor_zju()
{
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

            if (trim($pid) == "" || problem_get_id_from_virtual("ZJU", $pid)) continue;
            echo "ZJU $pid\n";
            pcrawler_zju($pid);
        }
        $i++;
    }
    pcrawler_zju_num();
}

function monitor_fzu()
{

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

            if (trim($pid) == "" || problem_get_id_from_virtual("FZU", $pid)) continue;
            echo "FZU $pid\n";
            pcrawler_fzu($pid);
        }
        $i++;
    }

    pcrawler_fzu_num();
}

function monitor_nbut()
{
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

            if (trim($pid) == "" || problem_get_id_from_virtual("NBUT", $pid)) continue;
            echo "NBUT $pid\n";
            pcrawler_nbut($pid);
        }
        $i++;
    }
    pcrawler_nbut_num();
}

function monitor_whu()
{
    $i = 1;
    while (true) {
        $html = file_get_contents("http://acm.whu.edu.cn/land/problem/list?volume=$i");
        $chr = "problem_data = ";
        $pos1 = stripos($html, $chr) + strlen($chr);
        $pos2 = stripos($html, "var is_admin", $pos1);
        $html = substr(trim(substr($html, $pos1, $pos2 - $pos1)), 0, -1);
        //echo $html;die();
        $html = json_decode($html);
        if (sizeof($html) < 1) break;
        foreach ($html as $row) {
            $pid = $row->problem_id;
            if (trim($pid) == "" || problem_get_id_from_virtual("WHU", $pid)) continue;
            echo "WHU $pid\n";
            pcrawler_whu($pid);
        }
        $i++;
    }
    pcrawler_whu_num();
}

function monitor_sysu()
{

    $html = str_get_html(get_url("http://soj.me/problem_tab.php?start=1000&end=999999"));
    $table = $html->find("table", 0);
    $rows = $table->find("tr");
    for ($j = 1; $j < sizeof($rows); $j++) {
        $row = $rows[$j];
        //echo htmlspecialchars($row);
        $pid = $row->find("td", 1)->plaintext;

        if (trim($pid) == "" || problem_get_id_from_virtual("SYSU", $pid)) continue;
        echo "SYSU $pid\n";
        pcrawler_sysu($pid);
    }

    pcrawler_sysu_num();
}

function monitor_openjudge()
{
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

            if (trim($pid) == "" || problem_get_id_from_virtual("OpenJudge", $pid)) continue;
            echo "OpenJudge $pid\n";
            pcrawler_openjudge($pid);
        }
        $i++;
    }

    pcrawler_openjudge_num();
}

function monitor_scu()
{
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

            if (trim($pid) == "" || problem_get_id_from_virtual("SCU", $pid)) continue;
            echo "SCU $pid\n";
            pcrawler_scu($pid);
        }
        $i++;
    }

    pcrawler_scu_num();
}

function monitor_hust()
{
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

            if (trim($pid) == "" || problem_get_id_from_virtual("HUST", $pid)) continue;
            echo "HUST $pid\n";
            pcrawler_hust($pid);
        }
        $i++;
    }

    pcrawler_hust_num();
}

function monitor_njupt()
{
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

            if (trim($pid) == "" || problem_get_id_from_virtual("NJUPT", $pid)) continue;
            echo "NJUPT $pid\n";
            pcrawler_njupt($pid);
        }
        $i++;
    }

    pcrawler_njupt_num();
}

function monitor_aizu()
{
    for ($i = 0; $i <= 100; ++$i) {
        $html = str_get_html(get_url("http://judge.u-aizu.ac.jp/onlinejudge/finder.jsp?volumeNo=$i"));
        $table = $html->find("table", 0);
        $rows = $table->find("tr");
        for ($j = 1; $j < sizeof($rows); $j++) {
            $row = $rows[$j];
            // echo htmlspecialchars($row);
            preg_match('/<td class="text-left">#(\d*)<.*<!--<td>(\d*)\/(\d*)<.*> x (\d*)<\/a>/sU', $row, $matches);
            $pid = $matches[1];

            if (trim($pid) == "" || problem_get_id_from_virtual("Aizu", $pid)) continue;
            echo "Aizu $pid\n";
            pcrawler_aizu($pid);
        }
    }

    pcrawler_aizu_num();
}

function monitor_acdream()
{
    $got = array();
    $i = 1;
    while (true) {
        $html = str_get_html(get_url("http://acdream.info/problem/list?page=$i"));
        $table = $html->find("table", 0);
        $rows = $table->find("tr");
        if (isset($got[$rows[1]->find("td", 0)->plaintext])) break;
        for ($j = 1; $j < sizeof($rows); $j++) {
            $row = $rows[$j];
            //echo htmlspecialchars($row);
            $pid = $row->find("td", 0)->plaintext;
            $got[$pid] = true;

            if (trim($pid) == "" || problem_get_id_from_virtual("ACdream", $pid)) continue;
            echo "ACdream $pid\n";
            pcrawler_acdream($pid);
        }
        $i++;
    }

    pcrawler_acdream_num();
}

function monitor_codechef()
{
    foreach (array("easy", "medium", "hard", "challenge", "extcontest", "school") as $typec) {
        $html = str_get_html(get_url("http://www.codechef.com/problems/$typec/"));
        if ($html == null) break;
        $table = $html->find("table.problems", 0);
        if ($table == null) break;
        $rows = $table->find("tr");
        for ($j = 1; $j < sizeof($rows); $j++) {
            $row = $rows[$j];
            $pid = trim($row->find("td", 1)->plaintext);

            if (trim($pid) == "" || problem_get_id_from_virtual("CodeChef", $pid)) continue;
            echo "CodeChef $pid\n";
            pcrawler_codechef($pid);
        }
    }

    pcrawler_codechef_num();
    pcrawler_codechef_sources();
}

function monitor_hrbust()
{
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

            if (trim($pid) == "" || problem_get_id_from_virtual("HRBUST", $pid)) continue;
            echo "HRBUST $pid\n";
            pcrawler_hrbust($pid);
        }
        $i++;
    }

    pcrawler_hrbust_num();
}

function monitor_uestc()
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'http://acm.uestc.edu.cn/problem/search');
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json;charset=UTF-8'));
    $page = 1;
    while (true) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, '{"currentPage":' . $page . ',"orderFields":"id","orderAsc":"true"}');
        $data = curl_exec($ch);
        $data = json_decode($data, true);
        if ($data['pageInfo']['currentPage'] != $page) break;
        foreach ($data['list'] as $prob) {
            $pid = $prob['problemId'];
            if (problem_get_id_from_virtual("UESTC", $pid)) continue;
            echo "UESTC $pid\n";
            pcrawler_uestc($pid);
        }
        $page++;
    }
    curl_close($ch);
}


if ($argc == 2) {
    $name = "monitor_" . $argv[1];
    if (function_exists($name)) {
        $name();
    }
}
?>
