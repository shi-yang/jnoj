<?php
include_once("header.php");
include_once("functions/sidebars.php");
?>
    <div class="col-md-9">
        <table class="table table-striped table-hover basetable" id="contestlist">
            <thead>
            <tr>
                <th width="15%">OJ</th>
                <th width="40%">Title</th>
                <th width="25%">Start time</th>
                <th width="10%">DOW</th>
                <th width="10%">Type</th>
            </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
    <div class="col-md-3">
        <?= sidebar_common() ?>
    </div>
    <script>
        $(document).ready(function () {
            $("#more").addClass("active");
            $("#contestlist").dataTable({
                "bProcessing": true,
                "sDom": '<"row-fluid"p>rt<"row-fluid"i>',
                "sPaginationType": "full_numbers",
                "oLanguage": {
                    "sEmptyTable": "Loading...",
                    "sZeroRecords": "No contests found.",
                    "sInfoEmpty": "No entries to show"
                },
                "aaSorting": [[2, 'asc']],
                "iDisplayLength":<?= $config["limits"]["contests_per_page"] ?>
            });
            $.get("external/contests.json", function (data) {
                data = eval(data);
                //var target=$("#contestlist tbody");
                for (var i = 0; i < data.length; i++) {
                    $("#contestlist").dataTable().fnAddData([
                        data[i].oj,
                        "<a href='" + data[i].link + "' target='_blank'>" + data[i].name + "</a>",
                        data[i].start_time,
                        data[i].week,
                        (data[i].access == "" ? "Public" : data[i].access)]
                    );
                    //$(target).append("<tr>"+"<td>"+data[i].oj+"</td>"+"<td><a href='"+data[i].url+"' target='_blank'>"+data[i].name+"</a></td>"+"<td>"+data[i].start_time+"</td>"+"<td>"+data[i].week+"</td>"+"<td>"+(data[i].access==""?"Public":data[i].access)+"</td>"+"</tr>")
                }
                $("#contestlist_processing").hide();
            });
        });
    </script>
<?php include_once("footer.php"); ?>