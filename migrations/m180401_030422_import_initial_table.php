<?php

use app\migrations\BaseMigration;
use yii\db\Schema;
use yii\base\Security;

/**
 * Class m180401_030422_import_initial_table
 */
class m180401_030422_import_initial_table extends BaseMigration
{
    const USER_AUTO_INCREMENT_NUM = 1;

    public function up()
    {
        $this->createTable('{{%user}}', [
            'id' => $this->primaryKey(),
            'username' => $this->string(64)->notNull(),
            'nickname' => $this->string(64)->notNull(),
            'auth_key' => $this->string(32)->notNull(),
            'password_hash' => $this->string(128)->notNull(),
            'password_reset_token' => $this->string(),
            'email' => $this->string(255)->notNull(),
            'status' => Schema::TYPE_SMALLINT . ' NOT NULL DEFAULT 10',
            'role' => Schema::TYPE_SMALLINT . ' NOT NULL DEFAULT 10',
            'language' => Schema::TYPE_SMALLINT . ' NOT NULL DEFAULT 1',
            'created_at' => $this->dateTime()->notNull(),
            'updated_at' => $this->dateTime(),
            'rating' => $this->integer()->null(),
        ], $this->tableOptions);

        $this->createIndex('idx-user-username-unique', '{{%user}}', 'username', true);
        $this->createIndex('idx-user-email-unique', '{{%user}}', 'email', true);


        // 输入管理员信息
        fwrite(STDOUT, 'Enter Administrator\'s name:');
        $username = trim(fgets(STDIN));
        fwrite(STDOUT, 'Enter Administrator\'s password:');
        $password = trim(fgets(STDIN));
        fwrite(STDOUT, 'Enter Administrator\'s email:');
        $email = trim(fgets(STDIN));
        $password_hash = (new Security)->generatePasswordHash($password);
        $auth_key = (new Security())->generateRandomString();
        $time = date("Y-m-d H:i:s");

        $this->insert('{{%user}}', [
            'id' => self::USER_AUTO_INCREMENT_NUM,
            'username' => $username,
            'nickname' => $username,
            'password_hash' => $password_hash,
            'auth_key' => $auth_key,
            'email' => $email,
            'created_at' => $time,
            'updated_at' => $time,
            'role' => \app\models\User::ROLE_ADMIN
        ]);


        $this->createTable('{{%user_profile}}', [
            'user_id' => $this->integer(),
            'gender' => $this->smallInteger()->defaultValue(0),
            'qq_number' => $this->bigInteger(11),
            'birthdate' => $this->date(),
            'signature' => $this->string(),
            'address' => $this->string(),
            'description' => $this->string(),
            'school' => $this->string(),
            'student_number' => $this->string(64),
            'major' => $this->string(64)
        ], $this->tableOptions);

        $this->execute('ALTER TABLE `user_profile` ADD PRIMARY KEY(`user_id`);');

        $this->insert('{{%user_profile}}', [
            'user_id' => self::USER_AUTO_INCREMENT_NUM,
        ]);

        $this->createTable('{{%contest}}', [
            'id' => $this->primaryKey(),
            'title' => $this->string(),
            'start_time' => $this->dateTime(),
            'end_time' => $this->dateTime(),
            'lock_board_time' => $this->dateTime(),
            'status' => $this->smallInteger(),
            'editorial' => $this->text(),
            'description' => $this->text(),
            'type' => $this->smallInteger()->notNull()->defaultValue(0),
            'scenario' => $this->smallInteger()->notNull()->defaultValue(0),
            'created_by' => $this->integer()->notNull()
        ], $this->tableOptions);

        $this->createTable('{{%contest_announcement}}', [
            'contest_id' => $this->integer()->notNull(),
            'content' => $this->text()->notNull(),
            'created_at' => $this->dateTime()->notNull()
        ], $this->tableOptions);

        $this->createTable('{{%contest_print}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'source' => $this->text()->notNull(),
            'created_at' => $this->dateTime()->notNull(),
            'status' => $this->smallInteger()->notNull()->defaultValue(0),
            'contest_id' => $this->integer()->notNull()
        ], $this->tableOptions);

        $this->createTable('{{%contest_problem}}', [
            'id' => $this->primaryKey(),
            'problem_id' => $this->integer()->notNull(),
            'contest_id' => $this->integer()->notNull(),
            'num' => $this->smallInteger()->notNull()
        ], $this->tableOptions);

        $this->createTable('{{%contest_user}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'contest_id' => $this->integer()->notNull(),
            'user_password' => $this->string(32),
            'rank' => $this->integer()->null(),
            'rating_change' => $this->integer()->null()
        ], $this->tableOptions);

        $this->createTable('{{%discuss}}', [
            'id' => $this->primaryKey(),
            'parent_id' => $this->integer()->defaultValue(0),
            'title' => $this->string(),
            'created_by' => $this->integer()->notNull(),
            'content' => $this->text(),
            'created_at' => $this->dateTime()->notNull(),
            'updated_at' => $this->dateTime()->notNull(),
            'status' => $this->smallInteger()->notNull()->defaultValue(0),
            'entity' => $this->string(32),
            'entity_id' => $this->integer()
        ], $this->tableOptions);

        $this->createTable('{{%problem}}', [
            'id' => $this->primaryKey(),
            'title' => $this->string(),
            'description' => $this->text(),
            'input' => $this->text(),
            'output' => $this->text(),
            'sample_input' => $this->text(),
            'sample_output' => $this->text(),
            'spj' => $this->smallInteger(1)->defaultValue(0),
            'hint' => $this->text(),
            'source' => $this->string(),
            'time_limit' => $this->integer(),
            'memory_limit' => $this->integer(),
            'status' => $this->smallInteger()->defaultValue(0),
            'accepted' => $this->integer()->defaultValue(0),
            'submit' => $this->integer()->defaultValue(0),
            'solved' => $this->integer()->defaultValue(0),
            'tags' => $this->text(),
            'created_at' => $this->dateTime(),
            'created_by' => $this->integer(),
            'updated_at' => $this->dateTime(),
            'polygon_problem_id' => $this->integer()
        ], $this->tableOptions);

        $this->createTable('{{%setting}}', [
            'key' => $this->string(),
            'value' => $this->text()
        ], $this->tableOptions);

        $this->createTable('{{%solution}}', [
            'id' => $this->primaryKey(),
            'problem_id' => $this->integer()->notNull(),
            'time' => $this->integer()->notNull()->defaultValue(0),
            'memory' => $this->integer()->notNull()->defaultValue(0),
            'created_at' => $this->dateTime()->notNull(),
            'source' => $this->text()->notNull(),
            'result' => $this->smallInteger()->notNull()->defaultValue(0),
            'language' => $this->smallInteger()->notNull(),
            'contest_id' => $this->integer()->defaultValue(null),
            'status' => $this->smallInteger()->notNull(),
            'code_length' => $this->integer()->notNull(),
            'judgetime' => $this->dateTime(),
            'pass_info' => $this->string(),
            'judge' => $this->string(32),
            'created_by' => $this->integer()->notNull()
        ], $this->tableOptions);

        $this->createTable('{{%solution_info}}', [
            'solution_id' => $this->integer()->notNull(),
            'error' => $this->text()
        ], $this->tableOptions);

        $this->createTable('{{%polygon_problem}}', [
            'id' => $this->primaryKey(),
            'title' => $this->string(),
            'description' => $this->text(),
            'input' => $this->text(),
            'output' => $this->text(),
            'sample_input' => $this->text(),
            'sample_output' => $this->text(),
            'spj' => $this->smallInteger(1)->defaultValue(0),
            'spj_lang' => $this->smallInteger(),
            'spj_source' => $this->text(),
            'hint' => $this->text(),
            'source' => $this->string(),
            'time_limit' => $this->integer(),
            'memory_limit' => $this->integer(),
            'status' => $this->smallInteger()->defaultValue(0),
            'accepted' => $this->integer()->defaultValue(0),
            'submit' => $this->integer()->defaultValue(0),
            'solved' => $this->integer()->defaultValue(0),
            'tags' => $this->text(),
            'solution_lang' => $this->smallInteger(),
            'solution_source' => $this->text(),
            'created_at' => $this->dateTime()->notNull(),
            'updated_at' => $this->dateTime()->notNull(),
            'created_by' => $this->integer()->notNull(),
        ], $this->tableOptions);

        $this->createTable('{{%polygon_status}}', [
            'id' => $this->primaryKey(),
            'problem_id' => $this->integer(),
            'result' => $this->smallInteger()->notNull()->defaultValue(0),
            'time' => $this->integer(),
            'memory' => $this->integer(),
            'info' => $this->text(),
            'created_at' => $this->dateTime()->notNull(),
        ], $this->tableOptions);
    }

    public function down()
    {
        $this->dropTable('{{%contest_print}}');
        $this->dropTable('{{%contest_announcement}}');
        $this->dropTable('{{%contest_problem}}');
        $this->dropTable('{{%contest_user}}');
        $this->dropTable('{{%discuss}}');
        $this->dropTable('{{solution_info}}');
        $this->dropTable('{{%solution}}');
        $this->dropTable('{{%contest}}');
        $this->dropTable('{{%problem}}');
        $this->dropTable('{{%polygon_status}}');
        $this->dropTable('{{%polygon_problem}}');
        $this->dropTable('{{%user_profile}}');
        $this->dropTable('{{%user}}');
        $this->dropTable('{{%setting}}');
    }
}
