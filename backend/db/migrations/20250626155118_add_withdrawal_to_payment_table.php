<?php

use Phinx\Migration\AbstractMigration;

class AddWithdrawalToPaymentTable extends AbstractMigration
{
    public function change()
    {
        $this->table('payments')
            ->changeColumn('method', 'enum', [
                'values' => ['flutterwave', 'crypto', 'referral', 'stockorder', 'investment', 'paystack', 'refund', 'bank', 'opay', 'safehaven', 'withdrawal']
            ])
            ->update();
    }
}