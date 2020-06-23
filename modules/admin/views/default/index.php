<?php
$this->title = "Admin";
function __($message)
{
    $messages = array(
        'Download' => '文件下载',
        'Gateway' => '网关管理',
        'Monitor' => '性能监控',
        'Server Information' => '服务器参数',
        'IP Address' => 'IP 地址',
        'Port' => '端口',
        'User' => '用户',
        'Server Realtime Data' => '服务器实时数据',
        'Time' => '当前时间',
        'Uptime' => '已运行时间',
        'CPU Model' => 'CPU 型号',
        'L2 Cache' => '二级缓存',
        'Frequency' => '频率',
        'CPU Usage' => 'CPU 使用状况',
        'CPU Temperature' => 'CPU 温度',
        'GPU Temperature' => 'GPU 温度',
        'Memory Usage' => '内存使用状况',
        'Physical Memory' => '物理内存',
        'Used' => '已用',
        'Cached' => '已缓存',
        'Free' => '空闲',
        'Percent' => '使用率',
        'Total Space' => '总空间',
        'Disk Usage' => '硬盘使用状况',
        'Loadavg' => '系统平均负载',
        'Network Usage' => '网络使用状况',
        'Tx' => '出网',
        'Rx' => '入网',
        'Realtime' => '实时',
        'Network Neighborhood' => '网络邻居',
        'Type' => '类型',
        'Device' => '设备',
        'PHP Information' => 'PHP 信息',
        'Version' => '版本',
        'Zend OpCache' => 'Zend OpCache',
        'Server API' => '服务器接口',
        'Memory Limit' => '内存限制',
        'POST Max Size' => '最大上传大小',
        'Upload Max FileSize' => '最大上传文件',
        'Max Execution Time' => '最大执行时间',
        'Default Socket Timeout' => '默认 Socket 超时',
        'PHP Extension' => 'PHP 扩展',
        'Prober' => '探针',
        'Turbo Version' => '极速版',
        'Back to top' => '返回顶部',
    );

    if (substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2) === 'zh') {
        print isset($messages[$message]) ? $messages[$message] : $message;
    } else {
        print $message;
    }
}
?>
<div class="admin-default-index">
    <h1>Hello, <?= Yii::$app->user->identity->nickname ?></h1>
</div>
<hr>
<div class="table-responsive">
    <table class="table table-bordered">
        <tr>
            <th colspan="4"><?php __('Server Realtime Data'); ?></th>
        </tr>
        <tr>
            <td><?php __('Time'); ?></td>
            <td><span id="stime"><?= $stime; ?></span></td>
            <td><?php __('Uptime'); ?></td>
            <td><span id="uptime"><?= $uptime; ?></span></td>
        </tr>
        <tr>
        </tr>
        <tr>
            <td><?php __('CPU Usage'); ?></td>
            <td colspan="3">
                <span id="stat_user" class="text-info">0.0</span> user,
                <span id="stat_sys" class="text-info">0.0</span> sys,
                <span id="stat_nice">0.0</span> nice,
                <span id="stat_idle" class="text-info">99.9</span> idle,
                <span id="stat_iowait">0.0</span> iowait,
                <span id="stat_irq">0.0</span> irq,
                <span id="stat_softirq">0.0</span> softirq,
                <span id="stat_steal">0.0</span> steal
                <div class="progress">
                    <div id="stat_UserBar" class="progress-bar progress-bar-success" role="progressbar"
                         style="width:1px">&nbsp;
                    </div>
                    <div id="stat_SystemBar" class="progress-bar progress-bar-warning" role="progressbar"
                         style="width:0px">&nbsp;
                    </div>
                </div>
            </td>
        </tr>
        <tr>
            <td><?php __('Memory Usage'); ?></td>
            <td colspan="3">
                <?php __('Physical Memory'); ?> <span id="meminfo_Total"
                                                      class="text-info"><?= $meminfo['memTotal']; ?> </span>
                , <?php __('Used'); ?> <span id="meminfo_Used"
                                             class="text-info"><?= $meminfo['memUsed']; ?></span>
                , <?php __('Cached'); ?> <span id="meminfo_Buffers"
                                               class="text-info"><?= $meminfo['memBuffers']; ?></span>
                / <span id="meminfo_Cached" class="text-info"><?= $meminfo['memCached']; ?></span>
                , <?php __('Free'); ?> <span id="meminfo_Free"
                                             class="text-info"><?= $meminfo['memFree']; ?></span>
                , <?php __('Percent'); ?> <span
                        id="meminfo_UsedPercent"><?= $meminfo['memUsedPercent']; ?></span>%<br>
                <div class="progress">
                    <div id="meminfo_UsedBar" class="progress-bar progress-bar-success" role="progressbar"
                         style="width:<?= $meminfo['memUsedPercent']; ?>%"></div>
                    <div id="meminfo_BuffersBar" class="progress-bar progress-bar-info" role="progressbar"
                         style="width:<?= $meminfo['memBuffersPercent']; ?>%"></div>
                    <div id="meminfo_CachedBar" class="progress-bar progress-bar-warning" role="progressbar"
                         style="width:<?= $meminfo['memCachedPercent']; ?>%"></div>
                </div>
                <?php if ($meminfo['swapTotal'] > 0): ?>
                    SWAP：<span id="meminfo_swapTotal"><?= $meminfo['swapTotal']; ?></span>
                    , <?php __('Used'); ?> <span id="meminfo_swapUsed"><?= $meminfo['swapUsed']; ?></span>
                    , <?php __('Free'); ?> <span id="meminfo_swapFree"><?= $meminfo['swapFree']; ?></span>
                    , <?php __('Percent'); ?> <span
                            id="meminfo_swapPercent"><?= $meminfo['swapPercent']; ?></span>%
                    <div class="progress">
                        <div id="meminfo_swapBar" class="progress-bar progress-bar-danger" role="progressbar"
                             style="width:<?= $meminfo['swapPercent']; ?>%"></div>
                    </div>
                <?php endif; ?>
            </td>
        </tr>
        <tr>
            <td><?php __('Disk Usage'); ?></td>
            <td colspan="3">
                <?php __('Total Space'); ?> <?= $diskinfo['diskTotal']; ?>&nbsp;G，
                <?php __('Used'); ?> <span id="diskinfo_Used"><?= $diskinfo['diskUsed']; ?></span>&nbsp;G，
                <?php __('Free'); ?> <span id="diskinfo_Free"><?= $diskinfo['diskFree']; ?></span>&nbsp;G，
                <?php __('Percent'); ?> <span id="diskinfo_Percent"><?= $diskinfo['diskPercent']; ?></span>%
                <div class="progress">
                    <div id="diskinfo_UsedBar" class="progress-bar progress-bar-black" role="progressbar"
                         style="width:<?= $diskinfo['diskPercent']; ?>%"></div>
                </div>
            </td>
        </tr>
        <tr>
            <td><?php __('Loadavg'); ?></td>
            <td colspan="3" class="text-danger"><span id="loadAvg"><?= $loadavg; ?></span></td>
        </tr>
    </table>

    <table class="table table-bordered">
        <tr>
            <th colspan="5"><?php __('Network Usage'); ?></th>
        </tr>
        <?php foreach ($netdev as $dev => $info) : ?>
            <tr>
                <td style="width:13%"><?= $dev; ?> :</td>
                <td style="width:29%"><?php __('Rx'); ?>: <span class="text-info"
                                                                id="<?php printf('netdev_%s_human_rx', $dev); ?>"><?= $info['human_rx'] ?></span>
                </td>
                <td style="width:14%"><?php __('Realtime'); ?>: <span class="text-info"
                                                                      id="<?php printf('netdev_%s_delta_rx', $dev); ?>">0B/s</span>
                </td>
                <td style="width:29%"><?php __('Tx'); ?>: <span class="text-info"
                                                                id="<?php printf('netdev_%s_human_tx', $dev); ?>"><?= $info['human_tx'] ?></span>
                </td>
                <td style="width:14%"><?php __('Realtime'); ?>: <span class="text-info"
                                                                      id="<?php printf('netdev_%s_delta_tx', $dev); ?>">0B/s</span>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>

    <table class="table table-bordered">
        <tr>
            <th colspan="12"><?php __('PHP Information'); ?></th>
        </tr>
        <tr>
            <td style="width:16.7%"><?php __('Version'); ?></td>
            <td><?= phpversion(); ?></td>
            <td style="width:16.7%"><?php __('Zend OpCache'); ?></td>
            <td><?= ini_get('opcache.enable') == 1 ? 'On' : 'Off'; ?></td>
        </tr>
        <tr>
            <td style="width:16.7%"><?php __('Server API'); ?></td>
            <td><?= php_sapi_name(); ?></td>
            <td style="width:16.7%"><?php __('Memory Limit'); ?></td>
            <td><?= ini_get('memory_limit'); ?></td>
        </tr>
        <tr>
            <td style="width:16.7%"><?php __('POST Max Size'); ?></td>
            <td><?= ini_get('post_max_size'); ?></td>
            <td style="width:16.7%"><?php __('Upload Max FileSize'); ?></td>
            <td><?= ini_get('upload_max_filesize'); ?></td>
        </tr>
        <tr>
            <td style="width:16.7%"><?php __('Max Execution Time'); ?></td>
            <td><?= ini_get('max_execution_time'); ?>s</td>
            <td style="width:16.7%"><?php __('Default Socket Timeout'); ?></td>
            <td><?= ini_get('default_socket_timeout'); ?>s</td>
        </tr>
    </table>

    <p>Processed in <?php printf('%0.1f', (microtime(true) - $time_start)*1000);?> ms, <?= round(memory_get_usage() / 1024, 0).' KB';?> memory usage.</p>
</div>

<script type="text/javascript">
    var dom = {
        element: null,
        get: function (o) {
            function F() {
            }

            F.prototype = this
            obj = new F()
            obj.element = (typeof o == "object") ? o : document.createElement(o)
            return obj
        },
        width: function (w) {
            if (!this.element)
                return
            this.element.style.width = w
            return this
        },
        html: function (h) {
            if (!this.element)
                return
            this.element.innerHTML = h
            return this
        }
    };

    $ = function (s) {
        return dom.get(document.getElementById(s.substring(1)))
    };

    $.getJSON = function (url, f) {
        var xhr = null;
        if (window.XMLHttpRequest) {
            xhr = new XMLHttpRequest();
        } else {
            xhr = new ActiveXObject('MSXML2.XMLHTTP.3.0');
        }
        xhr.open('GET', url + '&_=' + new Date().getTime(), true)
        xhr.onreadystatechange = function () {
            if (xhr.readyState == 4 && xhr.status == 200) {
                if (window.JSON) {
                    f(JSON.parse(xhr.responseText))
                } else {
                    f((new Function('return ' + xhr.responseText))())
                }
            }
        }
        xhr.send()
    }

    var stat = <?= json_encode($stat); ?>;
    var netdev = <?= json_encode($netdev); ?>;

    function getSysinfo() {
        $.getJSON('?method=sysinfo', function (data) {
            $('#uptime').html(data.uptime)
            $('#stime').html(data.stime)

            stat_total = 0
            for (var i = 0; i < data.stat.length; i++) {
                stat[i] = data.stat[i] - stat[i]
                stat_total += stat[i]
            }
            $("#stat_user").html((100 * stat[0] / stat_total).toFixed(1))
            $("#stat_nice").html((100 * stat[1] / stat_total).toFixed(1))
            $("#stat_sys").html((100 * stat[2] / stat_total).toFixed(1))
            $("#stat_idle").html((100 * stat[3] / stat_total).toFixed(1).substring(0, 4))
            $("#stat_iowait").html((100 * stat[4] / stat_total).toFixed(1))
            $("#stat_irq").html((100 * stat[5] / stat_total).toFixed(1))
            $("#stat_softirq").html((100 * stat[6] / stat_total).toFixed(1))
            $("#stat_steal").html((100 * stat[7] / stat_total).toFixed(1))
            $("#stat_UserBar").width(100 * (stat[0] + stat[1]) / stat_total + '%')
            $("#stat_SystemBar").width((100 * (stat_total - stat[0] - stat[1] - stat[3]) / stat_total) + '%')
            stat = data.stat

            $('#meminfo_Total').html(data.meminfo.memTotal)
            $('#meminfo_Used').html(data.meminfo.memUsed)
            $('#meminfo_Free').html(data.meminfo.memFree)
            $('#meminfo_UsedPercent').html(data.meminfo.memUsedPercent)
            $('#meminfo_UsedBar').width(data.meminfo.memUsedPercent + '%')
            $('#meminfo_BuffersBar').width(data.meminfo.memBuffersPercent + '%')
            $('#meminfo_CachedBar').width(data.meminfo.memCachedPercent + '%')

            $('#meminfo_swapTotal').html(data.meminfo.swapTotal)
            $('#meminfo_swapUsed').html(data.meminfo.swapUsed)
            $('#meminfo_swapFree').html(data.meminfo.swapFree)
            $('#meminfo_swapPercent').html(data.meminfo.swapPercent)
            $('#meminfo_swapBar').width(data.meminfo.swapPercent + '%')

            $('#diskinfo_Used').html(data.diskinfo.diskUsed)
            $('#diskinfo_Free').html(data.diskinfo.diskFree)
            $('#diskinfo_Percent').html(data.diskinfo.diskPercent)
            $('#diskinfo_UsedBar').width(data.diskinfo.diskPercent + '%')

            $('#loadAvg').html(data.loadavg)

            for (var dev in netdev) {
                var info = netdev[dev]
                $('#netdev_' + dev + '_human_rx').html(data.netdev[dev].human_rx)
                $('#netdev_' + dev + '_human_tx').html(data.netdev[dev].human_tx)
                $('#netdev_' + dev + '_delta_rx').html(((data.netdev[dev].rx - info.rx) / 1024).toFixed(2) + 'K/s')
                $('#netdev_' + dev + '_delta_tx').html(((data.netdev[dev].tx - info.tx) / 1024).toFixed(2) + 'K/s')
            }
            netdev = data.netdev
        });
    }

    window.onload = function () {
        setInterval(getSysinfo, 1000)
    }

</script>
