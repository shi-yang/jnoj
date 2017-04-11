<?php
$pagetitle = "Ranklist";
include_once("header.php");
include_once("functions/sidebars.php");
?>
<div id="flip-scroll" class="col-md-9">
    <!-- insert the page content here -->
    <table class="table table-hover table-striped basetable cf" id="rank" width="100%">
        <thead>
        <tr>
            <th width='10%'> Rank</th>
            <th width='20%'> Username</th>
            <th width='40%'> Nickname</th>
            <th width='10%'> Local AC</th>
            <th width='10%'> Total AC</th>
            <th width='10%'> All</th>
        </tr>
        </thead>
        <tbody>
        </tbody>
        <tfoot>
        </tfoot>
    </table>
</div>
<div class="col-md-3">
    <?= sidebar_common() ?>
</div>
<script type="text/javascript">
    var userperpage =<?=$config["limits"]["users_per_page"]?>;
</script>
<script type="text/javascript" src="assets/js/ranklist.js?<?= filemtime("assets/js/ranklist.js") ?>"></script>
<?php include_once("footer.php"); ?>
