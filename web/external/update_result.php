<?php
include_once(dirname(__FILE__) . "/../functions/users.php");
include_once(dirname(__FILE__) . "/../functions/runs.php");

die(); // please manually delete this line if you need this function

if ($_POST["token"] != $config["contact"]["dispatcher_token"]) die();

$result = $_POST["result"];
$memory_used = $_POST["memory_used"];
$time_used = $_POST["time_used"];
$ce_info = $_POST["ce_info"];
$runid = $_POST["runid"];
$update_stat = $_POST["update_stat"];

$db->query("UPDATE status 
        SET    result = '" . $db->escape($result) . "', 
               memory_used = '" . $db->escape($memory_used) . "', 
               time_used = '" . $db->escape($time_used) . "', 
               ce_info = '" . $db->escape($ce_info) . "' 
        WHERE  runid = '" . $db->escape($runid) . "'");

if (!isset($_POST["update_stat"])) die();


$row = $db->get_row("SELECT username, status.pid as pid, vname 
        FROM   status, problem 
        WHERE  runid = '" . $db->escape($runid) . "' AND problem.pid = status.pid", ARRAY_A);

if (strstr($result, "Accept")) {
    $info = $db->get_row("SELECT count(*) 
        FROM   status 
        WHERE  username = '" . $db->escape($row["username"]) . "' AND 
               pid = '" . $db->escape($row["pid"]) . "' AND 
               result = 'Accepted'", ARRAY_N);

    if ($info[0] == "1") {
        // first time AC, add total_ac to user
        $db->query("UPDATE user SET total_ac=total_ac+1 WHERE username='" . $db->escape($row["username"]) . "'");
        if ($row["vname"] == $config["OJcode"]) {
            // if it's local problem, update local_ac
            $db->query("UPDATE user SET local_ac=local_ac+1 WHERE username='" . $db->escape($row["username"]) . "'");
        }
    }
    // update problem stats
    $db->query("UPDATE problem SET total_ac=total_ac+1 WHERE pid='" . $db->escape($row["pid"]) . "'");
}
