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

class InvestmentService
{
    public static function getUserInvestments($user_id, $getResult = false)
    {
        $conn = Database::getConnection();

        $sql = "SELECT order_ref, amount, days, rate, duration, status, date 
                FROM investments 
                WHERE user_id = ?";
        
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            Response::error('Failed to get investments', 500);
        }

        $stmt->bind_param("s", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        $investments = [];
        while ($row = $result->fetch_assoc()) {
            $investments[] = $row;
        }

        $stmt->close();
        if($getResult === true) {
            return $investments;
            exit;
        }
        Response::success($investments);
    }

    public static function createInvestment(string $user_id, array $input)
    {
        $conn = Database::getConnection();

        $amount = $input['amount'];
        $tenor_id = $input['tenor'];

        if($amount <= 0) {
            Response::error('Input amount to buy', 422);
        }


        // Find plan (tenor)
        $check = $conn->prepare("SELECT days, rate, duration FROM plans WHERE id = ?");
        $check->bind_param("s", $tenor_id);
        $check->execute();
        
        $result = $check->get_result()->fetch_assoc();
        if (!$result) {
            Response::error('Plan does not exist', 404);
        }

        $days = $result['days'];
        $rate = $result['rate'];
        $duration = $result['duration'];

        // Fetch user balance
        $stmt = $conn->prepare("SELECT balance FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if (!$result || $result->num_rows === 0) {
            Response::error('User not found', 404);
        }

        $user = $result->fetch_assoc();

        // Ensure the balance is a float and check if the user has enough balance
        $balance = (float) $user['balance'];
        if ($balance < $amount) {
            Response::error('Insufficient balance', 402);
        }

        $conn->begin_transaction();

        try {
            $order_ref = uniqid("od_");
            $status = 'pending';
            $date = gmdate('Y-m-d H:i:s');
            $stmt = $conn->prepare("INSERT INTO investments (user_id, order_ref, amount, days, rate, duration, plan_id, status, date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            if (!$stmt) {
                throw new Exception('Prepare failed');
            }

            $stmt->bind_param("ssdidssss", $user_id, $order_ref, $amount, $days, $rate, $duration, $tenor_id, $status, $date);

            if (!$stmt->execute()) {
                throw new Exception('Failed to store investment');
            }
 
            // Update user balance 
            $update = $conn->prepare("UPDATE users SET balance = balance - ? WHERE id = ?");
            $update->bind_param("di", $amount, $user_id);

            if (!$update->execute()) {
                throw new Exception('Failed to update balance');
            }

            // Fetch updated user balance
            $balanceStmt = $conn->prepare("SELECT balance FROM users WHERE id = ?");
            $balanceStmt->bind_param("i", $user_id);
            $balanceStmt->execute();
            $balanceResult = $balanceStmt->get_result();
            $updatedBalance = $balanceResult->fetch_assoc()['balance'] ?? null;

            // Log to payments table
            $insert = $conn->prepare("INSERT INTO payments (user_id, amount, order_ref, method, type, status, date) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $method = "investment";
            $type = "debit";
            $status = "success";
            $date = gmdate('Y-m-d H:i:s');
            $insert->bind_param("sdsssss", $user_id, $amount, $order_ref, $method, $type, $status, $date);
            $insert->execute();

            // Commit the transaction
            $conn->commit();

            // Fetch and return investments
            $investments = self::getUserInvestments($user_id, true);
            return Response::success([
                'investments' => $investments,
                'balance' => $updatedBalance
            ]);
        } catch (Exception $e) {
            // Rollback the transaction in case of an error
            $conn->rollback();
            Response::error('Something went wrong. Please try again', 500);
        }
    }

    public static function getUserTotalSpent($user_id)
    {
        $conn = Database::getConnection();

        // Get total investments (number)
        $sqlOrders = "SELECT COUNT(*) AS total_investments FROM investments WHERE user_id = ?";
        $stmtOrders = $conn->prepare($sqlOrders);
        if (!$stmtOrders) {
            Response::error('Failed to get total investment', 500);
        }
        $stmtOrders->bind_param("s", $user_id);
        $stmtOrders->execute();
        $ordersResult = $stmtOrders->get_result()->fetch_assoc();
        $totalInvestments = intval($ordersResult['total_investments']);

        // Get pending profit
        $sqlProfit = "SELECT COALESCE(SUM(roi), 0) AS pending_profit FROM investments WHERE user_id = ? AND status='pending'";
        $stmtProfit = $conn->prepare($sqlProfit);
        if (!$stmtProfit) {
            Response::error('Failed to get pending profit', 500);
        }
        $stmtProfit->bind_param("s", $user_id);
        $stmtProfit->execute();
        $profitResult = $stmtProfit->get_result()->fetch_assoc();
        $pendingProfit = floatval($profitResult['pending_profit']);

        // Get total profit
        $sqlProfit = "SELECT COALESCE(SUM(roi), 0) AS total_profit FROM investments WHERE user_id = ? AND status='complete'";
        $stmtProfit = $conn->prepare($sqlProfit);
        if (!$stmtProfit) {
            Response::error('Failed to get total profit', 500);
        }
        $stmtProfit->bind_param("s", $user_id);
        $stmtProfit->execute();
        $profitResult = $stmtProfit->get_result()->fetch_assoc();
        $totalProfit = floatval($profitResult['total_profit']);

        // Get counts per status
        $statusCounts = [];
        $statuses = ['pending', 'complete'];

        foreach ($statuses as $status) {
            $sql = "SELECT COUNT(*) AS count FROM investments WHERE user_id = ? AND status = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ss", $user_id, $status);
            $stmt->execute();
            $res = $stmt->get_result()->fetch_assoc();
            $statusCounts[$status] = intval($res['count']);
        }

        Response::success([
            'total_investments' => $totalInvestments,
            'pending_profit' => $pendingProfit,
            'total_profit' => $totalProfit,
            'status_breakdown' => $statusCounts
        ]);
    }

    public static function userSearchInvestments($user_id, $searchTerm)
    {
        $conn = Database::getConnection();

        $likeTerm = "%" . $searchTerm . "%";

        $query = "
            SELECT id, order_ref, amount, days, rate, duration, status, date 
            FROM investments
            WHERE user_id = ?
            AND (
                order_ref LIKE ? OR
                status LIKE ? OR
                date LIKE ?
            )
        ";

        $stmt = $conn->prepare($query);
        if (!$stmt) {
            // Response::error("Prepare failed: " . $conn->error, 500);
            return;
        }

        $stmt->bind_param("isss", $user_id, $likeTerm, $likeTerm, $likeTerm);
        $stmt->execute();

        $result = $stmt->get_result();
        $investments = [];

        while ($row = $result->fetch_assoc()) {
            $investments[] = $row;
        }

        Response::success($investments);

        
    }

    // ADMIN METHODS
    public static function getAdminOrderStats()
    {
        $conn = Database::getConnection();

        // Total investments count
        $stmt = $conn->query("SELECT COUNT(*) AS total_investments FROM investments");
        $totalInvestmentsCount = intval($stmt->fetch_assoc()['total_investments']);

        // Total investments spent
        $stmt = $conn->query("SELECT COALESCE(SUM(amount), 0) AS total_spent FROM investments");
        $totalInvestmentsSpent = floatval($stmt->fetch_assoc()['total_spent']);

        // Total investments roi
        $stmt = $conn->query("SELECT COALESCE(SUM(roi), 0) AS total_roi FROM investments");
        $totalInvestmentsRoi = floatval($stmt->fetch_assoc()['total_roi']);

        // Pending investments count
        $stmt = $conn->query("SELECT COUNT(*) AS pending_investments FROM investments WHERE status = 'pending'");
        $pendingInvestmentsCount = intval($stmt->fetch_assoc()['pending_investments']);

        // Pending investments spent
        $stmt = $conn->query("SELECT COALESCE(SUM(amount), 0) AS pending_spent FROM investments WHERE status = 'pending'");
        $pendingInvestmentsSpent = floatval($stmt->fetch_assoc()['pending_spent']);

        // Pending investments roi
        $stmt = $conn->query("SELECT COALESCE(SUM(roi), 0) AS pending_roi FROM investments WHERE status = 'pending'");
        $pendingInvestmentsRoi = floatval($stmt->fetch_assoc()['pending_roi']);

        // Status breakdown
        $statuses = ['pending', 'complete'];
        $statusCounts = [];
        foreach ($statuses as $status) {
            $stmt = $conn->prepare("SELECT COUNT(*) AS count FROM investments WHERE status = ?");
            $stmt->bind_param("s", $status);
            $stmt->execute();
            $res = $stmt->get_result()->fetch_assoc();
            $statusCounts[$status] = intval($res['count']);
        }

        // Orders today
        $todayQuery = "
            SELECT COUNT(*) AS investments_today, COALESCE(SUM(amount), 0) AS spent_today
            FROM investments
            WHERE date >= UTC_TIMESTAMP() - INTERVAL 1 DAY
            AND status = 'completed'
        ";
        $todayResult = $conn->query($todayQuery)->fetch_assoc();
        $investmentsToday = intval($todayResult['investments_today']);
        $spentToday = floatval($todayResult['spent_today']);

        // investments this week
        $weekQuery = "
            SELECT COUNT(*) AS investments_week, COALESCE(SUM(amount), 0) AS spent_week
            FROM investments
            WHERE date >= UTC_TIMESTAMP() - INTERVAL 7 DAY
            AND status = 'completed'
        ";
        $weekResult = $conn->query($weekQuery)->fetch_assoc();
        $investmentsThisWeek = intval($weekResult['investments_week']);
        $spentThisWeek = floatval($weekResult['spent_week']);

        // investments this month
        $monthQuery = "
            SELECT COUNT(*) AS investments_month, COALESCE(SUM(amount), 0) AS spent_month
            FROM investments
            WHERE date >= UTC_TIMESTAMP() - INTERVAL 30 DAY
            AND status = 'completed'
        ";
        $monthResult = $conn->query($monthQuery)->fetch_assoc();
        $investmentsThisMonth = intval($monthResult['investments_month']);
        $spentThisMonth = floatval($monthResult['spent_month']);

        Response::success([
            'total_investments_count'       => $totalInvestmentsCount,
            'total_investments_spent'       => $totalInvestmentsSpent,
            'total_investments_roi'        => $totalInvestmentsRoi,
            'pending_investments_count'   => $pendingInvestmentsCount,
            'pending_investments_spent'   => $pendingInvestmentsSpent,
            'pending_investments_roi'   => $pendingInvestmentsRoi,
            'status_breakdown'   => $statusCounts,
            'investments_today'       => $investmentsToday,
            'spent_today'        => $spentToday,
            'investments_this_week'   => $investmentsThisWeek,
            'spent_this_week'    => $spentThisWeek,
            'investments_this_month'  => $investmentsThisMonth,
            'spent_this_month'   => $spentThisMonth
        ]);
    }

    public static function getAllInvestments() {
        $conn = Database::getConnection();
        $stmt = $conn->prepare("
            SELECT 
                i.id AS order_id, i.order_ref, i.amount, i.days, i.rate, i.duration, i.status, i.roi, i.date,
                u.id AS user_id, u.fname, u.lname, u.email
            FROM 
                investments i
            INNER JOIN 
                users u ON i.user_id = u.id
        ");
        $stmt->execute();
        $result = $stmt->get_result();
        $investments = [];
        
        while ($row = $result->fetch_assoc()) {
            $investments[] = $row;
        }

        // if (empty($investments)) {
        //     Response::error('No investments found', 404);       No need for error when fetching all
        // }

        // Total investments
        $stmtTotal = $conn->query("SELECT COUNT(*) AS total_investments FROM investments");
        $total_investments_count = intval($stmtTotal->fetch_assoc()['total_investments']);

        Response::success([
            'total_investments'     => $investments,
            'total_investments_count' => $total_investments_count
        ]);
    }

    public static function getInvestmentById($id) {
        $conn = Database::getConnection();
        $stmt = $conn->prepare("
             SELECT 
                i.id AS order_id, i.order_ref, i.amount, i.days, i.rate, i.duration, i.status, i.roi, i.plan_id, i.date,
                u.id AS user_id, u.fname, u.lname, u.email
            FROM 
                investments i
            INNER JOIN 
                users u ON i.user_id = u.id
        WHERE i.id = ?");
        $stmt->bind_param("s", $id);
        $stmt->execute();
        if (!$stmt) {
            // Not returning here .... remember to check when using to return error abeg (probably no result check)
            Response::error('Could not get investment', 500);
        }

        $result = $stmt->get_result()->fetch_assoc();

        if (!$result) {
            Response::error('Investment not found', 404);
        }

        return $result;
    }

    public static function deleteInvestment($id) {
        $conn = Database::getConnection();

        // Check if order exists
        $stmt = $conn->prepare("SELECT id FROM investments WHERE id = ?");
        $stmt->bind_param("s", $id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();

        if (!$result) {
            Response::error('Investments not found', 404);
        }

        $stmt = $conn->prepare("DELETE FROM investments WHERE id = ?");
        $stmt->bind_param("s", $id);

        if ($stmt->execute()) {
            Response::success("Investment deleted successfully.");
        } else {
            Response::error("Failed to delete investment.", 500);
        }
    }

    public static function searchAllInvestments($searchTerm)
    {
        $conn = Database::getConnection();

        $likeTerm = "%" . $searchTerm . "%";

        $query = "
            SELECT 
                i.id AS order_id, i.order_ref, i.amount, i.days, i.rate, i.duration, i.status, i.roi, i.date,
                u.id AS user_id, u.fname, u.lname, u.email
            FROM 
                investments i
            INNER JOIN 
                users u ON i.user_id = u.id
            WHERE (
                i.order_ref LIKE ? OR
                i.status LIKE ? OR
                i.date LIKE ?
            )
        ";

        $stmt = $conn->prepare($query);
        if (!$stmt) {
            // Response::error("Prepare failed: " . $conn->error, 500);
            return;
        }

        $stmt->bind_param("sss", $likeTerm, $likeTerm, $likeTerm);
        $stmt->execute();

        $result = $stmt->get_result();
        $investments = [];

        while ($row = $result->fetch_assoc()) {
            $investments[] = $row;
        }

        Response::success($investments);
    }
}



?>