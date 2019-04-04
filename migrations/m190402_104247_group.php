<?php

use app\migrations\BaseMigration;

/**
 * Class m190402_104247_group
 */
class m190402_104247_group extends BaseMigration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%group}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string('32')->notNull(),
            'description' => $this->string('255')->notNull()->defaultValue(''),
            'status' => $this->tinyInteger()->notNull(),
            'join_policy' => $this->tinyInteger()->defaultValue(0)->notNull(),
            'created_by' => $this->integer()->notNull(),
            'created_at' => $this->dateTime()->notNull(),
            'updated_at' => $this->dateTime()->notNull(),
        ], $this->tableOptions);

        $this->createTable('{{%group_user}}', [
            'id' => $this->primaryKey(),
            'group_id' => $this->integer()->notNull(),
            'user_id' => $this->integer()->notNull(),
            'role' => $this->tinyInteger()->notNull()->defaultValue(0),
            'created_at' => $this->dateTime()->notNull(),
        ], $this->tableOptions);

        $this->addColumn('{{%contest}}', 'group_id','INT(11) NOT NULL DEFAULT 0 AFTER type');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%group_user}}');
        $this->dropTable('{{%group}}');
        $this->dropColumn('{{%contest}}', 'group_id');
    }
}
