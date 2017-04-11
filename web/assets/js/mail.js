function escapeHtml(unsafe) {
    return unsafe
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

$(document).ready(function () {

    $("#sendmail").click(function () {
        $("#newmailwindow input#reciever").val("");
        $("#newmailwindow input#mailtitle").val("New Mail");
        $("#newmailwindow textarea#newmailcontent").val("");
        $("#newmailwindow #msgbox").hide();
        $("#newmailwindow").modal("show");
    });
    $("#newmailwindow").bind("shown", function () {
        $("#newmailcontent", this).focus();
    });
    $("#mailsend").bind("correct", function () {
        window.location.reload();
    })

    var oTable = $('#maillist').dataTable({
        "bProcessing": true,
        "bServerSide": true,
        "sDom": '<"row-fluid"pf>rt<"row-fluid"<"span8"i><"span4"l>>',
        "oLanguage": {
            "sEmptyTable": "No mails found.",
            "sZeroRecords": "No mails found.",
            "sInfoEmpty": "No entries to show"
        },
        "sAjaxSource": "ajax/mail_data.php?username=" + $.cookie(cookie_prefix + 'username'),
        "aaSorting": [[0, 'desc']],
        "sPaginationType": "input",
        "aLengthMenu": [[25, 50, 100, 150, 200], [25, 50, 100, 150, 200]],
        "iDisplayLength": mailperpage,
        "aoColumnDefs": [
            {"bSortable": false, "aTargets": [1, 2, 3, 4]},
            {"bVisible": false, "aTargets": [0, 5]},
            {
                "mRender": function (data, type, full) {
                    return "<a href='userinfo.php?name=" + data + "' target='_blank'>" + data + "</a>";
                },
                "aTargets": [1, 2]
            },
            {
                "mRender": function (data, type, full) {
                    data = escapeHtml(data);
                    if (full[5] == "0") return "<a class='getmail' href='#' name='" + full[0] + "' title='" + data + "'><b>" + data + "</b></a>";
                    else return "<a class='getmail' href='#' name='" + full[0] + "' title='" + data + "'>" + data + "</a>";
                },
                "aTargets": [3]
            }
        ],
        "fnRowCallback": function (nRow, aData, iDisplayIndex, iDisplayIndexFull) {
            if (aData[5] == 0) $(nRow).addClass('info');
            return nRow;
        },
        "fnDrawCallback": function () {
            $("a.getmail").click(function () {
                var target = $("#mailwindow");
                $("#mcontent", target).html('<img src="img/ajax-loader.gif" /> Loading...');
                $("#mailwindow").modal("show");
                $.get('ajax/get_mail.php', {mailid: $(this).attr("name")}, function (data) {
                    data = eval("(" + data + ")");

                    $("#mtitle", target).html(escapeHtml(data.title));
                    $("#mcontent", target).html(escapeHtml(data.content));
                    $("#mreciever", target).html("<a href='userinfo.php?name=" + data.reciever + "' target='_blank'><b>" + data.reciever + "</b></a>");
                    $("#msender", target).html("<a href='userinfo.php?name=" + data.sender + "' target='_blank'><b>" + data.sender + "</b></a>");
                    $("#mtime", target).html(data.mail_time);

                    $(".replybutton").click(function () {

                        $("#newmailwindow input#reciever").val(data.sender);
                        $("#newmailwindow input#mailtitle").val("RE: " + data.title);
                        $("#newmailwindow textarea#newmailcontent").val("\n--------------------------------\n" + data.content);
                        $("#newmailwindow #msgbox").hide();
                        $("#mailwindow").modal("hide");
                        $("#newmailwindow").modal("show");

                    });
                });
                return false;
            });
        }
    });

    $("#showoutbox").click(function () {
        $("#mailnav .btn").removeClass("active");
        $(this).addClass("active");
        oTable.fnFilter("", 2);
        oTable.fnFilter($.cookie(cookie_prefix + 'username'), 1);
    });

    $("#showinbox").click(function () {
        $("#mailnav .btn").removeClass("active");
        $(this).addClass("active");
        oTable.fnFilter("", 1);
        oTable.fnFilter($.cookie(cookie_prefix + 'username'), 2);
    });
    $("#showinbox").click();
    $("#userspace").addClass("active");
});