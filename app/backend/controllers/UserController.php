<?php

use Core\SanitizationService;

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


require_once __DIR__ . '/../services/UserService.php';
require_once __DIR__ . '/../core/response.php';
require_once __DIR__ . '/../core/SanitizationService.php';
require_once __DIR__ . '/../middleware/Validator.php';

class UserController {

    public static function getUser($user_id) {
        $userData = UserService::getUserById($user_id);
        if($userData['status'] === 'suspended') {
            Response::error('User suspended', 400);
        }
        Response::success($userData);
    }

    public static function getUserToAdmin($user_id) {
        $userData = UserService::getUserById($user_id);
        Response::success($userData);
    }

    public static function getUsers() {
        UserService::getAllUsers();
    }

    public static function deleteUser($user_id) {
        UserService::deleteUser($user_id);
    }

    public static function getAdmin($user_id) {
        $userData = UserService::getUserById($user_id);
        Response::success($userData);
    }

    public static function getAdmins() {
        UserService::getAllUsers('admin');
    }

    public static function deleteAdmin($user_id) {
        UserService::deleteUser($user_id, 'admin');
    }

    public static function searchUsersByEmail($email) {
        UserService::searchUsersByEmail($email);
    }

    public static function getReferralCount($user_id) {
        $count = UserService::getUserReferralCount($user_id);
        Response::success(['referrals' => $count['count']]);
    }

    public static function checkEmail() {
        $rawInput = json_decode(file_get_contents("php://input"), true);
        $input = SanitizationService::sanitize($rawInput);
        
        // Validate Input
        $rules = [
            'email'  => 'required|email'
        ];
        $input_errors = Validator::validate($input, $rules);
        if(!empty($input_errors)) {
            Response::error(['validation_errors' => $input_errors], 422);
        }

        UserService::checkEmail($input);
    }

    public static function createNewPassword($action) {
        $rawInput = json_decode(file_get_contents("php://input"), true);
        $input = SanitizationService::sanitize($rawInput);
        
        // Validate Input
        $rules = [
            'otp'  => 'required|string',
            'password'  => 'required|password',
            'email'  => 'required|email'
        ];
        $input_errors = Validator::validate($input, $rules);
        if(!empty($input_errors)) {
            Response::error(['validation_errors' => $input_errors], 422);
        }

        UserService::createNewPassword($input, $action);
    }

    public static function updateProfile($user_id) {
        $rawInput = json_decode(file_get_contents("php://input"), true);
        $input = SanitizationService::sanitize($rawInput);
        
        // Validate Input
        $rules = [
            'fname'  => 'required|string',
            'lname'  => 'required|string',
            'email'  => 'required|email'
        ];
        $input_errors = Validator::validate($input, $rules);
        if(!empty($input_errors)) {
            Response::error(['validation_errors' => $input_errors], 422);
        }

        UserService::updateUserProfile($user_id, $input);
    }

    public static function updateBalance($type) {
        $rawInput = json_decode(file_get_contents("php://input"), true);
        $input = SanitizationService::sanitize($rawInput);
        
        // Validate Input
        $rules = [
            'amount'  => 'required|float',
            'user_id'  => 'required|integer',
        ];
        $input_errors = Validator::validate($input, $rules);
        if(!empty($input_errors)) {
            Response::error(['validation_errors' => $input_errors], 422);
        }

        if($type === 'topup') {
            UserService::topUpBalance($input);
        } elseif($type === 'deduct') {
            UserService::deductBalance($input);
        } else {
            Response::error('Method not allowed', 405);
        };
    }

    public static function updateAdmin($user_id) {
        $rawInput = json_decode(file_get_contents("php://input"), true);
        $input = SanitizationService::sanitize($rawInput);
        
        // Validate Input
        $rules = [
            'fname'  => 'required|string',
            'lname'  => 'required|string',
            'email'  => 'required|email',
            'permissions'  => 'required|permission',
        ];
        $input_errors = Validator::validate($input, $rules);
        if(!empty($input_errors)) {
            Response::error(['validation_errors' => $input_errors], 422);
        }

        UserService::updateUserProfile($user_id, $input);
    }

    public static function updateUserStatus($user_id) {
        $rawInput = json_decode(file_get_contents("php://input"), true);
        $input = SanitizationService::sanitize($rawInput);
        
        // Validate Input
        $rules = [
            'status'  => 'required|string'
        ];
        $input_errors = Validator::validate($input, $rules);
        if(!empty($input_errors)) {
            Response::error(['validation_errors' => $input_errors], 422);
        }

        $response = UserService::updateUserStatus($user_id, $input);
        if($response) {
            self::getUserToAdmin($user_id);
        };
    }

    public static function createAdmin() {
        $rawInput = json_decode(file_get_contents("php://input"), true);
        $input = SanitizationService::sanitize($rawInput);
        
        // Validate Input
        $rules = [
            'fname'  => 'required|string',
            'lname'  => 'required|string',
            'email'  => 'required|email',
            'password'  => 'required|password',
            'permissions'  => 'required|permission',
        ];
        $input_errors = Validator::validate($input, $rules);
        if(!empty($input_errors)) {
            Response::error(['validation_errors' => $input_errors], 422);
        }

        UserService::createAdmin($input);
    }
    
    public static function updatePassword($user_id) {
        $rawInput = json_decode(file_get_contents("php://input"), true);
        $input = SanitizationService::sanitize($rawInput);
        
        // Validate Input
        $rules = [
            'oldPassword'  => 'required|string',
            'newPassword'  => 'required|password',
        ];
        $input_errors = Validator::validate($input, $rules);
        if(!empty($input_errors)) {
            Response::error(['validation_errors' => $input_errors], 422);
        }

        $oldPassword = $input['oldPassword'] ?? null;
        $newPassword = $input['newPassword'] ?? null;
        
        UserService::updateUserPassword($user_id, $oldPassword, $newPassword);
    }

    public static function login() {
        $rawInput = json_decode(file_get_contents("php://input"), true);
        $input = SanitizationService::sanitize($rawInput);
        
        // Validate Input
        $rules = [
            'email'  => 'required|email',
            'password'  => 'required|string'
        ];
        $input_errors = Validator::validate($input, $rules);
        if(!empty($input_errors)) {
            Response::error(['validation_errors' => $input_errors], 422);
        }

        $email = $input['email'] ?? '';
        $password = $input['password'] ?? '';
        $type = $input['type'] ?? '';

        UserService::loginUser($email, $password, $type);
    }

    public static function preRegister() {
        $rawInput = json_decode(file_get_contents("php://input"), true);
        $input = SanitizationService::sanitize($rawInput);
        
        // Validate Input
        $rules = [
            'email'  => 'required|email'
        ];
        $input_errors = Validator::validate($input, $rules);
        if(!empty($input_errors)) {
            Response::error(['validation_errors' => $input_errors], 422);
        }

        UserService::preRegisterUser($input);
    }

    public static function register() {
        $rawInput = json_decode(file_get_contents("php://input"), true);
        $input = SanitizationService::sanitize($rawInput);
        
        // Validate Input
        $rules = [
            'email'  => 'required|email',
            'password'  => 'required|password',
            'fname'  => 'required|string',
            'lname'  => 'required|string',
            'otp'  => 'required|string'
        ];
        $input_errors = Validator::validate($input, $rules);
        if(!empty($input_errors)) {
            Response::error(['validation_errors' => $input_errors], 422);
        }

        $email = $input['email'] ?? '';
        $password = $input['password'] ?? '';
        $type = '';

        $registerStatus = UserService::registerUser($input);
        if($registerStatus) {
            UserService::loginUser($email, $password, $type);
        }
    }
}

