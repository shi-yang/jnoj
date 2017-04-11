<?php
include_once(dirname(__FILE__) . "/../config.php");
if (!isset($_COOKIE[$config["cookie_prefix"] . "username"]) || !isset($_COOKIE[$config["cookie_prefix"] . "password"])) {
    $nowuser = "";
    $nowpass = "";
} else if ($_COOKIE[$config["cookie_prefix"] . "username"] == "" || $_COOKIE[$config["cookie_prefix"] . "password"] == "") {
    $nowuser = "";
    $nowpass = "";
} else {
    $nowuser = addslashes($_COOKIE[$config["cookie_prefix"] . "username"]);
    $nowpass = addslashes($_COOKIE[$config["cookie_prefix"] . "password"]);
}
