<?php
include_once(dirname(__FILE__) . "/global.php");
function news_get_detail($newsid)
{
    global $db;
    $db->query("select * from news where newsid='$newsid'");
    if ($db->num_rows == 0) {
        $res["code"] = 1;
    } else {
        $res = $db->get_row(null, ARRAY_A);
        $res["code"] = 0;
    }
    return $res;
}
