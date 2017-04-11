<?php
include_once(dirname(__FILE__) . "/../functions/problems.php");
include_once(dirname(__FILE__) . "/../functions/users.php");
$ret = array();
$ret["code"] = 1;
if ($current_user->is_root()) {
    $title = convert_str($_POST['p_name']);
    $pid = convert_str($_POST['p_id']);
    $hide = convert_str($_POST['p_hide']);
    $description = convert_str($_POST['description']);
    $input = convert_str($_POST['input']);
    $output = convert_str($_POST['output']);
    $sample_in = htmlspecialchars(convert_str($_POST['sample_in']));
    $sample_out = htmlspecialchars(convert_str($_POST['sample_out']));
    $hint = convert_str($_POST['hint']);
    $source = convert_str($_POST['source']);
    $author = convert_str($_POST['author']);
    $memory_limit = convert_str($_POST['memory_limit']);
    $time_limit = convert_str($_POST['time_limit']);
    $special_judge_status = convert_str($_POST['special_judge_status']);
    $case_time_limit = convert_str($_POST['case_time_limit']);
    $basic_solver_value = convert_str($_POST['basic_solver_value']);
    $noc = convert_str($_POST['noc']);
    $tags = convert_str($_POST['tags']);
    $ignore_noc = convert_str($_POST['p_ignore_noc']);
    $OJcode = $config['OJcode'];
    if ($pid == "") {
        $sql_add_pro = "insert into problem (title,description,input,output,sample_in,sample_out,hint,source,hide,memory_limit,time_limit,special_judge_status,case_time_limit,basic_solver_value,number_of_testcase,vname,vid,ignore_noc,author, tags)values ('$title','$description','$input','$output','$sample_in','$sample_out','$hint','$source','$hide','$memory_limit','$time_limit','$special_judge_status','$case_time_limit','$basic_solver_value','$noc', '$OJcode', '$pid', '$ignore_noc','$author', '$tags')";
    } else {
        $sql_add_pro = "update problem set title='$title',description='$description',input='$input',output='$output',sample_in='$sample_in',sample_out='$sample_out',hint='$hint',source='$source',hide='$hide',memory_limit='$memory_limit',time_limit='$time_limit',special_judge_status='$special_judge_status',case_time_limit='$case_time_limit',basic_solver_value='$basic_solver_value',number_of_testcase='$noc',ignore_noc='$ignore_noc',author='$author',tags='$tags' where pid='$pid'";
    }
    $db->query($sql_add_pro);
    $ret["code"] = 0;
    if ($pid == '') {
        $currpid = $db->insert_id;
        $db->query("update problem set vid=pid where pid='$currpid'");
    } else $currpid = $pid;
    $ret["msg"] = "Success! PID: $currpid.";
} else $ret["msg"] = "Please login as root!";
echo json_encode($ret);
?>
