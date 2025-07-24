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
require_once __DIR__ . '/../../controllers/PlanController.php';

$user = AuthMiddleware::requirePermission('manage_plans');
$user_id = $user->user_id;

$raw_id = $_GET['id'] ?? '';
$id = SanitizationService::sanitizeParam($raw_id);
if(!$id) {
    Response::error('ID is required', 400);
}

switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        if($id === 'all') {
            PlanController::getPlans();  
        } else  {
            PlanController::getSelectPlan($id);
        };
        break;

    case 'PUT':
        PlanController::updatePlan($id);
        break;

    case 'DELETE':
        PlanController::deletePlan($id);
        break;
        
    case 'POST':
        PlanController::createPlan();
        break;

    default:
        http_response_code(405);
        echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
        break;
}