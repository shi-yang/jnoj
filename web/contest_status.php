<?php
include_once('functions/users.php');
include_once('functions/contests.php');
include_once('functions/problems.php');
$cid = convert_str($_GET['cid']);
?>
<div id="flip-scroll" class="col-md-12">
    <h1 class="pagetitle" style="display:none">Status of Contest <?= $cid ?></h1>
    <div>
        <form id="filterform" class="form-inline" method="">
            <b>Filter: </b>
            <label>Username: <input type='text' name='showname' id="showname" placeholder="Username"
                                    class="form-control" value='<?= $current_user->get_username() ?>'/></label>
            <label>ID:
                <select type='text' name='showpid' id="showpid" class="form-control">
                    <option value=''>All</option>
                    <?php
                    if (contest_started($cid)) {
                        foreach ((array)contest_get_problem_basic($cid) as $row) {
                            ?>
                            <option value='<?= $row["lable"] ?>'><?= $row["lable"] . ". " . $row['title'] ?></option>
                            <?php
                        }
                    }
                    ?>
                </select>
            </label>
            <label>Result:
                <select name="showres" id="showres" class="form-control">
                    <option value=''>All</option>
                    <option value='Accepted'>Accepted</option>
                    <?php
                    if (contest_get_val($cid, "has_cha")) {
                        ?>
                        <option value='Pretest Passed'>Pretest Passed</option>
                        <option value='Challenged'>Challenged</option>
                        <?php
                    }
                    ?>
                    <option value='Wrong Answer'>Wrong Answer</option>
                    <option value='Runtime Error'>Runtime Error</option>
                    <option value='Time Limit Exceed'>Time Limit Exceed</option>
                    <option value='Memory Limit Exceed'>Memory Limit Exceed</option>
                    <option value='Output Limit Exceed'>Output Limit Exceed</option>
                    <option value='Presentation Error'>Presentation Error</option>
                    <option value='Restricted Function'>Restricted Function</option>
                    <option value='Compile Error'>Compile Error</option>
                </select>
            </label>
            <label>Language:
                <select name="showlang" id="showlang" class="form-control">
                    <option value="">All</option>
                    <option value="1">GNU C++</option>
                    <option value="2">GNU C</option>
                    <option value="3">Oracle Java</option>
                    <option value="4">Free Pascal</option>
                    <option value="5">Python2</option>
                    <option value="16">Python3</option>
                    <option value="6">C# (Mono)</option>
                    <option value="7">Fortran</option>
                    <option value="8">Perl</option>
                    <option value="9">Ruby</option>
                    <option value="10">Ada</option>
                    <option value="11">SML</option>
                    <option value="12">Visual C++</option>
                    <option value="13">Visual C</option>
                </select>
            </label>
            <button type='submit' class="btn btn-primary">Show</button>
        </form>
    </div>
    <?php
    if (contest_get_val($cid, "hide_others") && !$current_user->is_root() && !(contest_get_val($cid, "owner_viewable") && $current_user->match(contest_get_val($cid, "owner")))) {
        ?>
        <div class="tcenter"><b>In this contest, you can only view the submits from yourself.</b></div>
        <?php
    }
    ?>
    <div>
        <table class="table table-hover table-striped basetable cf" id="statustable" width="100%">
            <thead>
            <tr>
                <th width='9%'>Username</th>
                <th width='7%'>RunID</th>
                <th width='6%'>ID</th>
                <th width='12%'>Result</th>
                <th width='9%'>Language</th>
                <th width='8%'>Time</th>
                <th width='8%'>Memory</th>
                <th width='7%'>Length</th>
                <th width='13%'>Submit Time</th>
                <th width='0%'>Visible</th>
            </tr>
            </thead>
            <tbody></tbody>
            <tfoot></tfoot>
        </table>
    </div>
</div>

<div id="statusdialog" class="modal fade" style="display:none">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h3>Title</h3>
            </div>
            <div class="modal-body">
                <div class="well" style="text-align:center" id="rcontrol">
                    Result: <span id="rresult"></span> &nbsp;&nbsp;&nbsp; Memory Used: <span id="rmemory"></span> KB
                    &nbsp;&nbsp;&nbsp; Time Used: <span id="rtime"></span> ms <br/>
                    Language: <span id="rlang"></span> &nbsp;&nbsp;&nbsp; Username: <span id="ruser"></span> &nbsp;&nbsp;&nbsp;
                    Problem: <span id="rpid"></span> <br/>
                    Share Code?
                    <div class="btn-group" id="rshare">
                        <button id="sharey" type="button" class="btn btn-info">Yes</button>
                        <button id="sharen" type="button" class="btn btn-info">No</button>
                    </div>
                    <?php if ($current_user->is_root()) { ?>
                        <button id="rejudge" class="btn btn-warning">Rejudge</button>
                    <?php } ?>
                    <br/><b id='sharenote'>This code is shared.</b>
                </div>
                <button class="pull-right btn btn-mini btn-inverse" data-clipboard-target="dcontent" id="copybtn">Copy
                </button>
                <pre id="dcontent"></pre>
            </div>
        </div>
    </div>
</div>

<script src="assets/js/ZeroClipboard.min.js"></script>
