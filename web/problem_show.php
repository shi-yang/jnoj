<?php
include_once('functions/problems.php');
$pid = intval(convert_str($_GET['pid']));
if ($pid == "")
    $pid = "0";
$show_problem = new Problem;
$show_problem->set_problem($pid);
if ($show_problem->is_valid() && $show_problem->get_val("hide") == 0)
    $pagetitle = "JNUOJ " . $pid . " - " . $show_problem->get_val("title");
else
    $pagetitle = "Problem Unavailable";
$lastlang = fetch_default($_COOKIE, $config["cookie_prefix"] . "lastlang", 1);
include_once("header.php");
?>
<script src="https://cdn.bootcss.com/mathjax/2.7.1/MathJax.js?config=TeX-AMS_HTML"></script>
<script type="text/x-mathjax-config">
MathJax.Hub.Config({
    tex2jax: {inlineMath: [['$','$'], ['\\(','\\)']]}
});
</script>
<?php if (!$show_problem->is_valid() || ($show_problem->get_val("hide") == 1 && !$current_user->is_root())): ?>
    <p class="alert alert-error">Problem Unavailable!</p>
<?php else: ?>
    <div class="col-md-9">
        <div id="showproblem">
            <h2 style="text-align:center"><?= $show_problem->get_val("title") ?></h2>
            <div id="conditions" class="well tcenter">
                <?php if ($show_problem->get_val("ignore_noc") == "0"): ?>
                    <?php if ($show_problem->get_val("time_limit") == $show_problem->get_val("case_time_limit")): ?>
                        <div class="col-md-6">Time Limit: <?= $show_problem->get_val("time_limit") ?>ms</div>
                        <div class="col-md-6">Memory Limit: <?= $show_problem->get_val("memory_limit") ?>KB</div>
                    <?php else: ?>
                        <div class="col-md-4">Time Limit: <?= $show_problem->get_val("time_limit") ?>ms</div>
                        <div class="col-md-4">Case Time Limit: <?= $show_problem->get_val("case_time_limit") ?>ms</div>
                        <div class="col-md-4">Memory Limit: <?= $show_problem->get_val("memory_limit") ?>KB</div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="col-md-6">Case Time Limit: <?= $show_problem->get_val("case_time_limit") ?>ms</div>
                    <div class="col-md-6">Memory Limit: <?= $show_problem->get_val("memory_limit") ?>KB</div>
                <?php endif; ?>
                <?php if ($show_problem->get_val("isvirtual")): ?>
                    This problem will be judged on <span
                        class="badge badge-info"><?= $show_problem->get_val("vname") ?></span>. Original ID: <?= $show_problem->get_val("to_url") ?>
                    <br/>
                <?php endif; ?>
                64-bit integer IO format: <span
                    class="badge badge-inverse"><?= htmlspecialchars($show_problem->get_val("i64io_info")) ?></span>
                &nbsp;&nbsp;&nbsp;&nbsp; Java class name: <span
                    class="badge badge-inverse"><?= htmlspecialchars($show_problem->get_val("java_class")) ?></span>

                <?php if ($show_problem->get_val("special_judge_status")): ?>
                    <div id="spjinfo"><span class="badge badge-important">Special Judge</span></div>
                <?php endif; ?>

                <?php if ($show_problem->get_val("hide")): ?>
                    <br/><b>This problem is hidden.</b>
                <?php endif; ?>
            </div>
            <?php if ($current_user->is_root() || $current_user->aced_problem($pid)): ?>
                <div class="functions tcenter">
                    <form method="post" id="tagform" class="form-inline">
                        <input type="hidden" name="tagpid" value="<?= $pid ?>"/>
                        Type: <select class="input-xxlarge" name='utags' id="utags">
                            <option value="0">None</option>
                            <?php
                            $categories = problem_get_category();
                            foreach ($categories as $cat) {
                                echo "<option value=" . $cat["id"] . ">" . str_repeat("&nbsp;", $config["problem"]["category_tab_spaces"] * $cat["depth"]) . $cat["name"] . "</option>\n";
                            }
                            ?>
                        </select>
                        <?php if ($current_user->is_root()): ?>
                            <label class="inline input">Weight: <input class="input-mini" type="text" name="weight"
                                                                       value="10"/></label>
                            <label class="inline checkbox"><input type="checkbox" name="force" value="1"/>Force </label>
                        <?php endif; ?>
                        <button type="submit" class="btn btn-inverse">Tag it!</button>
                    </form>
                </div>
            <?php endif; ?>
            <?php if ($show_problem->get_val("description") != ""): ?>
                <div class="content-wrapper well">
                    <?= latex_content(preg_replace('/<style[\s\S]*\/style>/', "", $show_problem->get_val("description"))) . "\n" ?>
                    <div style="clear:both"></div>
                </div>
            <?php endif; ?>
            <?php if ($show_problem->get_val("input") != ""): ?>
                <h3> Input </h3>
                <div class="content-wrapper well">
                    <?= latex_content($show_problem->get_val("input")) . "\n" ?>
                    <div style="clear:both"></div>
                </div>
            <?php endif; ?>
            <?php if ($show_problem->get_val("output") != ""): ?>
                <h3> Output </h3>
                <div class="content-wrapper well">
                    <?= latex_content($show_problem->get_val("output")) . "\n" ?>
                    <div style="clear:both"></div>
                </div>
            <?php endif; ?>
            <h3>Examples</h3>
            <div class="well">
                <?php
                if ($show_problem->get_val("sample_in") != ""):
                    $sin = $show_problem->get_val("sample_in");
                    ?>
                    <p>Input</p>
                    <?php if (stristr($sin, '<br') == null && stristr($sin, '<pre') == null && stristr($sin, '<p') == null): ?>
                    <pre class="content-wrapper"><?= $sin ?></pre>
                <?php else: ?>
                    <div class="content-wrapper well"><?= $sin ?> </div>
                <?php endif; ?>
                <?php endif; ?>

                <?php
                if ($show_problem->get_val("sample_out") != ""):
                    $sout = $show_problem->get_val("sample_out");
                    ?>
                    <p>Output</p>
                    <?php if (stristr($sout, '<br') == null && stristr($sout, '<pre') == null && stristr($sout, '<p>') == null): ?>
                    <pre class="content-wrapper"><?= $sout ?></pre>
                <?php else: ?>
                    <div class="content-wrapper well"><?= $sout ?></div>
                <?php endif; ?>
                <?php endif; ?>
            </div>

            <?php if (trim(strip_tags($show_problem->get_val("hint"))) != "" || strlen($show_problem->get_val("hint")) > 50): ?>
                <h3> Hint </h3>
                <div class="content-wrapper well">
                    <?= latex_content($show_problem->get_val("hint")) . "\n" ?>
                    <div style="clear:both"></div>
                </div>
            <?php endif; ?>
            <?php if ($show_problem->get_val("source") != ""): ?>
                <h3> Source </h3>
                <div class="content-wrapper well">
                    <?= "<a href='problem.php#searchstr=" . urlencode($show_problem->get_val("source")) . "'>" . $show_problem->get_val("source") . "</a>\n" ?>
                </div>
            <?php endif; ?>
            <?php if ($show_problem->get_val("author") != ""): ?>
                <h3> Author </h3>
                <div class="content-wrapper well">
                    <?= $show_problem->get_val("author") ?>
                </div>
            <?php endif; ?>
            <?php $p_cat = $show_problem->get_val("tagged_category"); ?>
            <?php if (sizeof($p_cat) > 0): ?>
                <h3> Tags
                    <button class="btn btn-danger" id="ptags">Toggle</button>
                </h3>
                <div id="ptagdetail" class="content-wrapper well hide" style="line-height:420%">
                    <?php foreach ($p_cat as $cat): ?>
                        <span style="margin-right:30px"><a
                                style='font-size:<?= (doubleval($cat["weight"]) * 0.15 + 90) ?>%'
                                href='problem_category_result.php?category=<?= $cat["catid"] ?>'
                                target='_blank'><?= $cat["name"] ?></a></span>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <div class="col-md-3">
        <br>
        <br>
        <br>
        <div class="btn-group-vertical" role="group" style="position:fixed">
            <a href="#" class="submitprob btn btn-success">Submit</a>
            <a href="status.php?showpid=<?= $pid ?>" class="btn">Status</a>
            <a href="problem_stat.php?pid=<?= $pid ?>" class="btn">Statistics</a>
            <a href="discuss.php?pid=<?= $pid ?>" class="btn">Discuss</a>
            <?php if ($current_user->is_root()): ?>
                <a href="admin_index.php?r=problem&pid=<?= $pid ?>" class="btn btn-primary">Edit</a>
            <?php endif; ?>
            <?php if (problem_exist(intval($pid) - 1)): ?>
                <a href="problem_show.php?pid=<?= intval($pid) - 1 ?>" class="btn">Prev</a>
            <?php endif; ?>
            <?php if (problem_exist(intval($pid) + 1)): ?>
                <a href="problem_show.php?pid=<?= intval($pid) + 1 ?>" class="btn">Next</a>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>
<div id="submitdialog" class="modal fade" style="display:none">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4><?= "Submit " . $show_problem->get_val("vname") . " " . $show_problem->get_val("vid") . ": " . htmlspecialchars($show_problem->get_val("title")) ?></h4>
            </div>
            <form action="ajax/problem_submit.php" method="post" id="probsubmit" class="ajform form-horizontal">
                <div class="modal-body">
                    <table width="100%">
                        <tr>
                            <th class="col-md-4">Username:</th>
                            <td class="col-md-8"><?= $current_user->get_username() ?><input name="user_id"
                                                                                            value="<?= $current_user->get_username() ?>"
                                                                                            readonly="readonly"
                                                                                            style="display:none"></td>
                        </tr>
                        <tr style="display:none">
                            <th>PID:</th>
                            <td><?= $pid ?><input name="problem_id" value="<?= $pid ?>" readonly="readonly"
                                                  style="display:none"></td>
                        </tr>
                        <tr>
                            <th>Language:</th>
                            <td style="text-align:left;">
                                <select name="language" id="lang" accesskey="l">
                                    <option value="1" <?= $lastlang == 1 ? "selected='selected'" : "" ?>>GNU C++
                                    </option>
                                    <option value="2" <?= $lastlang == 2 ? "selected='selected'" : "" ?>>GNU C</option>
                                    <option value="3" <?= $lastlang == 3 ? "selected='selected'" : "" ?>>Oracle Java
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
                            <td>
                                <div class="span2"><label class="radio"><input name="isshare" type="radio"
                                                                               style="width:16px" value="1"/>Yes</label>
                                </div>
                                <div class="span2"><label class="radio"><input name="isshare" type="radio"
                                                                               style="width:16px" value="0"/>No</label>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <th colspan="2">Source Code:</th>
                        </tr>
                        <tr>
                            <td colspan="2"><textarea rows="12" class="form-control" name="source"
                                                      onKeyUp="if(this.value.length > <?= $config["limits"]["max_source_code_len"] ?>) this.value=this.value.substr(0,<?= $config["limits"]["max_source_code_len"] ?>)"
                                                      placeholder="Put your solution here..."></textarea></td>
                        </tr>
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
<script type="text/javascript">
    var ppid = '<?= $pid ?>';
    var pvid = "<?= $show_problem->get_val("vid") ?>";
    var pvname = "<?= $show_problem->get_val("vname") ?>";
    var support_lang =<?= json_encode($show_problem->get_val("support_lang")) ?>;
</script>
<script type="text/javascript" src="assets/js/problem_show.js?<?= filemtime("assets/js/problem_show.js") ?>"></script>
<?php include("footer.php"); ?>
