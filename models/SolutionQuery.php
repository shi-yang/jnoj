<?php

namespace app\models;

/**
 * This is the ActiveQuery class for [[Solution]].
 *
 * @see Solution
 */
class SolutionQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * @inheritdoc
     * @return Solution[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return Solution|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
