<?php

use Phinx\Migration\AbstractMigration;
use Phinx\Util\Literal;

class CreateUsersTable extends AbstractMigration
{
    public function change()
    {
        $this->table('users')
            ->addColumn('role', 'enum', [
                'values' => ['user', 'admin', 'superadmin'],
                'default' => 'user'
            ])
            ->addColumn('permissions', 'json', ['null' => true])
            ->addColumn('avatar', 'string', [
                'limit' => 50,
                'default' => 'bundle/account/avatars/err.png'
            ])
            ->addColumn('fname', 'string', ['limit' => 100])
            ->addColumn('lname', 'string', ['limit' => 100])
            ->addColumn('email', 'string', ['limit' => 200])
            ->addColumn('phone', 'string', ['limit' => 25])
            ->addColumn('country', 'string', [
                'limit' => 5,
                'default' => null
            ])
            ->addColumn('balance', 'decimal', [
                'precision' => 10,
                'scale' => 2,
                'default' => 0
            ])
            ->addColumn('ref_balance', 'decimal', [
                'precision' => 10,
                'scale' => 2,
                'default' => 0
            ])
            ->addColumn('ref_code', 'string', ['limit' => 10])
            ->addColumn('referred_by', 'string', [
                'limit' => 10,
                'null' => true
            ])
            ->addColumn('password', 'string', ['limit' => 255])
            ->addColumn('otp_2fa', 'string', [
                'limit' => 10,
                'default' => 'no'
            ])
            ->addColumn('date_registered', 'timestamp', [
                'default' => Literal::from('CURRENT_TIMESTAMP')
            ])
            ->addIndex(['email'], ['unique' => true])
            ->create();
    }
}
