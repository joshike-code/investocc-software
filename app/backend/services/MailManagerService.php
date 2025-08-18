<?php  
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../core/response.php';

class MailManagerService {

    private static string $envFile = __DIR__ . '/../.env';

    // Read mail settings from .env
    public static function getMailSettings()
    {
        if (!file_exists(self::$envFile)) {
            Response::error(".env file not found", 500);
        }

        $lines = file(self::$envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $settings = [];

        foreach ($lines as $line) {
            if (strpos(trim($line), 'PHPMAILER_') === 0) {
                [$key, $value] = explode('=', $line, 2);
                $key = strtolower(str_replace('PHPMAILER_', '', $key));
                $settings[$key] = $value;
            }
        }

        return $settings;
    }

    // Update mail settings in .env
    public static function updateMailSettings($input)
    {
        if (!file_exists(self::$envFile)) {
            Response::error(".env file not found", 500);
        }

        $env = file_get_contents(self::$envFile);

        $keys = [
            'PHPMAILER_HOST',
            'PHPMAILER_USERNAME',
            'PHPMAILER_FROM',
            'PHPMAILER_PASSWORD',
            'PHPMAILER_AUTH',
            'PHPMAILER_SECURITY',
            'PHPMAILER_PORT',
        ];

        foreach ($keys as $key) {
            if (isset($input[strtolower(str_replace('PHPMAILER_', '', $key))])) {
                $value = $input[strtolower(str_replace('PHPMAILER_', '', $key))];
                // Replace or add the key
                if (preg_match("/^$key=.*$/m", $env)) {
                    $env = preg_replace("/^$key=.*$/m", "$key=$value", $env);
                } else {
                    $env .= "\n$key=$value";
                }
            }
        }

        if (file_put_contents(self::$envFile, $env) === false) {
            Response::error("Failed to write to .env file", 500);
        }

        // Response::success("Mailer settings updated successfully");
        $mailData = self::getMailSettings();
        Response::success($mailData);
    }
}
?>