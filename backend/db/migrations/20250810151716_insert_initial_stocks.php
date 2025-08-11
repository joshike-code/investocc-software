<?php

use Phinx\Migration\AbstractMigration;

class InsertInitialStocks extends AbstractMigration
{
    public function up()
    {
        $stocks = require __DIR__ . '/../../data/stocks_data.php';
        $this->table('stocks')->insert($stocks)->saveData();
    }

    public function down()
    {
        $tradeNames = array_column(require __DIR__ . '/../../data/stocks_data.php', 'trade_name');
        $placeholders = implode(',', array_fill(0, count($tradeNames), '?'));
        
        // Prepare delete query
        $sql = "DELETE FROM stocks WHERE trade_name IN ($placeholders)";
        $this->execute($this->getAdapter()->quoteTableName($sql), $tradeNames);
    }
}