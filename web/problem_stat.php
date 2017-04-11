<?php
include_once('functions/problems.php');
$pid = intval($_GET['pid']);
$show_problem = new Problem;
$show_problem->set_problem($pid);
if ($show_problem->is_valid() && $show_problem->get_val("hide") == 0)
    $pagetitle = "Statistics of Problem " . $pid;
else
    $pagetitle = "Problem Unavailable";
include_once("header.php");
include_once("functions/sidebars.php");
?>
<div class="col-md-8">
    <h3>Leaderboard of <a href="problem_show.php?pid=<?= $pid ?>">Problem <?= $pid ?></a></h3>
    <table class="table table-hover table-striped" id="pleader">
        <thead>
        <tr>
            <th width="10%">Rank</th>
            <th width="10%">ACs</th>
            <th width="10%">Runid</th>
            <th width="20%">Username</th>
            <th width="15%">Time</th>
            <th width="10%">Memory</th>
            <th width="15%">Language</th>
            <th width="10%">Length</th>
        </tr>
        </thead>
        <tfoot></tfoot>
        <tbody></tbody>
    </table>
</div>
<div class="col-md-4">
    <?= sidebar_problem_stat($show_problem) ?>
</div>
<script type="text/javascript">
    var ppid = '<?=$pid?>';
    var pstatperpage =<?=$config["limits"]["users_on_problem_stat"] ?>;
</script>
<script type="text/javascript" src="assets/js/highcharts.js"></script>
<script type="text/javascript" src="assets/js/problem_stat.js"></script>
<?php include("footer.php"); ?>
