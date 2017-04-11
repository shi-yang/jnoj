<?php
include_once("header.php");
include_once("functions/sidebars.php");
?>
<div class="span9">

    <b style="color:red;font-size:16px;"> Arrange YOUR OWN contest <a href="contest.php?open=1">HERE</a>! </b>

    <?php
    include_once("functions/contests.php");
    /** Running standard contests **/
    $running_contest = contest_get_standard_running_list();
    if (sizeof($running_contest) > 0) {
        ?>
        <h2>Running Contests</h2>
        <p>
            <?php
            foreach ($running_contest as $contest) {
                ?>
                <a href='contest_show.php?cid=<?= $contest["cid"] ?>'><?= $contest["title"] ?></a> ends at <?= $contest["end_time"] ?>
                <br/>
                <?php
            }
            ?>
        </p>
        <?php
    }
    ?>


    <?php
    /** Running virtual contests **/
    $running_vcontest = contest_get_virtual_running_list();
    if (sizeof($running_vcontest) > 0) {
        ?>
        <h2>Running Virtual Contests</h2>
        <p>
            <?php
            foreach ($running_vcontest as $contest) {
                ?>
                <a href='contest_show.php?cid=<?= $contest["cid"] ?>'><?= $contest["title"] ?></a> ends at <?= $contest["end_time"] ?>
                <br/>
                <?php
            }
            ?>
        </p>
        <?php
    }
    ?>


    <?php
    /** Scheduled standard contests **/
    $scheduled_contest = contest_get_standard_scheduled_list();
    if (sizeof($scheduled_contest) > 0) {
        ?>
        <h2>Upcoming Contests</h2>
        <p>
            <?php
            foreach ($scheduled_contest as $contest) {
                ?>
                <a href='contest_show.php?cid=<?= $contest["cid"] ?>'><?= $contest["title"] ?></a> at <?= $contest["start_time"] ?>
                <br/>
                <?php
            }
            ?>
        </p>
        <?php
    }
    ?>


    <?php
    /** Scheduled virtual contests **/
    $scheduled_vcontest = contest_get_virtual_scheduled_list();
    if (sizeof($scheduled_vcontest) > 0) {
        ?>
        <h2>Upcoming Virtual Contests</h2>
        <p>
            <?php
            foreach ($scheduled_vcontest as $contest) {
                ?>
                <a href='contest_show.php?cid=<?= $contest["cid"] ?>'><?= $contest["title"] ?></a> at <?= $contest["start_time"] ?>
                <br/>
                <?php
            }
            ?>
        </p>
        <?php
    }
    ?>

    <h2>Greetings!</h2>
    <div class="well">
        Welcome to BNU Online Judge 2.0! <br/>
        If you don't like it, <a href='../contest' target='_blank'>click here</a> for the original BNUOJ. <br/>
        IE 9+, Opera 9.5+, Safari 4.0+, Firefox 8+ and Chrome 12+ are <span style='color:red;font-weight:bold'>STRONGLY RECOMMENDED!</span>
        <br/>
        I wish all bugs are gone....<br/>
        Bug report or feature requests: <a href='mailto:yichao#mail.bnu.edu.cn'>click here</a>.<br/>
        Source code: <a href='http://code.google.com/p/bnuoj' target="_blank">go to Google Code</a>.
    </div>
    <h2>Currently Supported OJ</h2>
    <div class="well">
        <a href="http://poj.org" target="_blank">PKU</a>&nbsp;
        <a href="http://acm.hdu.edu.cn" target="_blank">HDU</a>&nbsp;
        <a href="http://livearchive.onlinejudge.org" target="_blank">UVALive</a>&nbsp;
        <a href="http://www.codeforces.com" target="_blank">Codeforces</a>&nbsp;
        <a href="http://acm.sgu.ru" target="_blank">SGU</a>&nbsp;
        <a href="http://www.lightoj.com" target="_blank">LightOJ</a>&nbsp;
        <a href="http://acm.timus.ru" target="_blank">Ural</a>&nbsp;
        <a href="http://acm.zju.edu.cn" target="_blank">ZJU</a>&nbsp;
        <a href="http://uva.onlinejudge.org" target="_blank">UVA</a>&nbsp;
        <a href="http://www.spoj.pl" target="_blank">SPOJ</a>&nbsp;
        <a href="http://acm.uestc.edu.cn" target="_blank">UESTC</a>&nbsp;
        <a href="http://acm.fzu.edu.cn" target="_blank">FZU</a>&nbsp;
        <a href="http://acm.nbut.cn:8081" target="_blank">NBUT</a>&nbsp;
        <a href="http://acm.whu.edu.cn/land" target="_blank">WHU</a>&nbsp;
        <a href="http://soj.me" target="_blank">SYSU</a>
    </div>
    <h2>Todo List</h2>
    <div class="well">
        <ol>
            <li>Virtual Judge on many other OJs</li>
            <li>Class/Interactive Module</li>
            <li>AI Battle Module</li>
            <li>SNS link</li>
        </ol>
        Any suggestion is welcome!
    </div>
    <h2>Spin-off projects</h2>
    <div class="well">
        <ol>
            <li>acmicpc.info Hackathon Platform: <a href="http://www.bnuoj.com/hackathon/" target="_blank">click
                    here</a></li>
            <li>JNUOJ lite version (for onsite contests)</li>
        </ol>
    </div>


</div>
<div class="span3">
    <?= sidebar_index() ?>
</div>

<script>
    $("#home").addClass("active");
</script>
<?php
include_once("footer.php");
?>
