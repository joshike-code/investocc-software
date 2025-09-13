<?php
use Core\SanitizationService;
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../controllers/StockController.php';
require_once __DIR__ . '/../core/response.php';
require_once __DIR__ . '/../core/SanitizationService.php';

$user = AuthMiddleware::handle(['user','admin','superadmin']);
$user_id = $user->user_id;

switch ($_SERVER['REQUEST_METHOD']) { 
    case 'GET':
        if(isset($_GET['filter'])) {
            $filter = SanitizationService::sanitizeParam($_GET['filter']);
            StockController::getStocksByCategory($filter);
        } elseif (isset($_GET['search'])) {
            $searchTerm = SanitizationService::sanitizeParam($_GET['search']);
            StockController::searchStocks($searchTerm);
        } elseif (isset($_GET['id'])) {
            $id = SanitizationService::sanitizeParam($_GET['id']);
            StockController::getStockById($id);
        };
        StockController::getStocks();
        break;

    case 'POST':
        // Only allow admins to manually update stock prices
        $admin = AuthMiddleware::handle(['admin', 'superadmin']);
        if(isset($_GET['action']) && $_GET['action'] === 'update_prices') {
            StockController::updateStockPrices();
        }
        Response::error('Invalid action', 400);
        break;

    default:
        http_response_code(405);
        echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
        break;
}