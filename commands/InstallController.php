<?php

namespace app\commands;

use Yii;
use yii\console\Controller;

class InstallController extends Controller
{
    public function actionIndex()
    {
        echo "================================================\n";
        echo " Jiangnan Online Judge Initialization Tool v1.0\n";
        echo "================================================\n\n";
        $root = str_replace('\\', '/', __DIR__ . '/..');
        $env = [
            'setWritable' => [
                'runtime',
                'web/assets',
                'web/uploads',
                'polygon/log',
                'polygon/data',
                'judge/data',
                'judge/log',
            ],
            'setCookieValidationKey' => [
                'config/web.php',
            ]
        ];
        $callbacks = ['setCookieValidationKey', 'setWritable', 'setExecutable'];
        foreach ($callbacks as $callback) {
            if (!empty($env[$callback])) {
                $this->$callback($root, $env[$callback]);
            }
        }

        echo "\n================================================";
        echo "\n正在导入数据库...";
        $dsn = Yii::$app->db->dsn;
        preg_match_all("/=[a-zA-Z0-9\.]*/", $dsn, $matches);
        $host = substr($matches[0][0], 1);
        $dbname = substr($matches[0][1], 1);
        //根据 config\db.php 文件修改judge、polygon的数据库信息
        $this->setConfig('judge/config.ini', 'OJ_HOST_NAME', $host);
        $this->setConfig('judge/config.ini', 'OJ_USER_NAME', Yii::$app->db->username);
        $this->setConfig('judge/config.ini', 'OJ_PASSWORD', Yii::$app->db->password);
        $this->setConfig('judge/config.ini', 'OJ_DB_NAME', $dbname);
        $this->setConfig('polygon/config.ini', 'OJ_HOST_NAME', $host);
        $this->setConfig('polygon/config.ini', 'OJ_USER_NAME', Yii::$app->db->username);
        $this->setConfig('polygon/config.ini', 'OJ_PASSWORD', Yii::$app->db->password);
        $this->setConfig('polygon/config.ini', 'OJ_DB_NAME', $dbname);

        echo "\nRun: ./yii migrate";
        echo "\n================================================\n";
        passthru("./yii migrate");

        echo "\n================================================";
        echo "\nRun: php socket.php start -d";
        echo "\n================================================\n";
        passthru("php socket.php start -d");
        echo "\nInitialization completed.\n\n";
    }

    private function setConfig($file, $key, $value)
    {
        $str = file_get_contents($file);
        $str2 = preg_replace("/" . $key . "=(.*)/", $key . "=" . $value, $str);
        file_put_contents($file, $str2);
    }

    private function setWritable($root, $paths)
    {
        foreach ($paths as $writable) {
            echo "   chmod 0777 $writable\n";
            @chmod("$root/$writable", 0777);
        }
    }

    private function setExecutable($root, $paths)
    {
        foreach ($paths as $executable) {
            echo "   chmod 0755 $executable\n";
            @chmod("$root/$executable", 0755);
        }
    }

    private function setCookieValidationKey($root, $paths)
    {
        foreach ($paths as $file) {
            echo "   generate cookie validation key in $file\n";
            $file = $root . '/' . $file;
            $length = 32;
            $bytes = openssl_random_pseudo_bytes($length);
            $key = strtr(substr(base64_encode($bytes), 0, $length), '+/=', '_-.');
            $content = preg_replace('/(("|\')cookieValidationKey("|\')\s*=>\s*)(""|\'\')/', "\\1'$key'", file_get_contents($file));
            file_put_contents($file, $content);
        }
    }
}
