<?php
use Core\SanitizationService;

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../core/response.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../core/SanitizationService.php';
require_once __DIR__ . '/../controllers/PaymentController.php';

$user = AuthMiddleware::handle(['user']);
$user_id = $user->user_id;

switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        if(isset($_GET['search'])) {
            $search = SanitizationService::sanitizeParam($_GET['search']);
            PaymentController::searchUserPayments($user_id, $search);
        }
        PaymentController::getPayments($user_id);
        break;

    case 'POST':
        $input = json_decode(file_get_contents("php://input"), true);
        $method = $input['method'] ?? '';
        if($method === 'crypto') {
            PaymentController::handleCryptoPayment($user_id, $input);
        } elseif ($method === 'flutterwave') {
            PaymentController::initiateFlutterwavePayment($user_id, $input);
        } elseif ($method === 'paystack') {
            PaymentController::initiatePaystackPayment($user_id, $input);
        } elseif ($method === 'opay') {
            PaymentController::initiateOpayPayment($user_id, $input);
        } elseif ($method === 'safehaven') {
            PaymentController::initiateSafehavenPayment($user_id, $input);
        } elseif ($method === 'bank') {
            PaymentController::handleBankPayment($user_id, $input);
        } else {
            Response::error('Invalid payment method', 400);
        };
        break; 

    default:
        http_response_code(405);
        echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
        break;
}