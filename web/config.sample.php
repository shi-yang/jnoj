<?php
//database informations
$config["database"]["type"] = "mysqli";
$config["database"]["host"] = "127.0.0.1";
$config["database"]["port"] = 3306;
$config["database"]["username"] = "bnuojuser";
$config["database"]["password"] = "bnuojpassword";
$config["database"]["table"] = "bnuoj";

//limitations
$config["limits"]["status_per_page"] = 20;
$config["limits"]["problems_per_page"] = 25;
$config["limits"]["mails_per_page"] = 20;
$config["limits"]["users_per_page"] = 25;
$config["limits"]["contests_per_page"] = 20;
$config["limits"]["discuss_per_page"] = 30;

$config["limits"]["news_on_index"] = 4;

$config["limits"]["problems_on_contest_add"] = 15;
$config["limits"]["problems_on_contest_add_cf"] = 6;

$config["limits"]["users_on_problem_stat"] = 25;

$config["limits"]["news_on_index_title_len"] = 30;
$config["limits"]["news_on_index_content_len"] = 200;

$config["limits"]["max_runid"] = 1000000;
$config["limits"]["max_status_username_len"] = 20;

$config["limits"]["max_source_code_len"] = 256000;
$config["limits"]["max_mail_len"] = 256000;

$config["limits"]["max_rank_in_animation"] = 16;

$config["limits"]["max_error_rejudge_times"] = 3;

//contact strings ( no spaces )
$config["contact"]["submit"] = "yoursubmitstring";
$config["contact"]["rejudge"] = "yourrejudgestring";
$config["contact"]["error_rejudge"] = "yourerrorjudgestring";
$config["contact"]["challenge"] = "yourchallengestring";
$config["contact"]["pretest"] = "yourpreteststring";
$config["contact"]["test_all"] = "yourtestallstring";

// please manually uncomment the following line if you need external functions
// $config["contact"]["dispatcher_token"]          =   "3492yTGef8RhwGrujYGHUJ190ipo2rwmfSWwde";

//contact port
$config["contact"]["server"] = "127.0.0.1";
$config["contact"]["port"] = 5907;

//problems
$config["problem"]["category_tab_spaces"] = 4;

//status
$config["status"]["refresh_rate"] = 5000; //ms
$config["status"]["max_refresh_times"] = 5;


//other
$config["OJcode"] = "BNU";
$config["base_url"] = "http://localhost/bnuoj/v3/";
$config["base_path"] = "/bnuoj/v3";
$config["base_local_path"] = "/var/www/bnuoj/v3/";
$config["local_timezone"] = "Asia/Shanghai";
$config["salt_problem_in_contest"] = "[-,-]";
$config["database_debug"] = false;
$config["cookie_prefix"] = "bnuoj_v3_";

$config["use_latex_render"] = false;

//accounts
$config["accounts"]["lightoj"]["username"] = "public@51isoft.com";
$config["accounts"]["lightoj"]["password"] = "sjkaqwq5";


$ojoptions = '<option value="BNU">BNU</option>' .
    '<option value="PKU">PKU</option>' .
    '<option value="CodeForces">CodeForces</option>' .
    '<option value="CodeForcesGym">CodeForcesGym</option>' .
    '<option value="HDU">HDU</option>' .
    '<option value="UVALive">UVALive</option>' .
    '<option value="SGU">SGU</option>' .
    '<option value="LightOJ">LightOJ</option>' .
    '<option value="Ural">Ural</option>' .
    '<option value="ZJU">ZJU</option>' .
    '<option value="UVA">UVA</option>' .
    '<option value="SPOJ">SPOJ</option>' .
    '<option value="UESTC">UESTC</option>' .
    '<option value="FZU">FZU</option>' .
    '<option value="NBUT">NBUT</option>' .
    '<option value="WHU">WHU</option>' .
    '<option value="SYSU">SYSU</option>' .
    '<option value="OpenJudge">OpenJudge</option>' .
    '<option value="SCU">SCU</option>' .
    '<option value="HUST">HUST</option>' .
    '<option value="NJUPT">NJUPT</option>' .
    '<option value="Aizu">Aizu</option>' .
    '<option value="ACdream">ACdream</option>' .
    '<option value="CodeChef">CodeChef</option>' .
    '<option value="HRBUST">HRBUST</option>';
