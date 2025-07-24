<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


use Core\SanitizationService;
// use Core\InputData;

require_once __DIR__ . '/../services/PlanService.php';
require_once __DIR__ . '/../core/response.php';
require_once __DIR__ . '/../core/SanitizationService.php';
require_once __DIR__ . '/../middleware/Validator.php';

class PlanController {

    public static function getPlans() {
        $plans = PlanService::getAllPlans();
        Response::success($plans);
    }

    public static function getSelectPlan($id) {
        $plan = PlanService::getPlanById($id);
        Response::success($plan);
    }

    public static function deletePlan($id) {
        PlanService::deletePlan($id);
    }

    public static function createPlan() {
        $rawInput = json_decode(file_get_contents("php://input"), true);
        $input = SanitizationService::sanitize($rawInput);
        
        // Validate Input
        $rules = [
            'days'  => 'required|integer',
            'rate'     => 'required|float',
            'duration' => 'required|string'
        ];
        $validator = new Validator();
        $input_errors = Validator::validate($input, $rules);
        if(!empty($input_errors)) {
            Response::error(['validation_errors' => $input_errors], 422);
        }

        PlanService::createPlan($input);
    }

    public static function updatePlan($id) {
        $rawInput = json_decode(file_get_contents("php://input"), true);
        $input = SanitizationService::sanitize($rawInput);
        
        // Validate Input
        $rules = [
            'days'  => 'required|integer',
            'rate'     => 'required|float',
            'duration' => 'required|string'
        ];
        $validator = new Validator();
        $input_errors = Validator::validate($input, $rules);
        if(!empty($input_errors)) {
            Response::error(['validation_errors' => $input_errors], 422);
        }

        PlanService::updatePlan($id, $input);
    }
}

