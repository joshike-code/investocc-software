<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


use Core\SanitizationService;
// use Core\InputData;

require_once __DIR__ . '/../services/InvestmentService.php';
require_once __DIR__ . '/../core/response.php';
require_once __DIR__ . '/../core/SanitizationService.php';
require_once __DIR__ . '/../middleware/Validator.php';

class InvestmentController {

    public static function getInvestments($user_id) {
        InvestmentService::getUserInvestments($user_id);
    }

    public static function userSearchInvestments($user_id, $term) {
        InvestmentService::userSearchInvestments($user_id, $term);
    }

    public static function getSelectInvestment($id) {
        $selectInvestment = InvestmentService::getInvestmentById($id);
        Response::success($selectInvestment);
    }

    public static function getAllInvestments() {
        InvestmentService::getAllInvestments();
    }

    public static function deleteinvestment($id) {
        InvestmentService::deleteinvestment($id);
    }

    public static function searchAllInvestments($order_ref) {
        StockOrderService::searchAllInvestments($order_ref);
    }

    public static function createInvestment($user_id) {
        $rawInput = json_decode(file_get_contents("php://input"), true);
        $input = SanitizationService::sanitize($rawInput);
        
        // Validate Input
        $rules = [
            'amount'  => 'required|float',
            'tenor'     => 'required|float'
        ];
        $input_errors = Validator::validate($input, $rules);
        if(!empty($input_errors)) {
            Response::error(['validation_errors' => $input_errors], 422);
        }

        InvestmentService::createInvestment($user_id, $input);
    }
}

