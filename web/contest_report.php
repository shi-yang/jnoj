<?php
include_once("functions/contests.php");
include_once("functions/users.php");
include_once("functions/sidebars.php");
$cid = convert_str($_GET["cid"]);
if (contest_exist($cid) && $current_user->is_root() || contest_get_val($cid, "isprivate") == 0 ||
    (contest_get_val($cid, "isprivate") == 1 && $current_user->is_in_contest($cid)) ||
    (contest_get_val($cid, "isprivate") == 2 && contest_get_val($cid, "password") == $_COOKIE[$config["cookie_prefix"] . "contest_pass_$cid"])
) {
    ?>

    <div class="col-md-9">
        <?php
        if (contest_passed($cid)) {
            ?>
            <h3 class="pagetitle">Contest Report</h3>
            <div id="contestrep" class="well">
                <?= contest_get_val($cid, "report") ?>
            </div>

            <?php
        } else {
            ?>
            <p class="alert alert-warning">Contest not finished, come back later :) .</p>
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
        <p class="alert alert-error">Report Unavailable!</p>
    </div>
    <?php
}
?>
