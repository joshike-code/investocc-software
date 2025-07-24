<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
date_default_timezone_set('GMT'); // align PHP with DB's GMT time

use Core\SanitizationService;

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../core/response.php';
require_once __DIR__ . '/../utility/notify.php';
require_once __DIR__ . '/../services/PlatformService.php';
require_once __DIR__ . '/../core/SanitizationService.php';

class PlanService
{
    public static function getAllPlans()
    {
        $conn = Database::getConnection();

        $sql = "SELECT id, days, rate, duration, date
                FROM plans";
        
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            Response::error('Failed to get plans', 500);
        }

        $stmt->execute();
        $result = $stmt->get_result();

        $plans = [];
        while ($row = $result->fetch_assoc()) {
            $plans[] = $row;
        }

        $stmt->close();
        return $plans;
    }

    public static function getPlanById($id)
    {
        $conn = Database::getConnection();
        $stmt = $conn->prepare("SELECT days, rate, duration, date
            FROM plans 
            WHERE id = ?");
        $stmt->bind_param("s", $id);
        $stmt->execute();
        if (!$stmt) {
            // Not returning here .... remember to check when using to return error abeg (probably no execute check)
            Response::error('Could not plan', 500);
        }

        $result = $stmt->get_result()->fetch_assoc();

        if (!$result) {
            Response::error('Plan not found', 404);
        }

        return $result;
    }

    public static function createPlan(array $input)
    {
        $conn = Database::getConnection();

        $days = $input['days'];
        $rate = $input['rate'];
        $duration = $input['duration'];
        $date = gmdate('Y-m-d H:i:s');

        $stmt = $conn->prepare("INSERT INTO plans (days, rate, duration, date) VALUES (?, ?, ?, ?)");
        if (!$stmt) {
            Response::error('Prepare failed', 500);
        }

        $stmt->bind_param("idss", $days, $rate, $duration, $date);

        if (!$stmt->execute()) {
            Response::error('Failed to store plan', 500);
        }

        $plans = self::getAllPlans();
        Response::success($plans);
    }

    public static function updatePlan($id, $input)
    {
        $conn = Database::getConnection();

        $days = $input['days'];
        $rate = $input['rate'];
        $duration = $input['duration'];
        $date = gmdate('Y-m-d H:i:s');

        $stmt = $conn->prepare("UPDATE plans SET days = ?, rate = ?, duration = ?, date = ? WHERE id = ?");
        $stmt->bind_param("idsss", $days, $rate, $duration, $date, $id);

        if (!$stmt->execute()) {
            Response::error('Failed to update plan', 500);
        }

        $plans = self::getAllPlans();
        Response::success($plans);
    }

    public static function deletePlan($id) {
        $conn = Database::getConnection();

        // Check if plan exists
        $stmt = $conn->prepare("SELECT id FROM plans WHERE id = ?");
        $stmt->bind_param("s", $id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();

        if (!$result) {
            Response::error('Plan not found', 404);
        }

        $stmt = $conn->prepare("DELETE FROM plans WHERE id = ?");
        $stmt->bind_param("s", $id);

        if (!$stmt->execute()) {
            Response::error('Failed to delete plan', 500);
        }

        $plans = self::getAllPlans();
        Response::success($plans);
    }
}
?>