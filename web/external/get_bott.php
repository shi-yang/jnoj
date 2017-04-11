<?php
include_once(dirname(__FILE__) . "/../functions/users.php");
include_once(dirname(__FILE__) . "/../functions/runs.php");

die(); // please manually delete this line if you need this function

if ($_POST["token"] != $config["contact"]["dispatcher_token"]) die();
$row = $db->get_row("SELECT status.source AS source, 
                   status.runid AS runid, 
                   status.language AS language, 
                   status.pid AS pid,
                   problem.ignore_noc AS ignore_noc, 
                   problem.number_of_testcase AS number_of_testcase,
                   problem.time_limit AS time_limit,
                   problem.case_time_limit AS case_time_limit,
                   problem.memory_limit AS memory_limit,
                   problem.special_judge_status AS special_judge_status,
                   problem.vname AS vname,
                   problem.vid AS vid
            FROM status, problem
            WHERE status.pid = problem.pid AND runid = '" . $db->escape($_POST["runid"]) . "' ", ARRAY_A);
if ($row["ignore_noc"] == "1") {
    $jtype = 6;
} else {
    $jtype = 2;
}
?>
<type> <?= $jtype ?>

    __SOURCE-CODE-BEGIN-LABLE__
    <?= $row["source"] ?>

    __SOURCE-CODE-END-LABLE__
    <runid> <?= $row["runid"] ?>

        <language> <?= $row["language"] ?>

            <pid> <?= $row["pid"] ?>

                <testcases> <?= $row["number_of_testcase"] ?>

                    <time_limit> <?= $row["time_limit"] ?>

                        <case_limit> <?= $row["case_time_limit"] ?>

                            <memory_limit> <?= $row["memory_limit"] ?>

                                <special> <?= $row["special_judge_status"] ?>

                                    <vname> <?= $row["vname"] ?>

                                        <vid> <?= $row["vid"] ?>
