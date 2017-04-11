<form id="crej" method='get' action="#" class="ajform form-horizontal">
    <fieldset>
        <legend>Rejudge Problem in Contest</legend>
        <div class="control-group">
            <label class="control-label" for="rejcid">Contest ID</label>
            <div class="controls">
                <input type="text" id="rejcid" placeholder="Contest ID"/>
            </div>
        </div>
        <div class="control-group">
            <label class="control-label" for="rejpid">Problem ID</label>
            <div class="controls">
                <input type="text" id="rejpid" placeholder="Problem ID"/>
            </div>
        </div>
        <div class="control-group">
            <label class="control-label">Rejudge AC?</label>
            <div class="controls">
                <label class="radio inline">
                    <input type="radio" name="rejac" value="1"/> Yes
                </label>
                <label class="radio inline">
                    <input type="radio" name="rejac" value="0" checked="checked"/> No
                </label>
            </div>
        </div>
    </fieldset>
    <div class="control-group">
        <div class="controls">
            <button type="submit" class="btn btn-primary">Rejudge</button>
            <span id="msgbox" style="display:none"></span>
        </div>
    </div>
</form>
<form id="cprej" method='get' action="#" class="ajform form-horizontal">
    <fieldset>
        <legend>Rejudge Problem in Contest (Using Label)</legend>
        <div class="control-group">
            <label class="control-label" for="rcid">Contest ID</label>
            <div class="controls">
                <input type="text" id="rcid" placeholder="Contest ID"/>
            </div>
        </div>
        <div class="control-group">
            <label class="control-label" for="rpid">Problem Label</label>
            <div class="controls">
                <input type="text" id="rpid" placeholder="Problem Label"/>
            </div>
        </div>
        <div class="control-group">
            <label class="control-label">Rejudge AC?</label>
            <div class="controls">
                <label class="radio inline">
                    <input type="radio" name="rac" value="1"/> Yes
                </label>
                <label class="radio inline">
                    <input type="radio" name="rac" value="0" checked="checked"/> No
                </label>
            </div>
        </div>
    </fieldset>
    <div class="control-group">
        <div class="controls">
            <button type="submit" class="btn btn-primary">Rejudge</button>
            <span id="msgbox" style="display:none"></span>
        </div>
    </div>
</form>
<form id="runrej" method='post' action="#" class="ajform form-horizontal">
    <fieldset>
        <legend>Rejudge Run</legend>
        <div class="input-append">
            <input type="text" id="runid" placeholder="Run ID"/>
            <button type="submit" class="btn btn-primary">Rejudge</button>
        </div>
        <span id="msgbox" style="display:none"></span>
    </fieldset>
</form>
<form id="cha_crej" method='get' action="#" class="ajform form-horizontal">
    <fieldset>
        <legend>Rejudge All Challenges in Contest</legend>
        <div class="input-append">
            <input type="text" id="rcha_cid" placeholder="Contest ID"/>
            <button type="submit" class="btn btn-primary">Rejudge</button>
        </div>
        <span id="msgbox" style="display:none"></span>
    </fieldset>
</form>