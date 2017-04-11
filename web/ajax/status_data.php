<?php
include_once(dirname(__FILE__) . "/../functions/users.php");

$aColumns = array('username', 'runid', 'pid', 'result', 'language', 'time_used', 'memory_used', 'length(source)', "time_submit", "isshared");
$sIndexColumn = "runid";
$sTable = "status";

//paging
$sLimit = "";
if (isset($_GET['iDisplayStart']) && $_GET['iDisplayLength'] != '-1') {
    $sLimit = "LIMIT " . intval($_GET['iDisplayStart']) . ", " .
        intval($_GET['iDisplayLength']);
}

$sOrder = "ORDER BY runid desc";


/* Individual column filtering */
for ($i = 0; $i < count($aColumns); $i++) {
    if ($_GET['bSearchable_' . $i] == "true" && $_GET['sSearch_' . $i] != '') {
        if ($sWhere == "") {
            $sWhere = "WHERE ";
        } else {
            $sWhere .= " AND ";
        }
        if ($aColumns[$i] == "pid" || $aColumns[$i] == "language" || $aColumns[$i] == "username" || $aColumns[$i] == "result") {
            $sWhere .= $aColumns[$i] . " = '" . convert_str($_GET['sSearch_' . $i]) . "' ";
        } else $sWhere .= $aColumns[$i] . " LIKE '%" . convert_str($_GET['sSearch_' . $i]) . "%' ";
    }
}
$extra_condition = "(contest_belong=0 or contest_belong in (select cid from contest where end_time<now()))";
$normal_condition = "contest_belong=0";
$condition = $extra_condition;
//    echo "<script>alert($sWhere);</script>";
if ($sWhere == "") $sWhere = "WHERE " . $condition;
else $sWhere .= " AND " . $condition;

/* Data set length after filtering */
/*    $sQuery = "
    SELECT FOUND_ROWS()
";
$rResultFilterTotal = mysql_query( $sQuery ) or die(mysql_error());
$aResultFilterTotal = mysql_fetch_array($rResultFilterTotal);
$iFilteredTotal = $aResultFilterTotal[0];
$iFilteredTotal=$maxrunid;*/

/* Total data set length */
$sQuery = "
    SELECT max(" . $sIndexColumn . ")
    FROM   $sTable
";
$aResultTotal = $db->get_row($sQuery, ARRAY_N);
$iTotal = $aResultTotal[0];
if ($EZSQL_ERROR) die("SQL Error!");
//    $iTotal=$maxrunid;


/*
 * Output
 */
$output = array(
    "sEcho" => intval($_GET['sEcho']),
    "iTotalRecords" => $iTotal,
    "iTotalDisplayRecords" => $iTotal,
    "aaData" => array()
);

$isv = $current_user->is_codeviewer();

/*
 * SQL queries
 * Get data to display
 */
$sQuery = "
    SELECT " . str_replace(" , ", " ", implode(", ", $aColumns)) . "
    FROM   $sTable
    $sWhere
    $sOrder
    $sLimit
";

foreach ((array)$db->get_results($sQuery, ARRAY_A) as $aRow) {
    $row = array();
    $aRow["language"] = match_lang($aRow["language"]);
    if ($aRow["memory_used"] != 0) {
        $aRow["memory_used"] .= " KB";
        $aRow["time_used"] .= " ms";
    } else {
        $aRow["memory_used"] = "";
        if ($aRow["time_used"] != 0) $aRow["time_used"] .= " ms"; else $aRow["time_used"] = "";
    }
    $aRow["length(source)"] .= " B";
    for ($i = 0; $i < count($aColumns) - 1; $i++) {
        if ($aColumns[$i] != ' ') {
            $row[] = $aRow[$aColumns[$i]];
        }
    }
    if ($aRow["isshared"] == TRUE || $current_user->match($aRow["username"]) || $isv) $row[] = 1;
    else $row[] = 0;
    $output['aaData'][] = $row;
}

echo json_encode($output);
