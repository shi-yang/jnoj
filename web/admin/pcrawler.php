<h4>Crawl a single problem/contest</h4>
<form id='pcbasic' method="get" action="ajax/admin_deal_crawl_problem.php?type=0" class="ajform form-inline">
    <label>
        OJ:
        <select class="input-medium" name="pcoj">
            <?= $ojoptions ?>
        </select>
    </label>
    <input type="text" name="pcid" placeholder="Problem/Contest    ID/Code"/>
    <button type="submit" id="spinfo" class="btn btn-primary">Crawl!</button>
    <div id="msgbox" style="display:none;clear:both"></div>
</form>
<h4>Crawl problems/contests in range</h4>
<form id='pcrange' method="get" action="ajax/admin_deal_crawl_problem.php?type=1" class="ajform form-inline">
    <label>
        OJ:
        <select class="input-medium" name="pcoj">
            <?= $ojoptions ?>
        </select>
    </label>
    <label>From: <input type="text" name="pcidfrom" placeholder="Problem/Contest    ID/Code"/></label>
    <label>To: <input type="text" name="pcidto" placeholder="Problem/Contest    ID/Code"/></label>
    <button type="submit" id="spinfo" class="btn btn-primary">Crawl!</button>
    <div id="msgbox" style="display:none;clear:both"></div>
</form>
<h4>Crawl problem stats</h4>
<form id='pcnum' method="get" action="ajax/admin_deal_crawl_problem.php?type=2" class="ajform form-inline">
    <label>
        OJ:
        <select class="input-medium" name="pcoj">
            <?= $ojoptions ?>
        </select>
    </label>
    <button type="submit" id="spinfo" class="btn btn-primary">Crawl!</button>
    <div id="msgbox" style="display:none;clear:both"></div>
</form>
