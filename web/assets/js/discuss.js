function escapeHtml(unsafe) {
    return unsafe
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}


var dishtml;
function showlist(data, isfirst) {
    dishtml += "<ul" + (isfirst ? "" : " class='hide'") + ">";
    if (data) for (var i = 0; i < data.length; i++) {
        dishtml += "<li>" + (isfirst ? "<button class='btn btn-mini texpand'" + (data[i].child_num != 0 ? "" : " disabled") + "><i class='icon-plus'></i></button><button class='btn btn-mini hide thide'><i class='icon-minus'></i></button>" : "") +
            " <a href='#' class='topicshow' name='" + data[i].id + "'>" + escapeHtml(data[i].title) + "</a> <span style='font-size:smaller'> (" + data[i].content_length + " bytes) </span>" +
            " <a href='userinfo.php?name=" + data[i].uname + "' target='_blank'>" + data[i].uname + "</a> " + data[i].time +
            (isfirst ? (" <b>" + (data[i].pid == 0 ? "General Topic" : ("<a target='_blank' href='problem_show.php?pid=" + data[i].pid + "'>Problem " + data[i].pid + "</a>")) + "</b>") : "");
        if (data[i].child_num != 0) showlist(data[i].child, false);
        dishtml += "</li>";
    }
    ;
    dishtml += "</ul>";
}

var showtfunc = function () {
    var target = $("#showtopic");
    $("#tcontent", target).html('<img src="assets/img/ajax-loader.gif" /> Loading...');
    $.get("ajax/topic_data.php", {id: $(this).attr('name'), randomid: Math.random()}, function (data) {
        data = eval("(" + data + ")");
        tdata = [data];
        dishtml = "";
        showlist(tdata, false);
        data = data.vis_sub;

        $("#tdetail", target).html(dishtml);
        $("#tdetail a[name='" + data.id + "']", target).wrap("<b>").wrap("<i>");
        $("#tdetail ul", target).show();
        $("#ttime", target).html(escapeHtml(data.time));
        $("#ttitle", target).html(escapeHtml(data.title));
        $("#tcontent", target).html(escapeHtml(data.content));
        $("#tuser", target).html("<a href='userinfo.php?name=" + data.uname + "' target='_blank'><b>" + data.uname + "</b></a>");
        data.pid == 0 ? $("#tproblem", target).html("<b>General Topic</b>") : $("#tproblem", target).html("<a href='problem_show.php?pid=" + data.pid + "' target='_blank'><b>Problem " + data.pid + "</b></a>");
        $("input[name='title']", target).val("RE: " + data.title);
        $("#replybox").attr("action", "ajax/topic_reply.php?pid=" + data.pid + "&rid=" + data.rid + "&id=" + data.id);

        $("a.topicshow", target).click(showtfunc);
    });
    target.modal("show");
    return false;
}


var disfunc = function (data) {
    data = eval("(" + data + ")");
    dishtml = "";
    showlist(data, true);
    $("#dcontent").html(dishtml);

    $(".texpand").click(function () {
        $(this).hide();
        $(this).next().show();
        $("ul", $(this).parent()).show("blind");
        return false;
    });
    $(".thide").click(function () {
        $(this).hide();
        $(this).prev().show();
        $("ul", $(this).parent()).hide("blind");
        return false;
    });


    $(".dcontrol .button").removeAttr("disabled");
    $(".dcontrol .button").removeClass("ui-state-disabled");
    if (curr_page > 0) {
        $("#disprev").show();
        $("#disfirst").show();
    }
    $("a.topicshow").click(showtfunc);

}


$(document).ready(function () {
    $("#discuss").addClass("active");
    $.get("ajax/discuss_data.php", {pid: ppid, page: curr_page, randomid: Math.random()}, disfunc);


    $("#disnew").click(function () {
        $("#newtopic").modal("show");
        return false;
    });
    $("#newtopic").bind("shown", function () {
        $("input[name='title']", this).focus();
    });
    $("#newtopicform,#replybox").bind("correct", function () {
        window.location.href = "discuss.php";
    });
});
