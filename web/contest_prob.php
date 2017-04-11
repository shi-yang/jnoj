<?php
include_once('functions/users.php');
include_once('functions/sidebars.php');
include_once('functions/contests.php');
include_once('functions/problems.php');
$cpid = convert_str($_GET['cpid']);
$cid = convert_str($_GET['cid']);
$prob_info = contest_get_problem_from_mixed($cid, $cpid);
$lastlang = $_COOKIE[$config["cookie_prefix"] . "lastlang"];
if ($lastlang == null) $lastlang = 1;
if (!contest_started($cid) || !($current_user->is_root() || contest_get_val($cid, "isprivate") == 0 ||
        (contest_get_val($cid, "isprivate") == 1 && $current_user->is_in_contest($cid)) ||
        (contest_get_val($cid, "isprivate") == 2 && contest_get_val($cid, "password") == $_COOKIE[$config["cookie_prefix"] . "contest_pass_$cid"]))
) {
    ?>
    <div class="col-md-12">
        <p class="alert alert-error">Problem Unavailable! Or it's a private contest!</p>
    </div>

    <?php
} else {

    ?>
    <div class="col-md-9">
        <?php
        $show_problem = new Problem;
        $pid = $prob_info["pid"];
        $label = $prob_info["lable"];
        $show_problem->set_problem($pid);
        if (!$show_problem->is_valid()) {
            ?>
            <p class="alert alert-error">Problem Unavailable!</p>
        <?php
        } else {
        ?>
        <?php
        if (in_array($show_problem->get_val('vname'), array('UESTC', 'HDU', 'JNU'))) {
        ?>
            <script src="assets/js/Mathjax/MathJax.js?config=TeX-AMS_HTML"></script>
            <script type="text/x-mathjax-config">
        MathJax.Hub.Config({
            tex2jax: {inlineMath: [['$','$'], ['\\(','\\)']]}
        });


            </script>
        <?php
        }
        ?>
            <div id="showproblem">
                <ul class="nav nav-pills" id="probpagi">
                    <?php
                    foreach (contest_get_problem_basic($cid) as $prob) {
                        ?>
                        <li<?= $prob['lable'] == $label ? " class='active'" : "" ?>><a
                                href="#problem/<?= $prob['lable'] ?>"><?= $prob['lable'] ?></a></li>
                        <?php
                    }
                    ?>
                </ul>
                <h2 style="text-align:center"
                    class="pagetitle"><?= $label . ". " . $show_problem->get_val("title") ?></h2>
                <div id="conditions" class="well tcenter">
                    <?php if ($show_problem->get_val("ignore_noc") == "0") { ?>
                        <?php if ($show_problem->get_val("time_limit") == $show_problem->get_val("case_time_limit")) { ?>
                            <div class="col-md-6">Time Limit: <?= $show_problem->get_val("time_limit") ?>ms</div>
                            <div class="col-md-6">Memory Limit: <?= $show_problem->get_val("memory_limit") ?>KB</div>
                        <?php } else { ?>
                            <div class="col-md-4">Time Limit: <?= $show_problem->get_val("time_limit") ?>ms</div>
                            <div class="col-md-4">Case Time Limit: <?= $show_problem->get_val("case_time_limit") ?>ms
                            </div>
                            <div class="col-md-4">Memory Limit: <?= $show_problem->get_val("memory_limit") ?>KB</div>
                        <?php } ?>
                    <?php } else { ?>
                        <div class="col-md-6">Case Time Limit: <?= $show_problem->get_val("case_time_limit") ?>ms</div>
                        <div class="col-md-6">Memory Limit: <?= $show_problem->get_val("memory_limit") ?>KB</div>
                    <?php } ?>
                    64-bit integer IO format: <span
                        class="badge badge-inverse"><?= htmlspecialchars($show_problem->get_val("i64io_info")) ?></span>
                    &nbsp;&nbsp;&nbsp;&nbsp; Java class name: <span
                        class="badge badge-inverse"><?= htmlspecialchars($show_problem->get_val("java_class")) ?></span>
                    <?php
                    if ($show_problem->get_val("special_judge_status")) {
                        ?>
                        <div id="spjinfo"><span class="badge badge-important">Special Judge</span></div>
                        <?php
                    }
                    ?>
                </div>
                <div class="functions tcenter" style="margin-bottom:20px">
                    <div class="btn-group">
                        <a href="#" class="submitprob btn btn-primary">Submit</a>
                        <a href="#status/<?= $label ?>" class="btn">Status</a>
                        <?php
                        if (contest_passed($cid) && $show_problem->get_val("hide") == 0) {
                            ?>
                            <a href="problem_show.php?pid=<?= $pid ?>" class="btn">PID: <?= $pid ?></a>
                            <?php
                        }
                        ?>
                    </div>
                    <?php
                    if ($current_user->is_root()) {
                        ?>
                        <a href="admin_index.php?pid=<?= $pid ?>#problemtab" class="btn btn-primary">Edit</a>
                        <?php
                    }
                    ?>
                </div>
                <?php
                if ($show_problem->get_val("description") != "") {
                    ?>
                    <div class="content-wrapper well">
                        <?= latex_content(preg_replace('/<style[\s\S]*\/style>/', "", $show_problem->get_val("description"))) . "\n" ?>
                        <div style="clear:both"></div>
                    </div>
                    <?php
                }
                if ($show_problem->get_val("input") != "") {
                    ?>
                    <h3> Input </h3>
                    <div class="content-wrapper well">
                        <?= latex_content($show_problem->get_val("input")) . "\n" ?>
                        <div style="clear:both"></div>
                    </div>
                    <?php
                }
                if ($show_problem->get_val("output") != "") {
                    ?>
                    <h3> Output </h3>
                    <div class="content-wrapper well">
                        <?= latex_content($show_problem->get_val("output")) . "\n" ?>
                        <div style="clear:both"></div>
                    </div>
                    <?php
                }
                if ($show_problem->get_val("sample_in") != "") {
                    $sin = $show_problem->get_val("sample_in");
                    ?>
                    <h3> Sample Input </h3>
                    <?php
                    if (stristr($sin, '<br') == null && stristr($sin, '<pre') == null && stristr($sin, '<p>') == null) {
                        ?>
                        <pre class="content-wrapper"><?= $sin ?></pre>
                        <?php
                    } else echo '<div class="content-wrapper well">' . $sin . "</div>\n";
                }
                if ($show_problem->get_val("sample_out") != "") {
                    $sout = $show_problem->get_val("sample_out");
                    ?>
                    <h3> Sample Output </h3>
                    <?php
                    if (stristr($sout, '<br') == null && stristr($sout, '<pre') == null && stristr($sout, '<p>') == null) {
                        ?>
                        <pre class="content-wrapper"><?= $sout ?></pre>
                        <?php
                    } else echo '<div class="content-wrapper well">' . $sout . "</div>\n";
                }
                if (trim(strip_tags($show_problem->get_val("hint"))) != "" || strlen($show_problem->get_val("hint")) > 50) {
                    ?>
                    <h3> Hint </h3>
                    <div class="content-wrapper well">
                        <?= latex_content($show_problem->get_val("hint")) . "\n" ?>
                        <div style="clear:both"></div>
                    </div>
                    <?php
                }
                ?>
                <div class="functions tcenter" style="margin-bottom:20px">
                    <div class="btn-group">
                        <a href="#" class="submitprob btn btn-primary">Submit</a>
                        <a href="#status/<?= $label ?>" class="btn">Status</a>
                        <?php
                        if (contest_passed($cid) && $show_problem->get_val("hide") == 0) {
                            ?>
                            <a href="problem_show.php?pid=<?= $pid ?>" class="btn">PID: <?= $pid ?></a>
                            <?php
                        }
                        ?>
                    </div>
                    <?php
                    if ($current_user->is_root()) {
                        ?>
                        <a href="admin_index.php?pid=<?= $pid ?>#problemtab" class="btn btn-primary">Edit</a>
                        <?php
                    }
                    ?>
                </div>
            </div>
            <?php
        }
        ?>
    </div>
    <div class="col-md-3">
        <?= sidebar_contest_show($cid) ?>
    </div>

    <div id="submitdialog" class="modal fade" name="<?= $show_problem->get_val("vname") ?>">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4><?= "Submit " . $prob_info["lable"] . ": " . htmlspecialchars($show_problem->get_val("title")) ?></h4>
                </div>
                <form action="ajax/contest_problem_submit.php" method="post" id="cprobsubmit"
                      class="ajform form-horizontal">
                    <div class="modal-body">
                        <table width="100%">
                            <tr>
                                <th class="col-md-4">Username:</th>
                                <td class="col-md-8"><?= $current_user->get_username() ?><input name="user_id"
                                                                                                value="<?= $current_user->get_username() ?>"
                                                                                                readonly="readonly"
                                                                                                style="display:none">
                                </td>
                            </tr>
                            <tr style="display:none">
                                <th>Label:</th>
                                <td><?= $prob_info["lable"] ?><input name="lable" value="<?= $prob_info["lable"] ?>"
                                                                     readonly="readonly" style="display:none"></td>
                            </tr>
                            <tr style="display:none">
                                <th>Contest:</th>
                                <td><?= $cid ?><input name="contest_id" value="<?= $cid ?>" readonly="readonly"
                                                      style="display:none"></td>
                            </tr>
                            <tr>
                                <th>Language:</th>
                                <td style="text-align:left;">
                                    <select name="language" id="lang" accesskey="l">
                                        <option value="1" <?= $lastlang == 1 ? "selected='selected'" : "" ?>>GNU C++
                                        </option>
                                        <option value="2" <?= $lastlang == 2 ? "selected='selected'" : "" ?>>GNU C
                                        </option>
                                        <option value="3" <?= $lastlang == 3 ? "selected='selected'" : "" ?>>Oracle
                                            Java
                                        </option>
                                        <option value="4">Free Pascal</option>
                                        <option value="5">Python2</option>
                                        <option value="16">Python3</option>
                                        <option value="6">C# (Mono)</option>
                                        <option value="7">Fortran</option>
                                        <option value="8">Perl</option>
                                        <option value="9">Ruby</option>
                                        <option value="10">Ada</option>
                                        <option value="11">SML</option>
                                        <option value="12">Visual C++</option>
                                        <option value="13">Visual C</option>
                                        <option value="14">CLang</option>
                                        <option value="15">CLang++</option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th>Share Code?</th>
                                <td style="text-align:left;">
                                    <div class="span2"><label class="radio"><input name="isshare" type="radio"
                                                                                   style="width:16px"
                                                                                   value="1"/>Yes</label></div>
                                    <div class="span2"><label class="radio"><input name="isshare" type="radio"
                                                                                   style="width:16px"
                                                                                   value="0"/>No</label></div>
                                </td>
                            </tr>
                            <tr>
                                <th colspan="2">Source Code:</th>
                            </tr>
                            <tr>
                                <td colspan="2"><textarea rows="12" class="input-block-level" name="source"
                                                          onKeyUp="if(this.value.length > <?= $config["limits"]["max_source_code_len"] ?>) this.value=this.value.substr(0,<?= $config["limits"]["max_source_code_len"] ?>)"
                                                          placeholder="Put your solution here..."></textarea></td>
                            </tr>
                            <?php
                            if (contest_get_val($cid, "owner_viewable")) {
                                ?>
                                <tr>
                                    <td colspan="2">The owner of the contest <b>WILL BE ABLE</b> to see your code.</td>
                                </tr>
                                <?php
                            }
                            ?>
                        </table>
                    </div>
                    <div class="modal-footer">
                        <span id="msgbox" style="display:none"></span>
                        <input name='login' class="btn btn-primary" type='submit' value='Submit'/>
                        <input name='reset' class="btn btn-danger" type='reset' value='Reset'/>
                    </div>
                </form>
            </div>
        </div>
    </div>


    <?php
}
?>

<script type="text/javascript">
    var support_lang =<?= json_encode($show_problem->get_val("support_lang")) ?>;
</script>
