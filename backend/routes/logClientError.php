<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../core/response.php';

switch ($_SERVER['REQUEST_METHOD']) {
    case 'POST':
    $data = json_decode(file_get_contents('php://input'), true);
    file_put_contents(__DIR__ . '/../error/client_errors.log', json_encode($data) . "[".date("Y-m-d H:i:s")."]\n", FILE_APPEND);
    Response::success('error logged successfully');
        break;

    default:
        http_response_code(405);
        echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
        break;
}