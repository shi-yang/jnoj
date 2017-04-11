<?php
$pagetitle = "Problem Category";
include_once("header.php");
include_once("functions/problems.php");


$scate = array();
if (isset($_GET['category'])) {
    $catarr = '[{"name": "catenum", "value":"1"}, {"name": "logic", "value":"or"}, {"name":"cate0", "value":' . json_encode($_GET['category']) . '}]';
    $scate[] = htmlspecialchars(problem_get_category_name_from_id(convert_str($_GET['category'])));
} else {
    if ($_POST['logic'] == "or") $catarr = '[ {"name":"logic", "value": "or"}';
    else $catarr = '[ {"name":"logic", "value": "and"}';
    $num = 0;
    foreach ($_POST as $kkey => $value) {
        if ($kkey == "logic") continue;

        $pt = problem_get_category_parent_from_id(convert_str($value));

        if (isset($_POST["check" . $pt]) == $value) continue;
        $scate[] = htmlspecialchars(problem_get_category_name_from_id(convert_str($value)));
        $catarr .= ',{"name":"cate' . $num . '", "value":' . json_encode($value) . '}';
        $num++;
    }
    $catarr .= ',{"name":"catenum", "value":"' . $num . '"} ]';
}

?>
<h2>Selected Categories</h2>
<div class="well">
    <?= implode(" &nbsp; <b> " . htmlspecialchars(strtoupper($_POST['logic'])) . " &nbsp; </b> ", $scate) ?>
</div>
<div>
    <?php
    if ($current_user->is_valid()) {
        ?>
        <div class="btn-group">
            <button class="btn btn-info active" id="showall">All</button>
            <button class="btn btn-info" id="showunsolve">Unsolved</button>
        </div>
        <?php
    }
    ?>
    <div class="btn-group">
        <button class="btn btn-info active" id="showlocal">Local Stat</button>
        <button class="btn btn-info" id="showremote">Remote Stat</button>
        <button class="btn btn-info" id="showremu">Remote User Stat</button>
    </div>
</div>
<div id="flip-scroll">
    <table class="table table-striped table-hover basetable cf" id="problist" width="100%">
        <thead>
        <tr>
            <th width="3%"> Flag</th>
            <th width="7%"> PID</th>
            <th width="30%"> Title</th>
            <th width="28%"> Source</th>
            <th width="8%"> AC</th>
            <th width="8%"> All</th>
            <th width="8%"> AC</th>
            <th width="8%"> All</th>
            <th width="8%"> AC(U)</th>
            <th width="8%"> All(U)</th>
            <th width="10%" class="selectoj"> OJ</th>
            <th width="8%"> VID</th>
        </tr>
        </thead>
        <tbody>
        </tbody>
    </table>
</div>

<script type="text/javascript">
    var probperpage =<?= $config["limits"]["problems_per_page"] ?>;
    var pstart = 0;
    var searchstr =<?=$catarr?>;
    var ojoptions = '<?= $ojoptions?>';
</script>
<script type="text/javascript" src="js/problem_category.js?<?php echo filemtime("js/problem_category.js"); ?>"></script>
<?php
include("footer.php");
?>
