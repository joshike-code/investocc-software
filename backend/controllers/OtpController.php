<?php
session_start();

use Core\SanitizationService;

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../services/OtpService.php';
require_once __DIR__ . '/../core/response.php';
require_once __DIR__ . '/../core/SanitizationService.php';
require_once __DIR__ . '/../middleware/Validator.php';

class OtpController {

    public static function validate() {
        $rawInput = json_decode(file_get_contents("php://input"), true);
        $input = SanitizationService::sanitize($rawInput);
        
        // Validate Input
        $rules = [
            'email'  => 'required|email',
            'otp'  => 'required|string'
        ];
        $input_errors = Validator::validate($input, $rules);
        if(!empty($input_errors)) {
            Response::error(['validation_errors' => $input_errors], 422);
        }

        $email = $input['email'] ?? '';
        $otp = $input['otp'] ?? '';

        if(OtpService::validateOtp($email, $otp)) {
            $_SESSION['verified_email'] = $email;
            Response::success(['message' => 'OTP verified']);
        } else {
            Response::error('Invalid or expired OTP', 400);
        }
    }
}

