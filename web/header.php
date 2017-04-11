<?php
include_once("functions/users.php");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8"/>
    <meta name="description" content="JNU Online Judge, A simple, full-featured Online Judge."/>
    <meta name="keywords" content="Online Judge, JNU, OJ, JNUOJ, JOJ, Virtual Judge, Replay Contest, Problem Category"/>
    <meta name="author" content="semprathlon shiyang">
    <link rel="shortcut icon" href="assets/ico/jnuoj.ico"/>
    <title><?= $pagetitle == "" ? "江南大学在线判题系统" : $pagetitle ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/common.css?<?= filemtime("assets/css/common.css") ?>" rel="stylesheet">
    <link href="assets/css/select2.css" rel="stylesheet">
    <link href="assets/css/fullcalendar.css" rel="stylesheet">
    <link href="assets/css/datetimepicker.css" rel="stylesheet">
    <link href="assets/css/dataTables.bootstrap.min.css" rel="stylesheet">

    <!-- HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
    <script src="//cdn.bootcss.com/html5shiv/r29/html5.min.js"></script>
    <![endif]-->

    <script src="assets/js/jquery-1.9.1.min.js"></script>
    <script src="assets/js/bootstrap.min.js"></script>
    <script src="assets/js/jquery.dataTables.js"></script>
    <script src="assets/js/select2.min.js"></script>
    <script src="assets/js/dataTables.bootstrap.min.js"></script>
    <script src="assets/js/bootstrap-datetimepicker.js"></script>
    <script src="assets/js/bnuoj-ext.js?<?= filemtime("assets/js/bnuoj-ext.js") ?>"></script>
</head>
<body>
<header id="header">
    <div class="container">
        <div class="page-header">
            <div class="logo pull-left">
                <div class="pull-left">
                    <a href="index.php"><img src="assets/img/logo.png"></a>
                </div>
                <div class="brand">
                    Online Judge
                </div>
            </div>
            <div class="clearfix"></div>
        </div>
    </div>
</header>
<nav class="navbar navbar-default">
    <div class="container">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target=".nav-collapse"
                    aria-expanded="false">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
        </div>
        <!-- Collect the nav links, forms, and other content for toggling -->
        <div class="collapse navbar-collapse">
            <ul class="nav navbar-nav">
                <li id="index"><a href="index.php">Home</a></li>
                <li id="problem"><a href="problem.php">Problems</a></li>
                <li id="status"><a href="status.php">Status</a></li>
                <li id="contest"><a href="contest.php?type=50">Contest</a></li>
                <li id="ranklist"><a href="ranklist.php">Ranklist</a></li>
                <li id="discuss"><a href="discuss.php">Discuss</a></li>
                <li id="discuss"><a href="/swap">Swap</a></li>
                <li class="dropdown" id="more">
                    <a class="dropdown-toggle" data-toggle="dropdown" href="teaminfo.php">More <b class="caret"></b></a>
                    <ul class="dropdown-menu">
                        <li><a href="news.php">News</a></li>
                        <li><a href="teaminfo.php">Our Team</a></li>
                        <li class="divider"></li>
                        <li><a href="recent_contest.php">Recent Contests</a></li>
                        <!--<li><a href="training_stat.php">Training Stats</a></li>-->
                        <li class="divider"></li>
                        <li class="disabled"><a>Coming Soon...</a></li>
                    </ul>
                </li>
            </ul>
            <?php if (!$current_user->is_valid()): ?>
                <ul id="loginbar" class="nav navbar-nav pull-right">
                    <li id="loginbutton"><a href="#" id="login">Login</a></li>
                    <li id="register"><a href="#" class="toregister">Register</a></li>
                </ul>
            <?php else: ?>
                <ul id="logoutbar" class="nav navbar-nav pull-right">
                    <li class="dropdown" id="userspace">
                        <a class="dropdown-toggle" data-toggle="dropdown" href="userinfo.php?name=<?= $nowuser ?>"
                           id="displayname"><?= $nowuser . ($current_user->get_unread_mail_count() > 0 ? "<b style='color:#F00'>(" . $current_user->get_unread_mail_count() . ")</b>" : "") ?>
                            <b class="caret"></b></a>
                        <ul class="dropdown-menu">
                            <li><a href="userinfo.php?name=<?= $nowuser ?>">Show My Information</a></li>
                            <li><a href="#" id="modify">Modify My Information</a></li>
                            <li><a href="mail.php"
                                   id="mail">Mail<?= ($current_user->get_unread_mail_count() > 0 ? "<b style='color:#F00'>(" . $current_user->get_unread_mail_count() . ")</b>" : "") ?></a>
                            </li>
                            <?php if ($current_user->is_root()) { ?>
                                <li><a href="admin_index.php" id="admin">Administration</a></li>
                            <?php } ?>
                            <li id="logoutbutton"><a href="#" id="logout">Logout</a></li>
                        </ul>
                    </li>
                </ul>
            <?php endif; ?>
            <p class="pull-right navbar-text"><span id="servertime"><?= date("Y-m-d H:i:s") ?></span>&nbsp;</p>
        </div><!-- /.navbar-collapse -->
    </div>
</nav>
<marquee class="hidden-phone" direction="left" behavior="alternate" scrollamount="2"
         style="position:absolute;width:100%;"><?= get_substitle() ?></marquee>
<div class="hidden-phone" id="marqueepos"></div>
<script>
    //var currenttime = '<?=date("l, F j, Y H:i:s", time())?>';
    var currenttime = '<?=time()?>';
    var cookie_prefix = '<?=$config["cookie_prefix"] ?>';
    var default_style = '<?=$config["default_style"]?>';
</script>
<div class="container">
    <div class="row">
