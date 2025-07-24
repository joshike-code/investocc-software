<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


use Core\SanitizationService;
// use Core\InputData;

require_once __DIR__ . '/../services/StockOrderService.php';
require_once __DIR__ . '/../core/response.php';
require_once __DIR__ . '/../core/SanitizationService.php';
require_once __DIR__ . '/../middleware/Validator.php';

class OrderController {

    public static function getOrders($user_id) {
        StockOrderService::getUserOrders($user_id);
    }

    public static function userSearchOrders($user_id, $term) {
        StockOrderService::userSearchOrders($user_id, $term);
    }

    public static function getSelectOrder($id) {
        $selectOrder = StockOrderService::getOrderById($id);
        Response::success($selectOrder);
    }

    public static function getAllOrders() {
        StockOrderService::getAllOrders();
    }

    public static function deleteOrder($id) {
        StockOrderService::deleteOrder($id);
    }

    public static function searchOrdersByRef($order_ref) {
        StockOrderService::searchOrdersByRef($order_ref);
    }

    public static function createBuyOrder($user_id, $type) {
        $rawInput = json_decode(file_get_contents("php://input"), true);
        $input = SanitizationService::sanitize($rawInput);
        
        // Validate Input
        $rules = [
            'amount'  => 'required|float',
            'stock'     => 'required|string'
        ];
        $input_errors = Validator::validate($input, $rules);
        if(!empty($input_errors)) {
            Response::error(['validation_errors' => $input_errors], 422);
        }

        StockOrderService::createBuyOrder($user_id, $input);
    }

    public static function createSellOrder($user_id, $type) {
        $rawInput = json_decode(file_get_contents("php://input"), true);
        $input = SanitizationService::sanitize($rawInput);
        
        // Validate Input
        $rules = [
            'shares'  => 'required|float',
            'stock'     => 'required|string'
        ];
        $input_errors = Validator::validate($input, $rules);
        if(!empty($input_errors)) {
            Response::error(['validation_errors' => $input_errors], 422);
        }

        StockOrderService::createSellOrder($user_id, $input);
    }
}

