<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

use Core\SanitizationService;

require_once __DIR__ . '/../core/response.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../controllers/PaymentController.php';
require_once __DIR__ . '/../core/SanitizationService.php';

$user = AuthMiddleware::handle(['user']);
$user_id = $user->user_id;
$raw_id = $_GET['id'] ?? '';
$id = SanitizationService::sanitizeParam($raw_id);
if(!$id) {
    Response::error('ID is required', 400);
}

switch ($_SERVER['REQUEST_METHOD']) {
    case 'POST':
        if($id === 'flutterwave') {
            PaymentController::verifyFlutterwavePayment($user_id);
        } else if($id === 'paystack') {
            PaymentController::updatePaystackPayment($user_id);
        } else {
            Response::error('Method not allowed', 405);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
        break;
}