<form id="notiform" action="ajax/admin_deal_notify.php" method="post" class="ajform">
    <div class="form-group">
        <label for="notifycontent">Notify Content</label>
        <textarea id="notifycontent" name="sub" rows="10"
                  class="input-block-level form-control"><?= get_substitle() ?></textarea>
    </div>
    <div class="pull-right">
        <span id="msgbox" style="display:none"></span>
        <button class="btn btn-primary" type="submit">Change</button>
    </div>
</form>