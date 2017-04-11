<?php
include_once(dirname(__FILE__) . "/../functions/global.php");

$aColumns = array('cid', 'title', 'start_time', 'end_time', 'hide_others', 'isprivate', 'owner', 'isvirtual', 'type', 'has_cha', 'challenge_end_time', 'challenge_start_time');
$sIndexColumn = "cid";
$sTable = "contest";


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

//filtering
$sWhere = "";
if ($_GET['sSearch'] != "") {
    $sWhere = "WHERE (";
    for ($i = 0; $i < count($aColumns); $i++) {
        $sWhere .= $aColumns[$i] . " LIKE '%" . convert_str($_GET['sSearch']) . "%' OR ";
    }
    $sWhere = substr_replace($sWhere, "", -3);
    $sWhere .= ')';
}

/* Individual column filtering */
for ($i = 0; $i < count($aColumns); $i++) {
    if ($_GET['bSearchable_' . $i] == "true" && $_GET['sSearch_' . $i] != '') {
        if ($sWhere == "") {
            $sWhere = "WHERE ";
        } else {
            $sWhere .= " AND ";
        }
        if ($aColumns[$i] == "isvirtual" || $aColumns[$i] == "isprivate") $sWhere .= $aColumns[$i] . " = '" . convert_str($_GET['sSearch_' . $i]) . "' ";
        else if ($aColumns[$i] == "type") {
            if ($_GET['sSearch_' . $i] != "-99") $sWhere .= $aColumns[$i] . " = '" . convert_str($_GET['sSearch_' . $i]) . "' ";
            else $sWhere .= $aColumns[$i] . " != '99' ";
        } else $sWhere .= $aColumns[$i] . " LIKE '%" . convert_str($_GET['sSearch_' . $i]) . "%' ";
    }
}

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
    SELECT COUNT(" . $sIndexColumn . ")
    FROM   $sTable
    $sWhere
    $sOrder
";
$db->query($sQuery);
list($iFilteredTotal) = $db->get_row($sQuery, ARRAY_N);

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
    $sWhere
    $sOrder
    $sLimit
";

foreach ((array)$db->get_results($sQuery, ARRAY_A) as $aRow) {
    //var_dump($aRow);
    $row = array();
    $orgt = $aRow['title'];
    if ($aRow[$aColumns[8]] == 1) $aRow['title'] = "<span style='color:blue'>[CF]</span> " . $aRow['title'];
    else if ($aRow[$aColumns[8]] == 99) $aRow['title'] = "<span style='color:blue'>[Replay]</span> " . $aRow['title'];
    for ($i = 0; $i < count($aColumns); $i++) {
        if ($aColumns[$i] == 'hide_others') continue;
        if ($aColumns[$i] == 'end_time') {
            if ($aRow['has_cha'] == 1) $row[] = $aRow[$aColumns[10]];
            else $row[] = $aRow[$aColumns[$i]];
            $nowt = date("Y-m-d H:i:s");
            if ($nowt < $aRow['start_time']) $row[] = "<span class='cscheduled'>Scheduled</span>";
            else if ($aRow['has_cha'] == 1 && $nowt > $aRow['end_time'] && $nowt < $aRow[$aColumns[11]]) $row[] = "<span class='crunning'>Intermission</span>";
            else if ($aRow['has_cha'] == 1 && $nowt > $aRow[$aColumns[11]] && $nowt < $aRow[$aColumns[10]]) $row[] = "<span class='crunning'>Challenging</span>";
            else if ($nowt < $aRow['end_time']) $row[] = "<span class='crunning'>Running</span>";
            else $row[] = "<span class='cpassed'>Passed</span>";
        } else if ($aColumns[$i] == 'isprivate') {
            if ($aRow[$aColumns[$i]] == 0) $row[] = "<span class='cpublic'>Public</span>";
            else if ($aRow[$aColumns[$i]] == 2) $row[] = "<span class='cprivate'>Password</span>";
            else $row[] = "<span class='cprivate'>Private</span>";
        } else if ($aColumns[$i] == 'isvirtual') {
            if ($aRow[$aColumns[$i]] == 0) $row[] = "Normal";
            else if ($aRow[$aColumns[$i]] == 1) $row[] = "Virtual";
        } else if ($aColumns[$i] != 'challenge_end_time' && $aColumns[$i] != 'challenge_start_time' && $aColumns[$i] != 'has_cha' && $aColumns[$i] != ' ') {
            /* General output */
            $row[] = $aRow[$aColumns[$i]];
        }
    }
    $output['aaData'][] = $row;
}

echo json_encode($output);
