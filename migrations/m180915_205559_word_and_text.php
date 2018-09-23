<?php

use yii\db\Migration;

/**
 * Class m180915_205559_word_and_text
 */
class m180915_205559_word_and_text extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'ENGINE=InnoDB';
        }

        $this->createTable("{{%word}}", [
            'id' => $this->primaryKey(),
            'headword' => $this->string(),
            'lemma' => $this->text(),
            'score' => $this->decimal(9, 6),
            'frequency' => $this->decimal(9, 6),
            'dispersion' => $this->decimal(9, 6),
        ], $tableOptions);

        $this->createTable("{{%text}}", [
            'id' => $this->primaryKey(),
            'name' => $this->string(),
            'file_path' => $this->string(),
            'file_time' => $this->integer()->unsigned(),
            'status' => $this->boolean(),
            'length' => $this->bigInteger()->unsigned(),
            'count_words' => $this->integer(),
        ], $tableOptions);

        $this->createTable("{{%text_word}}", [
            'text_id' => $this->integer(),
            'word_id' => $this->integer(),
            'count_words' => $this->integer(),
            'context' => $this->text(),
        ], $tableOptions);

        $this->createIndex('idx-text_word', '{{%text_word}}', ['text_id',  'word_id']);
        $this->addPrimaryKey('pk-text_word', '{{%text_word}}', ['text_id',  'word_id']);

        $this->addForeignKey('fk-text_word-text', '{{%text_word}}', 'text_id', '{{%text}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk-text_word-word', '{{%text_word}}', 'word_id', '{{%word}}', 'id', 'CASCADE', 'CASCADE');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%text_word}}');
        $this->dropTable('{{%text}}');
        $this->dropTable('{{%word}}');
    }
}
