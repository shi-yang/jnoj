<?php

namespace app\models;

/**
 * This is the ActiveQuery class for [[Contest]].
 *
 * @see Contest
 */
class ContestQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * @inheritdoc
     * @return Contest[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return Contest|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
