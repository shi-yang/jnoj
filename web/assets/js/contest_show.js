var reftable;
var cookiename = cookie_prefix + 'cstandset_' + $.cookie(cookie_prefix + 'username') + '_' + getURLPara('cid');

function displaycountdown() {
    //cnt+=stp;
    var tm = cnt - currenttime - 2;
    if (tm < 0) cnt = 0;
    var dh = Math.floor(tm / 3600);
    var dm = Math.floor((tm - dh * 3600) / 60);
    var ds = tm - dh * 3600 - dm * 60;
    var timestring = dh + ":" + dm + ":" + ds;
    $("#counttime").text(timestring);
}


var showpfunc = function (gcpid) {
    if (gcpid == null) gcpid = "0";
    self.document.location.hash = "#problem/" + gcpid;
    $("#cprob_a").attr("name", gcpid);

    $.get("contest_prob.php", {cid: getURLPara('cid'), cpid: gcpid, randomid: Math.random()}, function (data) {
        $("#contest_content").html(data);
        $("#submitdialog").on("shown", function () {
            $("#submitdialog textarea").focus();
        });
        $("#lang option").each(function () {
            if ($.inArray($(this).val(), support_lang) == -1) $(this).remove();
        });
        if ($.cookie(cookie_prefix + "defaultshare") == "0") $("input[name='isshare']:nth(1)").attr("checked", true);
        else $("input[name='isshare']:nth(0)").attr("checked", true);

        $(".submitprob").click(function () {
            if ($.cookie(cookie_prefix + "username") == null) $("#logindialog").modal("show");
            else $("#submitdialog").modal("show");
            return false;
        });

        $("#cprobsubmit").ajaxForm({
            beforeSubmit: function (formData, tform, options) {
                $("input:submit,button:submit,.btn", tform).attr("disabled", "disabled").addClass("disabled");
                $("#msgbox", tform).removeClass().addClass('alert alert-block').html('<img style="height:20px" src="assets/img/ajax-loader.gif" /> Validating....').fadeIn(500);
                return true;
            },
            success: function (responseText, statusText, xhr, form) {
                responseText = eval("(" + responseText + ")");
                if (responseText.code == '0') {
                    $("#msgbox", form).fadeTo(100, 0.1, function () {
                        $(this).html(responseText.msg).removeClass().addClass('alert alert-success').fadeTo(100, 1, function () {
                            if (responseText.msg == "Submitted.") {
                                $("#submitdialog").modal("hide");
                                self.document.location.hash = "#status";
                            }
                            else self.document.location = "status.php";
                        });
                    });
                }
                else {
                    $("#msgbox", form).fadeTo(100, 0.1, function () {
                        $(this).html(responseText.msg).removeClass().addClass('alert alert-error').fadeTo(300, 1);
                    });
                    $("input:submit,button:submit,.btn", form).removeAttr("disabled").removeClass("disabled");
                }
            }
        });


        document.title = $("#contest_content .pagetitle").text();
        $("#contest_nav li").removeAttr("disabled").removeClass("disabled");

    });
    $("#contest_content").html('<div class="tcenter"><img src="assets/img/ajax-loader.gif" />Loading...</div>');
    return false;
}


var defaultfunc = function (data) {
    if (self.document.location.hash != "") self.document.location.hash = "#info";
    $("#contest_content").html(data);
    //   $("td:first","#cplist tr").each(function(){
    //       if ($.trim($(this).text())=="Yes") $(this).parent().addClass('success');
    //       else if ($.trim($(this).text())=="No") $(this).parent().addClass('error');
    //   });
    document.title = $("#contest_content .pagetitle").text();
    $("#contest_nav li").removeAttr("disabled").removeClass("disabled");
};

var refr;
var rtimes = 0;

var ceclick = function () {
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

var statusfunc = function (data, slabel) {
    if (slabel == null) self.document.location.hash = "#status";
    else self.document.location.hash = "#status/" + slabel;
    $("#contest_content").html(data);
    var oTable = $('#statustable').dataTable({
        "bProcessing": true,
        "bServerSide": true,
        "sDom": '<"row-fluid"p>rt<"row-fluid"<"span8"i><"span4"l>>',
        "sAjaxSource": "ajax/contest_status_data.php?cid=" + gcid + "&randomid=" + Math.random(),
        "sPaginationType": "full_numbers",
        "iDisplayLength": statperpage,
        "bLengthChange": false,
        "oLanguage": {
            "sEmptyTable": "No status found.",
            "sZeroRecords": "No status found.",
            "sInfoEmpty": "No entries to show"
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
                    return "<a href='#problem/" + data + "'>" + data + "</a>";
                },
                "aTargets": [2]
            },
            {
                "mRender": function (data, type, full) {
                    var tdata = "<span class='" + get_short(data) + "'>" + data + "</span>";
                    if (data.substr(0, 7) == "Compile") return "<a href='#' class='ceinfo' runid='" + full[1] + "'>" + tdata + "</a>";
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
                    $.ajax({
                        type: "POST",
                        url: "ajax/error_rejudge.php",
                        data: "runid=" + aData[1],
                        cache: false,
                        success: function (html) {
                            html = eval("(" + html + ")");
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
                $.get('ajax/get_source.php', {
                    cid: getURLPara("cid"),
                    runid: trunid,
                    randomid: Math.random()
                }, function (data) {
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
                    $("#statusdialog #ruser").html("<a href='userinfo.php?pid=" + data.username + "' target='_blank'>" + data.username + "</a>");
                    $("#statusdialog #rpid").html("<a href='contest_show.php?cid=" + getURLPara('cid') + "#problem/" + data.pid + "'>" + data.pid + "</a>");
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

            $(".btn", "#filterform").removeAttr("disabled").removeClass("disabled")
        },
        "iDisplayStart": 0
    });

    var clip = new ZeroClipboard($("#copybtn"), {
        moviePath: "assets/img/ZeroClipboard.swf",
        activeClass: "active"
    });
    clip.on('complete', function (client, args) {
        alert("Copied text to clipboard.");
    });

    $("#filterform").submit(function () {
        $(".btn", this).attr("disabled", "disabled").addClass("disabled");
        oTable.fnFilter($("#filterform [name='showname']").val(), 0);
        oTable.fnFilter($("#filterform [name='showpid']").val(), 2);
        oTable.fnFilter($("#filterform [name='showres']").val(), 3);
        oTable.fnFilter($("#filterform [name='showlang']").val(), 4);
        return false;
    });
    if (slabel != null) {
        oTable.fnFilter(slabel, 2);
        $(".filter #showpid [value=" + slabel + "]").attr("selected", "selected");
    }
    document.title = $("#contest_content .pagetitle").text();
    $("#contest_nav li").removeAttr("disabled").removeClass("disabled");
}

function formtime(t) {
    var str = "";
    str += parseInt(t / 3600);
    t %= 3600;
    str += ":" + parseInt(t / 60) + ":" + parseInt(t % 60);
    return str;
}

function updaterank(passtime, isadmin) {
    self.document.location.hash = isadmin ? "#adminstanding" : "#standing";
    var extstr = "";
    if (passtime != "") extstr = "&passtime=" + passtime;
    $.get(("contest_standing.php?" + (isadmin ? "admin=1&" : "") + "randomid=") + Math.random() + extstr + "&" + $("#csetform").serialize(), function (data) {
        $("#temp_standing").html(data);
        if ($("#stat_dis_nick").prop("checked") == true) {
            $(".tusername").hide();
            $(".tnickname").show();
        }
        else {
            $(".tnickname").hide();
            $(".tusername").show();
        }
        if ($("#contest_content .cstanding")[0] == null) {
            $("#contest_content").html($("#temp_standing").html());
            $("#stat_dis_user").change(function () {
                $("#stat_dis_nick").removeAttr("checked");
                $(".tnickname").hide();
                $(".tusername").show();
            });
            $("#stat_dis_nick").change(function () {
                $("#stat_dis_user").removeAttr("checked");
                $(".tusername").hide();
                $(".tnickname").show();
            });

            $('.timeslider').noUiSlider('init', {
                knobs: 1,
                change: function () {
                    var values = $(this).noUiSlider('value');
                    var v = values[1];
                    $("#contest_content .slidediv .passtime").text(formtime(v));
                },
                start: [$("#temp_standing .slidediv .timeslider").attr("name"), $("#temp_standing .slidediv .timeslider").attr("name")],
                scale: [1, $("#temp_standing .slidediv .maxval").attr("name")],
                end: function () {
                    var values = $(this).noUiSlider('value');
                    var v = values[1];
                    updaterank(v, isadmin);
                    $(this).addClass("disabled");
                }
            });
            document.title = $("#contest_content .pagetitle").text();
            if ($("#contest_content").width() > $("#contest_content .cstanding").width()) $("#contest_content .cstanding").css("width", "100%");
            if (!cpass && $("#autoref").prop('checked') == true) reftable = setTimeout("updaterank(null," + (isadmin ? "true" : "false") + ")", 10000);
        }
        else {
            $("#temp_standing .cstanding").width($("#contest_content .cstanding").width());
            if ($("#contest_content #stat_dis_nick").attr("checked")) {
                $(".tusername").hide();
                $(".tnickname").show();
            }
            if ($("#animate").prop('checked') == true) {
                $(".cstanding").addClass("visover");
                $("#contest_content .cstanding").rankingTableUpdate("#temp_standing .cstanding", {
                    onComplete: function () {
                        //$("#trypos").height($("#contest_content #cstandingcontainer").height());
                        $("#contest_content .currentstat b").html($("#temp_standing .currentstat b").html());
                        $('.timeslider').removeClass('disabled');
                        $(".cstanding").removeClass("visover");
                    }
                });
            }
            else {
                $("#contest_content .rankcontainer").html($("#temp_standing .rankcontainer").html());
                $("#contest_content .currentstat b").html($("#temp_standing .currentstat b").html());
                //$("#trypos").height($("#contest_content #cstandingcontainer").height());

                $('.timeslider').removeClass('disabled');
            }
            if (!cpass && $("#autoref").prop('checked') == true) reftable = setTimeout("updaterank(null," + (isadmin ? "true" : "false") + ")", 10000);
        }
        $(".cha_click").click(function () {
            var uname = $(this).attr("chauname");
            var pid = $(this).attr("chaprob");
            $("#chasrcimage").attr("src", "assets/img/ajax-loader.gif");
            $("#cchahistory").html("");
            $("#cchadetailcontent").html('<img height="15px" src="assets/img/ajax-loader.gif" />Loading....');
            $("#cchadetail").hide();
            $("#chasrcimage").attr("src", "challenge_src_image.php?pid=" + pid + "&username=" + uname + "&cid=" + gcid + "&random=" + Math.random());
            $.get("fetch_challenge_history.php?pid=" + pid + "&username=" + uname + "&cid=" + gcid + "&random=" + Math.random(), function (data) {
                $("#cchahistory").html(data);
                $(".showchadet").click(function () {
                    $("#cchadetailcontent").html('<img height="15px" src="assets/img/ajax-loader.gif" />Loading....');
                    var chaid = $(this).attr('name');
                    $("#cchadetail").show();
                    $.get("fetch_challenge_detail.php?cha_id=" + chaid + "&random=" + Math.random(), function (data) {
                        $("#cchadetailcontent").html(data);
                    });
                });
            });
            $("#chaformuser").val(uname);
            $("#chaformpid").val(pid);
            $("#chaformcid").val(gcid);
            $("#chasrcimage").show();
            $("#chamsgbox").hide();
            $("#cchaform").show();
            $("#cchainfo").modal("show");
            return false;
        });
        $(".user_cha").click(function () {
            var uname = $(this).attr("chauname");
            $("#chasrcimage").hide();
            $("#cchahistory").html("");
            $("#cchadetailcontent").html('<img height="15px" src="assets/img/ajax-loader.gif" />Loading....');
            $("#cchadetail").hide();
            $.get("fetch_challenge_history_user.php?username=" + uname + "&cid=" + gcid + "&random=" + Math.random(), function (data) {
                $("#cchahistory").html(data);
                $(".showchadet").click(function () {
                    $("#cchadetailcontent").html('<img height="15px" src="assets/img/ajax-loader.gif" />Loading....');
                    var chaid = $(this).attr('name');
                    $("#cchadetail").show();
                    $.get("fetch_challenge_detail.php?cha_id=" + chaid + "&random=" + Math.random(), function (data) {
                        $("#cchadetailcontent").html(data);
                    });
                });
            });
            $("#chamsgbox").hide();
            $("#cchaform").hide();
            $("#cchainfo").modal("show");
            return false;
        });
        $("a.standingp").click(function () {
            showpfunc($(this).attr("name"));
            return false;
        });
        $("#contest_nav li").removeAttr("disabled").removeClass("disabled");
    });
}

var showreportfunc = function () {
    self.document.location.hash = "#report";
    $.get("contest_report.php", {cid: gcid, randomid: Math.random()}, function (data) {
        $("#contest_content").html(data);
        document.title = $("#contest_content .pagetitle").text();
        $("#contest_nav li").removeAttr("disabled").removeClass("disabled");
    });
    $("#contest_content").html('<div class="tcenter"><img src="assets/img/ajax-loader.gif" />Loading...</div>');
}


var clarfunc = function (data) {
    self.document.location.hash = "#clarify";
    $("#contest_content").html(data);
    $("#questiondialog").on("shown", function () {
        $("#questiondialog textarea").focus();
    });
    $("#questionform, .clarform").ajaxForm({
        beforeSubmit: function (formData, tform, options) {
            $("input:submit,button:submit,.btn", tform).attr("disabled", "disabled").addClass("disabled");
            $("#msgbox", tform).removeClass().addClass('alert alert-block').html('<img style="height:20px" src="assets/img/ajax-loader.gif" /> Validating....').fadeIn(500);
            return true;
        },
        success: function (responseText, statusText, xhr, form) {
            responseText = eval("(" + responseText + ")");
            if (responseText.code == '0') {
                $("#msgbox", form).fadeTo(100, 0.1, function () {
                    $(this).html(responseText.msg).removeClass().addClass('alert alert-success').fadeTo(100, 1, function () {
                        form.trigger("correct");
                    });
                });
            }
            else {
                $("#msgbox", form).fadeTo(100, 0.1, function () {
                    $(this).html(responseText.msg).removeClass().addClass('alert alert-error').fadeTo(300, 1);
                });
                $("input:submit,button:submit,.btn", form).removeAttr("disabled").removeClass("disabled");
            }
        }
    });

    $("#questionform").bind("correct", function () {
        $("#questiondialog").modal("hide");
        $.get("contest_clarify.php", {cid: gcid, randomid: Math.random()}, clarfunc);
    });

    $(".clarform").bind("correct", function () {
        $("input:submit,button:submit,.btn", this).removeAttr("disabled").removeClass("disabled");
    });
    $("#newquestion").click(function () {
        $("#questiondialog").modal("show");
        return false;
    });
    document.title = $("#contest_content .pagetitle").text();
    $("#contest_nav li").removeAttr("disabled").removeClass("disabled");
}


var charesfunc = function (chaid) {
    $.get("fetch_challenge_result.php?cha_id=" + chaid + "&random=" + Math.random(), function (data) {
        if ($.trim(data).substring(0, 9) == "Challenge") alert(data);
        else setTimeout("charesfunc(" + chaid + ")", 2000);
    });
}

$(document).ready(function () {
    $("#contest").addClass("active");

    $("#cpasssub").submit(function () {
        $.post("ajax/deal_contest_pass.php", {"cid": gcid, "password": $("#contest_password").val()}, function (data) {
            data = eval("(" + data + ")");
            if (data.code == 0) window.location.reload();
            else alert(data.msg);
        });
        return false;
    });


    $("#csetall").change(function () {
        var t = $(this).prop('checked');
        if (t == false) $("#csettable input.othc:checkbox").prop('checked', false);
        else $("#csettable input.othc:checkbox").prop('checked', true);
    });

    $("#csetform").submit(function () {
        $("#csetdlg").modal('hide');
        clearTimeout(reftable);
        $("#contest_content").html('<div class="tcenter"><img src="assets/img/ajax-loader.gif" />Loading...</div>');
        $.cookie(cookiename, $("#csetform").serialize());
        if (self.document.location.hash == "#standing") updaterank();
        else self.document.location.hash = "#standing";
        return false;
    });

    if ($.cookie(cookiename) == null) {
        $.cookie(cookiename, $("#csetform").serialize());
    }

    $("#csetform").deserialize($.cookie(cookiename));

    if ($("input.othc:not(:checked)").length == 0) $("#csetall").attr('checked', true);
    else $("#csetall").attr('checked', false);

    $("#csettable input.othc:checkbox").change(function () {
        if ($("input.othc:not(:checked)").length == 0) $("#csetall").attr('checked', true);
        else $("#csetall").attr('checked', false);
    });

    $("input[name='chadata_type']").change(function () {
        var v = $(this).val();
        if (v == 1) $("#cha_lang_select").show();
        else $("#cha_lang_select").hide();
    })


    $("#cchaform").submit(function () {
        var tform = this;
        $("input:submit", tform).attr("disabled", "disabled");
        $("input:submit", tform).addClass("ui-state-disabled");
        $("#chamsgbox").removeClass().addClass('normalmessagebox').html('<img height="15px" src="assets/img/ajax-loader.gif" />Validating....').fadeIn(500);
        $.post("deal_challenge.php", $(this).serialize(), function (data) {
            if ($.trim(data).substring(0, 9) == 'Challenge') {
                $("#chamsgbox").fadeTo(100, 0.1, function () {
                    $.get("fetch_challenge_history.php?pid=" + $("#chaformpid").val() + "&username=" + $("#chaformuser").val() + "&cid=" + $("#chaformcid").val() + "&random=" + Math.random(), function (data) {
                        $("#cchahistory").html(data);
                        $("#cchadetailcontent").html('<img height="15px" src="assets/img/ajax-loader.gif" />Loading....');
                        $(".showchadet").click(function () {
                            var chaid = $(this).attr('name');
                            $("#cchadetail").show();
                            $.get("fetch_challenge_detail.php?cha_id=" + chaid + "&random=" + Math.random(), function (data) {
                                $("#cchadetailcontent").html(data);
                            });
                        });
                    });
                    $(this).html(data).addClass('normalmessageboxok').fadeTo(1000, 1, function () {
                        $("#cchainfo").dialog("close");
                    });
                    reg = /[0-9]+/;
                    var chaid = data.match(reg);
                    charesfunc(chaid[0]);
                });
                $("input:submit", tform).removeAttr("disabled");
                $("input:submit", tform).removeClass("ui-state-disabled");
            }
            else {
                $("#chamsgbox").fadeTo(100, 0.1, function () {
                    $(this).html(data).addClass('normalmessageboxerror').fadeTo(300, 1);
                });
                $("input:submit", tform).removeAttr("disabled");
                $("input:submit", tform).removeClass("ui-state-disabled");
            }
        });
        return false;
    });


    $("#cset_a").click(function () {
        $("#csetdlg").modal('show');
        return false;
    });

    $("#cdel_a").click(function () {
        var conf = confirm("Do you really want to delete this contest?");
        if (conf) {
            clearTimeout(reftable);
            clearTimeout(refr);
            $("#contest_nav li, #contest_nav .btn").attr("disabled", true).addClass("disabled");
            $.get("ajax/contest_delete.php", {cid: gcid, randomid: Math.random()}, function (data) {
                data = eval("(" + data + ")");
                alert(data.msg);
                if (data.code == 0) window.location.href = "contest.php";
                else $("#contest_nav li, #contest_nav .btn").removeAttr("disabled").removeClass("disabled");
            });
        }
        return false;
    });

    $(window).hashchange(function () {
        var dest = self.document.location.hash.substring(1);
        clearTimeout(reftable);
        clearTimeout(refr);
        $("#contest_nav li").attr("disabled", true).addClass("disabled");
        if (dest == "" || dest == "info") {
            $("#contest_nav li").removeClass("active").filter("#cinfo_a").addClass("active");
            $.get("contest_info.php", {cid: gcid, randomid: Math.random()}, defaultfunc);
            $("#contest_content").html('<div class="tcenter"><img src="assets/img/ajax-loader.gif" />Loading...</div>');
        }
        else if (dest == "standing") {
            $("#contest_nav li").removeClass("active").filter("#cstand_a").addClass("active");
            $("#contest_content").html('<div class="tcenter"><img src="assets/img/ajax-loader.gif" />Loading...</div>');
            updaterank();
        }
        else if (dest == "adminstanding") {
            $("#contest_nav li").removeClass("active").filter("#cadminstand_a").addClass("active");
            $("#contest_content").html('<div class="tcenter"><img src="assets/img/ajax-loader.gif" />Loading...</div>');
            updaterank(null, true);
        }
        else if (dest.substring(0, 8) == "problem/") {
            $("#contest_nav li").removeClass("active").filter("#cprob_a").addClass("active");
            showpfunc(dest.substring(8));
        }
        else if (dest.substring(0, 6) == "status") {
            $("#contest_nav li").removeClass("active").filter("#cstatus_a").addClass("active");
            $.get("contest_status.php", {cid: gcid, randomid: Math.random()}, function (data) {
                if (dest.length > 6) statusfunc(data, dest.substring(7));
                else statusfunc(data);
            });
            $("#contest_content").html('<div class="tcenter"><img src="assets/img/ajax-loader.gif" />Loading...</div>');
        }
        else if (dest == "report") {
            $("#contest_nav li").removeClass("active").filter("#creport_a").addClass("active");
            showreportfunc();
        }
        else if (dest == "clarify") {
            $("#contest_nav li").removeClass("active").filter("#cclar_a").addClass("active");
            $.get("contest_clarify.php", {cid: gcid, randomid: Math.random()}, clarfunc);
            $("#contest_content").html('<div class="tcenter"><img src="assets/img/ajax-loader.gif" />Loading...</div>');
        }
    });

    $(window).hashchange();

    setInterval("displaycountdown()", 1000);
});
