<?php

use yii\db\Migration;

/**
 * Class m181109_032216_index
 */
class m181109_032216_index extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createIndex('idx_user_id', 'contest_print', 'user_id');
        $this->createIndex('idx_contest_id', 'contest_print', 'contest_id');

        $this->createIndex('idx_problem_id', 'contest_problem', 'problem_id');
        $this->createIndex('idx_contest_id', 'contest_problem', 'contest_id');

        $this->createIndex('idx_user_id', 'contest_user', 'user_id');
        $this->createIndex('idx_contest_id', 'contest_user', 'contest_id');

        $this->createIndex('idx_parent_id', 'discuss', 'parent_id');
        $this->createIndex('idx_entity_id', 'discuss', 'entity_id');
        $this->createIndex('idx_entity', 'discuss', 'entity');
        $this->createIndex('idx_created_by', 'discuss', 'created_by');

        $this->createIndex('idx_created_by', 'polygon_problem', 'created_by');

        $this->createIndex('idx_problem_id', 'polygon_status', 'problem_id');
        $this->createIndex('idx_created_by', 'polygon_status', 'created_by');

        $this->createIndex('idx_created_by', 'problem', 'created_by');

        $this->createIndex('idx_problem_id', 'solution', 'problem_id');
        $this->createIndex('idx_contest_id', 'solution', 'contest_id');
        $this->createIndex('idx_created_by', 'solution', 'created_by');
        $this->createIndex('idx_result', 'solution', 'result');


        $this->addPrimaryKey('pk_solution_id', 'solution_info', 'solution_id');

        $this->createIndex('idx_created_by', 'contest', 'created_by');
        $this->addPrimaryKey('pk_contest_id', 'contest_announcement', 'contest_id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('idx_user_id', 'contest_print');
        $this->dropIndex('idx_contest_id', 'contest_print');

        $this->dropIndex('idx_problem_id', 'contest_problem');
        $this->dropIndex('idx_contest_id', 'contest_problem');

        $this->dropIndex('idx_user_id', 'contest_user');
        $this->dropIndex('idx_contest_id', 'contest_user');

        $this->dropIndex('idx_parent_id', 'discuss');
        $this->dropIndex('idx_entity_id', 'discuss');
        $this->dropIndex('idx_entity', 'discuss');
        $this->dropIndex('idx_created_by', 'discuss');

        $this->dropIndex('idx_created_by', 'polygon_problem');

        $this->dropIndex('idx_problem_id', 'polygon_status');
        $this->dropIndex('idx_created_by', 'polygon_status');

        $this->dropIndex('idx_created_by', 'problem');

        $this->dropIndex('idx_problem_id', 'solution');
        $this->dropIndex('idx_contest_id', 'solution');
        $this->dropIndex('idx_created_by', 'solution');
        $this->dropIndex('idx_result', 'solution');

        $this->dropPrimaryKey('pk_solution_id', 'solution_info');

        $this->dropIndex('idx_created_by', 'contest');
        $this->dropPrimaryKey('pk_contest_id', 'contest_announcement');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m181109_032216_index cannot be reverted.\n";

        return false;
    }
    */
}
