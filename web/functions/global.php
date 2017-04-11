<?php
//put some global functions here
include_once(dirname(__FILE__) . "/db_basic.php");
include_once(dirname(__FILE__) . "/cookie.php");
require_once(dirname(__FILE__) . "/../vendor/autoload.php");

if ($config["use_latex_render"]) include_once(dirname(__FILE__) . "/latexrender/latex.php");

if (!function_exists("latex_content")) {
    function latex_content($a)
    {
        return $a;
    }
}

function pwd($a)
{
    return sha1("fdsoijfdows" . md5($a . "8943udo1=_*()3e2"));
}

function match_shjs($lang)
{
    switch ($lang) {
        case "1":
            $lang = "cpp";
            break;
        case "2":
            $lang = "c";
            break;
        case "3":
            $lang = "java";
            break;
        case "4":
            $lang = "pascal";
            break;
        case "5":
            $lang = "python2";
            break;
        case "6":
            $lang = "csharp";
            break;
        case "7":
            $lang = "cpp";
            break;
        case "8":
            $lang = "perl";
            break;
        case "9":
            $lang = "ruby";
            break;
        case "10":
            $lang = "cpp";
            break;
        case "11":
            $lang = "sml";
            break;
        case "12":
            $lang = "cpp";
            break;
        case "13":
            $lang = "c";
            break;
        case "14":
            $lang = "c";
            break;
        case "15":
            $lang = "cpp";
            break;
        case "16":
            $lang = "python3";
            break;
    }
    return $lang;
}

function match_lang($lang)
{
    switch ($lang) {
        case "1":
            $lang = "GNU C++";
            break;
        case "2":
            $lang = "GNU C";
            break;
        case "3":
            $lang = "Oracle Java";
            break;
        case "4":
            $lang = "Free Pascal";
            break;
        case "5":
            $lang = "Python2";
            break;
        case "6":
            $lang = "C# (Mono)";
            break;
        case "7":
            $lang = "Fortran";
            break;
        case "8":
            $lang = "Perl";
            break;
        case "9":
            $lang = "Ruby";
            break;
        case "10":
            $lang = "Ada";
            break;
        case "11":
            $lang = "Standard ML";
            break;
        case "12":
            $lang = "Visual C";
            break;
        case "13":
            $lang = "Visual C++";
            break;
        case "14":
            $lang = "CLang";
            break;
        case "15":
            $lang = "CLang++";
            break;
        case "16":
            $lang = "Python3";
            break;
    }
    return $lang;
}

function get_ip()
{
    $ip = "";
    if (isset($_SERVER["HTTP_X_FORWARDED_FOR"])) {
        $ip = $_SERVER["HTTP_X_FORWARDED_FOR"];
    }
    if ($ip == "") $ip = $_SERVER['REMOTE_ADDR'];
    return convert_str(htmlspecialchars($ip));
}

function get_all_vnames()
{
    global $db;
    $sql = "select name from ojinfo where name not like 'JNU'";
    $result = array();
    foreach ((array)$db->get_results($sql, ARRAY_N) as $value) $result[] = $value[0];
    return $result;
}

function get_substitle()
{
    global $db;
    $substitle = $db->get_row("select value from config where name='substitle'", ARRAY_N);
    return $substitle[0];
}

function convert_str($mixed)
{
    global $db;
    if (is_array($mixed)) {
        foreach ($mixed as $k => $v) $mixed[$k] = convert_str($v);
        return $mixed;
    } else {
        if ($mixed === null) return "";
        if (get_magic_quotes_gpc()) {
            return $mixed;
        }
        return $db->escape($mixed);
    }
}

function hash_password($pwd)
{
    return sha1(md5($pwd));
}

function change_out_nick($str)
{
    $change = array(
        '&lt;' => '<',
    );
    $s = strtr($str, $change);
    $s = strip_tags(nl2br($s));
    return htmlspecialchars($s);
}

function clear_cookies()
{
    global $config;
    setcookie($config["cookie_prefix"] . "username", "", 0, $config["base_path"]);
    setcookie($config["cookie_prefix"] . "password", "", 0, $config["base_path"]);
}

function set_cookies($username, $password, $time = 0)
{
    global $config;
    setcookie($config["cookie_prefix"] . "username", $username, $time, $config["base_path"]);
    setcookie($config["cookie_prefix"] . "password", $password, $time, $config["base_path"]);
}

function mkdirs($path, $mode = 0755)
{ //creates directory tree recursively
    $dirs = explode('/', $path);
    $pos = strrpos($path, ".");
    if ($pos === false) $subamount = 0;
    else $subamount = 1;
    for ($c = 0; $c < count($dirs) - $subamount; $c++) {
        $thispath = "";
        for ($cc = 0; $cc <= $c; $cc++) $thispath .= $dirs[$cc] . '/';
        if (!file_exists($thispath)) mkdir($thispath, $mode);
    }
}

function randomstr($l)
{
    $alphabet = "abcdefghijkmnpqrstuwxyzABCDEFGHJKLMNPQRSTUWXYZ23456789";
    $pass = array();
    $alphaLength = strlen($alphabet) - 1;
    for ($i = 0; $i < $l; $i++) {
        $n = rand(0, $alphaLength);
        $pass[] = $alphabet[$n];
    }
    return implode($pass);
}

function get_url($url)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    $content = curl_exec($ch);
    curl_close($ch);

    return $content;
}

function isset_and_equal($array, $needle, $expect)
{
    return isset($array[$needle]) && $array[$needle] == $expect;
}

function fetch_default($array, $needle, $default)
{
    return isset($array[$needle]) && $array[$needle] != NULL ? $array[$needle] : $default;
}
