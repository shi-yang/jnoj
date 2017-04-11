<?php
include_once(dirname(__FILE__) . "/../functions/users.php");
include_once(dirname(__FILE__) . "/../functions/problems.php");

$pid = convert_str($_POST["tagpid"]);
$tagid = convert_str($_POST["utags"]);
$weight = 10;

$ret = array();
$ret["code"] = 1;
if (!$current_user->is_valid()) {
    $ret["msg"] = "Please Login.";
    echo json_encode($ret);
    die();
}
if ($current_user->is_root()) {
    $num = 1;
    $force = convert_str($_POST["force"]);
    $weight = intval(convert_str($_POST["weight"]));
} else if (!$current_user->aced_problem($pid)) {
    $ret["msg"] = "You haven't solved this problem.";
    echo json_encode($ret);
    die();
}

$num = problem_get_category_name_from_id($tagid);
if ($num == "") {
    $ret["msg"] = "No such type.";
    echo json_encode($ret);
    die();
}

if ($force != 1) {
    if ($current_user->tagged($pid, $tagid)) {
        $ret["msg"] = "You have already tagged this type or one of its sub-types.";
        echo json_encode($ret);
        die();
    }
}

do {
    $current_user->tag_problem_as_category($pid, $tagid, $weight, $force);
    $tagid = problem_get_category_parent_from_id($tagid);
} while ($tagid > 0);
$ret["msg"] = "Tag success!";
$ret["code"] = 0;
echo json_encode($ret);
?>
