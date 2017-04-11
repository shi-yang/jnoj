<?php
include_once(dirname(__FILE__) . "/../functions/users.php");

$aColumns = array('distinct(problem.pid)', 'problem.pid', 'title', 'source', 'total_ac', 'total_submit', 'vacnum', 'vtotalnum', 'vacpnum', 'vtotalpnum', 'vname', 'vid', 'author');
$sIndexColumn = "problem.pid";
$sTable = "problem";
$sWhere = "";

if ($_GET["logic"] == "and") {
    for ($i = 0; $i < $_GET["catenum"]; $i++) {
        if ($i == 0) $sTable .= " INNER JOIN ( SELECT pid FROM  `problem_category` WHERE catid ='" . convert_str($_GET["cate$i"]) . "' ) db$i ON db$i.pid = problem.pid";
        else $sTable .= " INNER JOIN ( SELECT pid FROM  `problem_category` WHERE catid ='" . convert_str($_GET["cate$i"]) . "' ) db$i ON db$i.pid = db" . ($i - 1) . ".pid";
    }
} else if ($_GET["logic"] == "or") {
    $sTable .= " INNER JOIN `problem_category` ON problem.pid =  `problem_category`.pid ";
    for ($i = 0; $i < $_GET["catenum"]; $i++) {
        if ($sWhere == "") $sWhere = " WHERE ( catid ='" . convert_str($_GET["cate$i"]) . "' ";
        else $sWhere .= " OR catid ='" . convert_str($_GET["cate$i"]) . "' ";
    }
    if ($sWhere != "") $sWhere .= " ) ";
}

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
                " . ($_GET['sSortDir_' . $i] == "asc" : "asc" ? "desc") .", ";
        }
    }

    $sOrder = substr_replace($sOrder, "", -2);
    if ($sOrder == "ORDER BY") {
        $sOrder = "";
    }
}

//filtering
if ($_GET['sSearch'] != "") {
    $sWhere = "WHERE (";
    for ($i = 1; $i < count($aColumns); $i++) {
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
    if ($_GET['bSearchable_' . $i] == "true" && $_GET['sSearch_' . $i] != '') {
        if ($sWhere == "") {
            $sWhere = "WHERE ";
        } else {
            $sWhere .= " AND ";
        }
        if ($i == 0) {
            if ($_GET['sSearch_' . $i] == "1") $sWhere .= " problem.pid not in (select distinct(pid) from status where result='accepted' and username='$nowuser') ";
            else if ($_GET['sSearch_' . $i] == "0") $sWhere .= "problem.pid like '%'";
        } else if ($aColumns[$i] == "vname") $sWhere .= $aColumns[$i] . " = '" . convert_str($_GET['sSearch_' . $i]) . "' ";
        else $sWhere .= $aColumns[$i] . " LIKE '%" . convert_str($_GET['sSearch_' . $i]) . "%' ";
    }
}
if ($sWhere == "") $sWhere = "WHERE hide=0";
else $sWhere .= " AND hide=0";

/* Total data set length */
$sQuery = "
    SELECT COUNT(" . $sIndexColumn . ")
    FROM   $sTable where hide=0
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


foreach ((array)$db->get_results($sQuery, ARRAY_N) as $aRow) {
    $row = array();
    //var_dump($aRow);
    for ($i = 0; $i < count($aColumns); $i++) {
        if ($i == 0) {
            if ($current_user->is_valid()) {
                $db->query("select * from status where pid='" . $aRow[0] . "' and username='" . $current_user->get_username() . "' and result='Accepted'");
                if ($db->num_rows > 0) $row[] = "Yes";
                else {
                    $db->query("select * from status where pid='" . $aRow[0] . "' and username='" . $current_user->get_username() . "'");
                    if ($db->num_rows > 0) $row[] = "No";
                    else $row[] = "";
                }
            } else $row[] = "";
        } else if ($aColumns[$i] != ' ' && $aColumns[$i] != 'author') {
            /* General output */
            $row[] = $aRow[$i - 1];
        }
    }
    $output['aaData'][] = $row;
}

echo json_encode($output);

?>
