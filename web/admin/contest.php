<?php
include_once __DIR__ . '/../ckeditor/ckeditor.php';
$ckeditor = new CKEditor();
$ckeditor->basePath = 'ckeditor/';
?>
    <form id='cload' method="get" action="#" class="form-inline">
        <input type="text" id="ncid" placeholder="Contest ID" class="form-control"/>
        <button class="btn btn-primary" type="submit"> Load</button>
        <div class="btn-group">
            <button class="btn" type="button" id="clockp"> Lock Problem</button>
            <button class="btn" type="button" id="culockp"> Unlock Problem</button>
        </div>
        <div class="btn-group">
            <button class="btn" type="button" id="cshare"> Share Code</button>
            <button class="btn" type="button" id="cunshare"> Unshare Code</button>
        </div>
        <button class="btn" type="button" id="ctestall"> Test All</button>
        <button class="btn btn-danger" type="button" onclick="resetcdetail()"> Reset</button>
    </form>
    <form method="post" action="ajax/admin_deal_contest.php" id="cdetail" class="ajform form-horizontal">
        <h4>Contest Information</h4>
        <div class="form-group">
            <label for="cid" class="col-sm-2 control-label">Contest ID</label>
            <div class="col-sm-10">
                <input type="text" class="form-control" id="cid" name="cid" placeholder="Contest ID"
                       readonly="readonly">
            </div>
        </div>
        <div class="form-group">
            <label for="title" class="col-sm-2 control-label">Title</label>
            <div class="col-sm-10">
                <input type="text" class="form-control" id="title" name="title" placeholder="Title">
            </div>
        </div>
        <div class="form-group">
            <label for="ctype" class="col-sm-2 control-label">Type</label>
            <div class="col-sm-10">
                <label class="radio-inline">
                    <input type="radio" name="ctype" id="ctype1" value="0" checked> ICPC format
                </label>
                <label class="radio-inline">
                    <input type="radio" name="ctype" id="ctype2" value="1"> CF format
                </label>
            </div>
        </div>
        <div class="form-group">
            <label for="has_cha" class="col-sm-2 control-label">Has Challenge?</label>
            <div class="col-sm-10">
                <label class="radio-inline">
                    <input type="radio" name="has_cha" id="has_cha1" value="0" checked> No
                </label>
                <label class="radio-inline">
                    <input type="radio" name="has_cha" id="has_cha2" value="1"> Yes
                </label>
            </div>
        </div>
        <div class="form-group">
            <label for="title" class="col-sm-2 control-label">Description</label>
            <div class="col-sm-10">
                <textarea name="description" id="description" class="form-control" rows="8"></textarea>
            </div>
        </div>
        <div class="form-group">
            <label for="start_time" class="col-sm-2 control-label">Start Time</label>
            <div class="col-sm-10">
                <input type="text" name="start_time" class="datepick form-control"
                       value='<?= date("Y-m-d") . " 09:00:00" ?>'>
            </div>
        </div>
        <div class="form-group">
            <label for="end_time" class="col-sm-2 control-label">End Time</label>
            <div class="col-sm-10">
                <input type="text" name="end_time" class="datepick form-control"
                       value='<?= date("Y-m-d") . " 14:00:00" ?>'>
            </div>
        </div>
        <div class="form-group">
            <label for="lock_board_time" class="col-sm-2 control-label">Lock Board Time</label>
            <div class="col-sm-10">
                <input type="text" name="lock_board_time" class="datepick form-control"
                       value='<?= date("Y-m-d") . " 14:00:00" ?>'>
            </div>
        </div>
        <div class="form-group">
            <label for="challenge_start_time" class="col-sm-2 control-label">Challenge Start Time</label>
            <div class="col-sm-10">
                <input type="text" name="challenge_start_time" class="datepick form-control"
                       value='<?= date("Y-m-d") . " 14:10:00" ?>'>
            </div>
        </div>
        <div class="form-group">
            <label for="challenge_end_time" class="col-sm-2 control-label">Lock Board Time</label>
            <div class="col-sm-10">
                <input type="text" name="challenge_end_time" class="datepick form-control"
                       value='<?= date("Y-m-d") . " 14:25:00" ?>'>
            </div>
        </div>
        <div class="form-group">
            <label for="hide_others" class="col-sm-2 control-label">Hide Others' Status</label>
            <div class="col-sm-10">
                <label class="radio-inline">
                    <input type="radio" name="hide_others" id="hide_others1" value="1"> Yes
                </label>
                <label class="radio-inline">
                    <input type="radio" name="hide_others" id="hide_others2" value="0" checked> No
                </label>
            </div>
        </div>
        <div class="form-group">
            <label for="isprivate" class="col-sm-2 control-label">Private</label>
            <div class="col-sm-10">
                <label class="radio-inline">
                    <input type="radio" name="isprivate" id="isprivate1" value="1"> Yes
                </label>
                <label class="radio-inline">
                    <input type="radio" name="isprivate" id="isprivate2" value="0" checked> No
                </label>
            </div>
        </div>
        <div class="form-group">
            <label for="report" class="col-sm-2 control-label">Report</label>
            <div class="col-sm-10">
                <textarea class="form-control" id="treport" name="report" placeholder="Report"></textarea>
            </div>
        </div>
        <?php $nn = $config["limits"]["problems_on_contest_add"]; ?>
        <h4>Add Problems For Contest</h4>
        <div class="input-append">
            <input type='text' id="clcid" id="appendedInput" class="input-small" placeholder="CID"/>
            <button class="btn btn-primary" type="button" id="cclonecid">Clone</button>
        </div>
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        <div class="input-append">
            <input type='text' id="clsrc" id="appendedInput" class="input-large" placeholder="Source"/>
            <button class="btn btn-primary" type="button" id="cclonesrc">Clone</button>
        </div>
        <p><b>Leave Problem ID blank if you don't want to add it.</b></p>
        <div id="cprobs" class="con_probs"></div>
        <h4>Add User For Contest (Seperate them by characters other than [A-Z0-9a-z_-] )</h4>
        <textarea name="names" class="form-control" rows="8"></textarea>
        <div class="pull-right" style="margin-top:10px">
            <button class="btn btn-primary" type="submit">Submit</button>
        </div>
        <div id="msgbox" style="display:none;clear:both"></div>
    </form>
<?php
$ckeditor->replace('treport');
?>