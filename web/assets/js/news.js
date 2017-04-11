$(document).ready(function () {
    var oTable = $('#newslist').dataTable({
        "bProcessing": true,
        "bServerSide": true,
        "sDom": '<"row"pf>rt<"row"<"col-md-8"i><"col-md-4"l>>',
        "oLanguage": {
            "sEmptyTable": "No news found.",
            "sZeroRecords": "No news found.",
            "sInfoEmpty": "No entries to show"
        },
        "sAjaxSource": "ajax/news_data.php",
        "aaSorting": [[2, 'desc']],
        "sPaginationType": "full_numbers",
        "aLengthMenu": [[25, 50, 100, 150, 200], [25, 50, 100, 150, 200]],
        "iDisplayLength": 25,
        "fnRowCallback": function (nRow, aData, iDisplayIndex, iDisplayIndexFull) {
            $(nRow).children().each(function () {
                $(this).addClass('gradeC');
            });
            return nRow;
        },
        "aoColumnDefs": [
            {
                "mRender": function (data, type, full) {
                    return "<a name='" + full[0] + "' class='newslink' href='#'>" + data + "</a>";
                },
                "aTargets": [1]
            }
        ],
        "fnDrawCallback": function () {
            $(".newslink").click(function () {
                var nnid = $(this).attr("name");
                $.get("ajax/get_news.php", {'nnid': nnid, 'rand': Math.random()}, function (data) {
                    var gval = eval("(" + data + ")");
                    if (gval.code == 0) {
                        $("#newsshowdialog #sntitle").html(gval.title);
                        $("#newsshowdialog #sncontent").html(gval.content);
                        $("#newsshowdialog #sntime").html(gval.time_added);
                        $("#newsshowdialog #snauthor").html("<a href='userinfo.php?name=" + gval.author + "'>" + gval.author + "</a>");
                        $("#newsshowdialog .newseditbutton").attr("name", gval.newsid);
                        $("#newsshowdialog #ntitle").html(gval.title);
                        $("#newsshowdialog").modal("show");
                    }
                });
                return false;
            });
        }
    });
    $("#more").addClass("active");
});