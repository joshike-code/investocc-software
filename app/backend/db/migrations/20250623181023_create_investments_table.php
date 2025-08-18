<?php

use Phinx\Migration\AbstractMigration;

class CreateInvestmentsTable extends AbstractMigration
{
    public function change()
    {
        $this->table('investments')
            ->addColumn('user_id', 'string', ['limit' => 100])
            ->addColumn('order_ref', 'string', ['limit' => 100])
            ->addColumn('amount', 'decimal', ['precision' => 10, 'scale' => 2])
            ->addColumn('days', 'integer', ['limit' => 15])
            ->addColumn('rate', 'decimal', ['precision' => 10, 'scale' => 2])
            ->addColumn('duration', 'enum', ['values' => ['daily', 'weekly', 'monthly', 'yearly'], 'default' => 'yearly'])
            ->addColumn('roi', 'decimal', ['precision' => 10, 'scale' => 2])
            ->addColumn('plan_id', 'string', ['limit' => 100])
            ->addColumn('status', 'enum', ['values' => ['pending', 'complete'], 'default' => 'pending'])
            ->addColumn('date', 'datetime', ['precision' => 6])
            ->create();
    }
}