<?php

use Phinx\Migration\AbstractMigration;

class AddBasePlatformKeys extends AbstractMigration
{
    public function up()
    {
        $data = [
            ['key' => 'max_deposit', 'value' => '500000'],
            ['key' => 'min_deposit', 'value' => '1000'],
            ['key' => 'min_withdrawal', 'value' => '1000'],
            ['key' => 'referral_percentage', 'value' => '5'],
            ['key' => 'stock_commission', 'value' => '10'],
            ['key' => 'safehavenpay_percent', 'value' => '0.5'],
        ];

        foreach ($data as $entry) {
            $key = $entry['key'];
            $value = addslashes($entry['value']);

            $exists = $this->fetchRow("SELECT * FROM platform WHERE `key` = '$key'");

            if (!$exists) {
                $this->execute("INSERT INTO platform (`key`, `value`) VALUES ('$key', '$value')");
            }
        }
    }

    public function down($key)
    {
        $this->execute("DELETE FROM platform WHERE `key` = '$key'");
    }
}