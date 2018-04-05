<?php

namespace app\modules\polygon\models;

use Yii;

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
 * @property int $created_by
 * @property int $time_limit
 * @property int $memory_limit
 * @property string $tags
 * @property int $status
 * @property int $solution_language
 * @property string $solution_source
 */
class Problem extends \yii\db\ActiveRecord
{
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
            [['description', 'input', 'output', 'sample_input', 'sample_output', 'hint', 'tags', 'spj_source', 'solution_source'], 'string'],
            [['created_at'], 'safe'],
            [['title'], 'required'],
            [['created_by', 'time_limit', 'memory_limit', 'status', 'solution_language', 'spj_lang'], 'integer'],
            [['title'], 'string', 'max' => 200],
            [['spj'], 'string', 'max' => 1],
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
            'solution_language' => Yii::t('app', 'Solution Language'),
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
                $this->created_by = Yii::$app->user->id;
                $this->created_at = time();
            }
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
}
