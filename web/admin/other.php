<button id="spinfo" class="btn btn-danger syncbutton">Sync Problem Info</button>
<button id="suinfo" class="btn btn-danger syncbutton">Sync User Info</button>
<div class="alert alert-block" id="syncwait" style="display:none"></div>
<h4>Delete virtual replays in range</h4>
<form id='delcontest' method="get" action="ajax/admin_deal_delete_vreplay.php" class="ajform form-inline">
    From: <input type="text" name="fcid" placeholder="Contest ID"/>
    To: <input type="text" name="tcid" placeholder="Contest ID"/>
    <button type="submit" class="btn btn-danger">Delete</button>
    <div id="msgbox"></div>
</form>
<h4>Generate contest users</h4>
<form id='genuser' method="post" action="ajax/admin_deal_gen_user.php" class="ajform form-inline">
    <div class="form-group">
        For: <input type="text" name="cid" placeholder="Contest ID"/>
        <span></span>
    </div>
    <div class="form-group">
        Prefix: <input type="text" name="prefix" placeholder="Prefix"/>
        From: <input type="text" name="ufrom" placeholder="Start Number"/>
        To: <input type="text" name="uto" placeholder="End Number"/>
        <button type="submit" class="btn btn-primary" disabled="disabled">Generate</button>
    </div>
    <div id="msgbox"></div>
</form>
<h4>Re-populate passwords</h4>
<form method="post" action="ajax/admin_deal_repopulate.php" class="ajform form-inline">
    <div class="form-group">
        Prefix: <input type="text" name="prefix" placeholder="Prefix"/>
        From: <input type="text" name="ufrom" placeholder="Start Number"/>
        To: <input type="text" name="uto" placeholder="End Number"/>
        <button type="submit" class="btn btn-primary">Do</button>
    </div>
    <div id="msgbox"></div>
</form>
<h4>Re-populate password for single user</h4>
<form method="post" action="ajax/admin_deal_repopulate.php" class="ajform form-inline">
    <div class="form-group">
        Username: <input type="text" name="username" placeholder="username"/>
        <button type="submit" class="btn btn-primary">Do</button>
    </div>
    <div id="msgbox"></div>
</form>