<?php

use yii\db\Migration;

/**
 * Class m240626_040412_create_table_categories
 */
class m240626_040412_create_table_categories extends Migration
{
    public function up()
    {
        $this->createTable('categories', [
            'id' => $this->primaryKey(),
            'name' => $this->string(100)->comment('Название'),
        ]);
    }

    public function down()
    {
        $this->dropTable('categories');
    }
}
