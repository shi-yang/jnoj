<?php

namespace app\modules\admin\models;

use Yii;
use yii\db\Query;
use yii\base\Model;
use app\models\Solution;

/**
 * ContactForm is the model behind the contact form.
 */
class SettingForm extends Model
{
    public $problem_data_path;

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            [['problem_data_path'], 'string'],
        ];
    }

    /**
     * @return array customized attribute labels
     */
    public function attributeLabels()
    {
        return [
            'problem_data_path' => 'Problem Data Path',
        ];
    }
}
