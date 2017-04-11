function resetpdetail() {
    $("#pdetail")[0].reset();
    CKEDITOR.instances.tdescription.setData("");
    CKEDITOR.instances.tinput.setData("");
    CKEDITOR.instances.toutput.setData("");
    CKEDITOR.instances.thint.setData("");
    $("textarea[name='sample_in']").text("");
    $("textarea[name='sample_out']").text("");
    $("textarea[name='hint']").text("");
    $("textarea[name='source']").text("");
}

function resetcdetail() {
    $("#cdetail")[0].reset();
    $("#cprobs").problemlist('reset');
    CKEDITOR.instances.treport.setData("");
}

function resetndetail() {
    $("#ndetail")[0].reset();
    CKEDITOR.instances.tncontent.setData("");
}

function probload(pid) {
    $.get('ajax/admin_get_problem.php?pid=' + pid + "&rand=" + Math.random(), function (data) {
        data = eval('(' + data + ')');
        if (data.code != 0) {
            alert(data.msg);
        } else {
            $("input[name='p_id']").val(data.pid);
            $("input[name='p_name']").val(data.title);
            $("input[name='time_limit']").val(data.tl);
            $("input[name='case_time_limit']").val(data.ctl);
            $("input[name='memory_limit']").val(data.ml);
            $("input[name='noc']").val(data.noc);
            $("textarea[name='sample_in']").html(data.sinp);
            $("textarea[name='sample_out']").html(data.sout);
            $("textarea[name='hint']").html(data.hint);
            $("textarea[name='source']").html(data.source);
            $("textarea[name='author']").html(data.author);
            $("input[name='p_hide']").each(function () {
                if (this.value == data.p_hide) this.checked = true;
            });
            $("input[name='p_ignore_noc']").each(function () {
                if (this.value == data.p_ignore_noc) this.checked = true;
            });
            $("input[name='special_judge_status']").each(function () {
                if (this.value == data.spj) this.checked = true;
            });
            $("input[name='hide']").each(function () {
                if (this.value == data.hide) this.checked = true;
            });
            CKEDITOR.instances.tdescription.setData(data.desc);
            CKEDITOR.instances.tinput.setData(data.inp);
            CKEDITOR.instances.toutput.setData(data.oup);
            CKEDITOR.instances.thint.setData(data.hint);
        }
    });
}

function problem_test_data_load(pid) {
    $.get('ajax/admin_get_problem_test_data.php?pid=' + pid + "&rand=" + Math.random(), function (data) {
        data = eval('(' + data + ')');
        if (data.code != 0) {
            alert(data.msg);
        } else {
            $("input[name='p_id']").val(pid);
            $("#files").empty();
            $(".fileinput-button").attr('disabled', false);
            var uploadButton = $('<button/>')
                .addClass('btn btn-danger')
                .text('Delete')
                .on('click', function () {
                    var _this = $(this);
                    var filename = _this.siblings('span').text();
                    var url = 'ajax/admin_deal_uploadfiles.php?action=delete&pid=' + pid + '&filename=' + filename;
                    $.get(url + "&rand=" + Math.random(), function (data) {
                        // 这里有个bug，data的值没定义
                        // console.log(data.code);
                        // if (data.code != 0) {
                        //     alert(data.msg);
                        // } else {
                        //     alert("Deleted successfully.");
                        // }
                        alert("Deleted successfully.");
                        _this.parent().remove();
                    });
                });
            var context = $('<div/>').appendTo('#files');
            $.each(data.filesnames, function (index, file) {
                if (file != '.' && file != '..') {
                    var node = $('<p/>')
                        .append($('<span/>').text(file))
                        .append('<br>')
                        .append(uploadButton.clone(true));
                    node.appendTo(context);
                }
            });
        }
    });
}
function conload(cid) {
    $.get('ajax/admin_get_contest.php?cid=' + cid + "&rand=" + Math.random(), function (data) {
        data = eval('(' + data + ')');
        if (data.code != 0) {
            alert(data.msg);
        }
        else {
            $("#cdetail").populate(data);
            CKEDITOR.instances.treport.setData(data.report);
            if ($("input[name='has_cha']:checked").val() == "1") $(".chatimerow").show();
            else $(".chatimerow").hide();
            $("#cprobs").problemlist("loadcontest", $("#ncid").val());
            var ctp = $("input[name='ctype']:checked").val();
            $("#cprobs").problemlist('settype', ctp);
        }
    });
}

function newsload(nnid) {
    $.get('ajax/get_news.php?nnid=' + nnid + "&rand=" + Math.random(), function (data) {
        data = eval('(' + data + ')');
        if (data.code != 0) {
            alert(data.msg);
        }
        else {
            $("#ndetail").populate(data);
            CKEDITOR.instances.tncontent.setData(data.ncontent);
        }
    });
}


$(document).ready(function () {
    $("#cprobs").problemlist();
    $("#vprobs").problemlist();

    $("option[value=JNU]", "select[name=pcoj]").remove();

    $("#notiform").bind("correct", function () {
        $("input:submit,button:submit,.btn", this).removeAttr("disabled").removeClass("disabled");
    });

    $("#pdetail").bind("preprocess", function () {
        CKEDITOR.instances.tdescription.updateElement();
        CKEDITOR.instances.tinput.updateElement();
        CKEDITOR.instances.toutput.updateElement();
        CKEDITOR.instances.thint.updateElement();
    });
    $("#pdetail").bind("correct", function () {
        $("input:submit,button:submit,.btn", this).removeAttr("disabled").removeClass("disabled");
        resetpdetail();
    });

    $("#pload").submit(function () {
        probload($("#npid").val());
        return false;
    });

    $("#problem_test_data").submit(function () {
        problem_test_data_load($("#dpid").val());
        return false;
    });

    $("#cdetail").bind("preprocess", function () {
        CKEDITOR.instances.treport.updateElement();
    });
    $("#cdetail").bind("correct", function () {
        $("input:submit,button:submit,.btn", this).removeAttr("disabled").removeClass("disabled");
        resetcdetail();
    });

    $("#cload").submit(function () {
        conload($("#ncid").val());
        return false;
    });

    $("#clockp").click(function () {
        $.get('ajax/admin_deal_lock.php?hide=1&cid=' + $("#ncid").val() + "&rand=" + Math.random(), function (data) {
            data = eval('(' + data + ')');
            alert(data.msg);
        });
    });
    $("#culockp").click(function () {
        $.get('ajax/admin_deal_lock.php?hide=0&cid=' + $("#ncid").val() + "&rand=" + Math.random(), function (data) {
            data = eval('(' + data + ')');
            alert(data.msg);
        });
    });
    $("#cshare").click(function () {
        $.get('ajax/admin_deal_share.php?share=1&cid=' + $("#ncid").val() + "&rand=" + Math.random(), function (data) {
            data = eval('(' + data + ')');
            alert(data.msg);
        });
    });
    $("#cunshare").click(function () {
        $.get('ajax/admin_deal_share.php?share=0&cid=' + $("#ncid").val() + "&rand=" + Math.random(), function (data) {
            data = eval('(' + data + ')');
            alert(data.msg);
        });
    });

    $("#ctestall").click(function () {
        $.get('ajax/admin_deal_testall.php?cid=' + $("#ncid").val() + "&rand=" + Math.random(), function (data) {
            data = eval('(' + data + ')');
            alert(data.msg);
        });
    });

    $("input[name='ctype']").change(function () {
        var ctp = $(this).val();
        $(this).parents("form").children(".con_probs").problemlist("settype", ctp);
        if (ctp == '1') {
            $(".typenote").text("In CF, Parameter A represents the points lost per minute. Parameter B represents the points lost for each incorrect submit.");
        }
    });


    $("#nload").submit(function () {
        newsload($("#nnid").val());
        return false;
    });
    $("#ndetail").bind("preprocess", function () {
        CKEDITOR.instances.tncontent.updateElement();
    });
    $("#ndetail").bind("correct", function () {
        $("input:submit,button:submit,.btn", this).removeAttr("disabled").removeClass("disabled");
        resetndetail();
    });

    $("#crej").submit(function () {
        $.get('ajax/admin_deal_rejudge.php?type=1&cid=' + $("#rejcid").val() + '&pid=' + $("#rejpid").val() + "&rac=" + $("input[name='rejac']:checked").val() + "&rand=" + Math.random(), function (data) {
            data = eval('(' + data + ')');
            alert(data.msg);
        });
        return false;
    });
    $("#cprej").submit(function () {
        $.get('ajax/admin_deal_rejudge.php?type=2&cid=' + $("#rcid").val() + '&pid=' + $("#rpid").val() + "&rac=" + $("input[name='rac']:checked").val() + "&rand=" + Math.random(), function (data) {
            data = eval('(' + data + ')');
            alert(data.msg);
        });
        return false;
    });
    $("#runrej").submit(function () {
        $.get('ajax/admin_deal_rejudge_run.php?runid=' + $("#runid").val() + "&rand=" + Math.random(), function (data) {
            data = eval('(' + data + ')');
            alert(data.msg);
        });
        return false;
    });
    $("#cha_crej").submit(function () {
        $.get('ajax/admin_deal_rejudge_challenge.php?type=all&cid=' + $("#rcha_cid").val() + "&rand=" + Math.random(), function (data) {
            data = eval('(' + data + ')');
            alert(data.msg);
        });
        return false;
    });


    $("#spinfo").click(function () {
        $(".syncbutton").attr("disabled", true).addClass("disabled");
        $("#syncwait").html('<img src="img/ajax-loader.gif" /> Loading...').show();
        $.get('ajax/admin_sync_problem.php', function (data) {
            data = eval('(' + data + ')');
            $("#syncwait").html(data.msg);
            $(".syncbutton").attr("disabled", false).removeClass("disabled");
        });
    });

    $("#suinfo").click(function () {
        $(".syncbutton").attr("disabled", true).addClass("disabled");
        $("#syncwait").html('<img src="img/ajax-loader.gif" /> Loading...').show();
        $.get('ajax/admin_sync_user.php', function (data) {
            data = eval('(' + data + ')');
            $("#syncwait").html(data.msg);
            $(".syncbutton").attr("disabled", false).removeClass("disabled");
        });
    });

    $("#replaycrawl").ajaxForm({
        beforeSubmit: function (formData, tform, options) {
            tform.trigger("preprocess");
            $("input:submit,button:submit,.btn", tform).attr("disabled", "disabled").addClass("disabled");
            $("#msgbox", tform).removeClass().addClass('alert').html('<img style="height:20px" src="img/ajax-loader.gif" /> Validating....').fadeIn(500);
            return true;
        },
        success: function (responseText, statusText, xhr, form) {
            responseText = eval("(" + responseText + ")");
            if (responseText.code == '0') {
                $("#msgbox", form).fadeTo(100, 0.1, function () {
                    $(this).html(responseText.msg).removeClass().addClass('alert alert-success').fadeTo(100, 1, function () {
                        $("#replayform").populate(responseText);
                        $("#vprobs").problemlist('reset');
                        $.each(responseText.prob, function (i, pid) {
                            $("#vprobs").problemlist('spawn', {"vpid": pid});
                        });
                        $("#vprobs").problemlist('spawn');
                        $("input:submit,button:submit,.btn", form).removeAttr("disabled").removeClass("disabled");
                        $(".vpid").keyup();
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

    $("#replayform").bind("correct", function () {
        $("input:submit,button:submit,.btn", this).removeAttr("disabled").removeClass("disabled");
        resetcdetail();
    });


    $("#cclonecid").click(function () {
        $("#cprobs").problemlist("loadcontest", $("#clcid").val());
        return false;
    });

    $("#cclonesrc").click(function () {
        $("#cprobs").problemlist("loadsource", $("#clsrc").val());
        return false;
    });

    $("#vclonecid").click(function () {
        $("#vprobs").problemlist("loadcontest", $("#vclcid").val());
        return false;
    });

    $("#vclonesrc").click(function () {
        $("#vprobs").problemlist("loadsource", $("#vclsrc").val());
        return false;
    });

    //$("#admintab").tabs();
    //$("input:submit, button").button();
    $('.datepick').datetimepicker({
        format: 'yyyy-mm-dd hh:ii:ss'
    });

    if (getURLPara('cid') != null) {
        conload(getURLPara('cid'));
    }

    if (getURLPara('pid') != null) {
        problem_test_data_load(getURLPara('pid'));
    }
    if (getURLPara('pid') != null) {
        probload(getURLPara('pid'));
    }

    if (getURLPara('newsid') != null) {
        newsload(getURLPara('newsid'));
    }

    $("input[name='has_cha']").change(function () {
        var hc = $(this).val();
        if (hc == 1) $(".chatimerow").show();
        else $(".chatimerow").hide();
    });

    $("#userspace").addClass("active");
    var dest = self.document.location.hash;
    if (dest != "#") $("[href='" + dest + "']", "#admintab").click();

    $("#genuser input[name='cid']").keyup(function () {
        var cid = $(this).val();
        var $target = $(this).next();
        var $submit = $(this).parent().parent().find("button");
        $.get('ajax/admin_get_contest.php?cid=' + cid + "&rand=" + Math.random(), function (data) {
            data = eval('(' + data + ')');
            if (data.code != 0) {
                $target.html("No such contest");
                $submit.attr("disabled", "disabled");
            }
            else {
                $target.html("<a href=\"contest_show.php?cid=" + data.cid + "\">" + data.title + "</a>");
                $submit.removeAttr("disabled");
            }
        });
    });

});
