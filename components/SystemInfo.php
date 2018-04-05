<?php

namespace app\components;

class SystemInfo
{
    public static function humanFileSize($bytes)
    {
        if ($bytes == 0)
            return '0 B';

        $units = ['B', 'K', 'M', 'G', 'T'];
        $size = '';

        while ($bytes > 0 && count($units) > 0) {
            $size = strval($bytes % 1024) . ' ' . array_shift($units) . ' ' . $size;
            $bytes = intval($bytes / 1024);
        }

        return $size;
    }

    public static function getRemoteAddr()
    {
        if (isset($_SERVER["HTTP_X_REAL_IP"])) {
            return $_SERVER["HTTP_X_REAL_IP"];
        } else if (isset($_SERVER["HTTP_X_FORWARDED_FOR"])) {
            return preg_replace('/^.+,\s*/', '', $_SERVER["HTTP_X_FORWARDED_FOR"]);
        } else {
            return $_SERVER["REMOTE_ADDR"];
        }
    }

    public static function getServerAddr()
    {
        if ($_SERVER["SERVER_ADDR"] != "127.0.0.1") {
            return $_SERVER["SERVER_ADDR"];
        } else {
            return gethostbyname(php_uname('n'));
        }
    }

    public static function getStat()
    {
        $content = file('/proc/stat');
        $array = array_shift($content);
        $array = preg_split('/\s+/', trim($array));
        return array_slice($array, 1);
    }

    public static function getCpuInfo()
    {
        $info = [];

        if (!($str = @file("/proc/cpuinfo")))
            return false;

        $str = implode("", $str);
        @preg_match_all("/processor\s{0,}\:+\s{0,}([\w\s\)\(\@.-]+)([\r\n]+)/s", $str, $processor);
        @preg_match_all("/model\s+name\s{0,}\:+\s{0,}([\w\s\)\(\@.-]+)([\r\n]+)/s", $str, $model);

        if (count($model[0]) == 0) {
            @preg_match_all("/Hardware\s{0,}\:+\s{0,}([\w\s\)\(\@.-]+)([\r\n]+)/s", $str, $model);
        }
        @preg_match_all("/cpu\s+MHz\s{0,}\:+\s{0,}([\d\.]+)[\r\n]+/", $str, $mhz);

        if (count($mhz[0]) == 0) {
            $values = @file("/sys/devices/system/cpu/cpu0/cpufreq/cpuinfo_max_freq");
            $mhz = array("", array(sprintf('%.3f', intval($values[0]) / 1000)));
        }

        @preg_match_all("/cache\s+size\s{0,}\:+\s{0,}([\d\.]+\s{0,}[A-Z]+[\r\n]+)/", $str, $cache);
        @preg_match_all("/(?i)bogomips\s{0,}\:+\s{0,}([\d\.]+)[\r\n]+/", $str, $bogomips);

        $zh = (substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2) === 'zh');

        if (is_array($model[1])) {
            $info['num'] = sizeof($processor[1]);
            $info['model'] = $model[1][0];
            $info['frequency'] = $mhz[1][0];
            $info['bogomips'] = $bogomips[1][0];
            if (count($cache[0]) > 0)
                $info['l2cache'] = trim($cache[1][0]);
        }

        return $info;
    }

    public static function getUpTime()
    {
        if (!($str = @file('/proc/uptime')))
            return false;

        $zh = (substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2) === 'zh');

        $uptime = '';
        $str = explode(' ', implode('', $str));
        $str = trim($str[0]);
        $min = $str / 60;
        $hours = $min / 60;
        $days = floor($hours / 24);
        $hours = floor($hours - ($days * 24));
        $min = floor($min - ($days * 60 * 24) - ($hours * 60));
        $duint = !$zh ? (' day' . ($days > 1 ? 's ' : ' ')) : '天';
        $huint = !$zh ? (' hour' . ($hours > 1 ? 's ' : ' ')) : '小时';
        $muint = !$zh ? (' minute' . ($min > 1 ? 's ' : ' ')) : '分钟';

        if ($days !== 0)
            $uptime = $days . $duint;
        if ($hours !== 0)
            $uptime .= $hours . $huint;
        $uptime .= $min . $muint;

        return $uptime;
    }

    public static function getTempInfo()
    {
        $info = ['cpu' => 0, 'gpu' => 'null'];

        if ($str = @file('/sys/class/thermal/thermal_zone0/temp'))
            $info['cpu'] = doubleval($str[0])  / 1000.0;

        if ($str = @file('/sys/class/thermal/thermal_zone10/temp'))
            $info['gpu'] = doubleval($str[0]) / 1000.0;

        return $info;
    }

    public static function getMemInfo()
    {
        $info = [];

        if (!($str = @file('/proc/meminfo')))
            return false;

        $str = implode('', $str);
        preg_match_all("/MemTotal\s{0,}\:+\s{0,}([\d\.]+).+?MemFree\s{0,}\:+\s{0,}([\d\.]+).+?Cached\s{0,}\:+\s{0,}([\d\.]+).+?SwapTotal\s{0,}\:+\s{0,}([\d\.]+).+?SwapFree\s{0,}\:+\s{0,}([\d\.]+)/s", $str, $buf);
        preg_match_all("/Buffers\s{0,}\:+\s{0,}([\d\.]+)/s", $str, $buffers);

        $info['memTotal'] = round($buf[1][0] / 1024, 2);
        $info['memFree'] = round($buf[2][0] / 1024, 2);
        $info['memBuffers'] = round($buffers[1][0] / 1024, 2);
        $info['memCached'] = round($buf[3][0] / 1024, 2);
        $info['memUsed'] = round($info['memTotal'] - $info['memFree'] - $info['memBuffers'] - $info['memCached'], 2);
        $info['memUsedPercent'] = (floatval($info['memTotal']) != 0) ? round($info['memUsed'] / $info['memTotal'] * 100, 2) : 0;
        $info['memBuffersPercent'] = (floatval($info['memTotal']) != 0) ? round($info['memBuffers'] / $info['memTotal'] * 100, 2) : 0;
        $info['memCachedPercent'] = (floatval($info['memTotal']) != 0) ? round($info['memCached'] / $info['memTotal'] * 100, 2) : 0;

        $info['swapTotal'] = round($buf[4][0] / 1024, 2);
        $info['swapFree'] = round($buf[5][0] / 1024, 2);
        $info['swapUsed'] = round($info['swapTotal'] - $info['swapFree'], 2);
        $info['swapPercent'] = (floatval($info['swapTotal']) != 0) ? round($info['swapUsed'] / $info['swapTotal'] * 100, 2) : 0;

        foreach ($info as $key => $value) {
            if (strpos($key, 'Percent') > 0)
                continue;
            if ($value < 1024)
                $info[$key] .= ' M';
            else
                $info[$key] = round($value / 1024, 3) . ' G';
        }

        return $info;
    }

    public static function getLoadAvg()
    {
        if (!($str = @file('/proc/loadavg')))
            return false;

        $str = explode(' ', implode('', $str));
        $str = array_chunk($str, 4);
        $loadavg = implode(' ', $str[0]);

        return $loadavg;
    }

    public static function getDistName()
    {
        foreach (glob('/etc/*release') as $name) {
            if ($name == '/etc/centos-release' || $name == '/etc/redhat-release' || $name == '/etc/system-release') {
                return array_shift(file($name));
            }

            $release_info = @parse_ini_file($name);

            if (isset($release_info['DISTRIB_DESCRIPTION']))
                return $release_info['DISTRIB_DESCRIPTION'];

            if (isset($release_info['PRETTY_NAME']))
                return $release_info['PRETTY_NAME'];
        }

        return php_uname('s') . ' ' . php_uname('r');
    }

    public static function getDiskInfo()
    {
        $info = [];

        $info['diskTotal'] = round(@disk_total_space('.') / (1024 * 1024 * 1024), 3);
        $info['diskFree'] = round(@disk_free_space('.') / (1024 * 1024 * 1024), 3);
        $info['diskUsed'] = round($info['diskTotal'] - $info['diskFree'], 3);
        $info['diskPercent'] = 0;
        if (floatval($info['diskTotal']) != 0)
            $info['diskPercent'] = round($info['diskUsed'] / $info['diskTotal'] * 100, 2);

        return $info;
    }

    public static function getNetDev()
    {
        $info = [];

        $strs = @file('/proc/net/dev');
        for ($i = 2; $i < count($strs); $i++) {
            $parts = preg_split('/\s+/', trim($strs[$i]));
            $dev = trim($parts[0], ':');
            $info[$dev] = array(
                'rx' => intval($parts[1]),
                'human_rx' => self::humanFileSize($parts[1]),
                'tx' => intval($parts[9]),
                'human_tx' => self::humanFileSize($parts[9]),
            );
        }

        return $info;
    }
}
