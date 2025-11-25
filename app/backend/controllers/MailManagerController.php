<?php

use Core\SanitizationService;

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../services/MailManagerService.php';
require_once __DIR__ . '/../core/response.php';
require_once __DIR__ . '/../core/SanitizationService.php';
require_once __DIR__ . '/../middleware/Validator.php';

class MailManagerController {

    public static function getMailSettings() {
        $settings = MailManagerService::getMailSettings();
        Response::success($settings);
    }

    public static function updateMailSettings()
    {
        $rawInput = json_decode(file_get_contents("php://input"), true);
        $input = SanitizationService::sanitize($rawInput);
        
        // Validate Input
        $rules = [
            'host' => 'required|string',
            'username' => 'required|string',
            'from' => 'required|string',
            'password' => 'required|string',
            'auth' => 'required|string',
            'security' => 'required|string',
            'port' => 'required|float',
        ];
        $input_errors = Validator::validate($input, $rules);
        if(!empty($input_errors)) {
            Response::error(['validation_errors' => $input_errors], 422);
        }

        MailManagerService::updateMailSettings($input);
    }
}



?>