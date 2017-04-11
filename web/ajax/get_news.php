<?php
include_once(dirname(__FILE__) . "/../functions/news.php");
$newsid = convert_str($_GET['nnid']);
$ret = news_get_detail($newsid);
echo json_encode($ret);
?>
