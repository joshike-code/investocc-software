<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


use Core\SanitizationService;

require_once __DIR__ . '/../services/WithdrawService.php';
require_once __DIR__ . '/../core/response.php';
require_once __DIR__ . '/../core/SanitizationService.php';
require_once __DIR__ . '/../middleware/Validator.php';

class WithdrawController {

    public static function getPendingWithdrawals() {
       WithdrawService::getPendingWithdrawals();
    }

    public static function getSelectPendingWithdrawals($id) {
       $select_withdrawal = WithdrawService::getPendingWithdrawalById($id);
       Response::success($select_withdrawal);
    }

    public static function updateWithdrawalStatus($id, $action) {
        if($action === 'approve') {
            WithdrawService::approveWithdrawal($id);
        } else {
            WithdrawService::declineWithdrawal($id);
        }
    }

    public static function createCryptoWithdrawal($user_id, $method) {
        $rawInput = json_decode(file_get_contents("php://input"), true);
        $input = SanitizationService::sanitize($rawInput);
        
        // Validate Input
        $rules = [
            'amount' => 'required|float',
            'coin' => 'required|string',
            'network' => 'required|string',
            'address' => 'required|string',
        ];
        $input_errors = Validator::validate($input, $rules);
        if(!empty($input_errors)) {
            Response::error(['validation_errors' => $input_errors], 422);
        }

        WithdrawService::createWithdrawal($user_id, $input, $method);
    }

    public static function createBankWithdrawal($user_id, $method) {
        $rawInput = json_decode(file_get_contents("php://input"), true);
        $input = SanitizationService::sanitize($rawInput);
        
        // Validate Input
        $rules = [
            'amount' => 'required|float',
            'bank_name' => 'required|string',
            'account_name' => 'required|string',
            'account_number' => 'required|string',
        ];
        $input_errors = Validator::validate($input, $rules);
        if(!empty($input_errors)) {
            Response::error(['validation_errors' => $input_errors], 422);
        }

        WithdrawService::createWithdrawal($user_id, $input, $method);
    }
}

