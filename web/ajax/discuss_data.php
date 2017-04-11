<?php
include_once(dirname(__FILE__) . "/../functions/global.php");
include_once(dirname(__FILE__) . "/../functions/discuss.php");
$proid = intval($_GET['pid']);
$page = intval($_GET['page']);

$res = discuss_load_list($page, $proid);
//print_r($res);

echo json_encode($res);
?>

