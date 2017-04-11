<?php
include_once(dirname(__FILE__) . "/../functions/users.php");
$ret = array();
$ret["code"] = 1;

if ($current_user->is_root()) {
    $query = "select pid from problem";
    foreach ((array)$db->get_results($query, ARRAY_N) as $row) {
        $qa = "select runid from status where pid='$row[0]'";
        $db->query($qa);
        $na = $db->num_rows;
        $db->query("update problem set total_submit=$na where pid='$row[0]'");

        $qa = "select runid from status where pid='$row[0]' and result='Accepted'";
        $db->query($qa);
        $na = $db->num_rows;
        $db->query("update problem set total_ac=$na where pid='$row[0]'");
    }
    $ret["msg"] = "Updated.";
} else $ret["msg"] = "Please login as root!";
echo json_encode($ret);
