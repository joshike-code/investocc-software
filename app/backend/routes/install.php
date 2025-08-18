<?php
use Core\SanitizationService;

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../controllers/InstallController.php';
require_once __DIR__ . '/../core/SanitizationService.php';

$raw_type = $_GET['type'] ?? '';
$type = SanitizationService::sanitizeParam($raw_type);
if(!$type) {
    Response::error('Type is required', 400);
}

switch ($_SERVER['REQUEST_METHOD']) {

    case 'POST':
        if($type === 'setup') {
            InstallController::createProfile();
        } else if($type === 'passkey') {
            InstallController::updatePasskey();
        } else {
            Response::error('Method not allowed', 405);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
        break;
}