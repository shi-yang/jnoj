<?php
include_once(dirname(__FILE__) . "/../functions/users.php");
$name1 = convert_str($_GET['name1']);
$name2 = convert_str($_GET['name2']);
if (!user_exist($name1) || !user_exist($name2)) {
    echo "<b>No Such User!</b>";
    die();
}
foreach ((array)$db->get_results("select distinct pid from status where username='$name1' and result='Accepted' order by pid", ARRAY_N) as $temp) $mapa1[$temp[0]] = true;
foreach ((array)$db->get_results("select distinct pid from status where username='$name1' order by pid", ARRAY_N) as $temp) $mapt1[] = $temp[0];
foreach ((array)$db->get_results("select distinct pid from status where username='$name2' and result='Accepted' order by pid", ARRAY_N) as $temp) $mapa2[$temp[0]] = true;
foreach ((array)$db->get_results("select distinct pid from status where username='$name2' order by pid", ARRAY_N) as $temp) $mapt2[] = $temp[0];
$numt1 = $numt2 = 0;

foreach ((array)$mapt1 as $temp) $pidt1[$numt1++] = $temp;
foreach ((array)$mapt2 as $temp) $pidt2[$numt2++] = $temp;

$nboth = $nonly1 = $nonly2 = $ntbf1 = $ntbf2 = $natbf = 0;
$i = $j = 0;
while ($i < $numt1 || $j < $numt2) {
    if ($i >= $numt1) {
        while ($j < $numt2) {
            if ($mapa2[$pidt2[$j]] == true) $only2[$nonly2++] = $pidt2[$j]; else $tbf2[$ntbf2++] = $pidt2[$j];
            $j++;
        }
        break;
    }
    if ($j >= $numt2) {
        while ($i < $numt1) {
            if ($mapa1[$pidt1[$i]] == true) $only1[$nonly1++] = $pidt1[$i]; else $tbf1[$ntbf1++] = $pidt1[$i];
            $i++;
        }
        break;
    }
    if ($pidt1[$i] == $pidt2[$j]) {
        if ($mapa1[$pidt1[$i]] == true && $mapa2[$pidt2[$j]] == true) $both[$nboth++] = $pidt1[$i];
        else if ($mapa1[$pidt1[$i]] == true && $mapa2[$pidt2[$j]] == false) {
            $only1[$nonly1++] = $pidt1[$i];
            $tbf2[$ntbf2++] = $pidt2[$j];
        } else if ($mapa1[$pidt1[$i]] == false && $mapa2[$pidt2[$j]] == true) {
            $only2[$nonly2++] = $pidt2[$j];
            $tbf1[$ntbf1++] = $pidt1[$i];
        } else $atbf[$natbf++] = $pidt1[$i];
        $i++;
        $j++;
    } else if ($pidt1[$i] < $pidt2[$j]) {
        if ($mapa1[$pidt1[$i]] == true) $only1[$nonly1++] = $pidt1[$i]; else $tbf1[$ntbf1++] = $pidt1[$i];
        $i++;
    } else {
        if ($mapa2[$pidt2[$j]] == true) $only2[$nonly2++] = $pidt2[$j]; else $tbf2[$ntbf2++] = $pidt2[$j];
        $j++;
    }
}
?>
<table width="100%">
    <?php
    echo "<tr>";
    echo "<th>Problems only <a href='userinfo.php?name=$name1'>$name1</a> Accepted:</th>";
    echo "</tr>";
    echo "<tr>";
    echo "<td>";
    for ($i = 0; $i < $nonly1; $i++)
        if (!$mapt2[$only1[$i]]) echo "<a href='problem_show.php?pid=$only1[$i]'>$only1[$i]</a>&nbsp; ";
        else echo "<a href='problem_show.php?pid=$only1[$i]' style='color:red;'>$only1[$i]</a>&nbsp; ";
    echo "</td>";
    echo "</tr>";
    echo "<tr>";
    echo "<th>Problems only <a href='userinfo.php?name=$name2'>$name2</a> Accepted:</th>";
    echo "</tr>";
    echo "<tr>";
    echo "<td>";
    for ($i = 0; $i < $nonly2; $i++)
        if (!$mapt1[$only2[$i]]) echo "<a href='problem_show.php?pid=$only2[$i]'>$only2[$i]</a>&nbsp; ";
        else echo "<a href='problem_show.php?pid=$only2[$i]' style='color:red;'>$only2[$i]</a>&nbsp; ";
    echo "</td>";
    echo "</tr>";
    echo "<tr>";
    echo "<th>Problems both <a href='userinfo.php?name=$name1'>$name1</a> and <a href='userinfo.php?name=$name2'>$name2</a> Accepted:</th>";
    echo "</tr>";
    echo "<tr>";
    echo "<td>";
    for ($i = 0; $i < $nboth; $i++) echo "<a href='problem_show.php?pid=$both[$i]'>$both[$i]</a>&nbsp; ";
    echo "</td>";
    echo "</tr>";
    echo "<tr>";
    echo "<th>Problems <a href='userinfo.php?name=$name1'>$name1</a> tried but failed:</th>";
    echo "</tr>";
    echo "<tr>";
    echo "<td>";
    for ($i = 0; $i < $ntbf1; $i++) echo "<a href='problem_show.php?pid=$tbf1[$i]'>$tbf1[$i]</a>&nbsp; ";
    echo "</td>";
    echo "</tr>";
    echo "<tr>";
    echo "<th>Problems <a href='userinfo.php?name=$name2'>$name2</a> tried but failed:</th>";
    echo "</tr>";
    echo "<tr>";
    echo "<td>";
    for ($i = 0; $i < $ntbf2; $i++) echo "<a href='problem_show.php?pid=$tbf2[$i]'>$tbf2[$i]</a>&nbsp; ";
    echo "</td>";
    echo "</tr>";
    echo "<tr>";
    echo "<th>Problems both <a href='userinfo.php?name=$name1'>$name1</a> and <a href='userinfo.php?name=$name2'>$name2</a> tried but failed:</th>";
    echo "</tr>";
    echo "<tr>";
    echo "<td>";
    for ($i = 0; $i < $natbf; $i++) echo "<a href='problem_show.php?pid=$atbf[$i]'>$atbf[$i]</a>&nbsp; ";
    echo "</td>";
    echo "</tr>";
    ?>
</table>
