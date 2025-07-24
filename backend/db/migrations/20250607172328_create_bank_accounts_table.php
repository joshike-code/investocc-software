<?php

use Phinx\Migration\AbstractMigration;
use Phinx\Util\Literal;

class CreateBankAccountsTable extends AbstractMigration
{
    public function change()
    {
        $this->table('bank_accounts')
            ->addColumn('account_name', 'string', ['limit' => 50])
            ->addColumn('bank_name', 'string', ['limit' => 50])
            ->addColumn('account_number', 'string', ['limit' => 255])
            ->addColumn('date', 'timestamp', ['default' => Literal::from('CURRENT_TIMESTAMP')])
            ->create();
    }
}
