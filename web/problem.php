<?php
$pagetitle = "Problem List";
include_once("header.php");
if (isset($_GET["page"]) && $_GET["page"] != "")
    $stp = intval(convert_str($_GET["page"])) - 1;
else
    $stp = "0";
?>
<div class="col-md-12">
    <!-- insert the page content here -->
    <div class="col-md-12">
        <?php if ($current_user->is_valid()): ?>
            <div class="btn-group">
                <button class="btn btn-info active" id="showall">All</button>
                <button class="btn btn-info" id="showunsolve">Unsolved</button>
            </div>
        <?php endif; ?>
    </div>
    <div id="flip-scroll">
        <table class="table table-striped table-hover cf basetable" id="problist" width="100%">
            <thead>
            <tr>
                <th width="6%"> Flag</th>
                <th width="8%"> PID</th>
                <th width="35%"> Title</th>
                <th width="25%"> Tags</th>
                <th width="13%"> AC / All</th>
                <th width="13%" class="selectoj"> OJ</th>
            </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>
</div>
<script type="text/javascript">
    var probperpage =<?= $config["limits"]["problems_per_page"] ?>;
    var pstart =<?= $stp ?>;
    var searchstr =<?= json_encode(isset($_GET['search']) ? $_GET['search'] : "") ?>;
    var ojoptions = '<?= $ojoptions ?>';
</script>
<script type="text/javascript" src="assets/js/problem.js?<?= filemtime("assets/js/problem.js") ?>"></script>
<?php include("footer.php"); ?>
