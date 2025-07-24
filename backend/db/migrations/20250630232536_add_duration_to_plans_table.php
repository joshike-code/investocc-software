<?php

use Phinx\Migration\AbstractMigration;

class AddDurationToPlansTable extends AbstractMigration
{
    public function change()
    {
        $this->table('plans')
             ->addColumn('duration', 'enum', ['values' => ['daily', 'weekly', 'monthly', 'yearly'], 'default' => 'yearly'])
            ->update();
    }
}