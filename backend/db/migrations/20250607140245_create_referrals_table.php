<?php

use Phinx\Migration\AbstractMigration;
use Phinx\Util\Literal;

class CreateReferralsTable extends AbstractMigration
{
    public function change()
    {
        $this->table('referrals', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'string', ['limit' => 50, 'null' => false])
            ->addColumn('referrer_id', 'string', ['limit' => 50])
            ->addColumn('referred_user_id', 'string', ['limit' => 50])
            ->addColumn('amount', 'decimal', ['precision' => 10, 'scale' => 2])
            ->addColumn('percentage', 'decimal', ['precision' => 5, 'scale' => 2])
            ->addColumn('date', 'timestamp', ['default' => Literal::from('CURRENT_TIMESTAMP')])
            ->addColumn('payment_id', 'string', ['limit' => 50, 'null' => true])
            ->addColumn('description', 'text', ['null' => true])
            ->create();
    }
}
