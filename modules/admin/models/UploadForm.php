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
        $xmlDoc = simplexml_load_file($tempFile);
        $searchNodes = $xmlDoc->xpath("/fps/item");
        foreach ($searchNodes as $searchNode) {
            $title = (string)$searchNode->title;
            if (!self::hasProblem($title)) {
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
                $newProblem->sample_input = self::getValue($searchNode, 'sample_input');
                $newProblem->sample_output = self::getValue($searchNode, 'sample_output');
                $newProblem->created_by = Yii::$app->user->id;
                $newProblem->save();
                $spjcode = self::getValue($searchNode, 'spj');
                $spj = trim($spjcode) ? 1 : 0;
            }
        }
        die;
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
}