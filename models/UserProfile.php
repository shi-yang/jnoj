<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%user_profile}}".
 *
 * @property int $user_id
 * @property int $gender
 * @property int $qq_number
 * @property string $birthdate
 * @property string $signature
 * @property string $address
 * @property string $description
 * @property string $school
 * @property string $student_number
 * @property string $major
 */
class UserProfile extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%user_profile}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id'], 'required'],
            [['user_id', 'gender', 'qq_number', 'student_number'], 'integer'],
            [['birthdate'], 'safe'],
            [['address', 'description', 'major'], 'string'],
            [['signature', 'school'], 'string', 'max' => 128],
            [['major'], 'string', 'max' => 64],
            [['user_id'], 'unique'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'user_id' => Yii::t('app', 'User ID'),
            'gender' => Yii::t('app', 'Gender'),
            'qq_number' => Yii::t('app', 'QQ'),
            'birthdate' => Yii::t('app', 'Birthdate'),
            'signature' => Yii::t('app', 'Signature'),
            'address' => Yii::t('app', 'Address'),
            'description' => Yii::t('app', 'Description'),
            'school' => Yii::t('app', 'School'),
            'student_number' => Yii::t('app', 'Student Number'),
            'major' => Yii::t('app', 'Major')
        ];
    }
}
