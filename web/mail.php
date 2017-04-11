<?php
include_once("functions/users.php");
include_once("functions/sidebars.php");
if ($current_user->is_valid()) $pagetitle = "Mailbox of " . $current_user->get_username();
else $pagetitle = "Mailbox Unavailable";
include_once("header.php");
?>

<div class="span9" id="flip-scroll">
    <!-- insert the page content here -->
    <?php
    if (!$current_user->is_valid()){
        ?>
        <p class="alert alert-error">Please Login!!</p>
        <?php
    } else {
    ?>
    <h3>Mailbox of <?= $current_user->get_username() ?> </h3>
    <button class="btn btn-primary" id="sendmail">New Mail</button>
    <div class="btn-group" id="mailnav">
        <button class="btn btn-info" id="showinbox">Show Inbox</button>
        <button class="btn btn-info" id="showoutbox">Show Outbox</button>
    </div>

    <table class="table table-striped table-hover basetable cf" width="100%" id="maillist">
        <thead>
        <tr>
            <th width="0%">Mail ID</th>
            <th width="15%">Sender</th>
            <th width="15%">Reciever</th>
            <th width="45%">Title</th>
            <th width="25%">Time</th>
            <th width="0%">Status</th>
        </tr>
        </thead>
        <tfoot></tfoot>
        <tbody></tbody>
    </table>
</div>
    <div class="span3">
        <?= sidebar_common() ?>
    </div>


    <div id="newmailwindow" class="modal hide fade">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h3>New Mail</h3>
        </div>
        <form action="ajax/mail_send.php" method="post" id="mailsend" class="ajform">
            <div class="modal-body">
                <input type="text" name="reciever" id="reciever" value="" class="input-medium" placeholder="Reciever"/>
                <input type="text" name="title" id="mailtitle" value="" class="input-block-level"
                       placeholder="Mail title"/>
                <textarea rows="12" name="content" id="newmailcontent" class="input-block-level"
                          placeholder="Input the content here..."
                          onKeyUp="if(this.value.length > <?= $config["limits"]["max_mail_len"] ?>) this.value=this.value.substr(0,<?= $config["limits"]["max_mail_len"] ?>)"></textarea>
            </div>
            <div class="modal-footer">
                <span id="msgbox" style="display:none"></span>
                <input name='submit' class="btn btn-primary" type='submit' value='Send'/>
                <input name='reset' class="btn btn-danger" type='reset' value='Reset'/>
            </div>
        </form>
    </div>

    <div id="mailwindow" class="modal hide fade">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h3 id="mtitle">Topic Title</h3>
        </div>
        <div class="modal-body">
            <div>
                Sent to <span id="mreciever"></span> by <span id="msender"></span> At <span id="mtime"></span>
            </div>
            <pre id="mcontent"></pre>
        </div>
        <div class="modal-footer">
            <button class="btn btn-primary replybutton" type='submit'>Reply</button>
        </div>
    </div>


<?php
}
?>
<script type="text/javascript">
    var mailperpage =<?=$config["limits"]["mails_per_page"] ?>;
</script>
<script type="text/javascript" src="assets/js/mail.js?<?php echo filemtime("assets/js/mail.js"); ?>"></script>
<?php
include("footer.php");
?>
