<?php


namespace app\modules\polygon\models;

use Yii;
use yii\db\Expression;
use app\models\User;

/**
 * This is the model class for table "{{%polygon_problem}}".
 *
 * @property int $id
 * @property string $title
 * @property string $description
 * @property string $input
 * @property string $output
 * @property string $sample_input
 * @property string $sample_output
 * @property string $spj
 * @property int $spj_lang
 * @property string $spj_source
 * @property string $hint
 * @property string $created_at
 * @property string $updated_at
 * @property int $created_by
 * @property int $time_limit
 * @property int $memory_limit
 * @property string $tags
 * @property string $solution
 * @property int $status
 * @property int $solution_lang
 * @property string $solution_source
 */
class Problem extends \yii\db\ActiveRecord
{
    const OJ_WT0 = 0;
    const OJ_WT1 = 1;
    const OJ_CI  = 2;
    const OJ_RI  = 3;
    const OJ_AC  = 4;
    const OJ_PE  = 5;
    const OJ_WA  = 6;
    const OJ_TL  = 7;
    const OJ_ML  = 8;
    const OJ_OL  = 9;
    const OJ_RE  = 10;
    const OJ_CE  = 11;
    const OJ_SE  = 12;
    const OJ_NT  = 13;

    public $sample_input_2;
    public $sample_output_2;
    public $sample_input_3;
    public $sample_output_3;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%polygon_problem}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['description', 'input', 'output', 'sample_input', 'sample_output', 'sample_input_2', 'sample_output_2',
              'sample_input_3', 'sample_output_3', 'hint', 'tags', 'spj_source', 'solution_source', 'solution'], 'string'],
            [['created_at', 'updated_at'], 'safe'],
            [['title'], 'required'],
            [['created_by', 'time_limit', 'memory_limit', 'status', 'solution_lang', 'spj_lang', 'spj'], 'integer'],
            [['title'], 'string', 'max' => 200],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'title' => Yii::t('app', 'Title'),
            'description' => Yii::t('app', 'Description'),
            'input' => Yii::t('app', 'Input'),
            'output' => Yii::t('app', 'Output'),
            'sample_input' => Yii::t('app', 'Sample Input'),
            'sample_output' => Yii::t('app', 'Sample Output'),
            'spj' => Yii::t('app', 'Special Judge'),
            'spj_lang' => Yii::t('app', 'Lang'),
            'spj_source' => Yii::t('app', 'Source'),
            'hint' => Yii::t('app', 'Hint'),
            'created_at' => Yii::t('app', 'Created At'),
            'created_by' => Yii::t('app', 'Created By'),
            'time_limit' => Yii::t('app', 'Time Limit'),
            'memory_limit' => Yii::t('app', 'Memory Limit'),
            'tags' => Yii::t('app', 'Tags'),
            'status' => Yii::t('app', 'Status'),
            'solution_lang' => Yii::t('app', 'Solution Language'),
            'solution_source' => Yii::t('app', 'Solution Source'),
        ];
    }

    /**
     * This is invoked before the record is saved.
     * @return boolean whether the record should be saved.
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($this->isNewRecord) {
                $this->memory_limit = 128;
                $this->time_limit = 1;
                $this->created_by = Yii::$app->user->id;
                $this->created_at = new Expression('NOW()');
            }
            $this->updated_at = new Expression('NOW()');

            $hint = preg_replace("/(\s|\&nbsp\;|　|\xc2\xa0)/", '', strip_tags($this->hint));
            if (empty($hint)) {
                $this->hint = $hint;
            }
            //标签处理
            $tagArr = explode(',', str_replace('，', ',', $this->tags));
            foreach ($tagArr as &$tag) {
                $tag = trim($tag);
            }
            $explodeTags = array_unique($tagArr);
            $this->tags = implode(',', $explodeTags);
            return true;
        } else {
            return false;
        }
    }

    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'created_by']);
    }

    public static function getResultList($res = '')
    {
        $results = [
            '' => 'All',
            self::OJ_WT0 => Yii::t('app', 'Pending'),
            self::OJ_WT1 => Yii::t('app', 'Pending Rejudge'),
            self::OJ_CI => Yii::t('app', 'Compiling'),
            self::OJ_RI => Yii::t('app', 'Running & Judging'),
            self::OJ_AC => Yii::t('app', 'Accepted'),
            self::OJ_PE => Yii::t('app', 'Presentation Error'),
            self::OJ_WA => Yii::t('app', 'Wrong Answer'),
            self::OJ_TL => Yii::t('app', 'Time Limit Exceeded'),
            self::OJ_ML => Yii::t('app', 'Memory Limit Exceeded'),
            self::OJ_OL => Yii::t('app', 'Output Limit Exceeded'),
            self::OJ_RE => Yii::t('app', 'Runtime Error'),
            self::OJ_CE => Yii::t('app', 'Compile Error'),
            self::OJ_SE => Yii::t('app', 'System Error'),
            self::OJ_NT => Yii::t('app', 'No Test Data')
        ];
        return $res === '' ? $results : $results[$res];
    }

    /**
     * 将序列化保存后的数组解出来
     */
    public function setSamples()
    {
        $input = unserialize($this->sample_input);
        $output = unserialize($this->sample_output);
        $this->sample_input = $input[0];
        $this->sample_output = $output[0];
        $this->sample_input_2 = $input[1];
        $this->sample_output_2 = $output[1];
        $this->sample_input_3 = $input[2];
        $this->sample_output_3 = $output[2];
    }

    public function getDataFiles()
    {
        $path = Yii::$app->params['polygonProblemDataPath'] . $this->id;
        if (!is_dir($path)) {
            @mkdir(Yii::$app->params['polygonProblemDataPath'] . $this->id);
        }
        $files = [];
        try {
            if ($handler = opendir($path)) {
                while (($file = readdir($handler)) !== false) {
                    $files[$file]['name'] = $file;
                    $files[$file]['size'] = filesize($path . '/' . $file);
                    $files[$file]['time'] = filemtime($path . '/' . $file);
                }
                closedir($handler);
            }
            usort($files, function($a, $b) {
                return (int) $a['name'] >  (int) $b['name'];
            });
        } catch(\Exception $e) {
            echo 'Message: ' .$e->getMessage();
        }
        return $files;
    }
}
