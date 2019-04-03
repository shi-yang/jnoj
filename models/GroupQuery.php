<?php

namespace app\models;

/**
 * This is the ActiveQuery class for [[Group]].
 *
 * @see Group
 */
class GroupQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return Group[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return Group|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
