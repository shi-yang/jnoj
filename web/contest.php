<?php
$pagetitle = "Contest List";
include_once("header.php");
include_once("functions/contests.php");
?>
<div class="col-md-12">
    <button id="arrangevirtual" class="btn btn-primary">Arrange VContest</button>
    <div class="btn-group">
        <button id="showall" class="btn btn-info active">All</button>
        <button id="showstandard" class="btn btn-info">Standard</button>
        <button id="showvirtual" class="btn btn-info">Virtual</button>
    </div>
    <div class="btn-group">
        <button id="showcall" class="btn btn-info active">All</button>
        <button id="showcicpc" class="btn btn-info">ICPC</button>
        <button id="showccf" class="btn btn-info">CF</button>
        <button id="showcreplay" class="btn btn-info">Replay</button>
        <button id="showcnonreplay" class="btn btn-info">Non-Replay</button>
    </div>
    <div class="btn-group">
        <button id="showtall" class="btn btn-info active">All</button>
        <button id="showtpublic" class="btn btn-info">Public</button>
        <button id="showtprivate" class="btn btn-info">Private</button>
        <button id="showtpassword" class="btn btn-info">Password</button>
    </div>

    <div id="flip-scroll">
        <table width="100%" class="table table-hover table-striped cf basetable" id="contestlist">
            <thead>
            <tr>
                <th width='10%'> CID</th>
                <th width="30%"> Title</th>
                <th width='15%'> Start Time</th>
                <th width='15%'> End Time</th>
                <th width='10%'> Status</th>
                <th width='10%'> Access</th>
                <th width="10%"> Manager</th>
                <th> Private</th>
                <th> Type</th>
            </tr>
            </thead>
            <tbody>
            </tbody>
            <tfoot>
            </tfoot>
        </table>
    </div>
</div>

<div id="arrangevdialog" class="modal fade">
    <div class="modal-dialog  modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h3>Arrange a virtual contest</h3>
            </div>
            <form method="post" action="ajax/vcontest_arrange.php" class="ajform" id="arrangeform">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="well hide typenote">
                                In CF, Parameter A represents the points lost per minute. Parameter B represents the
                                points
                                lost for each incorrect submit.<br/>
                                In CF Dynamic, parameters will decrease according to the AC ratio.<br/>
                                In TC, parameters defined as below. A + B must equal to 1. Parameter C is usually the
                                length
                                of this contest in TopCoder. Parameter E is the percentage of penalty for each incorrect
                                submit.<br/>
                                <img src='assets/img/tcpoint.png'/>
                            </div>
                            <h4>Contest Information</h4>
                            <div class="form-group">
                                <label for="contest_title">Contest Title</label>
                                <input type="text" name="title" class="form-control" id="contest_title"
                                       placeholder="Contest Title *">
                            </div>
                            <div class="form-group">
                                Type:
                                <label class="radio-inline">
                                    <input type="radio" name="ctype" id="icpc" value="0" checked>
                                    ICPC format
                                </label>
                                <label class="radio-inline">
                                    <input type="radio" name="ctype" id="cf_format" value="1">
                                    CF format
                                </label>
                            </div>
                            <div class="form-group">
                                <label for="desc">Contest Description</label>
                                <textarea name="description" rows="4" class="form-control" id="desc"
                                          placeholder="Contest Description"></textarea>
                            </div>
                            <div class="contest-time-pick">
                                <div class="form-group date">
                                    <label for="start_time">Start Time* : </label>
                                    <input class="form-control" id="start_time" type="text" name="start_time"
                                           value='<?= date("Y-m-d") . " 09:00:00" ?>'>
                                    <span class="add-on"><span class="icon-th"></span></span>
                                    <p class="help-block">At least after 10 minutes</p>
                                </div>
                                <div class="form-group">
                                    <label for="contest_title">Duration* : </label>
                                    <input type="text" class="form-control" name="duration" id="duration"
                                           value='5:00:00' placeholder="Duration">
                                    <p class="help-block">Duration should be between 30 minutes and 15 days</p>
                                </div>
                                <div class="form-group date">
                                    <label for="end_time">End Time* : </label>
                                    <input class="form-control" id="end_time" type="text" name="end_time"
                                           value='<?= date("Y-m-d") . " 14:00:00" ?>'>
                                    <span class="add-on"><span class="icon-th"></span></span>
                                    <p class="help-block">Has to be later than start time</p>
                                </div>
                                <div class="form-group date">
                                    <label for="lock_board_time">Lock Board Time: </label>
                                    <input class="form-control" id="lock_board_time" type="text" name="lock_board_time"
                                           value='<?= date("Y-m-d") . " 14:00:00" ?>'>
                                    <span class="add-on"><span class="icon-th"></span></span>
                                    <p class="help-block">Set it later than end time if you don't want to lock board</p>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="radio-inline">
                                    <input type="radio" name="localtime" id="localtime1" value="1"> Use local timezone
                                </label>
                                <label class="radio-inline">
                                    <input type="radio" name="localtime" id="localtime2" value="0" checked> Use server timezone
                                </label>
                                <p class="help-block">
                                    Your timezone:<span id="localtz"></span>
                                    <input name="localtz" type="hidden" id="tzinp">
                                </p>
                            </div>
                            <div class="form-group">
                                <label class="radio-inline">
                                    <input type="radio" name="hide_others" id="hide_others1" value="1"> Hide others' status
                                </label>
                                <label class="radio-inline">
                                    <input type="radio" name="hide_others" id="hide_others2" value="0" checked> Show others' status
                                </label>
                            </div>
                            <div class="form-group">
                                <label for="password">Password</label>
                                <input type="password" name="password" class="form-control" id="password" placeholder="Password">
                                <p class="help-block">Leave it blank if not needed</p>
                            </div>
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" name="owner_viewable"> Allow owner view participant's code
                                </label>
                            </div>
                            <?php
                            if ($_GET['clone'] == 1) {
                                $ccid = convert_str($_GET['cid']);
                                if (contest_passed($ccid) && (!contest_is_private($ccid) || ($current_user->is_valid() && ($current_user->is_in_contest($ccid) || $current_user->is_root())))) {
                                    $ccrow = contest_get_problem_basic($ccid);
                                }
                            }
                            ?>
                        </div>
                        <div class="col-md-6">
                            <div id="probs">
                                <h4>Add Problems For Contest</h4>
                                <p>Leave Problem ID blank if you don't want to add it.</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <span id="msgbox" style="display:none"></span>
                    <input name='login' class="btn btn-primary" type='submit' value='Submit'/>
                </div>
            </form>
        </div>
    </div>
</div>

<script type="text/javascript" src="assets/js/jstz.min.js"></script>
<script type="text/javascript">
    var timezone = jstz.determine_timezone();
    $("#localtz").html(timezone.name() + " GMT" + timezone.offset());
    $("#tzinp").val(timezone.name());
    var searchstr =<?=json_encode($_GET['search'])?>;
    var conperpage =<?=$config["limits"]["contests_per_page"]?>;
    var cshowtype =<?=json_encode($_GET['type'])?>;
    $.fn.problemlist.ojoptions = "<?=addslashes($ojoptions)?>";
</script>
<script type="text/javascript" src="assets/js/moment.min.js"></script>
<script type="text/javascript" src="assets/js/contest.js?<?= filemtime("assets/js/contest.js") ?>"></script>

<?php include("footer.php"); ?>
