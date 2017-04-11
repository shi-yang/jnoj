<?php
include_once(dirname(__FILE__) . "/../functions/global.php");
$pid = convert_str($_GET['pid']);
if ($pid == "") $pid = "0";
$aColumns = array("time_submit", "count(*)", "runid", "username", "time_used", "memory_used", "language", "length(source)");
$sTable = "(select runid,username,time_used,memory_used,language,source,time_submit from status where result='Accepted' and pid='$pid' order by time_used,memory_used,length(source),time_submit) status2 ";
$sIndexColumn = "runid";
//paging
$sLimit = "";
if (isset($_GET['iDisplayStart']) && $_GET['iDisplayLength'] != '-1') {
    $sLimit = "LIMIT " . intval($_GET['iDisplayStart']) . ", " .
        intval($_GET['iDisplayLength']);
}

//ordering
if (isset($_GET['iSortCol_0'])) {
    $sOrder = "ORDER BY  ";
    for ($i = 0; $i < intval($_GET['iSortingCols']); $i++) {
        if ($_GET['bSortable_' . intval($_GET['iSortCol_' . $i])] == "true") {
            $sOrder .= $aColumns[intval($_GET['iSortCol_' . $i])] . "
                " . ($_GET['sSortDir_' . $i] == "asc" ? "asc" : "desc") . ", ";
        }
    }

    $sOrder = substr_replace($sOrder, "", -2);
    if ($sOrder == "ORDER BY") {
        $sOrder = "";
    }
}

//$db->debug_all = true;

/* Total data set length */
$sQuery = "
    SELECT COUNT(" . $sIndexColumn . ")
    FROM   $sTable
";
$aResultTotal = $db->get_row($sQuery, ARRAY_N);
$iTotal = $aResultTotal[0];
if ($EZSQL_ERROR) die("SQL Error!");

/*
 * SQL queries
 * Get data to display
 */
$sQuery = "
    SELECT " . str_replace(" , ", " ", implode(", ", $aColumns)) . "
    FROM   $sTable
    GROUP BY username
    $sWhere
    $sOrder
";
$db->query($sQuery);
$iFilteredTotal = $db->num_rows;

/*
 * Output
 */
$output = array(
    "sEcho" => intval($_GET['sEcho']),
    "iTotalRecords" => $iTotal,
    "iTotalDisplayRecords" => $iFilteredTotal,
    "aaData" => array()
);

$sQuery = "
    SELECT " . str_replace(" , ", " ", implode(", ", $aColumns)) . "
    FROM   $sTable
    GROUP BY username
    $sWhere
    $sOrder
    $sLimit
";

$cnt = 0;

foreach ((array)$db->get_results($sQuery, ARRAY_N) as $aRow) {
    $cnt++;
    $row = array();
    for ($i = 0; $i < count($aColumns); $i++) {
        if ($i == 0) {
            $row[] = intval($_GET['iDisplayStart']) + $cnt;
        } else if ($aColumns[$i] == "time_used") {
            $row[] = $aRow[$i] . " ms";
        } else if ($aColumns[$i] == "memory_used") {
            $row[] = $aRow[$i] . " KB";
        } else if ($aColumns[$i] == "length(source)") {
            $row[] = $aRow[$i] . " B";
        } else if ($aColumns[$i] == "language") {
            $row[] = match_lang($aRow[$i]);
        } else if ($aColumns[$i] != ' ') {
            /* General output */
            $row[] = $aRow[$i];
        }
    }
    $output['aaData'][] = $row;
}

echo json_encode($output);

?>
