<?php

use yii\db\Migration;

/**
 * Class m180930_172757_glossary
 */
class m180930_172757_glossary extends Migration
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

        $this->createTable("{{%glossary}}", [
            'id' => $this->primaryKey(),
            'headword' => $this->string(),
            'description' => $this->text(),
        ], $tableOptions);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%glossary}}');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180930_172757_glossary cannot be reverted.\n";

        return false;
    }
    */
}
