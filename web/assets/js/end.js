function padlength(what) {
    var output = (what.toString().length == 1) ? "0" + what : what;
    return output;
}

function displaytime() {
    //alert(currenttime);
    currenttime++;
    var serverdate = new Date(currenttime * 1000);
    serverdate.setSeconds(serverdate.getSeconds() + 1);
    var datestring = serverdate.getFullYear() + "-" + padlength(serverdate.getMonth() + 1) + "-" + padlength(serverdate.getDate());
    var timestring = padlength(serverdate.getHours()) + ":" + padlength(serverdate.getMinutes()) + ":" + padlength(serverdate.getSeconds());
    $("#servertime").text(datestring + " " + timestring);
}

function gettime() {
    $.get("ajax/get_server_timestamp.php", function (data) {
        currenttime = data;
    });
}

$(document).ready(function () {

    /*** top height ***/
    $("marquee").css("margin-top", $(".navbar-fixed-top").height() - 72);
    $("#marqueepos").css("height", $("marquee").height() + $(".navbar-fixed-top").height() - 70);

    /*** dialogs ***/


    $("#login").click(function () {
        $("#logindialog").modal("show");
        return false;
    });
    $("#logindialog").bind("shown", function () {
        $("#logindialog #username").focus();
    });
    $(".toregister").click(function () {
        $("#logindialog").modal("hide");
        $("#regdialog").modal("show");
        return false;
    });
    $("#regdialog").bind("shown", function () {
        $("#regdialog #rusername").focus();
    });

    $("#modify").click(function () {
        $("#modifydialog").modal("show");
        return false;
    });
    $("#modifydialog").bind("shown", function () {
        $("#modifydialog #ropassword").focus();
    });


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

    $(".newseditbutton").click(function () {
        location.href = "admin_index.php?newsid=" + $(this).attr("name") + "#newstab";
        return false;
    });

    /**** basic configurations for ajax forms ***/
    $("form.ajform").ajaxForm({
        beforeSerialize: function (tform, options) {
            tform.trigger("preprocess");
        },
        beforeSubmit: function (formData, tform, options) {
            $("input:submit,button:submit,.btn", tform).attr("disabled", "disabled").addClass("disabled");
            $("#msgbox", tform).removeClass().addClass('alert').html('<img style="height:20px" src="assets/img/ajax-loader.gif" /> Validating....').fadeIn(500);
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
                $("input:submit,button:submit,.btn", form).removeAttr("disabled").removeClass("disabled");
            }
            else {
                $("#msgbox", form).fadeTo(100, 0.1, function () {
                    $(this).html(responseText.msg).removeClass().addClass('alert alert-error').fadeTo(300, 1);
                });
                $("input:submit,button:submit,.btn", form).removeAttr("disabled").removeClass("disabled");
            }
        }
    });

    $("#login_form").bind("correct", function () {
        window.location.reload();
    });

    $("#reg_form").bind("correct", function () {
        $("#regdialog").modal("hide");
        $("#logindialog").modal("show");
    });

    $("#modify_form").bind("correct", function () {
        window.location.reload();
    });

    $("#logout").click(function () {
        $.removeCookie(cookie_prefix + 'username');
        $.removeCookie(cookie_prefix + 'password');
        window.location.reload();
    });

    $("#selstyle").change(function () {
        $.cookie(cookie_prefix + "style", $(this).val());
        window.location.reload();
    })

    $("#selwidth").change(function () {
        if ($(this).prop("checked")) $.cookie(cookie_prefix + "fluid_width", true);
        else $.removeCookie(cookie_prefix + "fluid_width");
        window.location.reload();
    })

    var stname = $.cookie(cookie_prefix + "style") == null ? $("#selstyle option[value='" + default_style + "']").text() : $("#selstyle option[value='" + $.cookie(cookie_prefix + "style") + "']").text();
    $("#selstyle").val($.cookie(cookie_prefix + "style") == null ? default_style : $.cookie(cookie_prefix + "style"));
    $("#stylename").text(stname);
    setInterval("displaytime()", 1000);
    setInterval("gettime()", 180000);
});