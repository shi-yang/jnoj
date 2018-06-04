<?php

require __DIR__ . '/../vendor/autoload.php';

function h($txt) {
    return htmlspecialchars($txt, ENT_QUOTES, 'UTF-8');
}

$tpl = array();

if (isset($_POST['textIn'])) {
    $textIn = str_replace("\r\n", "\n", $_POST['textIn']);

    $tpl['inBytes'] = strlen($textIn);
    $startTime = microtime(true);
    try {
        $tpl['output'] = \JSMin\JSMin::minify($textIn);
    } catch (Exception $e) {
        $tpl['exceptionMsg'] = getExceptionMsg($e, $textIn);
        $tpl['output'] = $textIn;
        sendPage($tpl);
    }
    $tpl['time'] = microtime(true) - $startTime;
    $tpl['outBytes'] = strlen($tpl['output']);
}

sendPage($tpl);


/**
 * @param Exception $e
 * @param string $input
 * @return string HTML
 */
function getExceptionMsg(Exception $e, $input) {
    $msg = "<p>" . h($e->getMessage()) . "</p>";

    if (0 !== strpos(get_class($e), 'JSMin\\Unterminated')
        || !preg_match('~byte (\d+)~', $e->getMessage(), $m)) {
        return $msg;
    }

    $msg .= "<pre>";
    if ($m[1] > 200) {
        $msg .= h(substr($input, ($m[1] - 200), 200));
    } else {
        $msg .= h(substr($input, 0, $m[1]));
    }
    $highlighted = isset($input[$m[1]]) ? h($input[$m[1]]) : '&#9220;';
    if ($highlighted === "\n") {
        $highlighted = "&#9166;\n";
    }
    $msg .= "<span style='background:#c00;color:#fff'>$highlighted</span>";
    $msg .= h(substr($input, $m[1] + 1, 200)) . "</span></pre>";

    return $msg;
}

/**
 * Draw page
 *
 * @param array $vars
 */
function sendPage($vars) {
    header('Content-Type: text/html; charset=utf-8');

    ?>
    <!DOCTYPE html><head><title>JSMin</title></head>
    <?php
    if (isset($vars['exceptionMsg'])) {
        echo $vars['exceptionMsg'];
    }
    if (isset($vars['time'])) {
        echo "
<table>
    <tr><th>Bytes in</th><td>{$vars['inBytes']} (after line endings normalized to <code>\\n</code>)</td></tr>
    <tr><th>Bytes out</th><td>{$vars['outBytes']} (reduced " . round(100 - (100 * $vars['outBytes'] / $vars['inBytes'])) . "%)</td></tr>
    <tr><th>Time (s)</th><td>" . round($vars['time'], 5) . "</td></tr>
</table>
    ";
    }
    ?>
    <form action="?2" method="post">
    <p><label>Content<br><textarea name="textIn" cols="80" rows="35" style="width:99%"><?php
                if (isset($vars['output'])) {
                    echo h($vars['output']);
                }
                ?></textarea></label></p>
    <p><input type="submit" name="method" value="JSMin::minify()"></p>
    </form><?php
    exit;
}
