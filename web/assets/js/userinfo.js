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

    var table = document.getElementById('userstat');
    var options = {
        chart: {
            renderTo: 'userpie',
            plotBackgroundColor: null,
            plotBorderWidth: null,
            backgroundColor: null,
            plotShadow: false
        },
        title: {
            text: 'User Statistics'
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


    $("#showac").click(function () {
        $(this).hide();
        $("#userac").collapse("show");
        $("#hideac").show();
    });
    $("#hideac").click(function () {
        $(this).hide();
        $("#userac").collapse("hide");
        $("#showac").show();
    });
    $("#compareform").submit(function () {
        var target = $("div#compareinfo");
        target.html('<img src="img/ajax-loader.gif" /> Loading...');
        target.collapse("show");
        $("#hidecompare").hide();
        $("#compare").show();
        $.get('ajax/compare.php', {name1: nametoc, name2: $("#user2").val()}, function (data) {
            //target.collapse("hide");
            target.html(data);
            target.collapse("show");
            $("#hidecompare").show();
            $("#compare").show();
        });
        return false;
    });
    $("#hidecompare").click(function () {
        $(this).hide();
        $("#compare").hide();
        $("#compareinfo").collapse("hide");
        $("#compare").show();
    });

    if (getURLPara("name") == $.cookie(cookie_prefix + "username")) $("#userspace").addClass("active");
});