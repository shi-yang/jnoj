<?php
include_once(dirname(__FILE__) . "/../functions/problems.php");
include_once(dirname(__FILE__) . "/../functions/users.php");
$res = array();
$res["code"] = 1;
if ($current_user->is_root()) {
    $pid = intval($_GET['pid']);
    $query = "select pid from problem where pid='$pid'";
    $db->query($query);
    if ($db->num_rows > 0) {
        if (!is_writable($config['test_data_path'])) {
            $res["msg"] = "测试数据目录不可写";
        } else {
            $res["code"] = 0;
            $dir = $config['test_data_path'] . $pid;
            if (is_writable($dir)) {
                $filesnames = scandir($dir);
                $res["filesnames"] = $filesnames;
            } else {
                @mkdir($dir);
            }
        }
    } else {
        $res["msg"] = "No such problem.";
    }
} else {
    $res["msg"] = "Please login as root!";
}
echo json_encode($res);
