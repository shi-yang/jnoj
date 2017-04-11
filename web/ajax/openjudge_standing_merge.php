<?php
require_once(dirname(__FILE__) . "/../functions/simple_html_dom.php");
$url = "http://poj.openjudge.cn/" . $_GET["contest"] . "/ranking/";
$str = get_url($url);
preg_match('/<table class="standing">(.*)<\/tbody>/s', $str, $match);
$result = $match[1];

$html = str_get_html($str);
$links = $html->find(".page-bar a");

foreach ($links as $link) {
//    echo $link;
    if ($link->class == "nextprev") continue;
    $url = "http://poj.openjudge.cn/" . $_GET["contest"] . "/ranking/" . $link->href;
    $str = get_url($url);
    preg_match('/<tbody>(.*)<\/tbody>/s', $str, $match);
    $result .= $match[1];
}
$result .= "</tbody></table>";
$result = '<html><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8" /><title>OpenJudge - 排名</title></head><body><table class="standing">' . $result . "</body></html>";

echo $result;

?>
