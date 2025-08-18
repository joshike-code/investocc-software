<?php

use Phinx\Migration\AbstractMigration;

class CreatePlansTable extends AbstractMigration
{
    public function change()
    {
        $this->table('plans')
            ->addColumn('days', 'integer', ['limit' => 15])
            ->addColumn('rate', 'decimal', ['precision' => 10, 'scale' => 2])
            ->addColumn('date', 'datetime', ['precision' => 6])
            ->create();
    }
}