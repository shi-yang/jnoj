<?php
include_once("../functions/users.php");
include_once("../functions/contests.php");

if ($current_user->is_root()) {
    $cid = $_POST['cid'];
    $prefix = $_POST['prefix'];
    $ufrom = $_POST['ufrom'];
    $uto = $_POST['uto'];

    $ret = array();
    $ret["code"] = 1;

    $usernames = array();
    for ($i = $ufrom; $i <= $uto; $i++) {
        $usernames[] = $prefix . $i;
    }

    load_contest_infos($cid);
    if (!$contest_infos[$cid]["valid"]) {
        $ret["msg"] = "No such contest!";
    } else if (empty($prefix) || empty($ufrom) || empty($uto)) {
        $ret["msg"] = "Empty items!";

    } else if ($ufrom > $uto) {
        $ret["msg"] = "Range invalid!";
    } else if (user_exist($usernames)) {
        $ret["msg"] = "Username conflict!";
    } else {
        $ret["code"] = 0;
        $ret["msg"] = ($uto - $ufrom + 1) . " user(s) genereted<br />";
        $infos = array();
        for ($i = $ufrom; $i <= $uto; $i++) {
            $row = array();
            $row[0] = $row[2] = $prefix . $i;
            $row[1] = randomstr(8);
            $infos[] = $row;
            $ret["msg"] .= $row[0] . "\t" . $row[1] . "<br/>";
        }
        if (!user_create($infos) || !add_user_to_contest($cid, $usernames)) {
            $ret["code"] = 1;
            $ret["msg"] = "Unknown error when generating";
        }
    }
    echo json_encode($ret);
} else {
    $ret["msg"] = "Invalid request!";
    echo json_encode($ret);
}
?>
