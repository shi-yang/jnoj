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

    public static function getStat()
    {
        $content = file('/proc/stat');
        $array = array_shift($content);
        $array = preg_split('/\s+/', trim($array));
        return array_slice($array, 1);
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
