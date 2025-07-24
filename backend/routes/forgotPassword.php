<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

use Core\SanitizationService;

require_once __DIR__ . '/../core/response.php';
require_once __DIR__ . '/../controllers/UserController.php';
require_once __DIR__ . '/../core/SanitizationService.php';

$raw_action = $_GET['action'] ?? '';
$action = SanitizationService::sanitizeParam($raw_action);
if(!$action) {
    Response::error('action is required', 400);
}

switch ($_SERVER['REQUEST_METHOD']) {
    case 'POST':
        if($action === 'check') {
            UserController::checkEmail();
        } elseif($action === 'confirm') {
            UserController::createNewPassword($action);
        } elseif($action === 'update') {
            UserController::createNewPassword($action);
        }
        Response::error('method not allowed', 405);
        break;

    // case 'PUT':
    //     UserController::updateProfile($user_id);
    //     break;

    default:
        http_response_code(405);
        echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
        break;
}