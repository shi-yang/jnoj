<?php

namespace app\models;

/**
 * This is the ActiveQuery class for [[GroupUser]].
 *
 * @see GroupUser
 */
class GroupUserQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return GroupUser[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return GroupUser|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
