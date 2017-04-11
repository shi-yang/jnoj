<?php
include_once("functions/contests.php");
include_once("functions/users.php");
$pagetitle = "Contest Editor";
include_once("header.php");
$cid = convert_str($_GET['cid']);
?>
<div class="col-md-12">
    <?php
    if (contest_exist($cid) && !contest_passed($cid) && ($current_user->is_root() || $current_user->match(contest_get_val($cid, "owner")))) {
        ?>
        <div class='well' style="color:blue;display:none;">
            In CF, Parameter A represents the points lost per minute. Parameter B represents the points lost for each
            incorrect submit.<br/>
            In CF Dynamic, parameters will decrease according to the AC ratio.<br/>
            In TC, parameters defined as below. A + B must equal to 1. Parameter C is usually the length of this contest
            in TopCoder. Parameter E is the percentage of penalty for each incorrect submit.<br/>
            <img src='img/tcpoint.png'/>
        </div>
        <form method="post" action="ajax/vcontest_modify.php" id="cmodifyform" class="ajform form-horizontal">
            <input name="cid" value='<?= $cid; ?>' type="hidden"/>
            <div class="row">
                <div class="col-md-6">
                    <h4>Contest Information</h4>

                    <input type="text" name="title" value="<?= contest_get_val($cid, "title") ?>"
                           class="input-block-level" placeholder="Contest Title *"/>
                    <?php
                    if (!contest_started($cid)) {
                        ?>
                        Type: <label class="radio inline"><input type="radio" name="ctype"
                                                                 value="0" <?= contest_get_val($cid, "type") == 0 ? 'checked="checked"' : '' ?> />
                            ICPC format</label><label class="radio inline"><input type="radio" name="ctype"
                                                                                  value="1" <?= contest_get_val($cid, "type") == 1 ? 'checked="checked"' : '' ?> />
                            CF format</label>
                        <?php
                    }
                    ?>
                    <textarea name="description" rows="8" class="input-block-level"
                              placeholder="Contest Description"><?= htmlspecialchars(contest_get_val($cid, "description")) ?></textarea>
                    <?php
                    if (!contest_started($cid)) {
                        ?>
                        <fieldset class="contest-time-pick">
                            <label class="input-append input-prepend date datepick"><span
                                    class="add-on">Start Time* : </span><input type="text" name="start_time"
                                                                               value='<?= contest_get_val($cid, "start_time") ?>'/><span
                                    class="add-on"><i class="icon-th"></i></span></label>
                            <p class="prompt_text">( At least after 10 minutes )</p>
                            <label class="input-append input-prepend"><span class="add-on">Duration* : </span><input
                                    type="text" name="duration" value=''/></label>
                            <p class="prompt_text">( Duration should be between 30 minutes and 15 days )</p>
                            <label class="input-append input-prepend date datepick"><span
                                    class="add-on">End Time* : </span><input type="text" name="end_time"
                                                                             value='<?= contest_get_val($cid, "end_time") ?>'/><span
                                    class="add-on"><i class="icon-th"></i></span></label>
                            <p class="prompt_text">( Has to be later than start time )</p>
                            <label class="input-append input-prepend date datepick"><span class="add-on">Lock Board Time: </span><input
                                    type="text" name="lock_board_time"
                                    value='<?= contest_get_val($cid, "lock_board_time") ?>'/><span class="add-on"><i
                                        class="icon-th"></i></span></label>
                            <p class="prompt_text">( Set it later than end time if you don't want to lock board )</p>
                        </fieldset>
                        <label class="radio inline"><input type="radio" name="localtime" value="1"/>Use local
                            timezone</label><label class="radio inline"><input type="radio" name="localtime" value="0"
                                                                               checked="checked"/> Use server
                            timezone</label>
                        <p class="prompt_text">Your timezone: <span id="localtz"></span><input name="localtz"
                                                                                               type="hidden"
                                                                                               id="tzinp"/></p>
                        <label class="radio inline"><input type="radio" name="hide_others"
                                                           value="1" <?= contest_get_val($cid, "hide_others") == 1 ? 'checked="checked"' : '' ?> />
                            Hide others' status</label><label class="radio inline"><input type="radio"
                                                                                          name="hide_others"
                                                                                          value="0" <?= contest_get_val($cid, "hide_others") == 0 ? 'checked="checked"' : '' ?> />
                            Show others' status</label>
                        <?php
                    }
                    ?>
                    <label class="input-prepend"><span class="add-on">Password: </span><input type="password"
                                                                                              name="password"/></label>
                    <p class="prompt_text">( Leave it blank if not needed )</p>
                    <?php
                    if (!contest_started($cid)) {
                        ?>
                        <label><input type="checkbox"
                                      name="owner_viewable" <?= contest_get_val($cid, "owner_viewable") == 1 ? 'checked="checked"' : '' ?>/>Allow
                            owner view participant's code</label>
                        <?php
                    }
                    ?>
                </div>
                <div id="probs" class="col-md-6<?= contest_started($cid) ? ' hide' : '' ?>">
                </div>
            </div>
            <div style='clear:both;'>
                <input class="btn btn-primary" type="submit" name="Submit" value="Submit"/>
                <span id="msgbox"></span>
            </div>
        </form>
        <?php
    } else {
        ?>
        <div class="alert alert-error">Invalid request!</div>
        <?php
    }
    ?>
</div>
<script type="text/javascript" src="assets/js/jstz.min.js"></script>
<script type="text/javascript">
    var timezone = jstz.determine_timezone();
    $("#localtz").html(timezone.name() + " GMT" + timezone.offset());
    $("#tzinp").val(timezone.name());
    $.fn.problemlist.ojoptions = "<?=addslashes($ojoptions)?>";
</script>
<script type="text/javascript" src="assets/js/moment.min.js"></script>
<script type="text/javascript" src="assets/js/contest_edit.js?<?= filemtime("assets/js/contest_edit.js") ?>"></script>

<?php
include_once("footer.php");
?>
