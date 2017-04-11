<?php
$pagetitle = "Admin Page";
$route = $_GET['r'];
if (!isset($route)) {
    $route = 'index.php';
} else {
    switch ($route) {
        case 'notification':
        case 'problem':
        case 'problem_data':
        case 'contest':
        case 'rejudge':
        case 'replay':
        case 'news':
        case 'pcrawler':
        case 'other':
            $route .= '.php';
            break;
        default:
            $route = 'error';
    }
}
include_once("header.php");
include_once("functions/global.php");
?>
<h1>Admin Page</h1>
<div class="col-md-3">
    <ul class="nav nav-pills nav-stacked">
        <li><a href="admin_index.php?r=notification">Notification</a></li>
        <li><a href="admin_index.php?r=problem">Problem</a></li>
        <li><a href="admin_index.php?r=problem_data">Problem Test Data</a></li>
        <li><a href="admin_index.php?r=contest">Contest</a></li>
        <li><a href="admin_index.php?r=rejudge">Rejudge</a></li>
        <li><a href="admin_index.php?r=replay">Replay</a></li>
        <li><a href="admin_index.php?r=news">News</a></li>
        <li><a href="admin_index.php?r=pcrawler">Problem Crawler</a></li>
        <li><a href="admin_index.php?r=other">Others</a></li>
    </ul>
</div>
<div class="col-md-9">
    <?php
    if ($current_user->is_root() && $route != 'error') {
        include_once('admin/' . $route);
    } else {
        echo '<div class="error">Invalid Request!</div>';
    }
    ?>
</div>
<script type="text/javascript" src="assets/js/admin.js?<?= filemtime("assets/js/admin.js") ?>"></script>
<script type="text/javascript">
    $.fn.problemlist.ojoptions = "<?=addslashes($ojoptions)?>";
</script>
<?php include("footer.php"); ?>
