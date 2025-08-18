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
require_once __DIR__ . '/../../controllers/WithdrawController.php';

$user = AuthMiddleware::requirePermission('manage_withdrawals');
$user_id = $user->user_id;

$raw_id = $_GET['id'] ?? '';
$id = SanitizationService::sanitizeParam($raw_id);
if(!$id) {
    Response::error('ID is required', 400);
}

switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        if($id === 'all') {
            WithdrawController::getPendingWithdrawals();  
        } else  {
            WithdrawController::getSelectPendingWithdrawals($id);  
        };
        break;

    case 'PUT':
        if(isset($_GET['action'])) {
            $action = SanitizationService::sanitizeParam($_GET['action']);
            if($action === 'approve' || $action === 'decline') {
                WithdrawController::updateWithdrawalStatus($id, $action);
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