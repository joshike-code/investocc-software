<?php

use Phinx\Migration\AbstractMigration;
use Phinx\Util\Literal;

class CreatePaymentWalletsTable extends AbstractMigration
{
    public function change()
    {
        $this->table('payment_wallets')
            ->addColumn('coin', 'string', ['limit' => 50])
            ->addColumn('network', 'string', ['limit' => 50])
            ->addColumn('address', 'string', ['limit' => 255])
            ->addColumn('date', 'timestamp', [
                'default' => Literal::from('CURRENT_TIMESTAMP')
            ])
            ->create();
    }
}
