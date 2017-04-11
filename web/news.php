<?php
$pagetitle = "News";
include_once("header.php");
include_once("functions/sidebars.php");
?>
<div class="col-md-9">
    <table class="table table-hover table-striped" id="newslist">
        <thead>
        <tr>
            <th width="15%">News ID</th>
            <th width="40%">Title</th>
            <th width="25%">Last Edit Time</th>
            <th width="15%">Author</th>
        </tr>
        </thead>
        <tfoot></tfoot>
        <tbody></tbody>
    </table>
</div>
<div class="col-md-3">
    <?= sidebar_common() ?>
</div>
<script type="text/javascript" src="assets/js/news.js"></script>
<?php include("footer.php"); ?>
