<?php
include_once __DIR__ . '/../ckeditor/ckeditor.php';
$ckeditor = new CKEditor();
$ckeditor->basePath = 'ckeditor/';
?>
<form id='nload' method="get" action="#" class="ajform form-inline">
    <input type="text" id="nnid" class="input-medium" placeholder='News ID'/>
    <button class="btn btn-primary" type="submit"> Load</button>
    <button class="btn btn-danger" type="button" onclick="resetndetail()"> Reset</button>
</form>
<h4>News Information</h4>
<form id='ndetail' method="post" action="ajax/admin_deal_news.php" class="ajform form-inline">
    <table style="width:100%;" class="table table-condensed table-bordered">
        <tr>
            <td class="span3">News ID</td>
            <td><input type="text" readonly="readonly" name="newsid"/></td>
        </tr>
        <tr>
            <td>Title</td>
            <td><input type="text" name="title" class="input-xxlarge"/></td>
        </tr>
        <tr>
            <td>Content</td>
            <td><textarea id="tncontent" name="content" class="input-block-level" rows="8"></textarea></td>
        </tr>
    </table>
    <div class="pull-right">
        <span id="msgbox" style="display:none"></span>
        <button class="btn btn-primary" type="submit">Submit</button>
    </div>
</form>
<?php
$ckeditor->replace('tncontent');
?>
