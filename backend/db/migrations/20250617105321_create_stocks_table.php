<?php

use Phinx\Migration\AbstractMigration;

class CreateStocksTable extends AbstractMigration
{
    public function change()
    {
        $this->table('stocks')
            ->addColumn('name', 'string', ['limit' => 200])
            ->addColumn('trade_name', 'string', ['limit' => 100])
            ->addColumn('price', 'decimal', ['precision' => 10, 'scale' => 2, 'default' => 0])
            ->addColumn('today_percent', 'decimal', ['precision' => 10, 'scale' => 2, 'default' => 0])
            ->addColumn('today_p_l', 'decimal', ['precision' => 10, 'scale' => 2, 'default' => 0])
            ->addColumn('one_week_percent', 'decimal', ['precision' => 10, 'scale' => 2, 'default' => 0])
            ->addColumn('one_week_p_l', 'decimal', ['precision' => 10, 'scale' => 2, 'default' => 0])
            ->addColumn('one_month_percent', 'decimal', ['precision' => 10, 'scale' => 2, 'default' => 0])
            ->addColumn('one_month_p_l', 'decimal', ['precision' => 10, 'scale' => 2, 'default' => 0])
            ->addColumn('three_month_percent', 'decimal', ['precision' => 10, 'scale' => 2, 'default' => 0])
            ->addColumn('three_month_p_l', 'decimal', ['precision' => 10, 'scale' => 2, 'default' => 0])
            ->addColumn('one_year_percent', 'decimal', ['precision' => 10, 'scale' => 2, 'default' => 0])
            ->addColumn('one_year_p_l', 'decimal', ['precision' => 10, 'scale' => 2, 'default' => 0])
            ->addColumn('five_year_percent', 'decimal', ['precision' => 10, 'scale' => 2, 'default' => 0])
            ->addColumn('five_year_p_l', 'decimal', ['precision' => 10, 'scale' => 2, 'default' => 0])
            ->addColumn('open', 'decimal', ['precision' => 10, 'scale' => 2, 'default' => 0])
            ->addColumn('high', 'decimal', ['precision' => 10, 'scale' => 2, 'default' => 0])
            ->addColumn('low', 'decimal', ['precision' => 10, 'scale' => 2, 'default' => 0])
            ->addColumn('month_high', 'decimal', ['precision' => 10, 'scale' => 2, 'default' => 0])
            ->addColumn('month_low', 'decimal', ['precision' => 10, 'scale' => 2, 'default' => 0])
            ->addColumn('volume', 'decimal', ['precision' => 10, 'scale' => 2, 'default' => 0])
            ->addColumn('market_cap', 'decimal', ['precision' => 10, 'scale' => 2, 'default' => 0])
            ->addColumn('description', 'string', ['limit' => 400])
            ->addColumn('about', 'string', ['limit' => 400])
            ->addColumn('categories', 'json', ['null' => true])
            ->addColumn('opinion', 'string', ['limit' => 200])
            ->addColumn('ceo', 'string', ['limit' => 100])
            ->addColumn('sector', 'string', ['limit' => 200])
            ->addColumn('last_update', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'precision' => 6])
            ->addIndex(['trade_name'], ['unique' => true])
            ->create();
    }
}