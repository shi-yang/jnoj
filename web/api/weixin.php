<?php
define("TOKEN", "yourtoken");
$wechatObj = new wechatCallbackapiTest();
$wechatObj->responseMsg();
function xml_entities($string)
{
    return str_replace(
        array("&", "<", ">", '"', "'"),
        array("&amp;", "&lt;", "&gt;", "&quot;", "&apos;"),
        $string
    );
}

class wechatCallbackapiTest
{
    private $postStr;

    public function valid()
    {
        $echoStr = $_GET["echostr"];

        //valid signature , option
        if ($this->checkSignature()) {
            echo $echoStr;
            exit;
        }
    }

    public function responseRecentContests($oj)
    {
        $resrow = json_decode(file_get_contents('../external/contests.json'), true);
        $contentStr = "";

        foreach ($resrow as $row) {
            if ($oj != "all" && strcasecmp(trim($oj), trim($row["oj"])) != 0) continue;
            $type = $row["access"];
            if ($type == "") $type = "Public";
            if ($oj == "all") $contentStr .= $row["name"] . " （" . $type . "） 将于 " . $row["start_time"] . " （" . $row["week"] . "） 在 " . $row["oj"] . "举行。\n\n";
            else $contentStr .= $row["name"] . " （" . $type . "） 将于 " . $row["start_time"] . " （" . $row["week"] . "） 在 " . $row["oj"] . "举行，地址：<a href=\"" . htmlspecialchars_decode($row["link"]) . "\">这里</a>\n\n";
        }

        if ($contentStr == "") $contentStr = "该OJ（" . $oj . "）最近木有比赛，休息一会吧～";
        else $contentStr .= "数据来源：<a href=\"http://acmicpc.info\">acmicpc.info</a>";
        $time = time();
        $textTpl = "<xml>
                    <ToUserName><![CDATA[%s]]></ToUserName>
                    <FromUserName><![CDATA[%s]]></FromUserName>
                    <CreateTime>%s</CreateTime>
                    <MsgType><![CDATA[%s]]></MsgType>
                    <Content><![CDATA[%s]]></Content>
                    <FuncFlag>0</FuncFlag>
                    </xml>";
        $msgType = "text";
        $resultStr = sprintf($textTpl, $this->postObj->FromUserName, $this->postObj->ToUserName, $time, $msgType, $contentStr);
        echo $resultStr;
    }

    public function responseRegister()
    {
        $time = time();
        $textTpl = "<xml>
                    <ToUserName><![CDATA[%s]]></ToUserName>
                    <FromUserName><![CDATA[%s]]></FromUserName>
                    <CreateTime>%s</CreateTime>
                    <MsgType><![CDATA[%s]]></MsgType>
                    <Content><![CDATA[%s]]></Content>
                    <FuncFlag>0</FuncFlag>
                    </xml>";
        $msgType = "text";
        $contentStr = "欢迎订阅BNUACM～回复任意字符可查看帮助哦～";
        $resultStr = sprintf($textTpl, $this->postObj->FromUserName, $this->postObj->ToUserName, $time, $msgType, $contentStr);
        echo $resultStr;
        exit;
    }

    public function responseOther()
    {
        $time = time();
        $textTpl = "<xml>
                    <ToUserName><![CDATA[%s]]></ToUserName>
                    <FromUserName><![CDATA[%s]]></FromUserName>
                    <CreateTime>%s</CreateTime>
                    <MsgType><![CDATA[%s]]></MsgType>
                    <Content><![CDATA[%s]]></Content>
                    <FuncFlag>0</FuncFlag>
                    </xml>";
        $msgType = "text";
        $contentStr = "听不懂肿么办……\n你可以发送OJ名字来查询该OJ最近比赛哦～\n\n例如回复［BNU］就能查询BNUOJ最近的比赛～\n回复［ALL］则可查看所有OJ。\n输入不区分大小写～\n\n广告：欢迎访问俺们<a href=\"http://www.bnuoj.com\">BNUOJ</a>";
        $resultStr = sprintf($textTpl, $this->postObj->FromUserName, $this->postObj->ToUserName, $time, $msgType, $contentStr);
        echo $resultStr;
        exit;
    }

    public function responseMsg()
    {
        //get post data, May be due to the different environments
        $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];

        //extract post data
        if ($this->checkSignature() && !empty($postStr)) {

            $this->postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
            $keyword = trim($this->postObj->Content);

            if ($keyword == "Hello2BizUser") $this->responseRegister();
            else if (strcasecmp($keyword, "cf") == 0 || strcasecmp($keyword, "codeforce") == 0 || strcasecmp($keyword, "CodeForces") == 0 || strcasecmp($keyword, "Code Forces") == 0 || strcasecmp($keyword, "Code Force") == 0) $this->responseRecentContests("CodeForces");
            else if (strcasecmp($keyword, "tc") == 0 || strcasecmp($keyword, "topcoder") == 0 || strcasecmp($keyword, "top coder") == 0 || strcasecmp($keyword, "srm") == 0 || strcasecmp($keyword, "tco") == 0) $this->responseRecentContests("Topcoder");
            else if (strcasecmp($keyword, "bnu") == 0 || strcasecmp($keyword, "bnuoj") == 0) $this->responseRecentContests("BNU");
            else if (strcasecmp($keyword, "pku") == 0 || strcasecmp($keyword, "poj") == 0) $this->responseRecentContests("PKU");
            else if (strcasecmp($keyword, "acdream") == 0 || strcasecmp($keyword, "ac dream") == 0) $this->responseRecentContests("ACdream");
            else if (strcasecmp($keyword, "bupt") == 0 || strcasecmp($keyword, "buptoj") == 0) $this->responseRecentContests("BUPT");
            else if (strcasecmp($keyword, "hdu") == 0 || strcasecmp($keyword, "hdoj") == 0) $this->responseRecentContests("HDU");
            else if (strcasecmp($keyword, "cc") == 0 || strcasecmp($keyword, "codechef") == 0 || strcasecmp($keyword, "code chef") == 0) $this->responseRecentContests("Codechef");
            else if (strcasecmp($keyword, "google") == 0 || strcasecmp($keyword, "gcj") == 0 || strcasecmp($keyword, "codejam") == 0) $this->responseRecentContests("Google");
            else if (strcasecmp($keyword, "zoj") == 0 || strcasecmp($keyword, "zju") == 0) $this->responseRecentContests("ZJU");
            else if (strcasecmp($keyword, "sgu") == 0) $this->responseRecentContests("SGU");
            else if (strcasecmp($keyword, "timus") == 0 || strcasecmp($keyword, "ural") == 0) $this->responseRecentContests("Ural");
            else if (strcasecmp($keyword, "uva") == 0) $this->responseRecentContests("UVA");
            else if (strcasecmp($keyword, "spoj") == 0 || strcasecmp($keyword, "sphere") == 0) $this->responseRecentContests("SPOJ");
            else if (strcasecmp($keyword, "sjtu") == 0 || strcasecmp($keyword, "sjtuoj") == 0) $this->responseRecentContests("SJTU");
            else if (strcasecmp($keyword, "sysu") == 0) $this->responseRecentContests("SYSU");
            else if (strcasecmp($keyword, "uestc") == 0) $this->responseRecentContests("UESTC");
            else if (strcasecmp($keyword, "fzu") == 0 || strcasecmp($keyword, "fzuoj") == 0) $this->responseRecentContests("FZU");
            else if (strcasecmp($keyword, "hit") == 0 || strcasecmp($keyword, "hitoj") == 0) $this->responseRecentContests("HIT");
            else if (strcasecmp($keyword, "hust") == 0 || strcasecmp($keyword, "hustoj") == 0) $this->responseRecentContests("HUST");
            else if (strcasecmp($keyword, "tju") == 0 || strcasecmp($keyword, "toj") == 0) $this->responseRecentContests("TJU");
            else if (strcasecmp($keyword, "all") == 0) $this->responseRecentContests("all");
            else $this->responseOther();

        } else {
            echo "";
            exit;
        }
    }

    private function checkSignature()
    {
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];

        $token = TOKEN;
        $tmpArr = array($token, $timestamp, $nonce);
        sort($tmpArr);
        $tmpStr = implode($tmpArr);
        $tmpStr = sha1($tmpStr);

        if ($tmpStr == $signature) {
            return true;
        } else {
            return false;
        }
    }
}

?>
