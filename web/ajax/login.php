<?php
include_once(dirname(__FILE__) . "/../functions/users.php");
$username = convert_str($_POST['username']);
$password = hash_password($_POST['password']);

$ret = array();
if (!user_exist($username)) {
    $ret["code"] = 1;
    $ret["msg"] = "No such user!";
    echo json_encode($ret);
    die();
}

if (!$current_user->set_user($username, $password)) {
    $ret["code"] = 1;
    $ret["msg"] = "Password incorrect!";
} else {
    $exp = time() + $_POST['cksave'] * 24 * 60 * 60;
    if ($_POST['cksave'] == 0) $exp = 0;
    set_cookies($username, $password, $exp);
    $current_user->update_last_login($username);
    $ret["code"] = 0;
    $ret["msg"] = "Success...";
}

echo json_encode($ret);

?>
