<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../core/response.php';
require_once __DIR__ . '/../core/update_env.php';

class InstallService
{
    private static $connection = null;

    public static function createProfile(array $input) {

        $hostlink = '/backend/';
        $dbHost = $input['db_host'];
        $dbName = $input['db_name'];
        $dbUser = $input['db_user'];
        $dbPass = $input['db_pass'];
        $dbPort = '';
        $platformName = $input['platform_name'];
        $platformURl = $input['platform_url'];
        $address = $input['address'];
        $supportMail = $input['support_mail'];
        $jwtSecret = 'ujbdi93ndufis30dbksdrtdcalg94';

        self::checkDBCredentials($dbHost, $dbUser, $dbPass, $dbName,);

        // Write backend .env
        $envContent = "# System Configuration\nHOST_LINK=$hostlink\n\n# Database Configuration\nDB_HOST=$dbHost\nDB_PORT=$dbPort\nDB_NAME=$dbName\nDB_USERNAME=$dbUser\nDB_PASSWORD=$dbPass\n\n# Platform Configuration\nPLATFORM_NAME=$platformName\nPLATFORM_URL=$platformURl\nPLATFORM_ADDRESS=$address\nPLATFORM_SUPPORT_MAIL=$supportMail\n\n# JWT Configuration\nJWT_SECRET_KEY=$jwtSecret\n\n# Degiant Configuration\nDEGIANT_PASSKEY=\n\n# Exchange Rates API Configuration\nEXCHANGE_RATES_API_KEY=\n\n# PHPMailer Configuration\nPHPMAILER_HOST=\nPHPMAILER_USERNAME=\nPHPMAILER_FROM=\nPHPMAILER_PASSWORD=\nPHPMAILER_AUTH=true\nPHPMAILER_SECURITY=TLS\nPHPMAILER_PORT=587\n\n";
        file_put_contents(__DIR__ . '/../.env', $envContent);

        // Install in index
        $indexPath = realpath(__DIR__ . '/../../index.html');
        if (file_exists($indexPath)) {
            $indexContent = file_get_contents($indexPath);
            $indexContent = str_replace('Investocc', $platformName, $indexContent);
            file_put_contents($indexPath, $indexContent);
        }

        // Create new manifest
        $manifest = [
            "name" => strtoupper($platformName),
            "short_name" => $platformName,
            "start_url" => "/",
            "theme_color" => "#FFFFFF",
            "description" => "$platformName: Crypto and stocks investment",
            "display" => "fullscreen",
            "icons" => [
                [
                    "src" => "assets/images/logo/logo-icon.png",
                    "sizes" => "608x608",
                    "type" => "image/png"
                ],
                [
                    "src" => "assets/images/logo/app-icon.png",
                    "sizes" => "200x200",
                    "type" => "image/png"
                ]
            ]
        ];
        $manifestPath = realpath(__DIR__ . '/../../') . '/manifest.json';
        file_put_contents($manifestPath, json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        // Run migrations
        $result = include __DIR__ . '/../run-migrations.php';
        if (is_array($result)) {
            if($result['status'] === 'error') {
                Response::error("Migration failed.", 400);
                $e = $result['error'];
                error_log("Update migrations error: {$e}");
            }
        } else {
            Response::error("Unknown migration error.", 400);
        }

        Response::success("Installation credentials saved successfully.");
    }

    public static function checkDBCredentials($host, $username, $password, $db_name) {

        try {
            self::$connection = new mysqli($host, $username, $password, $db_name);
        } catch (Exception $e) {
            Response::error('Database connection failed', 400);
        }

        return true;
    }

    public static function updatePasskey(array $input) {

        $passkey = $input['passkey'];
        $envFile = __DIR__ . '/../.env';
        $envKey = 'DEGIANT_PASSKEY';

        try {
            updateEnvValue($envFile, $envKey, $passkey);
            Response::success("Installation credentials submitted.");
        } catch (Exception $e) {
            error_log("Passkey update Error: {$e->getMessage()}");
            Response::error("An error occured", 500);
        }
    }
}



?>