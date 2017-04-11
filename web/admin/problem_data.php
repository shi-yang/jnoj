<form id='problem_test_data' method="get" action="#" class="form-inline">
    <input type="text" id="dpid" name="p_id" class="form-control" placeholder='Problem ID'/>
    <button class="btn btn-primary" type="submit"> Load</button>
    <button class="btn btn-danger" type="button" onclick="resetpdetail()"> Reset</button>
</form>
<form class="form-horizontal ajform" id="upload_data_form" action="ajax/admin_deal_uploadfiles.php?action=upload"
      enctype="multipart/form-data">
    <div class="form-group">
        <label for="p_id" class="col-sm-2 control-label">Problem ID</label>
        <div class="col-sm-10">
            <input type="text" class="form-control" id="p_id" name="p_id" placeholder="Problem ID" readonly="readonly">
        </div>
    </div>
    <div class="form-group">
        <label for="exampleInputFile" class="col-sm-2 control-label">Input and Output Files</label>
        <div class="col-sm-10">
            <div class="row">
                <div class="col-sm-12">
                    <input id="fileupload" type="file" name="files">
                    <br>
                    <span id="msgbox" style="display:none"></span>
                    <button class="btn btn-default" type="submit">Submit</button>
                </div>
            </div>
        </div>
    </div>
</form>
<div class="col-sm-12">
    <div id="files" class="files"></div>
</div>
