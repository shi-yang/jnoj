<?php
include_once(dirname(__FILE__) . "/../functions/global.php");
include_once(dirname(__FILE__) . "/../functions/users.php");

$aColumns = array('total_ce', 'pid', 'title', 'tags', 'total_ac', 'total_submit', 'vname', 'author');
$sIndexColumn = "pid";
$sTable = "problem";

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
        if ($aColumns[intval($_GET['iSortCol_' . $i])] == "pid" && $_GET['sSortDir_' . $i] == "asc")
            continue;
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
        if ($aColumns[$i] == "source" || $aColumns[$i] == "title") {
            $str = $_GET['sSearch'];
            $change = array(
                ' ' => '%',
            );
            $s = strtr(convert_str($str), $change);
            $sWhere .= $aColumns[$i] . " LIKE '%" . $s . "%' OR ";
        } else $sWhere .= $aColumns[$i] . " LIKE '%" . convert_str($_GET['sSearch']) . "%' OR ";
    }
    $sWhere = substr_replace($sWhere, "", -3);
    $sWhere .= ')';
}

/* Individual column filtering */
for ($i = 0; $i < count($aColumns); $i++) {
    if (isset_and_equal($_GET, 'bSearchable_' . $i, "true") &&
        !isset_and_equal($_GET, 'sSearch_' . $i, '')
    ) {
        if ($sWhere == "") {
            $sWhere = "WHERE ";
        } else {
            $sWhere .= " AND ";
        }
        if ($i == 0) {
            if ($_GET['sSearch_' . $i] == "1")
                $sWhere .= " pid not in (select distinct(pid) from status where result='accepted' and username='$nowuser') ";
            else if ($_GET['sSearch_' . $i] == "0")
                $sWhere .= "pid like '%'";
        } else if ($aColumns[$i] == "vname") {
            $sWhere .= $aColumns[$i] . " = '" . convert_str($_GET['sSearch_' . $i]) . "' ";
        } else {
            $sWhere .= $aColumns[$i] . " LIKE '%" . convert_str($_GET['sSearch_' . $i]) . "%' ";
        }
    }
}
//    echo "<script>alert($sWhere);</script>";
if ($sWhere == "") $sWhere = "WHERE hide=0";
$sWhere .= " AND hide=0";


/* Total data set length */
$sQuery = "
    SELECT COUNT(" . $sIndexColumn . ")
    FROM   $sTable where hide=0
";
$aResultTotal = $db->get_row($sQuery, ARRAY_N);
$iTotal = $aResultTotal[0];

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
    $row = array();
    for ($i = 0; $i < count($aColumns); $i++) {
        if ($i == 0) {
            if ($current_user->is_valid()) {
                $db->query("select pid from status where pid='" . $aRow[$aColumns[1]] . "' and username='" . $current_user->get_username() . "' and result='Accepted'");
                if ($db->num_rows > 0) {
                    $row[] = "Yes";
                } else {
                    $db->query("select pid from status where pid='" . $aRow[$aColumns[1]] . "' and username='" . $current_user->get_username() . "'");
                    if ($db->num_rows > 0) {
                        $row[] = "No";
                    } else {
                        $row[] = "";
                    }
                }
            } else {
                $row[] = "";
            }
        } else if ($aColumns[$i] != ' ' && $aColumns[$i] != 'author') {
            /* General output */
            $row[] = $aRow[$aColumns[$i]];
        }
    }
    $output['aaData'][] = $row;
}
echo json_encode($output);
