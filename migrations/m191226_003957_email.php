<?php

use yii\db\Migration;
use yii\db\Schema;

/**
 * Class m191226_003957_email
 */
class m191226_003957_email extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%user}}', 'is_verify_email', $this->smallInteger()->notNull()->defaultValue(0));
        $this->addColumn('{{%user}}', 'verification_token', $this->string()->notNull()->defaultValue(''));
        $this->insert('{{%setting}}', ['key' => 'passwordResetTokenExpire', 'value' => '7200']);
        $this->insert('{{%setting}}', ['key' => 'mustVerifyEmail', 'value' => '0']);
        $this->insert('{{%setting}}', ['key' => 'emailHost', 'value' => 'smtp.exmail.qq.com']);
        $this->insert('{{%setting}}', ['key' => 'emailUsername', 'value' => 'no-reply@jnoj.org']);
        $this->insert('{{%setting}}', ['key' => 'emailPassword', 'value' => '8hVeA6LN4LPqwHei']);
        $this->insert('{{%setting}}', ['key' => 'emailPort', 'value' => '465']);
        $this->insert('{{%setting}}', ['key' => 'emailEncryption', 'value' => 'ssl']);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%user}}', 'is_verify_email');
        $this->dropColumn('{{%user}}', 'verification_token');
        $this->delete('{{%setting}}', ['key' => 'passwordResetTokenExpire']);
        $this->delete('{{%setting}}', ['key' => 'mustVerifyEmail']);
        $this->delete('{{%setting}}', ['key' => 'emailHost']);
        $this->delete('{{%setting}}', ['key' => 'emailUsername']);
        $this->delete('{{%setting}}', ['key' => 'emailPassword']);
        $this->delete('{{%setting}}', ['key' => 'emailPort']);
        $this->delete('{{%setting}}', ['key' => 'emailEncryption']);
    }
}
