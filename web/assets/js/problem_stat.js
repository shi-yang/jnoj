Highcharts.visualize = function (table, options) {
    // the data series
    options.series = [{
        type: 'pie',
        name: '',
        data: []
    }];
    var flag = false;
    jQuery('tr', table).each(function (i) {
        if (i > 0) {
            var tr = this;
            options.series[0].data.push([]);
            jQuery('th, td', tr).each(function (j) {
                if (j == 1) options.series[0].data[i - 1].push(parseFloat(striptags(this.innerHTML)));
                else options.series[0].data[i - 1].push(this.innerHTML);
            });
        }
        else if (striptags(jQuery('td', this)[0].innerHTML) == "0") {
            flag = true;
        }
    });
    if (flag) {
        options.series[0].data[9][0] = "Total";
        options.series[0].data[9][1] = 1;
    }
    var chart = new Highcharts.Chart(options);
}

$(document).ready(function () {

    var table = document.getElementById('probstat');
    var options = {
        chart: {
            renderTo: 'probpie',
            plotBackgroundColor: null,
            plotBorderWidth: null,
            backgroundColor: null,
            plotShadow: false
        },
        title: {
            text: 'Problem Statistics'
        },
        tooltip: {
            formatter: function () {
                return '<b>' + this.point.name + '</b>: ' + this.percentage.toFixed(2) + ' %';
            }
        },
        plotOptions: {
            pie: {
                allowPointSelect: true,
                cursor: 'pointer',
                dataLabels: {
                    enabled: false
                }
            }
        }
    };
    Highcharts.visualize(table, options);

    var oTable = $('#pleader').dataTable({
        "bProcessing": true,
        "bServerSide": true,
        "sAjaxSource": "ajax/problem_leader.php?pid=" + ppid,
        "oLanguage": {
            "sEmptyTable": "No one solved this, yet."
        },
        "sDom": '<"row"p>rt<"row"i>',
        "sPaginationType": "full_numbers",
        "iDisplayLength": pstatperpage,
        "bLengthChange": false,
        "aaSorting": [[4, 'asc'], [5, 'asc'], [7, 'asc']],
        "aoColumnDefs": [
            {"bSortable": false, "aTargets": [0, 1, 2, 3, 6]},
            {
                "mRender": function (data, type, full) {
                    return "<a href='userinfo.php?name=" + data + "'>" + data + "</a>";
                },
                "aTargets": [3]
            },
            {
                "mRender": function (data, type, full) {
                    return "<a href='status.php?showpid=" + ppid + "&showres=Accepted&showname=" + full[3] + "'>" + data + "</a>";
                },
                "aTargets": [1]
            }
        ],
        "iDisplayStart": 0
    });

    $("#problem").addClass("active");

});