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
require_once __DIR__ . '/../../controllers/PaymentController.php';

$raw_id = $_GET['id'] ?? '';
$id = SanitizationService::sanitizeParam($raw_id);
if(!$id) {
    Response::error('ID is required', 400);
}

switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        $user = AuthMiddleware::requirePermission('view_payments');
        $user_id = $user->user_id;

        if(isset($_GET['search'])) {
            $search = SanitizationService::sanitizeParam($_GET['search']);
            PaymentController::searchPaymentsByRef($search);
        }
        if($id === 'all') {
            PaymentController::getAllPayments();  
        } else  {
            PaymentController::getSelectPayment($id);
        };
        break;

    case 'PUT':
        if(isset($_GET['action'])) {
            $action = SanitizationService::sanitizeParam($_GET['action']);
            if($action === 'crypto') {
                $user = AuthMiddleware::requirePermission('manage_crypto');
                $user_id = $user->user_id;
                PaymentController::updateCryptoPaymentStatus($id);
            } elseif($action === 'bank') {
                $user = AuthMiddleware::requirePermission('manage_bank_payments');
                $user_id = $user->user_id;
                PaymentController::updateBankPaymentStatus($id);
            } else {
                Response::error('Method not allowed', 403);
            }
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
        break;
}