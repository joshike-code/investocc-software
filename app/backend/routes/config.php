<?php
use Core\SanitizationService;
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../controllers/ConfigController.php';
require_once __DIR__ . '/../core/response.php';
require_once __DIR__ . '/../core/SanitizationService.php';

switch ($_SERVER['REQUEST_METHOD']) { 
    case 'GET':
        if(isset($_GET['action']) && $_GET['action'] === 'status') {
            if(isset($_GET['domain'])) {
                $domain = SanitizationService::sanitizeParam($_GET['domain']);
                ConfigController::getConfigurationStatus($domain);
            } else {
                Response::error('Domain parameter required', 400);
            }
        } else {
            Response::error('Invalid action', 400);
        }
        break;

    case 'POST':
        if(isset($_GET['action'])) {
            $action = SanitizationService::sanitizeParam($_GET['action']);
            
            switch($action) {
                case 'validate':
                    ConfigController::validateConfiguration();
                    break;
                    
                case 'verify':
                    ConfigController::verifyConfiguration();
                    break;
                    
                default:
                    Response::error('Invalid action', 400);
                    break;
            }
        } else {
            Response::error('Action parameter required', 400);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
        break;
}
