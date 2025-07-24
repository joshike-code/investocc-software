<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../core/response.php';
require_once __DIR__ . '/../core/jwt_utils.php';

class PlatformService {
    private static $cache = [];

    public static function getPlatformSettings()
    {
        $keys = require __DIR__ . '/../config/keys.php';

        $settings = self::fetchAllSettings();

        // my defaults
        $settings['passkey'] = $keys['degiant']['passkey'];

        Response::success($settings);
    }

    // Fetch single setting
    public static function getSetting($key, $default = null)
    {
        // Use cached if available
        if (isset(self::$cache[$key])) {
            return self::$cache[$key];
        }

        $conn = Database::getConnection();
        $stmt = $conn->prepare("SELECT `value` FROM platform WHERE `key` = ?");
        $stmt->bind_param("s", $key);
        $stmt->execute();
        $result = $stmt->get_result();

        $value = $result->num_rows ? $result->fetch_assoc()['value'] : $default;
        self::$cache[$key] = $value; // Cache it

        return $value;
    }

    public static function fetchAllSettings()
    {
        if (!empty(self::$cache)) return self::$cache;

        $conn = Database::getConnection();
        $sql = "SELECT `key`, `value` FROM platform";
        $result = $conn->query($sql);

        $settings = [];

        while ($row = $result->fetch_assoc()) {
            $settings[$row['key']] = $row['value'];
        }

        self::$cache = $settings;
        return $settings;
    }

    // DYNAMIC to allow me add in db and still update via frontend
    public static function updateSettings($settings)
    {
        $conn = Database::getConnection();
        $stmt = $conn->prepare("UPDATE platform SET `value` = ? WHERE `key` = ?");

        foreach ($settings as $key => $value) {
            $stmt->bind_param("ss", $value, $key);
            $stmt->execute();

            // Update cache
            self::$cache[$key] = $value;
        }

        $stmt->close();

        $platformData = self::fetchAllSettings();
        Response::success($platformData);
    }
}



?>