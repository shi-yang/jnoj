<?php
include_once(dirname(__FILE__) . "/../functions/contests.php");
$cid = convert_str($_GET['cid']);
$ret["prob"] = contest_get_problem_basic($cid);
echo json_encode($ret);
?>
