<?php

use Core\SanitizationService;

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


require_once __DIR__ . '/../services/UpdateService.php';
require_once __DIR__ . '/../core/response.php';
require_once __DIR__ . '/../core/SanitizationService.php';
require_once __DIR__ . '/../middleware/Validator.php';

class UpdateController {

    public static function getUpdateStatus() {
       $status = UpdateService::getUpdateStatus();
       Response::success($status);
    }

    public static function getLatestUpdate() {
        $result = UpdateService::getLatestUpdate();
        Response::success($result);
    }

    public static function getAllChangelogs() {
        $result = UpdateService::getAllChangelogs();
        Response::success($result);
    }

    public static function applyUpdate() {
        $rawInput = json_decode(file_get_contents("php://input"), true);
        $input = SanitizationService::sanitize($rawInput);
        
        // Validate Input
        $rules = [
            'version' => 'required|string',
        ];
        $input_errors = Validator::validate($input, $rules);
        if(!empty($input_errors)) {
            Response::error(['validation_errors' => $input_errors], 422);
        }

        UpdateService::applyUpdate($input);
    }
}

