<?php  
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../core/response.php';

class ApiKeysService {

    private static string $envFile = __DIR__ . '/../.env';

    // Read API keys from .env - returns true/false for keys that have values or not
    public static function getApiKeys($keyNames = ['FINNHUB_API_KEY'])
    {
        if (!file_exists(self::$envFile)) {
            Response::error(".env file not found", 500);
        }

        $lines = file(self::$envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $settings = [];

        // Initialize all requested keys as false (not found/empty)
        foreach ($keyNames as $keyName) {
            $settings[$keyName] = false;
        }

        foreach ($lines as $line) {
            $line = trim($line);
            
            // Skip comments and empty lines
            if (empty($line) || $line[0] === '#') {
                continue;
            }

            if (strpos($line, '=') !== false) {
                [$key, $value] = explode('=', $line, 2);
                
                // Check if this key is in our list of keys to fetch
                if (in_array($key, $keyNames)) {
                    // Return true if key has a non-empty value, false if empty
                    $settings[$key] = !empty(trim($value));
                }
            }
        }

        return $settings;
    }

    // Update API keys in .env - input key names should match ENV key names exactly
    public static function updateApiKeys($input)
    {
        if (!file_exists(self::$envFile)) {
            Response::error(".env file not found", 500);
        }

        $env = file_get_contents(self::$envFile);

        // Update each key in the input - input names match ENV names exactly
        foreach ($input as $envKey => $value) {
            // Replace or add the key
            if (preg_match("/^$envKey=.*$/m", $env)) {
                $env = preg_replace("/^$envKey=.*$/m", "$envKey=$value", $env);
            } else {
                $env .= "\n$envKey=$value";
            }
        }

        if (file_put_contents(self::$envFile, $env) === false) {
            Response::error("Failed to write to .env file", 500);
        }

        // Return updated settings - get the keys that were just updated
        $updatedKeys = array_keys($input);
        $updatedSettings = self::getApiKeys($updatedKeys);
        Response::success($updatedSettings);
    }
}
?>