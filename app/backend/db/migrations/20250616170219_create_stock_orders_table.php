<?php

use Phinx\Migration\AbstractMigration;

class CreateStockOrdersTable extends AbstractMigration
{
    public function change()
    {
        $this->table('stock_orders')
            ->addColumn('user_id', 'string', ['limit' => 100])
            ->addColumn('order_ref', 'string', ['limit' => 100])
            ->addColumn('stock', 'string', ['limit' => 100])
            ->addColumn('buy_price', 'decimal', ['precision' => 10, 'scale' => 2])
            ->addColumn('shares', 'decimal', ['precision' => 10, 'scale' => 5])
            ->addColumn('amount', 'decimal', ['precision' => 10, 'scale' => 2])
            ->addColumn('commission', 'decimal', ['precision' => 10, 'scale' => 2])
            ->addColumn('status', 'enum', ['values' => ['bought', 'sold'], 'default' => 'bought'])
            ->addColumn('date', 'datetime', ['precision' => 6])
            ->create();
    }
}