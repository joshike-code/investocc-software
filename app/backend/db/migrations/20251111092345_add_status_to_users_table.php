<?php

use Phinx\Migration\AbstractMigration;

class AddStatusToUsersTable extends AbstractMigration
{
    public function change()
    {
        $this->table('users')
            ->addColumn('status', 'enum', [
                'values' => ['active', 'suspended'],
                'default' => 'active'
            ])
            ->update();
    }
}