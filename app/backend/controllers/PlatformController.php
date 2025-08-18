<?php

use Core\SanitizationService;

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../services/PlatformService.php';
require_once __DIR__ . '/../core/response.php';
require_once __DIR__ . '/../core/SanitizationService.php';
require_once __DIR__ . '/../middleware/Validator.php';

class PlatformController {

    public static function getSettings() {
        PlatformService::getPlatformSettings();
    }

    public static function getPlatformData() {
        $platformData = PlatformService::fetchAllSettings();
        Response::success($platformData);
    }

    public static function updateSettings()
    {
        $rawInput = json_decode(file_get_contents("php://input"), true);
        $input = SanitizationService::sanitize($rawInput);

        if (!is_array($input) || empty($input)) {
            Response::error('No platform settings provided', 422);
        }

        // Get allowed keys from DB
        $allowedKeys = array_keys(PlatformService::fetchAllSettings());

        // Build rules only for known keys
        $rules = [];
        foreach ($allowedKeys as $key) {
            if (isset($input[$key])) {
                $rules[$key] = 'required|string';
            }
        }

        if (empty($rules)) {
            Response::error('No valid platform keys found to update', 422);
        }

        $validator = new Validator();
        $input_errors = $validator::validate($input, $rules);

        if (!empty($input_errors)) {
            Response::error(['validation_errors' => $input_errors], 422);
        }

        PlatformService::updateSettings($input);
    }
}



?>