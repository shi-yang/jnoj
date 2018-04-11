<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%discuss}}".
 *
 * @property int $id
 * @property int $parent_id
 * @property string $entity
 * @property int $entity_id
 * @property string title
 * @property int $created_by
 * @property string $updated_at
 * @property string $created_at
 * @property string $content
 * @property int $status
 */
class Discuss extends ActiveRecord
{
    const STATUS_DRAFT = 0;
    const STATUS_PUBLIC = 1;
    const STATUS_PRIVATE = 2;

    const ENTITY_CONTEST = 'contest';
    const ENTITY_PROBLEM = 'problem';
    const ENTITY_NEWS = 'news';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%discuss}}';
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
            [['created_by', 'problem_id', 'status', 'contest_id', 'parent_id', 'entity_id'], 'integer'],
            ['status', 'in', 'range' => [self::STATUS_DRAFT, self::STATUS_PUBLIC, self::STATUS_PRIVATE]],
            [['title', 'content', 'entity', 'created_at', 'updated_at'], 'string'],
            [['title'], 'required', 'on' => 'problem']
        ];
    }

    public function scenarios()
    {
        return [
            'default' => ['title', 'content', 'status'],
            'problem' => ['title', 'content'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'title' => Yii::t('app', 'Title'),
            'created_by' => Yii::t('app', 'Created By'),
            'updated_at' => Yii::t('app', 'Last reply time'),
            'created_at' => Yii::t('app', 'Created At'),
            'content' => Yii::t('app', 'Content'),
            'status' => Yii::t('app', 'Status'),
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
            }
            return true;
        } else {
            return false;
        }
    }

    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'created_by']);
    }

    public function getReply()
    {
        return Discuss::findAll(['parent_id' => $this->id]);
    }

    public function getProblem()
    {
        return $this->hasOne(Problem::className(), ['id' => 'entity_id']);
    }
}
