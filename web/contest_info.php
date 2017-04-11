<?php
include_once("functions/contests.php");
include_once("functions/users.php");
include_once("functions/sidebars.php");
$cid = convert_str($_GET["cid"]);
if (contest_exist($cid) && ($current_user->is_root() || contest_get_val($cid, "isprivate") == 0 ||
        (contest_get_val($cid, "isprivate") == 1 && $current_user->is_in_contest($cid)) ||
        (contest_get_val($cid, "isprivate") == 2 && contest_get_val($cid, "password") == $_COOKIE[$config["cookie_prefix"] . "contest_pass_$cid"]))
) {
    ?>

    <div class="col-md-9">
        <!-- insert the page content here -->
        <h3 class="pagetitle"><?= contest_get_val($cid, "title") ?></h3>
        <div class="tcenter well">
            Contest Start Time: <?= contest_get_val($cid, "start_time") ?> &nbsp;&nbsp;&nbsp;&nbsp;
            <?= contest_get_val($cid, "has_cha") == 0 ? "Contest End Time: " : "Coding End Time: " ?><?= contest_get_val($cid, "end_time") ?>
            <?php if (contest_get_val($cid, "has_cha") == 1) { ?>
                <br/> Challenge Start Time: <?= contest_get_val($cid, "challenge_start_time") ?> &nbsp;&nbsp;&nbsp;&nbsp; Challenge End Time: <?= contest_get_val($cid, "challenge_end_time") ?>
            <?php } ?>
            <br/>
            <?php
            $canshow = true;
            $nowtime = time();
            if (contest_passed($cid)) echo "<span class='cpassed'>Passed</span>";
            else if (contest_intermission($cid)) {
                $diff = strtotime(contest_get_val($cid, "challenge_start_time")) - $nowtime;
                $diffhour = (int)($diff / 3600);
                $diffminute = (int)(($diff - $diffhour * 3600) / 60);
                $diffsecond = $diff - $diffhour * 3600 - $diffminute * 60;
                echo "Countdown: <span id='counttime'>$diffhour:$diffminute:$diffsecond</span> &nbsp;&nbsp;&nbsp;&nbsp; <span class='crunning'>Intermission</span>";
            } else if (contest_challenging($cid)) {
                $diff = strtotime(contest_get_val($cid, "challenge_end_time")) - $nowtime;
                $diffhour = (int)($diff / 3600);
                $diffminute = (int)(($diff - $diffhour * 3600) / 60);
                $diffsecond = $diff - $diffhour * 3600 - $diffminute * 60;
                echo "Countdown: <span id='counttime'>$diffhour:$diffminute:$diffsecond</span> &nbsp;&nbsp;&nbsp;&nbsp; <span class='crunning'>Challenging</span>";
            } else if (contest_running($cid)) {
                $diff = strtotime(contest_get_val($cid, "end_time")) - $nowtime;
                $diffhour = (int)($diff / 3600);
                $diffminute = (int)(($diff - $diffhour * 3600) / 60);
                $diffsecond = $diff - $diffhour * 3600 - $diffminute * 60;
                echo "Countdown: <span id='counttime'>$diffhour:$diffminute:$diffsecond</span> &nbsp;&nbsp;&nbsp;&nbsp; <span class='crunning'>Running</span>";
            } else {
                $diff = strtotime(contest_get_val($cid, "start_time")) - $nowtime;
                $diffhour = (int)($diff / 3600);
                $diffminute = (int)(($diff - $diffhour * 3600) / 60);
                $diffsecond = $diff - $diffhour * 3600 - $diffminute * 60;
                $canshow = false;
                echo "Countdown: <span id='counttime'>$diffhour:$diffminute:$diffsecond</span> &nbsp;&nbsp;&nbsp;&nbsp; <span class='cscheduled'>Not Started</span>";
            }
            if ($current_user->is_root() || $current_user->match(contest_get_val($cid, "owner"))) {
                ?>
                <br/><a target="_blank" href="contest_problem_merge.php?cid=<?= $cid ?>">[Show All Problem Description]
                    ( For print, shown to owner only. )</a>
                <?php
            }
            ?>
        </div>
        <?php
        if ($canshow) {
            ?>
            <table id="cplist" class="table table-hover table-striped" width="100%">
                <thead>
                <tr>
                    <th width="10%">Flag</th>
                    <th width="10%">ID</th>
                    <th width="50%">Title</th>
                    <th width="15%">Ratio</th>
                    <th width="15%">User</th>
                </tr>
                </thead>
                <tfoot></tfoot>
                <tbody>
                <?php
                foreach ((array)contest_get_problem_summaries($cid) as $row) {
                    ?>
                    <tr>
                        <td> <?= $current_user->aced_problem_in_contest($row["pid"], $cid) ? "<span class='ac'>Yes</a>" : ($current_user->tried_problem_in_contest($row["pid"], $cid) ? "<span class='wa'>No</a>" : "") ?> </td>
                        <td><a href='#problem/<?= $row["lable"] ?>'><?= $row["lable"] ?></a></td>
                        <td><a href='#problem/<?= $row["lable"] ?>'><?= $row["title"] ?></a></td>
                        <td> <?= $row["ac_run"] ?>/<?= $row["submit_run"] ?> </td>
                        <td> <?= $row["ac_user"] ?>/<?= $row["submit_user"] ?> </td>
                    </tr>
                    <?php
                }

                ?>
                </tbody>
            </table>
            <?php
        }
        ?>
    </div>
    <div class="col-md-3">
        <?= sidebar_contest_show($cid) ?>
    </div>
    <?php
} else {
    ?>
    <div class="col-md-12">
        <p class="alert alert-error">Contest Unavailable!</p>
    </div>

    <?php
}
?>
