<?php

use yii\db\Migration;

class m130524_201442_init extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%user}}', [
            'id' => $this->primaryKey(),
            'phoneNumber' => $this->string()->notNull()->unique(),
            'passwordHash' => $this->string()->notNull(),
            'email' => $this->string()->notNull()->unique(),

            'createdAt' => $this->integer()->notNull(),
            'updatedAt' => $this->integer()->notNull(),
        ], $tableOptions);

        $this->createTable('{{%message}}', [
            'messageId' => $this->primaryKey(),
            'senderId' => $this->integer()->notNull(),
            'receiverId' => $this->integer()->notNull(),
            'title' => $this->string()->notNull(),
            'text' => $this->text()->notNull(),
            'isReceived' => $this->tinyInteger()->notNull()->unsigned()->defaultValue(0),

            'isDeleted' => $this->tinyInteger()->notNull()->unsigned()->defaultValue(0),
            'createdAt' => $this->integer()->notNull(),
            'updatedAt' => $this->integer()->notNull(),
        ], $tableOptions);

        $this->addForeignKey('fk_message_sender', 'message', 'senderId', 'user', 'id');
        $this->addForeignKey('fk_message_receiver', 'message', 'receiverId', 'user', 'id');
    }

    public function down()
    {
        $this->dropForeignKey('fk_message_receiver', 'message');
        $this->dropForeignKey('fk_message_sender', 'message');

        $this->dropTable('{{%message}}');
        $this->dropTable('{{%user}}');
    }
}
