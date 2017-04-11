var refr;
var rtimes = 0;

var showname = getURLPara('showname');
var showpid = getURLPara('showpid');
var showres = getURLPara('showres');
var showlang = getURLPara('showlang');
var showpage = getURLPara('showpage');
if (showpage == null || parseInt(showpage) == NaN) showpage = 1;
else showpage = parseInt(showpage);

var ceclick = function () {
    var row = $(this).parent().parent();
    var runid = $(this).attr("runid");
    $("#statusdialog h3").text("Compile Info of Run " + runid);
    $("#statusdialog #dcontent").html('<img src="assets/img/ajax-loader.gif" /> Loading...');
    $("#statusdialog #rcontrol,#statusdialog #copybtn").hide();
    $("#statusdialog").modal("show");
    $.get('ajax/get_ceinfo.php', {runid: $(this).attr("runid")}, function (data) {
        data = eval("(" + data + ")");
        $("#statusdialog #dcontent").removeClass().html(data.msg);
    });
    return false;
}

function updateResult() {
    if (lim_times != -1 && rtimes >= lim_times) {
        $(".fetching img").remove();
        $(".fetching").removeClass('fetching');
        return;
    }
    rtimes++;
    $(".fetching").each(function () {
        var tres = $.trim(striptags($("td:eq(3)", this).html()));
        if (tres.substr(0, 4) != "Judg" && tres != "Rejudging" && tres != "Waiting") $(this).removeClass('fetching');
        else {
            runid = striptags($("td:eq(1)", this).html());
            var crow = $(this);
            $.get("ajax/get_run_result.php", {"runid": runid, randomid: Math.random()}, function (data) {
                data = eval("(" + data + ")");
                $(crow).removeClass('fetching');
                var currr = $.trim($("td:eq(3)", crow).text());

                if (currr.substr(0, 4) != "Judg" && currr != "Rejudging" && currr != "Waiting") return;

                if (data.code != 0) $("img", crow).remove();
                else {
                    var visres = "<span class='" + get_short(data.result) + "'>" + data.result + "</span>";
                    if (data.result.substr(0, 7) == "Compile") visres = "<a href='#' class='ceinfo' runid='" + data.runid + "'>" + visres + "</a>";
                    $("td:eq(3)", crow).html(visres);
                    $("a.ceinfo", crow).click(ceclick);

                    if (data.result == "Waiting" || data.result == "Rejudging" || data.result == "Judging") {
                        $(crow).addClass('fetching');
                        $("td:eq(3)", crow).append(" <img src='assets/img/select2-spinner.gif' />");
                        return;
                    }

                    $("td:eq(5)", crow).html(data.time_used === null ? "" : data.time_used + " ms");
                    $("td:eq(6)", crow).html(data.memory_used === null ? "" : data.memory_used + " KB");


                    var tres = data.result;

                    if (tres.substr(0, 7) == "Compile") $(crow).removeClass().addClass('info');
                    else if (tres.substr(0, 4) == "Judg" || tres == "Rejudging" || tres == "Waiting") $(crow).removeClass().addClass('warning');
                    else if (tres != "Accepted" && tres.substr(0, 7) != "Pretest") $(crow).removeClass().addClass('error');
                    else $(crow).removeClass().addClass('success');
                }
            });
        }
    });
    refr = setTimeout("updateResult()", refrate);
}
var oTable;

$(document).ready(function () {
    $("#status").addClass("active");
    jQuery.fn.dataTableExt.oSort['num-html-asc'] = function (a, b) {
        var x = a.replace(/<.*?>/g, "");
        var y = b.replace(/<.*?>/g, "");
        x = parseFloat(x);
        y = parseFloat(y);
        return ((x < y) ? -1 : ((x > y) ? 1 : 0));
    };

    jQuery.fn.dataTableExt.oSort['num-html-desc'] = function (a, b) {
        var x = a.replace(/<.*?>/g, "");
        var y = b.replace(/<.*?>/g, "");
        x = parseFloat(x);
        y = parseFloat(y);
        return ((x < y) ? 1 : ((x > y) ? -1 : 0));
    };

    var need_page = true;

    oTable = $('#statustable').dataTable({
        "bProcessing": true,
        "bServerSide": true,
        "sDom": '<"row">rt<"row"<"col-md-4"i><"col-md-8"p>>',
        "sAjaxSource": "ajax/status_data.php",
        "sPaginationType": "full_numbers",
        "iDisplayLength": statperpage,
        "bLengthChange": false,
        "oLanguage": {
            "sEmptyTable": "No status found.",
            "sZeroRecords": "No status found.",
            "sInfoEmpty": "No status to show."
        },
        "aaSorting": [[1, 'desc']],
        "aoColumnDefs": [
            {"sType": "num-html", "aTargets": [1, 2]},
            {"sType": "html", "aTargets": [3]},
            {"bSortable": false, "aTargets": [0, 1, 2, 3, 4, 5, 6, 7, 8, 9]},
            {"bVisible": false, "aTargets": [9]},
            {
                "mRender": function (data, type, full) {
                    return "<a target='_blank' href='userinfo.php?name=" + data + "'>" + data + "</a>";
                },
                "aTargets": [0]
            },
            {
                "mRender": function (data, type, full) {
                    return "<a href='problem_show.php?pid=" + data + "'>" + data + "</a>";
                },
                "aTargets": [2]
            },
            {
                "mRender": function (data, type, full) {
                    var tdata = "<span class='" + get_short(data) + "'>" + data + "</span>";
                    if (data == "Compile Error" || data == "Wrong Answer")
                        return "<a href='#' class='ceinfo' runid='" + full[1] + "'>" + tdata + "</a>";
                    return tdata;
                },
                "aTargets": [3]
            }
        ],
        "fnPreDrawCallback": function () {
            clearTimeout(refr);
        },
        "fnRowCallback": function (nRow, aData, iDisplayIndex, iDisplayIndexFull) {
            if (aData[9] == '1') {
                $("td:eq(1)", nRow).html("<a href='#' class='showsource' runid='" + aData[1] + "'>" + aData[1] + "</a>")
                $("td:eq(4)", nRow).html("<a href='#' class='showsource' runid='" + aData[1] + "'>" + aData[4] + "</a>")
                $("td:eq(7)", nRow).html("<a href='#' class='showsource' runid='" + aData[1] + "'>" + aData[7] + "</a>")
            }
            tres = striptags(aData[3]);
            // if (tres.substr(0,7)=="Compile") $(nRow).addClass('info');
            // else if (tres.substr(0,4)=="Judg"||tres=="Rejudging"||tres=="Waiting"||tres=="Testing") $(nRow).addClass('warning');
            // else if (tres!="Accepted"&&tres.substr(0,7)!="Pretest") $(nRow).addClass('error');
            // else $(nRow).addClass('success');
            if (tres == "Judge Error" || tres == "Judge Error (Vjudge Failed)") {
                $("td:eq(3)", nRow).append(" <button class='btn btn-mini'><i class='icon-refresh'></i> Rejudge</button>")
                $("td:eq(3) button", nRow).click(function () {
                    $(this).attr("disabled", "disabled");
                    var row = $(this).parent().parent();
                    $.ajax({
                        type: "POST",
                        url: "ajax/error_rejudge.php",
                        data: "runid=" + aData[1],
                        cache: false,
                        success: function (html) {
                            html = eval("(" + html + ")");
                            if (html.code == 0) {
                                $("td:eq(3)", row).html("Rejudging  <img src='assets/img/select2-spinner.gif' />");
                                $(row).addClass("fetching");
                                rtimes = 0;
                                clearTimeout(refr);
                                updateResult();
                            }
                            alert(html.msg);
                        }
                    });
                    $(this).removeClass("able")
                });
            }
            if (tres == "Waiting" || tres == "Rejudging" || tres == "Judging") {
                $(nRow).addClass('fetching');
                $("td:eq(3)", nRow).append(" <img src='assets/img/select2-spinner.gif' />");
            }
            return nRow;
        },
        "fnDrawCallback": function () {
            $("a.ceinfo").click(ceclick);
            $("a.showsource").click(function () {
                var trunid = $(this).attr("runid");
                var row = $(this).parent().parent();
                $("#statusdialog h3").text("Source of Run " + trunid);
                $("#statusdialog #dcontent").html('<img src="assets/img/ajax-loader.gif" /> Loading...');
                $.get('ajax/get_source.php', {runid: trunid, randomid: Math.random()}, function (data) {
                    data = eval("(" + data + ")");
                    if (data.code == 1) {
                        $("#statusdialog #rcontrol,#statusdialog #copybtn").hide();
                        $("#statusdialog #dcontent").html(data.msg).addClass("alert alert-error");
                        return;
                    }
                    $("#statusdialog #rcontrol,#statusdialog #copybtn").show();
                    $("#statusdialog #rresult").html(data.result).removeClass().addClass(get_short(data.result));
                    $("#statusdialog #rmemory").html(data.memory_used);
                    $("#statusdialog #rtime").html(data.time_used);
                    $("#statusdialog #ruser").html("<a href='userinfo.php?name=" + data.username + "' target='_blank'>" + data.username + "</a>");
                    $("#statusdialog #rpid").html("<a href='problem_show.php?pid=" + data.pid + "'>" + data.pid + "</a>");
                    $("#statusdialog #rlang").html(data.language);
                    $("#statusdialog #dcontent").html(data.source).removeClass().addClass('prettyprint');
                    if (data.isshared == 1) {
                        $("#rshare .btn").removeClass("active").filter("#sharey").addClass("active");
                        $("#sharenote").show();
                    }
                    else {
                        $("#rshare .btn").removeClass("active").filter("#sharen").addClass("active");
                        $("#sharenote").hide();
                    }
                    if (data.control == 0)$("#rshare").hide();
                    else $("#rshare").show();

                    prettyPrint();
                    $("#sharey").off("click").click(function () {
                        $.get("ajax/deal_share.php", {
                            randomid: Math.random(),
                            runid: trunid,
                            type: 1
                        }, function (data) {
                            $("#sharen").removeClass("active");
                            $("#sharey").addClass("active");
                            data = eval("(" + data + ")");
                            if (data.code == 0) {
                                $("#sharenote").show();
                            } else alert(data.msg);
                        });
                    });
                    $("#sharen").off("click").click(function () {
                        $.get("ajax/deal_share.php", {
                            randomid: Math.random(),
                            runid: trunid,
                            type: 0
                        }, function (data) {
                            $("#sharey").removeClass("active");
                            $("#sharen").addClass("active");
                            data = eval("(" + data + ")");
                            if (data.code == 0) {
                                $("#sharenote").hide();
                            } else alert(data.msg);
                        });
                    });
                    if ($("#rejudge")) {
                        $("#rejudge").off("click").click(function () {
                            $.get('ajax/admin_deal_rejudge_run.php', {
                                runid: trunid,
                                random: Math.random()
                            }, function (data) {
                                data = eval("(" + data + ")");
                                if (data.code == 0) {
                                    $("td:eq(3)", row).html("Rejudging  <img src='assets/img/select2-spinner.gif' />");
                                    $(row).addClass("fetching");
                                    rtimes = 0;
                                    clearTimeout(refr);
                                    updateResult();
                                    $("#statusdialog").modal('hide');
                                }
                                alert(data.msg);
                            });
                        });
                    }
                });
                $("#statusdialog").modal("show");
                return false;
            });
            $(".btn", "#filterform").removeAttr("disabled");
            //$(".btn","#filterform").removeClass("disabled")
            $(".dataTables_paginate .last").remove();

            rtimes = 0;
            refr = setTimeout("updateResult()", refrate);
        }
    });

    var clip = new ZeroClipboard($("#copybtn"), {
        moviePath: "assets/img/ZeroClipboard.swf",
        activeClass: "active"
    });
    clip.on('complete', function (client, args) {
        alert("Copied text to clipboard.");
    });


    $("#filterform").submit(function () {
        $(".btn", this).attr("disabled", "disabled");
        need_page = true;

        showpage = 1;
        showname = $("#filterform [name='showname']").val();
        showpid = $("#filterform [name='showpid']").val();
        showres = $("#filterform [name='showres']").val();
        showlang = $("#filterform [name='showlang']").val();

        oTable.fnFilter(showname, 0);
        oTable.fnFilter(showpid, 2);
        oTable.fnFilter(showres, 3);
        oTable.fnFilter(showlang, 4);
        oTable.trigger('page', oTable.fnSettings());
        return false;
    });


    if (showname != null) {
        oTable.fnFilter(showname, 0);
        $("#showname").val(showname);
    }
    if (showpid != null) {
        oTable.fnFilter(showpid, 2);
        $("#showpid").val(showpid);
    }
    if (showres != null) {
        oTable.fnFilter(showres, 3);
        $("#showres").val(showres);
    }
    if (showlang != null) {
        oTable.fnFilter(showlang, 4);
        $("#showlang").val(showlang);
    }

    History.Adapter.bind(window, 'statechange', function () {

        cshowname = getURLPara('showname');
        cshowpid = getURLPara('showpid');
        cshowres = getURLPara('showres');
        cshowlang = getURLPara('showlang');
        cshowpage = getURLPara('showpage');

        if (cshowpage == null) cshowpage = 1;
        if (cshowname == null) cshowname = "";
        if (cshowpid == null) cshowpid = "";
        if (cshowres == null) cshowres = "";
        if (cshowlang == null) cshowlang = "";

        if (showpage == null) showpage = 1;
        if (showname == null) showname = "";
        if (showpid == null) showpid = "";
        if (showres == null) showres = "";
        if (showlang == null) showlang = "";

        if (showname != cshowname) {
            showname = cshowname;
            oTable.fnFilter(showname, 0);
            $("#showname").val(showname);
        }
        if (showpid != cshowpid) {
            showpid = cshowpid;
            oTable.fnFilter(showpid, 2);
            $("#showpid").val(showpid);
        }
        if (showres != cshowres) {
            showres = cshowres;
            oTable.fnFilter(showres, 3);
            $("#showres").val(showres);
        }
        if (showlang != cshowlang) {
            showlang = cshowlang;
            oTable.fnFilter(showlang, 4);
            $("#showlang").val(showlang);
        }
        if (showpage != cshowpage) {
            need_page = true;
            showpage = cshowpage;
        }
        if (showpage == null || parseInt(showpage) == NaN) showpage = 1;
        else showpage = parseInt(showpage);

        if (need_page) oTable.fnPageChange(showpage - 1);
    });
});
