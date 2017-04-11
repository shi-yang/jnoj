<?php
include_once(dirname(__FILE__) . "/../functions/users.php");
include_once(dirname(__FILE__) . "/../functions/contests.php");
$ret = array();
$ret['code'] = 0;
$src = convert_str($_GET['src']);

if ($src != "") $ret['prob'] = (array)$db->get_results("select pid,title from problem where source='$src' order by pid", ARRAY_A);
echo json_encode($ret);
?>
