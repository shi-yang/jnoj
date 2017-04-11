function escapeHtml(unsafe) {
    return unsafe
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

//$(document).ready(function() {
function fnCreateSelect() {
    return '<select class="col-md-12 form-control" style="width: 80px;"><option value="">All</option>' + ojoptions + '</select>';
}

var oj = "", coj = "";
var unsolved = "0", cunsolved = "0";
var showpage = "1", cpage = "1";
var searchstr = "", csearchstr = "";
var need_page = true;

function getUrlHash() {
    var turl = (oj == "" ? "" : "&oj=" + oj)
        + (unsolved == "0" ? "" : "&unsolved=" + unsolved)
        + (showpage == 1 ? "" : "&page=" + showpage)
        + (searchstr == "" ? "" : "&searchstr=" + encodeURI(searchstr));
    if (turl != "") return "#" + turl.substr(1);
    return "";
}

function processPara() {
    oj = (getURLPara("oj") == null ? "" : getURLPara("oj"));
    unsolved = (getURLPara("unsolved") == null ? "0" : getURLPara("unsolved"));
    showpage = (getURLPara("page") == null ? "1" : getURLPara("page"));
    showpage = parseInt(showpage);
    if (showpage == NaN) showpage = 1;
    searchstr = (getURLPara("searchstr") == null ? "" : getURLPara("searchstr"));
}
var oTable;

$(document).ready(function () {
    oTable = $('#problist').dataTable({
        "bProcessing": true,
        "bServerSide": true,
        "sDom": '<"row"f>rt<"row"<"col-md-8"i><"col-md-4"l><"col-md-12"p>>',
        //        "bStateSave": true,
        //        "sCookiePrefix": "bnu_datatable_problemlist_",
        //        "sDom": '<"H"pf>rt<"F"il>',
        "oLanguage": {
            "sEmptyTable": "No problems found.",
            "sZeroRecords": "No problems found.",
            "sInfoEmpty": "No entries to show"
        },
        "sAjaxSource": "ajax/problem_data.php",
        "aaSorting": [[1, 'asc']],
        "sPaginationType": "full_numbers",
        "aLengthMenu": [[25, 50, 100, 150, 200], [25, 50, 100, 150, 200]],
        "iDisplayLength": probperpage,
        "iDisplayStart": 0,
        "oSearch": {"sSearch": searchstr},
        "aoColumnDefs": [
            {"bSortable": false, "aTargets": [0, 5]},
            {
                "mRender": function (data, type, full) {
                    return full[6];
                },
                "aTargets": [5]
            },
            {
                "mRender": function (data, type, full) {
                    return "<a href='status.php?showpid=" + full[1] + "'>" + full[4] + "/" + full[5] + "</a>";
                },
                "aTargets": [4]
            },
            {
                "mRender": function (data, type, full) {
                    return "<a href='problem_show.php?pid=" + full[1] + "' title='" + escapeHtml(full[2]) + "' >" + full[2] + "</a>";
                },
                "aTargets": [2]
            },
            {
                "mRender": function (data, type, full) {
                    //tags split
                    var strArr = data.split(",");
                    var url = "";
                    var flag = false;
                    for (var i = 0; i < strArr.length; i++) {
                        if (flag) {
                            url += ", ";
                        } else {
                            flag = true;
                        }
                        url += "<a class='source_search' href='#' title='" + escapeHtml(strArr[i]) + "'>" + strArr[i] + "</a>";
                    }
                    return url;
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
                    searchstr = $(this).text();
                    self.document.location.hash = getUrlHash();
                    //oTable.fnFilter( $(this).text() );
                    return false;
                });
            });
        }
    }).bind("filter", function (o, e) {
        searchstr = $("#problist_filter .search-query").val();
        self.document.location.hash = getUrlHash();
    });

    $(".selectoj").each(function (i) {
        this.innerHTML = fnCreateSelect();
        $('select', this).change(function () {
            oj = $(this).val();
            showpage = 1;
            self.document.location.hash = getUrlHash();
        });
    });


    $("#showunsolve").click(function () {
        unsolved = "1";
        showpage = 1;
        self.document.location.hash = getUrlHash();
        return false;
    });

    $("#showall").click(function () {
        unsolved = "0";
        showpage = 1;
        self.document.location.hash = getUrlHash();
        return false;
    });

//    if (searchstr!="") {
//        oTable.fnFilter( "", 10 );
//        oTable.fnFilter(searchstr);
//        $(".selectoj select").val('');
//    }
    $("#problem").addClass("active");
    //$("#problist tr th:nth-child(1)").hide();
    $(window).hashchange(function () {
        processPara();
        if (oj != coj) {
            $(".selectoj select").val(oj);
            oTable.fnFilter(oj, 10);
        }
        coj = oj;
        if (cunsolved != unsolved) {
            if (unsolved == "1") {
                $(".btn", $("#showunsolve").parent()).removeClass("active");
                $("#showunsolve").addClass("active");
                oTable.fnFilter("1", 0);
            }
            else if (unsolved == "0") {
                $(".btn", $("#showunsolve").parent()).removeClass("active");
                $("#showall").addClass("active");
                oTable.fnFilter("0", 0);
            }
        }
        cunsolved = unsolved;
        if (csearchstr != searchstr) {
            showpage = 1;
            $("#problist_filter .search-query").val(searchstr);
            oTable.fnFilter(searchstr);
        }
        csearchstr = searchstr;
        if (cpage != showpage) {
            if (!need_page) oTable.fnPageChange(showpage - 1);
        }
        cpage = showpage;
    });
    $(window).hashchange();
});
