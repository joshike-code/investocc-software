<?php
require_once __DIR__ . '/../core/response.php';

class Database {
    private static $connection = null;

    public static function getConnection()
    {
        if (self::$connection === null) {
            $keys = require __DIR__ . '/../config/keys.php';

            $host = $keys['db']['host'];
            $port =  !empty($keys['db']['port']) ? (int)$keys['db']['port'] : 3306;
            $db_name = $keys['db']['name'];
            $username = $keys['db']['username'];
            $password = $keys['db']['password'];

            try {
                self::$connection = new mysqli($host, $username, $password, $db_name, $port);
            } catch (Exception $e) {
                error_log("DB Error: {$e->getMessage()}");
                Response::error('Database connection failed', 500);
            }
        }

        return self::$connection;
    }
}