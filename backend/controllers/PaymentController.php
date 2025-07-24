<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


use Core\SanitizationService;

require_once __DIR__ . '/../services/PaymentService.php';
require_once __DIR__ . '/../services/FlutterwaveService.php';
require_once __DIR__ . '/../services/PaystackService.php';
require_once __DIR__ . '/../services/OpayService.php';
require_once __DIR__ . '/../services/SafehavenService.php';
require_once __DIR__ . '/../services/CryptoPaymentService.php';
require_once __DIR__ . '/../services/BankPaymentService.php';
require_once __DIR__ . '/../core/response.php';
require_once __DIR__ . '/../core/SanitizationService.php';
require_once __DIR__ . '/../middleware/Validator.php';

class PaymentController {

    public static function getPayments($user_id) {
        $payments = PaymentService::getUserPayments($user_id);
        Response::success($payments);
    }

    public static function searchUserPayments($user_id, $term) {
        PaymentService::searchUserPayments($user_id, $term);
    }

    public static function getAllPayments() {
        PaymentService::getAllPayments();
    }

    public static function getSelectPayment($id) {
        $paymentData = PaymentService::getPaymentByID($id);
        Response::success($paymentData);
    }

    public static function searchPaymentsByRef($ref) {
        PaymentService::searchPaymentsByRef($ref);
    }

    public static function handleCryptoPayment($user_id, $rawInput) {
        $input = SanitizationService::sanitize($rawInput);
        
        // Validate Input
        $rules = [
            'amount' => 'required|float',
            'coin' => 'required|string'
        ];
        $input_errors = Validator::validate($input, $rules);
        if(!empty($input_errors)) {
            Response::error(['validation_errors' => $input_errors], 422);
        }

        CryptoPaymentService::createCryptoPayment($user_id, $input);
    }

    public static function handleBankPayment($user_id, $rawInput) {
        $input = SanitizationService::sanitize($rawInput);
        
        // Validate Input
        $rules = [
            'amount' => 'required|float',
            'bank_name' => 'required|string',
            'account_number' => 'required|string',
        ];
        $input_errors = Validator::validate($input, $rules);
        if(!empty($input_errors)) {
            Response::error(['validation_errors' => $input_errors], 422);
        }

        BankPaymentService::createBankPayment($user_id, $input);
    }

    public static function updateCryptoPaymentStatus($id) {
        $rawInput = json_decode(file_get_contents("php://input"), true);
        $input = SanitizationService::sanitize($rawInput);
        
        // Validate Input
        $rules = [
            'amount' => 'required|float',
            'status' => 'required|string'
        ];
        $input_errors = Validator::validate($input, $rules);
        if(!empty($input_errors)) {
            Response::error(['validation_errors' => $input_errors], 422);
        }

        CryptoPaymentService::updateCryptoPaymentStatus($id, $input);
    }

    public static function updateBankPaymentStatus($id) {
        $rawInput = json_decode(file_get_contents("php://input"), true);
        $input = SanitizationService::sanitize($rawInput);
        
        // Validate Input
        $rules = [
            'amount' => 'required|float',
            'status' => 'required|string'
        ];
        $input_errors = Validator::validate($input, $rules);
        if(!empty($input_errors)) {
            Response::error(['validation_errors' => $input_errors], 422);
        }

        BankPaymentService::updateBankPaymentStatus($id, $input);
    }

    public static function initiateFlutterwavePayment($user_id, $rawInput) {
        $input = SanitizationService::sanitize($rawInput);
        
        // Validate Input
        $rules = [
            'amount' => 'required|float'
        ];
        $input_errors = Validator::validate($input, $rules);
        if(!empty($input_errors)) {
            Response::error(['validation_errors' => $input_errors], 422);
        }

        FlutterwaveService::createFlutterwavePayment($user_id, $input);
    }

    public static function verifyFlutterwavePayment($user_id) {
        $rawInput = json_decode(file_get_contents("php://input"), true);
        $input = SanitizationService::sanitize($rawInput);
        
        $transaction_id = $input['transaction_id'] ?? null;
        if(!$transaction_id) {
            Response::error('Missing transaction ID', 400);
        }

        FlutterwaveService::verifyFlutterwaveTransaction($transaction_id);
    }

    public static function updatePaystackPayment($user_id) {
        $rawInput = json_decode(file_get_contents("php://input"), true);
        $input = SanitizationService::sanitize($rawInput);
        
        $transaction_id = $input['transaction_id'] ?? null;
        if(!$transaction_id) {
            Response::error('Missing transaction ID', 400);
        }

        PaystackService::updatePaystackTransaction($transaction_id);
    }

    public static function initiatePaystackPayment($user_id, $rawInput) {
        $input = SanitizationService::sanitize($rawInput);
        
        // Validate Input
        $rules = [
            'amount' => 'required|float'
        ];
        $input_errors = Validator::validate($input, $rules);
        if(!empty($input_errors)) {
            Response::error(['validation_errors' => $input_errors], 422);
        }

        PaystackService::createPaystackPayment($user_id, $input);
    }

    public static function initiateOpayPayment($user_id, $rawInput) {
        $input = SanitizationService::sanitize($rawInput);
        
        // Validate Input
        $rules = [
            'amount' => 'required|float'
        ];
        $input_errors = Validator::validate($input, $rules);
        if(!empty($input_errors)) {
            Response::error(['validation_errors' => $input_errors], 422);
        }

        OpayService::createOpayPayment($user_id, $input);
    }

    public static function initiateSafehavenPayment($user_id, $rawInput) {
        $input = SanitizationService::sanitize($rawInput);
        
        // Validate Input
        $rules = [
            'amount' => 'required|float'
        ];
        $input_errors = Validator::validate($input, $rules);
        if(!empty($input_errors)) {
            Response::error(['validation_errors' => $input_errors], 422);
        }

        SafehavenService::createVirtualAccount($user_id, $input);
    }
}

