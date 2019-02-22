<?php

namespace app\models;

use Yii;
use yii\db\Query;

/**
 * This is the model class for table "{{%problem}}".
 *
 * @property int $id
 * @property string $title
 * @property string $description
 * @property string $input
 * @property string $output
 * @property string $sample_input
 * @property string $sample_output
 * @property string $spj
 * @property string $hint
 * @property string $source
 * @property int $created_at
 * @property int $updated_at
 * @property int $time_limit
 * @property int $memory_limit
 * @property int $status
 * @property int $accepted
 * @property int $submit
 * @property int $solved
 * @property int $created_by
 * @property string $tags
 * @property int polygon_problem_id
 */
class Problem extends ActiveRecord
{
    const STATUS_HIDDEN = 0;
    const STATUS_VISIBLE = 1;
    const STATUS_PRIVATE = 2;

    public $contest_id;
    public $test_status;

    public $sample_input_2;
    public $sample_output_2;
    public $sample_input_3;
    public $sample_output_3;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%problem}}';
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'timestamp' => $this->timeStampBehavior(),
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['description', 'input', 'output', 'sample_input', 'sample_output', 'hint', 'test_status', 'tags'], 'string'],
            [['sample_input_2', 'sample_output_2', 'sample_input_3', 'sample_output_3', 'created_at',
              'updated_at', ], 'string'],
            [['time_limit', 'memory_limit', 'accepted', 'submit', 'solved', 'status', 'contest_id', 'created_by', 'polygon_problem_id'], 'integer'],
            [['title'], 'string', 'max' => 200],
            [['spj'], 'integer', 'max' => 1],
            [['source'], 'string', 'max' => 100],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'Problem ID'),
            'title' => Yii::t('app', 'Title'),
            'description' => Yii::t('app', 'Description'),
            'input' => Yii::t('app', 'Input'),
            'output' => Yii::t('app', 'Output'),
            'sample_input' => Yii::t('app', 'Sample Input'),
            'sample_output' => Yii::t('app', 'Sample Output'),
            'sample_input_2' => Yii::t('app', 'Sample Input 2'),
            'sample_output_2' => Yii::t('app', 'Sample Output 2'),
            'sample_input_3' => Yii::t('app', 'Sample Input 3'),
            'sample_output_3' => Yii::t('app', 'Sample Output 3'),
            'spj' => Yii::t('app', 'Special Judge'),
            'hint' => Yii::t('app', 'Hint'),
            'source' => Yii::t('app', 'Source'),
            'created_at' => Yii::t('app', 'Created At'),
            'time_limit' => Yii::t('app', 'Time Limit'),
            'memory_limit' => Yii::t('app', 'Memory Limit'),
            'status' => Yii::t('app', 'Status'),
            'accepted' => Yii::t('app', 'Accepted'),
            'submit' => Yii::t('app', 'Submit'),
            'solved' => Yii::t('app', 'Solved'),
            'problem_data' => Yii::t('app', 'Problem Data'),
            'test_status' => Yii::t('app', 'Test Status'),
            'tags' => Yii::t('app', 'Tags'),
            'created_by' => Yii::t('app', 'Created By')
        ];
    }

    /**
     * @inheritdoc
     * @return ProblemQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new ProblemQuery(get_called_class());
    }

    /**
     * This is invoked before the record is saved.
     * @return boolean whether the record should be saved.
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            $hint = preg_replace("/(\s|\&nbsp\;|　|\xc2\xa0)/", '', strip_tags($this->hint));
            if (empty($hint)) {
                $this->hint = $hint;
            }
            //标签分割
            $tags = trim($this->tags);
            $explodeTags = array_unique(explode(',', str_replace('，', ',', $tags)));
            $explodeTags = array_slice($explodeTags, 0, 10);
            $this->tags = implode(',', $explodeTags);
            return true;
        } else {
            return false;
        }
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
        $path = Yii::$app->params['judgeProblemDataPath'] . $this->id ;
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

    public function getDiscusses()
    {
        return $this->hasMany(Discuss::className(), ['problem_id' => 'id']);
    }

    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'created_by']);
    }

    public function getStatisticsData()
    {
        $data = Yii::$app->db->createCommand(
            'SELECT created_by, result FROM {{%solution}} WHERE problem_id=:pid AND contest_id is null',
            [':pid' => $this->id])->queryAll();
        $users = [];
        $accepted_submission = 0;
        $tle_submission = 0;
        $ce_submission = 0;
        $wa_submission = 0;
        $user_count = 0;
        $submission_count = count($data);
        foreach ($data as $v) {
            if (!isset($users[$v['created_by']])) {
                $user_count++;
                $users[$v['created_by']] = 1;
            }
            if ($v['result'] == Solution::OJ_WA) {
                $wa_submission++;
            } else if ($v['result'] == Solution::OJ_AC) {
                $accepted_submission++;
            } else if ($v['result'] == Solution::OJ_CE) {
                $ce_submission++;
            } else if ($v['result'] == Solution::OJ_TL) {
                $tle_submission++;
            }
        }
        return [
            'accepted_count' => $accepted_submission,
            'ce_submission' => $ce_submission,
            'wa_submission' => $wa_submission,
            'tle_submission' => $tle_submission,
            'submission_count' => $submission_count,
            'user_count' => $user_count
        ];
    }
}
