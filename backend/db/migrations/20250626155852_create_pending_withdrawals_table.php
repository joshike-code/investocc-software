<?php

use Phinx\Migration\AbstractMigration;

class CreatePendingWithdrawalsTable extends AbstractMigration
{
    public function change()
    {
        $this->table('pending_withdrawals')
            ->addColumn('user_id', 'integer', ['limit' => 100])
            ->addColumn('payment_ref', 'string', ['limit' => 255, 'null' => true])
            ->addColumn('amount', 'decimal', ['precision' => 10, 'scale' => 2])
            ->addColumn('coin', 'string', ['limit' => 50, 'null' => true])
            ->addColumn('network', 'string', ['limit' => 255, 'null' => true])
            ->addColumn('address', 'string', ['limit' => 255, 'null' => true])
            ->addColumn('bank_name', 'string', ['limit' => 255, 'null' => true])
            ->addColumn('account_name', 'string', ['limit' => 255, 'null' => true])
            ->addColumn('account_number', 'string', ['limit' => 255, 'null' => true])
            ->addColumn('date', 'datetime', ['precision' => 6])
            ->addIndex(['user_id'], ['unique' => true])
            ->create();
    }
}