<?php
//basic databases
include_once(dirname(__FILE__) . "/../config.php");
require_once(dirname(__FILE__) . "/../vendor/autoload.php");

$db_class = "ezSQL_" . $config["database"]["type"];
$db = new $db_class($config["database"]["username"], $config["database"]["password"], $config["database"]["table"], $config["database"]["host"], $config["database"]["port"], "UTF-8");
if ($config["database_debug"]) {
    $db->debug_all = true;
}
