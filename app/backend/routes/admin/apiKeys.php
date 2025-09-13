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
require_once __DIR__ . '/../../controllers/ApiKeysController.php';

$user = AuthMiddleware::requirePermission('manage_keys');
$user_id = $user->user_id;

switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        ApiKeysController::getApiKeys();
        break;
        
    case 'PUT':
        if(isset($_GET['key'])) {
            $key = SanitizationService::sanitizeParam($_GET['key']);
            if($key === 'finnhub_key') {
                ApiKeysController::updateFinnhubApiKey();
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