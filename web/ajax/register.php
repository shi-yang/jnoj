<?php
include_once("../functions/users.php");
$_POST['password'] = addslashes($_POST['password']);
$_POST['repassword'] = addslashes($_POST['repassword']);
$_POST['nickname'] = convert_str($_POST['nickname']);
$_POST['school'] = convert_str($_POST['school']);
$_POST['email'] = convert_str($_POST['email']);

$ret = array();
$ret["code"] = 1;
if (strlen($_POST['username']) == 0) {
    $ret["msg"] = "Empty Username!";
} else if (strlen($_POST['username']) < 3)
    $ret["msg"] = "Username too short!";
else if (strlen($_POST['username']) > 64)
    $ret["msg"] = "Username too long!";
else {
    $s = convert_str($_POST['username']);
    for ($i = 0; $i < strlen($s); $i++)
        if ($s[$i] >= '0' && $s[$i] <= '9' || $s[$i] >= 'a' && $s[$i] <= 'z' || $s[$i] >= 'A' && $s[$i] <= 'Z' || $s[i] == '-' || $s[i] == '_')
            continue;
        else break;
    if ($i != strlen($s))
        $ret["msg"] = "Invalid Username!";
    else if (user_exist($_POST['username']))
        $ret["msg"] = "Username Already Exists!";
    else if (strlen($_POST['password']) < 3)
        $ret["msg"] = "Password too short!";
    else if ($_POST['password'] != $_POST['repassword'])
        $ret["msg"] = "Password doesn't match!";
    else {
        $row[0] = $_POST['username'];
        $row[1] = $_POST['password'];
        if ($_POST['nickname'] == "") $row[2] = $_POST['username'];
        else $row[2] = $_POST['nickname'];
        $row[3] = $_POST['school'];
        $row[4] = $_POST['email'];
        if (user_create($row)) {
            $ret["code"] = 0;
            $ret["msg"] = "Success! Please login.";
        } else
            $ret["msg"] = "Register Failed.";
    }
}

echo json_encode($ret);
?>

