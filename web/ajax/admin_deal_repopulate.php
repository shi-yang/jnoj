<?php
include_once("../functions/users.php");
include_once("../functions/contests.php");

if ($current_user->is_root()) {
    $ret = array();
    $ret["code"] = 1;

    if (isset($_POST['prefix'])) {

        $prefix = $_POST['prefix'];
        $ufrom = $_POST['ufrom'];
        $uto = $_POST['uto'];

        $usernames = array();
        for ($i = $ufrom; $i <= $uto; $i++) {
            $usernames[] = $prefix . $i;
        }

        if (empty($prefix) || empty($ufrom) || empty($uto)) {
            $ret["msg"] = "Empty items!";
        } else if ($ufrom > $uto) {
            $ret["msg"] = "Range invalid!";
        } else {
            $ret["code"] = 0;
            $ret["msg"] = "";

            $nonexist = array();
            $failed = array();
            $ssd_cnt = 0;

            for ($i = $ufrom; $i <= $uto; $i++) {
                $row = array();
                $row[0] = $prefix . $i;
                if (!user_exist($row[0])) {
                    $nonexist[] = $row[0];
                } else {
                    $row[1] = randomstr(8);
                    $infos[] = $row;
                    if (!reset_password($infos)) {
                        $failed[] = $row[0];
                    } else {
                        $ret["msg"] .= $row[0] . "\t" . $row[1] . "<br />";
                        $ssd_cnt++;
                    }
                }
            }
            $ret["msg"] = $ssd_cnt . " user(s) password repopupated:<br />" . $ret["msg"];
            if (!empty($failed)) $ret["msg"] .= "<br />Failed for the following user(s):<br />" . implode("<br />", $failed);
            if (!empty($nonexist)) $ret["msg"] .= "<br />The following user(s) not exist:<br />" . implode("<br />", $nonexist);
        }
    } else {
        $username = $_POST['username'];

        if (empty($username)) {
            $ret["msg"] = "Empty items!";
        } else if (!user_exist($username)) {
            $ret["msg"] = "No such user!";
        } else {
            $ret["code"] = 0;
            $infos[0] = $username;
            $infos[1] = randomstr(8);
            $ret["msg"] = "password for $username is set to $infos[1]";

            if (!reset_password($infos)) {
                $ret["code"] = 1;
                $ret["msg"] = "Unknown error when repopulating";
            }
        }
    }
    echo json_encode($ret);
} else {
    $ret["msg"] = "Invalid request!";
    echo json_encode($ret);
}
?>
