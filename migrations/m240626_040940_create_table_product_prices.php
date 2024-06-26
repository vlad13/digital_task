<?php

use yii\db\Migration;

/**
 * Class m240626_040940_create_table_product_prices
 */
class m240626_040940_create_table_product_prices extends Migration
{
    public function up()
    {
        $this->createTable('product_prices', [
            'id' => $this->primaryKey(),
            'product_id' => $this->integer()->comment('Продукт'),
            'date' => $this->date()->comment('Дата'),
            'price' => $this->float(2)->comment('Цена'),
        ]);
    }

    public function down()
    {
        $this->dropTable('product_prices');
    }
}
