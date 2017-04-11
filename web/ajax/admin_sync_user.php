<?php

include_once(dirname(__FILE__) . "/../functions/users.php");
$ret = array();
$ret["code"] = 1;

if ($current_user->is_root()) {
    $query = "select username from user";
    foreach ((array)$db->get_results($query, ARRAY_N) as $row) {
        $qa = "select runid from status where username='$row[0]'";
        $db->query($qa);
        $na = $db->num_rows;
        $db->query("update user set total_submit=$na where username='$row[0]'");

        $qa = "select distinct pid from status where username='$row[0]' and result='Accepted'";
        $db->query($qa);
        $na = $db->num_rows;
        $db->query("update user set total_ac=$na where username='$row[0]'");

        $qa = "select distinct problem.pid from status,problem where username='$row[0]' and result='Accepted' and status.pid=problem.pid and vname='JNU'";
        $db->query($qa);
        $na = $db->num_rows;
        $db->query("update user set local_ac=$na where username='$row[0]'");
    }
    $ret["msg"] = "Updated.";
} else $ret["msg"] = "Please login as root!";
echo json_encode($ret);
