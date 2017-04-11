<?php
include_once(dirname(__FILE__) . "/../functions/discuss.php");
$id = convert_str($_GET['id']);
$tres = discuss_load_detail($id);
$res = discuss_load_detail($tres["rid"]);
discuss_load_subject_list($res);
$res["vis_sub"] = $tres;
echo json_encode($res);
