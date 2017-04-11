<?php
include_once('functions/global.php');
include_once('functions/sidebars.php');

$proid = intval($_GET['pid']);
$page = intval(convert_str($_GET['page']));
if ($page < 1) $page = 1;
$pagetitle = "Discuss";
if ($proid != "") $pagetitle = $pagetitle . " For Problem " . $proid;
include("header.php");
?>
<div class="col-md-9">
    <div id='dcontent'>
        <div class="tcenter"><img src="assets/img/ajax-loader.gif"/>Loading...</div>
    </div>
    <div class="dcontrol tcenter">
        <div class="btn-group">
            <a href='discuss.php?page=1&pid=<?= $proid ?>' class="btn" id='disfirst'>First</a>
            <a href='discuss.php?page=<?= $page - 1 ?>&pid=<?= $proid ?>' class="btn" id='disprev'>Prev</a>
            <a href='#' class="btn btn-primary" id='disnew'>New Topic</a>
            <a href='discuss.php?page=<?= $page + 1 ?>&pid=<?= $proid ?>' class="btn" id='disnext'>Next</a>
        </div>
    </div>
</div>
<div class="col-md-3">
    <?= sidebar_common() ?>
</div>

<div id="newtopic" class="modal fade">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h3>New Topic</h3>
            </div>
            <form id="newtopicform" action="ajax/topic_new.php?pid=<?= $proid ?>" method="post" class="ajform">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="title">Topic Title</label>
                        <input type="text" class="form-control" name="title" id="title" placeholder="Topic Title">
                    </div>
                    <div class="form-group">
                        <label for="content">Content</label>
                        <textarea class="form-control" name="content" placeholder="Enter your content here"
                                  rows="8"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <span id="msgbox" style="display:none"></span>
                    <input class="btn btn-primary" type="submit" name="name" value="Post"/>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="showtopic" class="modal fade">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h3 id="ttitle">Topic Title</h3>
            </div>
            <div class="modal-body">
                <div>
                    <span id="ttime"></span> by <span id="tuser"></span> At <span id="tproblem"></span>
                </div>
                <pre id="tcontent"></pre>
                <div id="tdetail"></div>
                <form id="replybox" action="#" method="post" class="ajform">
                    <div class="form-group">
                        <label for="title">Title</label>
                        <input type="text" name="title" class="form-control" placeholder="Reply Title">
                    </div>
                    <div class="form-group">
                        <label for="content">Content</label>
                        <textarea name="content" placeholder="Enter your reply here" rows="4"
                                  class="form-control"></textarea>
                    </div>
                    <span id="msgbox" style="display:none"></span>
                    <input class="btn btn-primary" type="submit" name="name" value="Post"/>
                </form>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    var ppid = '<?= $proid ?>';
    var curr_page = '<?= $page ?>';
</script>
<script type="text/javascript" src="assets/js/discuss.js?<?= filemtime("assets/js/discuss.js") ?>"></script>
<?php include("footer.php"); ?>
