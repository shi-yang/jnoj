//$(document).ready(function() {
function fnCreateSelect() {
    return '<select class="span12"><option value="">All</option>' + ojoptions + '</select>';
}
function escapeHtml(unsafe) {
    return unsafe
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}
$(document).ready(function () {
    var oTable = $('#problist').dataTable({
        "bProcessing": true,
        "bServerSide": true,
        "sDom": '<"row-fluid"pf>rt<"row-fluid"<"span8"i><"span4"l>>',
        //        "bStateSave": true,
        //        "sCookiePrefix": "bnu_datatable_problemlist_",
        //        "sDom": '<"H"pf>rt<"F"il>',
        "oLanguage": {
            "sEmptyTable": "No problems found.",
            "sZeroRecords": "No problems found.",
            "sInfoEmpty": "No entries to show"
        },
        "sAjaxSource": "ajax/problem_category_data.php",
        "aaSorting": [[1, 'asc']],
        "sPaginationType": "input",
        "aLengthMenu": [[25, 50, 100, 150, 200], [25, 50, 100, 150, 200]],
        "iDisplayLength": probperpage,
        "iDisplayStart": pstart,
        "fnServerParams": function (aoData) {
            var x;
            for (x in searchstr) aoData.push(searchstr[x]);
        },
        "aoColumnDefs": [
            {"bSortable": false, "aTargets": [0, 10]},
            {"bVisible": false, "aTargets": [6, 7, 8, 9]},
            {
                "mRender": function (data, type, full) {
                    return "<a href='status.php?showpid=" + full[1] + "&showres=Accepted'>" + full[4] + "</a>";
                },
                "aTargets": [4]
            },
            {
                "mRender": function (data, type, full) {
                    return "<a href='status.php?showpid=" + full[1] + "'>" + full[5] + "</a>";
                },
                "aTargets": [5]
            },
            {
                "mRender": function (data, type, full) {
                    return "<a href='problem_show.php?pid=" + full[1] + "' title='" + escapeHtml(full[2]) + "' >" + full[2] + "</a>";
                },
                "aTargets": [2]
            },
            {
                "mRender": function (data, type, full) {
                    return "<a class='source_search' href='#' title='" + escapeHtml(data) + "'>" + data + "</a>";
                },
                "aTargets": [3]
            },
            {
                "mRender": function (data, type, full) {
                    return "<a href='problem_show.php?pid=" + data + "'>" + data + "</a>";
                },
                "aTargets": [1]
            },
            {
                "mRender": function (data, type, full) {
                    if (data == "Yes") return "<span class='ac'>" + data + "</span>";
                    if (data == "No") return "<span class='wa'>" + data + "</span>";
                    return data;
                },
                "aTargets": [0]
            }
        ],
        "fnRowCallback": function (nRow, aData, iDisplayIndex, iDisplayIndexFull) {
            // if (aData[0]=="Yes") $(nRow).addClass('success');
            // else if (aData[0]=="No") $(nRow).addClass('error');
            return nRow;
        },
        "fnDrawCallback": function () {
            $(".source_search").each(function (i) {
                $(this).click(function () {
                    oTable.fnFilter($(this).text());
                    return false;
                });
            });
        }
    });

    $(".selectoj").each(function (i) {
        this.innerHTML = fnCreateSelect();
        $('select', this).change(function () {
            var sel = $(this).val();
            $("#showallp").show();
            $("#showlocalp").show();
            $(".selectoj select").val(sel);
            oTable.fnFilter(sel, 10);
        });
    });

    //    new FixedHeader( oTable );

    //} );

    $("#showunsolve").click(function () {
        $(".btn", $(this).parent()).removeClass("active");
        $(this).addClass("active");
        oTable.fnFilter("1", 0);
        return false;
    });

    $("#showall").click(function () {
        $(".btn", $(this).parent()).removeClass("active");
        $(this).addClass("active");
        oTable.fnFilter("0", 0);
        return false;
    });

    $("#showremote").click(function () {
        $(".btn", $(this).parent()).removeClass("active");
        $(this).addClass("active");
        oTable.fnSetColumnVis(4, false, false);
        oTable.fnSetColumnVis(5, false, false);
        oTable.fnSetColumnVis(8, false, false);
        oTable.fnSetColumnVis(9, false, false);
        oTable.fnSetColumnVis(6, true, false);
        oTable.fnSetColumnVis(7, true);
        return false;
    });

    $("#showlocal").click(function () {
        $(".btn", $(this).parent()).removeClass("active");
        $(this).addClass("active");
        oTable.fnSetColumnVis(6, false, false);
        oTable.fnSetColumnVis(7, false, false);
        oTable.fnSetColumnVis(8, false, false);
        oTable.fnSetColumnVis(9, false, false);
        oTable.fnSetColumnVis(4, true, false);
        oTable.fnSetColumnVis(5, true);
        return false;
    });

    $("#showremu").click(function () {
        $(".btn", $(this).parent()).removeClass("active");
        $(this).addClass("active");
        oTable.fnSetColumnVis(6, false, false);
        oTable.fnSetColumnVis(7, false, false);
        oTable.fnSetColumnVis(4, false, false);
        oTable.fnSetColumnVis(5, false, false);
        oTable.fnSetColumnVis(8, true, false);
        oTable.fnSetColumnVis(9, true);
        return false;
    });

    $("#problem").addClass("active");
    //$("#problist tr th:nth-child(1)").hide();
});