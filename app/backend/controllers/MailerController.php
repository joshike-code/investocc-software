<?php
session_start();

use Core\SanitizationService;

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../services/MailService.php';
require_once __DIR__ . '/../core/response.php';
require_once __DIR__ . '/../core/SanitizationService.php';
require_once __DIR__ . '/../middleware/Validator.php';

class MailerController {

    public static function mailAdmin() {
        $rawInput = json_decode(file_get_contents("php://input"), true);
        $input = SanitizationService::sanitize($rawInput);
        
        // Validate Input
        $rules = [
            'name'  => 'required|string',
            'email'  => 'required|email',
            'message'  => 'required|string',
        ];
        $input_errors = Validator::validate($input, $rules);
        if(!empty($input_errors)) {
            Response::error(['validation_errors' => $input_errors], 422);
        }

        $name = $input['name'] ?? '';
        $email = $input['email'] ?? '';
        $message = $input['message'] ?? '';

        MailService::sendContactFormToAdmin($name, $email, $message);
    }
}

