<?php
include_once("header.php");
include_once("functions/sidebars.php");
include_once("functions/contests.php");
?>
<div class="jumbotron">
    <h1>Hello, ACMer!</h1>
    <p> 欢迎来到江南大学在线判题系统 </p>
</div>
<div class="row">
    <div class="col-md-3">
        <?= sidebar_item_content_news(false) ?>
        <?php //echo sidebar_item_content_vjstatus_index() ?>
    </div>
    <div class="col-md-4">
        <h3>Introduction</h3>
        <p>
            ACM国际大学生程序设计竞赛（英文全称：ACM International Collegiate Programming
            Contest（简称ACM-ICPC或ICPC））是由美国计算机协会（ACM）主办的，一项旨在展示大学生创新能力、团队精神和在压力下编写程序、分析和解决问题能力的年度竞赛。经过近40年的发展，ACM国际大学生程序设计竞赛已经发展成为全球最具影响力的大学生程序设计竞赛。赛事目前由IBM公司赞助。
        </p>
    </div>
    <div class="col-md-5">
        <?php
        /** Running standard contests **/
        $running_contest = contest_get_standard_running_list();
        if (sizeof($running_contest) > 0) :
            ?>
            <h3>Running Contests</h3>
            <p>
                <?php foreach ($running_contest as $contest): ?>
                    <a href='contest_show.php?cid=<?= $contest["cid"] ?>'><?= $contest["title"] ?></a> ends at <?= $contest["end_time"] ?>
                    <br/>
                <?php endforeach; ?>
            </p>
        <?php endif; ?>
        <?php
        /** Running virtual contests **/
        $running_vcontest = contest_get_virtual_running_list();
        if (sizeof($running_vcontest) > 0):
            ?>
            <h3>Running Virtual Contests</h3>
            <p>
                <?php foreach ($running_vcontest as $contest) { ?>
                    <a href='contest_show.php?cid=<?= $contest["cid"] ?>'><?= $contest["title"] ?></a> ends at <?= $contest["end_time"] ?>
                    <br/>
                <?php } ?>
            </p>
        <?php endif; ?>
        <?php
        /** Scheduled standard contests **/
        $scheduled_contest = contest_get_standard_scheduled_list();
        if (sizeof($scheduled_contest) > 0):
            ?>
            <h3>Upcoming Contests</h3>
            <p>
                <?php foreach ($scheduled_contest as $contest) : ?>
                    <a href='contest_show.php?cid=<?= $contest["cid"] ?>'><?= $contest["title"] ?></a> at <?= $contest["start_time"] ?>
                    <br/>
                <?php endforeach; ?>
            </p>
        <?php endif; ?>
        <?php
        /** Scheduled virtual contests **/
        $scheduled_vcontest = contest_get_virtual_scheduled_list();
        if (sizeof($scheduled_vcontest) > 0) :
            ?>
            <h3>Upcoming Virtual Contests</h3>
            <p>
                <?php foreach ($scheduled_vcontest as $contest): ?>
                    <a href='contest_show.php?cid=<?= $contest["cid"] ?>'><?= $contest["title"] ?></a> at <?= $contest["start_time"] ?>
                    <br/>
                <?php endforeach; ?>
            </p>
        <?php endif; ?>
        <h3>Results</h3>
        <p>Accepted: 你的答案符合判题标准</p>
        <p>Runtime Error: 你的程序运行时出现错误（指针越界，栈溢出，有未处理的异常，主函数返回值非零等）</p>
        <p>Time Limit Exceeded: 你的程序执行时间超出题目要求</p>
        <p>Memory Limit Exceeded: 你的程序内存使用超出题目要求</p>
        <p>Compile Error: 你的程序在编译（包括链接）时出现错误</p>
        <p>Wrong Answer: 你的程序输出的答案不符合判题标准</p>
        <p>System Error: 判题系统发生故障，请等待重判</p>
        <p>Waiting: 你的提交正在等待处理</p>
    </div>
</div>
<?php include_once("footer.php"); ?>
