<form id="replaycrawl" method="get" action="ajax/admin_deal_crawl_replay.php" class="form-inline">
    <h4>Auto Crawl</h4>
    <label>OJ: <select name="oj" id="vcojname" class="input-medium">
            <option value="HUSTV">HUST Vjudge</option>
            <option value="ZJU">ZJU</option>
            <option value="UESTC">UESTC</option>
            <option value="UVA">UVA</option>
            <option value="OpenJudge">OpenJudge</option>
            <option value="SCU">SCU</option>
            <option value="HUST">HUST</option>
            <option value="CFGYM">CodeForcesGym</option>
        </select>
    </label>
    <div class="input-append">
        <input name="cid" id="vcid" type="text" class="input-small" placeholder="Contest ID"/>
        <button class="btn btn-primary">Crawl!</button>
    </div>
    <span id="msgbox" style="display:none"></span>
</form>
<form id="replaycrawlall" method="get" class="ajform form-inline" action="ajax/admin_crawl_hust_all.php">
    <h4>Crawl All HUSTV Contests</h4>
    <div class="input-append">
        <input type="text" name="cid" placeholder="Contest ID" class="input-medium">
        <button class="btn btn-danger">Crawl!</button>
    </div>
    <div id="msgbox" style="display:none;clear:both"></div>
</form>
<form id='replayform' method='post' class="ajform form-horizontal" action="ajax/admin_deal_replay.php"
      enctype="multipart/form-data">
    <h4>Replay Contest Information</h4>
    <table style="width:100%" class="table table-bordered table-condensed">
        <tr>
            <td class="span3">Contest Name</td>
            <td><input type="text" name="name" class="input-xxlarge"/></td>
        </tr>
        <tr>
            <td>Description</td>
            <td><textarea name="description" class="input-block-level" rows="8"></textarea></td>
        </tr>
        <tr>
            <td>Start Time</td>
            <td><input type="text" name="start_time" value='<?= date("Y-m-d") . " 09:00:00" ?>'/></td>
        </tr>
        <tr>
            <td>End Time</td>
            <td><input type="text" name="end_time" value='<?= date("Y-m-d") . " 14:00:00" ?>'/></td>
        </tr>
        <tr>
            <td>Submit Frequency</td>
            <td><input type="text" name="sfreq" value="180" class="input-small"/> seconds (Minimum 10)</td>
        </tr>
        <tr>
            <td>Standing File</td>
            <td><input type="file" name="file" id="file"/></td>
        </tr>
        <tr>
            <td>Or Standing URL</td>
            <td><input name="repurl" id="repurl" type="text" class="input-block-level"/></td>
        </tr>
        <tr>
            <td>File Type</td>
            <td>
                <label class="radio inline"><input type="radio" name="ctype" value="hdu" checked="checked"/> HDU
                    Excel</label>
                <label class="radio inline"><input type="radio" name="ctype" value="myexcel"/> My Excel</label>
                <label class="radio inline"><input type="radio" name="ctype" value="licstar"/> licstar 2011 version
                    (Zlinkin)</label>
                <label class="radio inline"><input type="radio" name="ctype" value="ctu"/> CTU Submits</label>
                <label class="radio inline"><input type="radio" name="ctype" value="ural"/> Ural</label>
                <label class="radio inline"><input type="radio" name="ctype" value="zju"/> ZJU Excel</label>
                <label class="radio inline"><input type="radio" name="ctype" value="jhinv"/> Jinhua</label>
                <label class="radio inline"><input type="radio" name="ctype" value="zjuhtml"/> ZJU HTML</label>
                <label class="radio inline"><input type="radio" name="ctype" value="neerc"/> NEERC</label>
                <label class="radio inline"><input type="radio" name="ctype" value="2011shstatus"/> 2011 Shanghai Status</label>
                <label class="radio inline"><input type="radio" name="ctype" value="pc2sum"/> PC<sup>2</sup>
                    Summary</label>
                <label class="radio inline"><input type="radio" name="ctype" value="pc2run"/> PC<sup>2</sup>
                    Runs</label>
                <label class="radio inline"><input type="radio" name="ctype" value="fdulocal2012"/> FDU Local
                    2012</label>
                <label class="radio inline"><input type="radio" name="ctype" value="uestc"/> UESTC</label>
                <label class="radio inline"><input type="radio" name="ctype" value="hustvjson"/> HUST VJudge
                    JSON</label>
                <label class="radio inline"><input type="radio" name="ctype" value="fzuhtml"/> FZU HTML</label>
                <label class="radio inline"><input type="radio" name="ctype" value="usuhtml"/> USU HTML</label>
                <label class="radio inline"><input type="radio" name="ctype" value="sguhtml"/> SGU HTML</label>
                <label class="radio inline"><input type="radio" name="ctype" value="amt2011"/> Amritapuri 2011</label>
                <label class="radio inline"><input type="radio" name="ctype" value="nwerc"/> NWERC</label>
                <label class="radio inline"><input type="radio" name="ctype" value="ncpc"/> NCPC</label>
                <label class="radio inline"><input type="radio" name="ctype" value="uva"/> UVA</label>
                <label class="radio inline"><input type="radio" name="ctype" value="gcpc"/> GCPC</label>
                <label class="radio inline"><input type="radio" name="ctype" value="phuket"/> Phuket</label>
                <label class="radio inline"><input type="radio" name="ctype" value="spacific"/> South Pacific</label>
                <label class="radio inline"><input type="radio" name="ctype" value="icpcinfostatus"/> ACMICPC info
                    Status</label>
                <label class="radio inline"><input type="radio" name="ctype" value="icpccn"/> ACMICPC.cn Board</label>
                <label class="radio inline"><input type="radio" name="ctype" value="spoj"/> SPOJ</label>
                <label class="radio inline"><input type="radio" name="ctype" value="openjudge"/> OpenJudge</label>
                <label class="radio inline"><input type="radio" name="ctype" value="scu"/> SCU</label>
                <label class="radio inline"><input type="radio" name="ctype" value="hust"/> HUST</label>
                <label class="radio inline"><input type="radio" name="ctype" value="cfgym"/> CodeForcesGym</label>
            </td>
        </tr>
        <tr>
            <td>Extra Information:</td>
            <td><input type="text" name="extrainfo" class="input-xlarge"/></td>
        </tr>
        <tr>
            <td>Virtual? :</td>
            <td>
                <label class="radio inline"><input type="radio" name="isvirtual" value="0" checked="checked"/>
                    No</label>
                <label class="radio inline"><input type="radio" name="isvirtual" value="1"/> Yes</label>
            </td>
        </tr>
    </table>
    <h4>Select Problems</h4>
    <div class="input-append">
        <input type='text' id="vclcid" id="appendedInput" class="input-small" placeholder="CID"/>
        <button class="btn btn-primary" type="button" id="vclonecid">Clone</button>
    </div>
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <div class="input-append">
        <input type='text' id="vclsrc" id="appendedInput" class="input-large" placeholder="Source"/>
        <button class="btn btn-primary" type="button" id="vclonesrc">Clone</button>
    </div>
    <p><b>Fill in order. Leave Problem ID blank if not exists.</b></p>
    <div id="vprobs" class="con_probs"></div>
    <div class="pull-right">
        <button class="btn btn-primary" type="submit">Submit</button>
    </div>
    <div id="msgbox" style="display:none;clear:both"></div>
</form>