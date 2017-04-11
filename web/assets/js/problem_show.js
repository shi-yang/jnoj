$(document).ready(function () {
    $("#problem").addClass("active");
    $(".submitprob").click(function () {
        if ($.cookie(cookie_prefix + "username") == null) $("#logindialog").modal("show");
        else $("#submitdialog").modal("show");
        return false;
    });

    $("#submitdialog").on("shown", function () {
        $("textarea", this).focus();
    })


    if ($.cookie(cookie_prefix + "defaultshare") == "0") $("input[name='isshare']:nth(1)").attr("checked", true);
    else $("input[name='isshare']:nth(0)").attr("checked", true);

    var oris = 100;

    $("#font-plus", ".functions").click(function () {
        oris += 10;
        $("#showproblem .content-wrapper").css("font-size", oris + "%");
        return false;
    });
    $("#font-minus", ".functions").click(function () {
        oris -= 10;
        $("#showproblem .content-wrapper").css("font-size", oris + "%");
        return false;
    });

    $("#ptags").click(function () {
        $("#ptagdetail").toggle()
    });

    $("#probsubmit").bind("correct", function () {
        window.location.href = "status.php";
    });

    $("#tagform").submit(function () {
        $.post("ajax/deal_tag_problem.php", $(this).serialize(), function (data) {
            data = eval("(" + data + ")");
            alert(data.msg);
        });
        return false;
    });

    $("#utags").select2();

    $("#lang option").each(function () {
        if ($.inArray($(this).val(), support_lang) == -1) $(this).remove();
    });
});
