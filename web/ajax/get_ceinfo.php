<?php

include_once(dirname(__FILE__) . "/../functions/global.php");
$runid = convert_str($_GET["runid"]);

$ret["code"] = 0;
$query = "select ce_info from status where runid='$runid'";
list($ceinfo) = $db->get_row($query, ARRAY_N);
$ceinfo = preg_replace('/\<br(\s*)?\/?\>/i', "\n", $ceinfo);
$ceinfo = htmlspecialchars($ceinfo);
$ret["msg"] = $ceinfo;

echo json_encode($ret);

?>
