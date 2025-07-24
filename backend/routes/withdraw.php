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
require_once __DIR__ . '/../controllers/WithdrawController.php';

$user = AuthMiddleware::handle(['user']);
$user_id = $user->user_id;

switch ($_SERVER['REQUEST_METHOD']) {
    case 'POST':
        if(isset($_GET['method'])) {
            $method = SanitizationService::sanitizeParam($_GET['method']);
            if($method === 'crypto') {
                WithdrawController::createCryptoWithdrawal($user_id, $method);
            } elseif($method === 'bank') {
                WithdrawController::createBankWithdrawal($user_id, $method);
            } else {
                Response::error('Invalid withdrawal method', 400);
            };
        } else {
            Response::error('Invalid method', 405);
        };
        break; 

    default:
        http_response_code(405);
        echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
        break;
}