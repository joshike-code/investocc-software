<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


require_once __DIR__ . '/../services/ServiceService.php';
require_once __DIR__ . '/../core/response.php';

class ServiceController {

    public static function getServices() {
        $services = ServiceService::getApiServices();
        Response::success($services);
    }
}

