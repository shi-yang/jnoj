<?php
require_once(dirname(__FILE__) . "/../functions/simple_html_dom.php");
include_once(dirname(__FILE__) . "/../functions/users.php");
include_once(dirname(__FILE__) . "/../functions/replays.php");

$ret["code"] = 1;

if ($current_user->is_root()) {
    $tuCurl = curl_init();
    curl_setopt($tuCurl, CURLOPT_URL, "http://acm.hust.edu.cn/vjudge/contest/showRankSetting.action?cid=" . $_GET["cid"]);
    curl_setopt($tuCurl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($tuCurl, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($tuCurl, CURLOPT_USERAGENT, "JNUOJ");
    $html = curl_exec($tuCurl);
    curl_close($tuCurl);
    $html = str_get_html($html);
    $contests = array(intval($_GET["cid"]));
    foreach ($html->find("table", 0)->find("tr") as $row) {
        $contests[] = intval($row->find("td input", 0)->value);
    }
    $total = 0;
    foreach ($contests as $cid) {
        $res = replay_crawl_hustv($cid);
        if ($res["code"]) {
            if ($res["msg"]) $ret["msg"] .= $res["msg"] . "<br>";
            continue;
        }
        $row = $db->get_row("SHOW TABLE STATUS LIKE 'contest'", ARRAY_A);
        $mcid = $row['Auto_increment'];
        $pnum = $res["pnum"];
        for ($i = 0; $i < $pnum; $i++) {
            $probs[$i]["pid"] = $res["prob"][$i];
            $probs[$i]["lable"] = chr(ord('A') + $i);
        }
        $_POST = $res;

        $sttime = strtotime($_POST['start_time']);
        $edtime = strtotime($_POST['end_time']);
        $sfreq = 180;

        // trigger hustv to produce json
        curl_setopt($tuCurl, CURLOPT_URL, "http://acm.hust.edu.cn/vjudge/contest/view.action?cid=$cid");
        curl_setopt($tuCurl, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($tuCurl, CURLOPT_USERAGENT, "JNUOJ");
        curl_exec($tuCurl);
        curl_close($tuCurl);
        set_time_limit(10);
        sleep(2);

        $filename = "replay_cid_" . $mcid . ".json";
        replay_move_uploaded_file($filename);
        $html = get_url("../uploadstand/" . $filename);
        replay_add_contest();
        replay_deal_hustvjson($html);
        $total++;
    }
    $ret["code"] = 0;
    $ret["msg"] .= "Done. $total contests added.";
    echo json_encode($ret);
} else {
    $ret["msg"] = "Invalid request!";
    echo json_encode($ret);
}
