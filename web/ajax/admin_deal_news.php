<?php
include_once(dirname(__FILE__) . "/../functions/users.php");
$ret = array();
$ret["code"] = 1;
if ($current_user->is_root()) {
    $title = convert_str($_POST['title']);
    $newsid = convert_str($_POST['newsid']);
    $content = convert_str($_POST['content']);
    if ($newsid == "") {
        $sql_add_pro = "insert into news (title,content,author,time_added) values ('$title','$content','$nowuser', NOW())";
    } else {
        $sql_add_pro = "update news set title='$title',content='$content',author='$nowuser',time_added=NOW() where newsid='$newsid'";
    }
    $db->query($sql_add_pro);
    $ret["code"] = 0;
    if ($newsid == '') $currnid = $db->insert_id;
    else $currnid = $newsid;
    $ret["msg"] = "Success! News ID: $currnid.";
} else {
    $ret["msg"] = "Please login as root!";
}
echo json_encode($ret);
