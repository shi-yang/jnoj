function escapeHtml(unsafe) {
    return unsafe
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}
$(document).ready(function () {
    var oTable = $('#contestlist').dataTable({
        "bProcessing": true,
        "bServerSide": true,
        //"sDom": '<"row"pf>rt<"row"<"col-md-8"i><"col-md-4"l>>',
        "oLanguage": {
            "sEmptyTable": "No contests found.",
            "sZeroRecords": "No contests found.",
            "sInfoEmpty": "No entries to show"
        },
        "pagingType": 'full_numbers',
        "sAjaxSource": "ajax/contest_data.php",
        "aaSorting": [[2, 'desc']],
        "aLengthMenu": [[25, 50, 100, 150, 200], [25, 50, 100, 150, 200]],
        "iDisplayLength": conperpage,
        "iDisplayStart": 0,
        "aoColumnDefs": [
            {"bSortable": false, "aTargets": [4, 5]},
            {"bVisible": false, "aTargets": [6, 7, 8]},
            {
                "mRender": function (data, type, full) {
                    return "<a href='contest_show.php?cid=" + full[0] + "' title='" + escapeHtml(striptags(data)) + "'>" + data + "</a>";
                },
                "aTargets": [1]
            },
            {
                "mRender": function (data, type, full) {
                    return "<a href='contest_show.php?cid=" + data + "'>" + data + "</a>";
                },
                "aTargets": [0]
            },
            {
                "mRender": function (data, type, full) {
                    if (data != "") return "<a href='userinfo.php?name=" + data + "' target='_blank'>" + data + "</a>";
                    else return "-";
                },
                "aTargets": [6]
            }
        ],
        "fnRowCallback": function (nRow, aData, iDisplayIndex, iDisplayIndexFull) {
            // if (striptags(aData[4])=="Passed") $(nRow).addClass('success');
            // else if (striptags(aData[4])=="Running"||striptags(aData[4])=="Challenging") $(nRow).addClass('error');
            // else $(nRow).addClass('info')
            $("td:eq(0)", nRow).html("<a href='contest_show.php?cid=" + aData[0] + "'>" + aData[0] + "</a>");
            return nRow;
        }
    });

    $("#contest").addClass("active");

    $("#arrangevirtual").click(function () {
        $("#arrangevdialog").modal("show");
    });

    $("#arrangevdialog").bind("shown", function () {
        $("input[name='title']", this).focus();
    });

    if ($.cookie(cookie_prefix + "username") != null) $("#arrangevirtual").show();

    $("#arrangeform").bind("correct", function () {
        window.location.href = "contest.php?virtual=1";
    });

    $("input[name='ctype']").change(function () {
        var ctp = $(this).val();
        $("#probs").problemlist("settype", ctp);
    });

    $("#showall").click(function () {
        $(".btn", $(this).parent()).removeClass("active");
        $(this).addClass("active");
        oTable.fnSetColumnVis(6, true);
        oTable.fnFilter('', 7);
    });
    $("#showstandard").click(function () {
        $(".btn", $(this).parent()).removeClass("active");
        $(this).addClass("active");
        oTable.fnSetColumnVis(6, false);
        oTable.fnFilter('0', 7);
    });
    $("#showvirtual").click(function () {
        $(".btn", $(this).parent()).removeClass("active");
        $(this).addClass("active");
        oTable.fnSetColumnVis(6, true);
        oTable.fnFilter('1', 7);
    });

    $("#showtall").click(function () {
        $(".btn", $(this).parent()).removeClass("active");
        $(this).addClass("active");
        oTable.fnFilter('', 5);
    });
    $("#showtpublic").click(function () {
        $(".btn", $(this).parent()).removeClass("active");
        $(this).addClass("active");
        oTable.fnFilter('0', 5);
    });
    $("#showtprivate").click(function () {
        $(".btn", $(this).parent()).removeClass("active");
        $(this).addClass("active");
        oTable.fnFilter('1', 5);
    });
    $("#showtpassword").click(function () {
        $(".btn", $(this).parent()).removeClass("active");
        $(this).addClass("active");
        oTable.fnFilter('2', 5);
    });

    $("#showcall").click(function () {
        $(".btn", $(this).parent()).removeClass("active");
        $(this).addClass("active");
        oTable.fnFilter('', 8);
    });
    $("#showcicpc").click(function () {
        $(".btn", $(this).parent()).removeClass("active");
        $(this).addClass("active");
        oTable.fnFilter('0', 8);
    });
    $("#showccf").click(function () {
        $(".btn", $(this).parent()).removeClass("active");
        $(this).addClass("active");
        oTable.fnFilter('1', 8);
    });
    $("#showcreplay").click(function () {
        $(".btn", $(this).parent()).removeClass("active");
        $(this).addClass("active");
        oTable.fnFilter('99', 8);
    });
    $("#showcnonreplay").click(function () {
        $(".btn", $(this).parent()).removeClass("active");
        $(this).addClass("active");
        oTable.fnFilter('-99', 8);
    });


    if (cshowtype === '0') {
        $("#showcicpc").click();
    }
    else if (cshowtype == 1) {
        $("#showccf").click();
    }
    else if (cshowtype == 99) {
        $("#showstandard").click();
        $("#showcreplay").click();
    }
    else {
        $("#showstandard").click();
        $("#showcnonreplay").click();
    }

    $("#probs").problemlist();
    if (getURLPara("open") == 1) $("#arrangevdialog").modal("show");
    if (getURLPara("cid") != null) {
        var cid = getURLPara("cid");
        $("#probs").problemlist('loadcontest', cid);
    }
    if (getURLPara("virtual") == 1) {
        $("#showvirtual").click();
    }

});
