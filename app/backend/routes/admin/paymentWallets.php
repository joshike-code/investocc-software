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
require_once __DIR__ . '/../../controllers/PaymentWalletsController.php';

$user = AuthMiddleware::requirePermission('manage_payment_wallets');
$user_id = $user->user_id;

$raw_id = $_GET['id'] ?? '';
$id = SanitizationService::sanitizeParam($raw_id);
if(!$id) {
    Response::error('ID is required', 400);
}

switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        if($id === 'all') {
            PaymentWalletsController::getWallets();  
        } else  {
            PaymentWalletsController::getSelectWallet($id);
        };
        break;

    case 'PUT':
        PaymentWalletsController::updateWallet($id);
        break;

    case 'DELETE':
        PaymentWalletsController::deleteWallet($id);
        break;
        
    case 'POST':
        PaymentWalletsController::createWallet();
        break;

    default:
        http_response_code(405);
        echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
        break;
}