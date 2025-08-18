<?php

use Phinx\Migration\AbstractMigration;

class AddDateCompletedToInvestmentsTable extends AbstractMigration
{
    public function change()
    {
        $this->table('investments')
            ->addColumn('date_completed', 'datetime', ['precision' => 6])
            ->update();
    }
}