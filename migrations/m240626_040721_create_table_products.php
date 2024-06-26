<?php

use yii\db\Migration;

/**
 * Class m240626_040721_create_table_products
 */
class m240626_040721_create_table_products extends Migration
{
    public function up()
    {
        $this->createTable('products', [
            'id' => $this->primaryKey(),
            'cat_id' => $this->integer()->comment('Категория'),
            'name' => $this->string(100)->comment('Название'),
        ]);
    }

    public function down()
    {
        $this->dropTable('products');
    }
}
