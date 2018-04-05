<?php

namespace app\models;

/**
 * This is the ActiveQuery class for [[Problem]].
 *
 * @see Problem
 */
class ProblemQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * @inheritdoc
     * @return Problem[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return Problem|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
