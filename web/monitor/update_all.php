<?php
include_once(dirname(__FILE__) . "/../functions/global.php");

$ojs = $db->get_results("select name from ojinfo where name not like 'JNU'", ARRAY_N);

foreach ($ojs as $one) {
    @system("php " . dirname(__FILE__) . "/monitor_util.php " . $one[0]);
}
