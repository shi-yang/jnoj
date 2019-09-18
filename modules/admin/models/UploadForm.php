<?php

namespace app\modules\admin\models;

use Yii;
use app\models\Problem;
use yii\base\Model;
use yii\db\Query;
use yii\web\UploadedFile;

/**
 * UploadForm 用来导入题目
 */
class UploadForm extends Model
{
    /**
     * @var UploadedFile
     */
    public $problemFile;

    public function rules()
    {
        return [
            [['problemFile'], 'file', 'skipOnEmpty' => false, 'extensions' => 'zip, xml'],
        ];
    }

    public function upload()
    {
        if ($this->validate()) {
            $tempFile = $this->problemFile->tempName;
            if ($this->problemFile->extension == "zip") {
                $resource = zip_open($tempFile);
                $tempFile = tempnam("/tmp", "fps");
                while ($dirResource = zip_read($resource)) {
                    $fileName = zip_entry_name($dirResource);
                    if (!is_dir($fileName)) {
                        $fileSize = zip_entry_filesize($dirResource);
                        $fileContent = zip_entry_read($dirResource, $fileSize);
                        file_put_contents($tempFile, $fileContent);
                        self::importFPS($tempFile);
                    }
                    zip_entry_close($dirResource);
                }
                zip_close($resource);
            } else {
                self::importFPS($tempFile);
            }
            return true;
        } else {
            return false;
        }
    }

    public static function importFPS($tempFile)
    {
        $xmlDoc = simplexml_load_file($tempFile, 'SimpleXMLElement', LIBXML_PARSEHUGE);
        $searchNodes = $xmlDoc->xpath("/fps/item");
        set_time_limit(0);
        ob_end_clean();
        foreach ($searchNodes as $searchNode) {
            $title = (string)$searchNode->title;
            if (!self::hasProblem($title)) {
                $spjCode = self::getValue($searchNode, 'spj');
                $spj = trim($spjCode) ? 1 : 0;
                $time_limit = $searchNode->time_limit;
                $unit = self::getAttribute($searchNode,'time_limit','unit');
                if ($unit == 'ms')
                    $time_limit /= 1000;
                $memory_limit = self::getValue($searchNode, 'memory_limit');
                $unit = self::getAttribute($searchNode,'memory_limit','unit');
                if ($unit == 'kb')
                    $memory_limit  /= 1024;
                $newProblem = new Problem();
                $newProblem->title = $title;
                $newProblem->description = self::getValue($searchNode, 'description');
                $newProblem->time_limit = $time_limit;
                $newProblem->memory_limit = $memory_limit;
                $newProblem->input = self::getValue($searchNode, 'input');
                $newProblem->output = self::getValue($searchNode, 'output');
                $newProblem->hint = self::getValue($searchNode, 'hint');
                $newProblem->source = self::getValue($searchNode, 'source');
                $newProblem->sample_input = serialize([self::getValue($searchNode, 'sample_input'), '', '']);
                $newProblem->sample_output = serialize([self::getValue($searchNode, 'sample_output'), '', '']);
                $newProblem->spj = $spj;
                $newProblem->created_by = Yii::$app->user->id;
                $newProblem->save();
                $pid = $newProblem->id;

                //创建输入文件
                $testInputs = $searchNode->children()->test_input;
                $testCnt = 0;
                foreach($testInputs as $testNode){
                    self::importTestData($pid, $testCnt++ . ".in", $testNode);
                }
                //创建输出文件
                $testOutputs = $searchNode->children()->test_output;
                $testCnt = 0;
                foreach($testOutputs as $testNode){
                    self::importTestData($pid, $testCnt++ . ".out", $testNode);
                }

                //SPJ 特判程序
                if ($spj) {
                    $basedir = Yii::$app->params['judgeProblemDataPath'] . $pid;
                    $fp = fopen("$basedir/spj.cc","w");
                    fputs($fp, $spjCode);
                    fclose($fp);
                    ////system( " g++ -o $basedir/spj $basedir/spj.cc  ");
                    if(!file_exists("$basedir/spj") ){
                        $fp = fopen("$basedir/spj.c","w");
                        fputs($fp, $spjCode);
                        fclose($fp);
                        ////system( " gcc -o $basedir/spj $basedir/spj.c  ");
                        if(!file_exists("$basedir/spj")){
                            echo "you need to compile $basedir/spj.cc for spj[  g++ -o $basedir/spj $basedir/spj.cc   ]<br> and rejudge $pid";
                        } else {
                            unlink("$basedir/spj.cc");
                        }
                    }
                }

                echo "$title 导入成功<br>";
            } else {
                echo "$title 已经存在<br>";
            }
            flush();
        }
        exit;
    }

    public static function hasProblem($title)
    {
        return (new Query())->select('1')
            ->from('{{%problem}}')
            ->where('md5(title)=:title', [':title' => md5($title)])
            ->count();
    }

    public static function getAttribute($Node, $TagName,$attribute)
    {
        return $Node->children()->$TagName->attributes()->$attribute;
    }

    public static function getValue($Node, $TagName)
    {
        return (string)$Node->$TagName;
    }

    public static function importTestData($pid, $filename, $fileContent)
    {
        $basedir = Yii::$app->params['judgeProblemDataPath'] . $pid;
        @mkdir($basedir);
        $fp = @fopen($basedir . "/$filename", "w");
        if ($fp) {
            fputs($fp, preg_replace("(\r\n)", "\n", $fileContent));
            fclose($fp);
        } else {
            echo "Error while opening ".$basedir . "/$filename.";
        }
    }
}
