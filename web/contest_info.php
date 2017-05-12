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
