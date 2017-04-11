<?php
include_once(dirname(__FILE__) . "/../functions/users.php");
include_once(dirname(__FILE__) . "/../functions/contests.php");
$cid = convert_str($_GET["cid"]);
if (!contest_exist($cid) || !($current_user->is_root() || contest_get_val($cid, "isprivate") == 0 ||
        (contest_get_val($cid, "isprivate") == 1 && $current_user->is_in_contest($cid)) ||
        (contest_get_val($cid, "isprivate") == 2 && contest_get_val($cid, "password") == $_COOKIE[$config["cookie_prefix"] . "contest_pass_$cid"]))
) die();


$aColumns = array('username', 'runid', 'pid', 'result', 'language', 'time_used', 'memory_used', 'length(source)', "time_submit", "isshared");
$sIndexColumn = "runid";
$sTable = "status";

//paging
$sLimit = "";
if (isset($_GET['iDisplayStart']) && $_GET['iDisplayLength'] != '-1') {
    $sLimit = "LIMIT " . intval($_GET['iDisplayStart']) . ", " .
        intval($_GET['iDisplayLength']);
}

foreach ((array)contest_get_problem_basic($cid) as $row) {
    $ltop[$row["lable"]] = $row["pid"];
    $ptocp[$row["pid"]] = $row["cpid"];
    $ptol[$row["pid"]] = $row["lable"];
}
$ishide = contest_get_val($cid, "hide_others");
if ($current_user->is_root() || (contest_get_val($cid, "owner_viewable") && $current_user->match(contest_get_val($cid, "owner")))) $isroot = true;
else $isroot = false;
if ($ishide && $isroot) $ishide = false;
if (contest_passed($cid)) $hidedt = false;
else $hidedt = true;

$sOrder = "ORDER BY runid desc";

/* Individual column filtering */
for ($i = 0; $i < count($aColumns); $i++) {
    if ($_GET['bSearchable_' . $i] == "true" && $_GET['sSearch_' . $i] != '') {
        if ($sWhere == "") {
            $sWhere = "WHERE ";
        } else {
            $sWhere .= " AND ";
        }
        if ($aColumns[$i] == "language" || $aColumns[$i] == "username" || $aColumns[$i] == "result") {
            $sWhere .= $aColumns[$i] . " = '" . convert_str($_GET['sSearch_' . $i]) . "' ";
        } else if ($aColumns[$i] == "pid") {
            $sWhere .= $aColumns[$i] . " = '" . convert_str($ltop[$_GET['sSearch_' . $i]]) . "' ";
        } else $sWhere .= $aColumns[$i] . " LIKE '%" . convert_str($_GET['sSearch_' . $i]) . "%' ";
    }
}
$condition = "contest_belong='$cid'";
if ($ishide && $hidedt) $condition .= " AND username='$nowuser'";
if ($sWhere == "") $sWhere = "WHERE " . $condition;
else $sWhere .= " AND " . $condition;


/* Total data set length */
$sQuery = "
    SELECT COUNT(" . $sIndexColumn . ")
    FROM   $sTable where contest_belong='$cid'
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

$cshows = false;
if ($current_user->is_valid() && contest_passed($cid)) $cshows = true;
$isv = $current_user->is_codeviewer();
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
    if (!$current_user->match($aRow["username"]) && !$isroot && $hidedt) {
        $aRow["memory_used"] = "";
        $aRow["time_used"] = "";
        $aRow["length(source)"] = "";
    }
    for ($i = 0; $i < count($aColumns) - 1; $i++) {
        if ($aColumns[$i] == "pid") {
            $row[] = $ptol[$aRow[$aColumns[$i]]];
        } else if ($aColumns[$i] != ' ') {
            /* General output */
            $row[] = $aRow[$aColumns[$i]];
        }
    }
    if ($aRow["isshared"] == TRUE || $current_user->match($aRow["username"]) || $isv) $row[] = 1;
    else $row[] = 0;
    $output['aaData'][] = $row;
}

echo json_encode($output);

?>
