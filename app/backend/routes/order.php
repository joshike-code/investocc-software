<?php
use Core\SanitizationService;

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../core/SanitizationService.php';
require_once __DIR__ . '/../controllers/OrderController.php';

$user = AuthMiddleware::handle(['user']);
$user_id = $user->user_id;

switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        if(isset($_GET['search'])) {
            $search = SanitizationService::sanitizeParam($_GET['search']);
            OrderController::userSearchOrders($user_id, $search);
        }
        OrderController::getOrders($user_id);
        break;

    case 'POST':
        if(isset($_GET['type'])) {
            $type = SanitizationService::sanitizeParam($_GET['type']);
            if($type === 'buy') {
                OrderController::createBuyOrder($user_id, $type);
            } elseif($type === 'sell') {
                OrderController::createSellOrder($user_id, $type);
            } else {
                Response::error('Invalid input', 503);
            };
        };
        break;

    default:
        http_response_code(405);
        echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
        break;
}