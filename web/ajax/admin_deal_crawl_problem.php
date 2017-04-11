<?php
include_once(dirname(__FILE__) . "/../functions/pcrawlers.php");
include_once(dirname(__FILE__) . "/../functions/users.php");
$ret = array();
$ret["code"] = 1;
if (!$current_user->is_root()) {
    $ret["msg"] = "Please login as root!";
    die(json_encode($ret));
}

$func = "pcrawler_" . strtolower($_GET["pcoj"]);

if (!function_exists($func)) {
    $ret["msg"] = "Invalid OJ!";
    die(json_encode($ret));
}


if ($_GET["type"] == 0) {//single
    $ret["msg"] = $func($_GET["pcid"]);
    $ret["code"] = 0;
    echo json_encode($ret);
} else if ($_GET["type"] == 1) {//range
    for ($i = intval($_GET["pcidfrom"]); $i <= intval($_GET["pcidto"]); $i++) $ret["msg"] .= $func($i);
    $ret["code"] = 0;
    echo json_encode($ret);

} else if ($_GET["type"] == 2) {//num
    $func .= "_num";
    if (!function_exists($func)) $ret["msg"] = "Invalid OJ!";
    else {
        $ret["msg"] = $func();
        $ret["code"] = 0;
    }
    echo json_encode($ret);
} else {
    $ret["msg"] = "Invalid request!";
    die(json_encode($ret));
}


?>
