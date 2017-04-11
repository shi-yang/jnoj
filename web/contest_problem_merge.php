<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">

<head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8"/>
    <style>
        html {
            height: 100%;
        }

        * {
            margin: 0;
            padding: 0;
        }

        body {
            font-family: arial, sans-serif;
            font-size: .80em;
        }

        p {
            padding: 0 0 20px 0;
            line-height: 1.7em;
        }

        img {
            border: 0;
        }

        a {
            text-decoration: underline;
        }

        a:hover {
            text-decoration: none;
        }

        table {
            border-collapse: collapse;
            border-spacing: 0px;
        }

        table td, table th {
            border: solid 1px black;
            padding: 5px;
        }

    </style>
</head>
<body>

<?php
include_once('functions/problems.php');
include_once('functions/users.php');
include_once('functions/contests.php');
$cid = convert_str($_GET['cid']);
if (!contest_exist($cid) || ($current_user->is_root() == false && !$current_user->match(contest_get_val($cid, "owner")))) {
    echo "<h1>You are not allowed to view this page.</h1>";
    die();
}

$show_problem = new Problem;
foreach ((array)contest_get_problem_summaries($cid) as $cp) {
    $show_problem->set_problem($cp["pid"]);
    $html = "<center><h1>" . $cp["lable"] . ". " . $show_problem->get_val("title") . "</h1></center>";
    if ($show_problem->get_val("description") != "") $html .= latex_content($show_problem->get_val("description"));
    if ($show_problem->get_val("input") != "") $html .= "<h2 style='margin-top:10px'>Input</h2>" . latex_content($show_problem->get_val("input"));
    if ($show_problem->get_val("output") != "") $html .= "<h2 style='margin-top:10px'>Output</h2>" . latex_content($show_problem->get_val("output"));
    if ($show_problem->get_val("sample_in") != "") {
        $html .= "<h2 style='margin-top:10px'>Sample Input</h2>";
        if (stristr($show_problem->get_val("sample_in"), '<br') == null && stristr($show_problem->get_val("sample_in"), '<pre') == null && stristr($show_problem->get_val("sample_in"), '<p>') == null) $html .= "<pre>" . $show_problem->get_val("sample_in") . "</pre>";
        else $html .= $show_problem->get_val("sample_in");
    }
    if ($show_problem->get_val("sample_out") != "") {
        $html .= "<h2 style='margin-top:10px'>Sample Output</h2>";
        if (stristr($show_problem->get_val("sample_out"), '<br') == null && stristr($show_problem->get_val("sample_out"), '<pre') == null && stristr($show_problem->get_val("sample_out"), '<p>') == null) $html .= "<pre>" . $show_problem->get_val("sample_out") . "</pre>";
        else $html .= $show_problem->get_val("sample_out");
    }
    if (trim(strip_tags($show_problem->get_val("hint"))) != "" || strlen($show_problem->get_val("hint")) > 50) $html .= "<h2 style='margin-top:10px'>Hint</h2>" . latex_content($show_problem->get_val("hint"));
    echo $html . '<div style="PAGE-BREAK-AFTER: always"></div>';
}
?>
</body>
</html>


