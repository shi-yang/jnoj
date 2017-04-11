<?php
include_once __DIR__ . '/../ckeditor/ckeditor.php';
require_once __DIR__ . '/../ckfinder/ckfinder.php';
$ckeditor = new CKEditor();
$ckeditor->basePath = 'ckeditor/';
CKFinder::SetupCKEditor($ckeditor, 'ckfinder/');
?>
<form id='pload' method="get" action="#" class="form-inline">
    <input type="text" id="npid" class="form-control" placeholder='Problem ID'/>
    <button class="btn btn-primary" type="submit"> Load</button>
    <button class="btn btn-danger" type="button" onclick="resetpdetail()"> Reset</button>
</form>
<br>
<form id="pdetail" method="post" action="ajax/admin_deal_problem.php" class="form-horizontal ajform">
    <div class="form-group">
        <label for="p_id" class="col-sm-2 control-label">Problem ID</label>
        <div class="col-sm-10">
            <input type="text" class="form-control" id="p_id" name="p_id" placeholder="Problem ID" readonly="readonly">
        </div>
    </div>
    <div class="form-group">
        <label for="p_name" class="col-sm-2 control-label">Title</label>
        <div class="col-sm-10">
            <input type="text" class="form-control" id="p_name" name="p_name" placeholder="Title">
        </div>
    </div>
    <div class="form-group">
        <label for="p_hide" class="col-sm-2 control-label">Hide</label>
        <div class="col-sm-10">
            <label class="radio-inline">
                <input type="radio" name="p_hide" id="inlineRadio1" value="1"> Yes
            </label>
            <label class="radio-inline">
                <input type="radio" name="p_hide" id="inlineRadio2" value="0" checked="checked"> No
            </label>
        </div>
    </div>
    <div class="form-group">
        <label for="time_limit" class="col-sm-2 control-label">Time Limit</label>
        <div class="col-sm-10">
            <div class="input-group">
                <input type="text" class="form-control" id="time_limit" name="time_limit" placeholder="Time Limit"
                       value="1000">
                <span class="input-group-addon" id="basic-addon2">ms</span>
            </div>
        </div>
    </div>
    <div class="form-group">
        <label for="case_time_limit" class="col-sm-2 control-label">Case Time Limit</label>
        <div class="col-sm-10">
            <div class="input-group">
                <input type="text" class="form-control" id="case_time_limit" name="case_time_limit"
                       placeholder="Case Time Limit" value="1000">
                <span class="input-group-addon" id="basic-addon2">ms</span>
            </div>
        </div>
    </div>
    <div class="form-group">
        <label for="p_ignore_noc" class="col-sm-2 control-label">Only Case Limit?</label>
        <div class="col-sm-10">
            <label class="radio-inline">
                <input type="radio" name="p_ignore_noc" id="inlineRadio3" value="1"> Yes
            </label>
            <label class="radio-inline">
                <input type="radio" name="p_ignore_noc" id="inlineRadio4" value="0" checked="checked"> No
            </label>
        </div>
    </div>
    <div class="form-group">
        <label for="memory_limit" class="col-sm-2 control-label">Memory Limit</label>
        <div class="col-sm-10">
            <div class="input-group">
                <input type="text" class="form-control" id="memory_limit" name="memory_limit" placeholder="Memory Limit"
                       value="65535">
                <span class="input-group-addon" id="basic-addon2">KB</span>
            </div>
        </div>
    </div>
    <div class="form-group hide"><label>Number of Testcases</label><input type="text" name="noc" value="1"
                                                                          class="form-control"/></div>
    <div class="form-group">
        <label for="p_ignore_noc" class="col-sm-2 control-label">Special Judge</label>
        <div class="col-sm-10">
            <label class="radio-inline">
                <input type="radio" name="special_judge_status" value="2"/> JAVA
            </label>
            <label class="radio-inline">
                <input type="radio" name="special_judge_status" value="1"/> C++
            </label>
            <label class="radio-inline">
                <input type="radio" name="special_judge_status" value="0" checked="checked"/> No
            </label>
        </div>
    </div>
    <div class="form-group">
        <label for="description" class="col-sm-2 control-label">Description</label>
        <div class="col-sm-10">
            <textarea class="form-control" id="tdescription" name="description"></textarea>
        </div>
    </div>
    <div class="form-group">
        <label for="tinput" class="col-sm-2 control-label">Input</label>
        <div class="col-sm-10">
            <textarea class="form-control" id="tinput" name="input"></textarea>
        </div>
    </div>
    <div class="form-group">
        <label for="toutput" class="col-sm-2 control-label">Output</label>
        <div class="col-sm-10">
            <textarea class="form-control" id="toutput" name="output"></textarea>
        </div>
    </div>
    <div class="form-group">
        <label for="sample_in" class="col-sm-2 control-label">Sample Input</label>
        <div class="col-sm-10">
            <textarea name="sample_in" class="form-control" rows="8" style="font-family: monospace;"></textarea>
        </div>
    </div>
    <div class="form-group">
        <label for="sample_out" class="col-sm-2 control-label">Sample Output</label>
        <div class="col-sm-10">
            <textarea name="sample_out" class="form-control" rows="8" style="font-family: monospace;"></textarea>
        </div>
    </div>
    <div class="form-group">
        <label for="hint" class="col-sm-2 control-label">Hint</label>
        <div class="col-sm-10">
            <textarea name="hint" id="thint" class="form-control"></textarea>
        </div>
    </div>
    <div class="form-group">
        <label for="source" class="col-sm-2 control-label">Source</label>
        <div class="col-sm-10">
            <textarea name="source" id="source" class="form-control""></textarea>
        </div>
    </div>
    <div class="form-group">
        <label for="tags" class="col-sm-2 control-label">Tags</label>
        <div class="col-sm-10">
            <textarea name="tags" id="tags" class="form-control""></textarea>
            <p class="help-block">Examples: search, dp, brute force, data structures, constructive algorithms,
                dfs and similar, 2-sat, graphs, greedy, implementation, binary search,
                math, sorting, geometry, number theory</p>
        </div>
    </div>
    <div class="form-group">
        <label for="author" class="col-sm-2 control-label">Author</label>
        <div class="col-sm-10">
            <textarea name="author" id="author" class="form-control""></textarea>
        </div>
    </div>
    <div class="form-group">
        <div class="col-sm-offset-2 col-sm-10">
            <span id="msgbox" style="display:none"></span>
            <button class="btn btn-primary" type="submit">Submit</button>
        </div>
    </div>
</form>
<?php
$ckeditor->replace('tdescription');
$ckeditor->replace('tinput');
$ckeditor->replace('toutput');
$ckeditor->replace('thint');
$ckeditor->replace('tncontent');
?>

