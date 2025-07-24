<?php

use Phinx\Migration\AbstractMigration;

class CreatePlatformTable extends AbstractMigration
{
    public function change()
    {
        $this->table('platform', ['id' => false, 'primary_key' => ['key']])
            ->addColumn('key', 'string', ['limit' => 50, 'null' => false])
            ->addColumn('value', 'text')
            ->create();
    }
}
