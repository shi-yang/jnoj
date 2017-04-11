<?php
include_once(dirname(__FILE__) . "/../functions/problems.php");
include_once(dirname(__FILE__) . "/../functions/users.php");
require(dirname(__FILE__) . "/../functions/upload_handler.php");
$ret = array();
$ret["code"] = 1;
if ($current_user->is_root()) {
    if (isset($_GET['action'])) {
        $action = $_GET['action'];
        $upload_file_path = $config['test_data_path'];
        if ($action == 'upload') {
            if (!isset($_POST['p_id'])) {
                $ret["msg"] = "Invalid Request!";
                goto end;
            }
            $upload_file_path .= convert_str($_POST['p_id']) . '/';
            $file = $_FILES['files'];
            if (!is_uploaded_file($file['tmp_name'])) {
                $ret["msg"] = "上传文件不存在！";
                goto end;
            }
            if (file_exists($upload_file_path . $file['name'])) {
                $ret["msg"] = "同文件名已存在！";
                goto end;
            }
            if (!move_uploaded_file($file['tmp_name'], $upload_file_path . $file['name'])) {
                $ret["msg"] = "移动文件出错！";
                goto end;
            }
            $ret["msg"] = "Success";
            $ret["code"] = 0;
        } else if ($action == 'delete') {
            if (isset($_GET['filename']) && isset($_GET['pid'])) {
                $file_path = $upload_file_path . $_GET['pid'] . '/' . $_GET['filename'];
                if (unlink($file_path)) {
                    $ret["msg"] = "Succese.";
                    $ret["code"] = 0;
                } else {
                    $ret["msg"] = "Failed";
                }
            } else {
                $ret["msg"] = "Invalid Request!";
            }
        }
    } else {
        $ret["msg"] = "Invalid Request!";
    }
} else {
    $ret["msg"] = "Please login as root!";
}
end:
echo json_encode($ret);
