<?php
use Core\SanitizationService;

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../../core/SanitizationService.php';
require_once __DIR__ . '/../../controllers/OrderController.php';

$raw_id = $_GET['id'] ?? '';
$id = SanitizationService::sanitizeParam($raw_id);
if(!$id) {
    Response::error('ID is required', 400);
}

switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        $user = AuthMiddleware::requirePermission('view_orders');
        $user_id = $user->user_id;

        if(isset($_GET['search'])) {
            $search = SanitizationService::sanitizeParam($_GET['search']);
            OrderController::searchOrdersByRef($search);
        }
        if($id === 'all') {
            OrderController::getAllOrders();  
        } else  {
            OrderController::getSelectOrder($id);
        };
        break;

    case 'DELETE':
        $user = AuthMiddleware::requirePermission('manage_orders');
        $user_id = $user->user_id;

        OrderController::deleteOrder($id);
        break;

    default:
        http_response_code(405);
        echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
        break;
}