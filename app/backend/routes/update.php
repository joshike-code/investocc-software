<?php
use Core\SanitizationService;

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../controllers/UpdateController.php';
require_once __DIR__ . '/../core/SanitizationService.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        $user = AuthMiddleware::handle(['superadmin', 'admin']);
        $user_id = $user->user_id;

        $raw_action = $_GET['action'] ?? '';
        $action = SanitizationService::sanitizeParam($raw_action);
        if(!$action) {
            Response::error('Action is required', 400);
        }
        if($action === 'latest') {
            UpdateController::getLatestUpdate();
        } else if($action === 'changelogs') {
            Response::success('Fetching changelogs may take a moment. Please wait...');
            // UpdateController::getAllChangelogs();
        } else {
            Response::error('Method not allowed', 405);
        }
        break;

    case 'POST':
        $user = AuthMiddleware::handle(['superadmin']);
        $user_id = $user->user_id;

        $raw_action = $_GET['action'] ?? '';
        $action = SanitizationService::sanitizeParam($raw_action);
        if(!$action) {
            Response::error('Action is required', 400);
        }
        
        if($action === 'update') {
            UpdateController::applyUpdate();
        } else if($action === 'status') {
            UpdateController::getUpdateStatus();
        } else {
            Response::error('Method not allowed', 405);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
        break;
}