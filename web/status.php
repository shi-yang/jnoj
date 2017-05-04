<?php
$pagetitle = "Online Status";
include_once("header.php");
if (isset($_GET['start'])) $start = convert_str($_GET['start']);
else $start = "0";
?>
<div id="flip-scroll" class="col-md-12">
    <div>
        <form id="filterform" class="form-inline" method="">
            <b>过滤</b>
            <div class="form-group">
                <label class="sr-only" for="showname">Username</label>
                <div class="input-group">
                    <span class="input-group-addon" id="basic-addon1"><span
                            class="glyphicon glyphicon-user"></span></span>
                    <input type="text" name="showname" class="form-control" id="showname" placeholder="Username"
                           aria-describedby="basic-addon1" value="<?= $current_user->get_username() ?>">
                </div>
            </div>
            <div class="form-group">
                <label class="sr-only" for="showpid">Problem ID</label>
                <input type="text" name="showpid" class="form-control" id="showpid" placeholder="Problem ID">
            </div>
            <div class="form-group">
                <label for="showres">Result:</label>
                <select name="showres" id="showres" class="form-control">
                    <option value=''>All</option>
                    <option value='Accepted'>Accepted</option>
                    <option value='Wrong Answer'>Wrong Answer</option>
                    <option value='Runtime Error'>Runtime Error</option>
                    <option value='Time Limit Exceed'>Time Limit Exceed</option>
                    <option value='Memory Limit Exceed'>Memory Limit Exceed</option>
                    <option value='Output Limit Exceed'>Output Limit Exceed</option>
                    <option value='Presentation Error'>Presentation Error</option>
                    <option value='Compile Error'>Compile Error</option>
                    <option value='Restricted Function'>Restricted Function</option>
                    <option value='Waiting'>Waiting</option>
                    <option value='Judge Error'>Judge Error</option>
                </select>
            </div>
            <div class="form-group">
                <label for="showlang">Language:</label>
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
            </div>
            <button type='submit' class="btn btn-primary">Show</button>
        </form>
    </div>
    <div>
        <table class="table table-hover table-striped basetable cf" id="statustable" width="100%">
            <thead>
            <tr>
                <th width='9%'>Username</th>
                <th width='7%'>RunID</th>
                <th width='6%'>PID</th>
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

<div id="statusdialog" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog">
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
                    Problem ID: <span id="rpid"></span> <br/>
                    Share Code?
                    <div class="btn-group" id="rshare">
                        <button id="sharey" type="button" class="btn btn-info">Yes</button>
                        <button id="sharen" type="button" class="btn btn-info">No</button>
                    </div>
                    <?php if ($current_user->is_root()): ?>
                        <button id="rejudge" class="btn btn-warning">Rejudge</button>
                    <?php endif; ?>
                    <br/><b id='sharenote'>This code is shared.</b>
                </div>
                <button class="pull-right btn btn-mini btn-inverse" data-clipboard-target="dcontent" id="copybtn">Copy</button>
                <pre id="dcontent"></pre>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    var statperpage =<?= $config["limits"]["status_per_page"] ?>;
    var spstart =<?= $start ?>;
    var refrate =<?=$config["status"]["refresh_rate"]?>;
    var lim_times =<?=$config["status"]["max_refresh_times"]?>;
</script>
<script src="assets/js/ZeroClipboard.min.js"></script>
<script src="assets/js/jquery.history.js"></script>
<script type="text/javascript" src="assets/js/status.js?<?= filemtime("assets/js/status.js") ?>"></script>
<link href="assets/css/prettify.css" type="text/css" rel="stylesheet"/>
<script type="text/javascript" src="assets/js/prettify.js"></script>
<?php include_once("footer.php"); ?>
