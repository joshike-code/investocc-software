<?php

use Phinx\Migration\AbstractMigration;

class CreateUserOtpTable extends AbstractMigration
{
    public function change()
    {
        $this->table('user_otp', ['id' => false, 'primary_key' => ['email']])
            ->addColumn('email', 'string', ['limit' => 255, 'null' => false])
            ->addColumn('otp', 'string', ['limit' => 6])
            ->addColumn('expires_at', 'datetime')
            ->create();
    }
}
