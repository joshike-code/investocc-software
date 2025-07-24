<?php

use Phinx\Migration\AbstractMigration;

class AddDefaultSuperadminUser extends AbstractMigration
{
    public function up()
    {
        $hashedPassword = password_hash('1234', PASSWORD_DEFAULT);

        $this->execute("
            INSERT INTO users (
                role, permissions, avatar, fname, lname, email, phone, country,
                balance, ref_balance, ref_code, referred_by, password, otp_2fa, date_registered
            ) VALUES (
                'superadmin', NULL, 'bundle/account/avatars/err.png', 'super', 'admin', 'owner@investocc.com',
                '', 'NG', 0, 0, '', NULL, '{$hashedPassword}', 'no', CURRENT_TIMESTAMP
            )
        ");
    }

    public function down()
    {
        // Optional: remove the user during rollback
        $this->execute("DELETE FROM users WHERE email = 'owner@investocc.com'");
    }
}