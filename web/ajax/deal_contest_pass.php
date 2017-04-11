<?php
include_once(dirname(__FILE__) . "/../functions/contests.php");
$cid = convert_str($_POST['cid']);
$opass = contest_get_val($cid, "password");
$pass = convert_str($_POST['password']);
$ret = array();
if ($opass == pwd($pass)) {
    setcookie($config["cookie_prefix"] . "contest_pass_$cid", pwd($pass), 0, $config["base_path"]);
    $ret["code"] = 0;
    $ret["msg"] = "Success!";
} else {
    $ret["code"] = 1;
    $ret["msg"] = "Wrong password.";
}
echo json_encode($ret);

?>
