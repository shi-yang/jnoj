<?php
include_once('functions/users.php');
include_once('functions/sidebars.php');
include_once('functions/contests.php');
$cid = convert_str($_GET["cid"]);
if (!contest_started($cid) || !($current_user->is_root() || contest_get_val($cid, "isprivate") == 0 ||
        (contest_get_val($cid, "isprivate") == 1 && $current_user->is_in_contest($cid)) ||
        (contest_get_val($cid, "isprivate") == 2 && contest_get_val($cid, "password") == $_COOKIE[$config["cookie_prefix"] . "contest_pass_$cid"]))
) {
    ?>
    <div class="col-md-12">
        <p class="alert alert-error">Contest not started or you're not in this contest.</p>
    </div>
    <?php
} else {

    ?>
    <div class="col-md-9">
        <h1 class="pagetitle" style="display:none">Clarifications For Contest <?= $cid ?></h1>
        <?php
        if ($current_user->is_root()) $res = contest_get_all_clarify($cid);
        else $res = contest_get_visible_clarify($cid, convert_str($current_user->get_username()));
        foreach ((array)$res as $row) {
            ?>
            <h4><?= $row["ispublic"] == '0' ? "Private Message" : "Public Message" ?></h4>
            <pre><?= "Q: " . htmlspecialchars($row["question"]) ?></pre>
            <?php
            if ($current_user->is_root()) {
                ?>
                <pre align='right'>By <?= $row["username"] ?></pre>
                <form class="clarform" method="post" action="ajax/admin_deal_clarify.php">
                    <div class="form-group">
                        <label for="answer">Answer: </label>
                        <textarea rows="6" name="answer"
                                  class="form-control"><?= htmlspecialchars($row["reply"]) ?></textarea>
                    </div>
                    <div class="radio">
                        <label>
                            <input name="<?= "ispublic" . $row["ccid"] ?>" type="radio"
                                   value="1" <?= $row["ispublic"] == '1' ? "checked" : "" ?> />
                            Public
                        </label>
                    </div>
                    <div class="radio">
                        <label>
                            <input name="<?= "ispublic" . $row["ccid"] ?>" type="radio"
                                   value="0" <?= $row["ispublic"] == '0' ? "checked" : "" ?> />
                            Private
                        </label>
                    </div>
                    <input type="submit" class="btn btn-primary" value="Answer"/>
                    <span id="msgbox" style="display:none;"></span>
                    <input name="ccid" type="hidden" value="<?= $row["ccid"] ?>"/>
                </form>
                <?php
            } else {
                ?>
                <pre><?= "A: " . htmlspecialchars($row["reply"]) ?></pre>
                <?php
            }
            ?>

            <?php
        }
        ?>
        <div class="tcenter">
            <button class="btn btn-primary" id="newquestion">Post New Question</button>
        </div>
    </div>
    <div class="col-md-3">
        <?= sidebar_contest_show($cid) ?>
    </div>
    <?php
}
?>

<div id="questiondialog" class="modal fade">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h3 id="dtitle">Post a new question</h3>
            </div>
            <form id='questionform' method='post' action='ajax/new_clarify.php?cid=<?= $cid ?>'>
                <div class="modal-body">
                    <textarea name="question" class="form-control" rows="10"
                              placeholder="Enter your question here..."></textarea>
                </div>
                <div class="modal-footer">
                    <span id="msgbox" style="display:none;"></span>
                    <input name='submit' type='submit' class="btn btn-primary" value='Submit'/>
                </div>
            </form>
        </div>
    </div>
</div>