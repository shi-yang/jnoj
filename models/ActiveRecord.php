<?php

namespace app\models;

use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

class ActiveRecord extends \yii\db\ActiveRecord
{
    protected function timeStampBehavior($hasUpdatedField = true)
    {
        $attributes = [
            self::EVENT_BEFORE_INSERT => 'created_at',
        ];
        if ($hasUpdatedField) {
            $attributes[self::EVENT_BEFORE_INSERT] = 'updated_at';
            $attributes[self::EVENT_BEFORE_UPDATE] = 'updated_at';
        }
        return [
            'class' => TimestampBehavior::class,
            'value' => new Expression('NOW()'),
            'attributes' => $attributes,
        ];
    }
}
