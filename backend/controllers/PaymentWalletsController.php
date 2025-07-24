<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


use Core\SanitizationService;
use Core\InputData;

require_once __DIR__ . '/../services/PaymentWalletsService.php';
require_once __DIR__ . '/../core/response.php';
require_once __DIR__ . '/../core/SanitizationService.php';
require_once __DIR__ . '/../middleware/Validator.php';

class PaymentWalletsController {

    public static function getWallets() {
        PaymentWalletsService::getAllWallets();
    }

    public static function getSelectWallet($id) {
        $wallet = PaymentWalletsService::getWalletById($id);
        Response::success($wallet);
    }

    public static function deleteWallet($id) {
        PaymentWalletsService::deleteWallet($id);
    }

    public static function createWallet() {
        $rawInput = json_decode(file_get_contents("php://input"), true);
        $input = SanitizationService::sanitize($rawInput);
        
        // Validate Input
        $rules = [
            'coin'  => 'required|string',
            'network'     => 'required|string',
            'address' => 'required|string'
        ];
        $validator = new Validator();
        $input_errors = Validator::validate($input, $rules);
        if(!empty($input_errors)) {
            Response::error(['validation_errors' => $input_errors], 422);
        }

        PaymentWalletsService::createWallet($input);
    }

    public static function updateWallet($id) {
        $rawInput = json_decode(file_get_contents("php://input"), true);
        $input = SanitizationService::sanitize($rawInput);
        
        // Validate Input
        $rules = [
            'coin'  => 'required|string',
            'network'     => 'required|string',
            'address' => 'required|string'
        ];
        $validator = new Validator();
        $input_errors = Validator::validate($input, $rules);
        if(!empty($input_errors)) {
            Response::error(['validation_errors' => $input_errors], 422);
        }

        PaymentWalletsService::updateWallet($id, $input);
    }

}

