<?php

use yii\db\Migration;

/**
 * Class m190519_044551_problem_solution
 */
class m190519_024551_problem_solution extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%problem}}', 'solution', 'TEXT AFTER tags');
        $this->addColumn('{{%polygon_problem}}', 'solution', 'TEXT AFTER tags');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%problem}}', 'solution');
        $this->dropColumn('{{%polygon_problem}}', 'solution');
    }
}
