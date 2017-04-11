<?php
include_once("../functions/users.php");
$username = convert_str($_POST['username']);
$ret = array();
$ret["code"] = 1;
if ($current_user->get_username() == $username) {
    $ops = addslashes($_POST['ol_password']);
    $ps = addslashes($_POST['password']);
    $rps = addslashes($_POST['repassword']);
    $nickname = convert_str($_POST['nickname']);
    $school = convert_str($_POST['school']);
    $email = convert_str($_POST['email']);
    $ops = hash_password($ops);
    $flag = 0;
    if ($ps != $rps) {
        $ret["msg"] = "Retype password doesn't match!";
        echo json_encode($ret);
        die();
    }
    if (strcasecmp($current_user->get_val("password"), $ops)) {
        $ret["msg"] = "Wrong password!";
        echo json_encode($ret);
        die();
    }
    if ($ps == "") {
        $ps = addslashes($_POST['ol_password']);
    } else if (strlen($ps) < 3) {
        $ret["msg"] = "Password too short!";
        echo json_encode($ret);
        die();
    }

    $infos["password"] = $ps;
    $infos["email"] = $email;
    $infos["school"] = $school;
    $infos["nickname"] = $nickname;
    $current_user->update_info($infos);

    $ret["msg"] = "Success!";
    $ret["code"] = 0;
    set_cookies($username, hash_password($ps));
    echo json_encode($ret);
} else {
    $ret["msg"] = "Invalid Request!";
    echo json_encode($ret);
}
