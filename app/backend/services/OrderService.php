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

class OrderService
{
    public static function getUserOrders($user_id, $getResult = false)
    {
        $conn = Database::getConnection();

        $sql = "SELECT order_ref, service_id, network, action, region, quality, name, link, amount, status, date 
                FROM orders 
                WHERE user_id = ?";
        
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            Response::error('Failed to get orders', 500);
        }

        $stmt->bind_param("s", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        $orders = [];
        while ($row = $result->fetch_assoc()) {
            $orders[] = $row;
        }

        $stmt->close();
        if($getResult === true) {
            return $orders;
            exit;
        }
        Response::success($orders);
    }

    
    
    public static function createOrder($user_id, $input)
    {
        $conn = Database::getConnection();

        $keys = require __DIR__ . '/../config/keys.php';
        $api_url = $keys['sizzle']['api_url'];
        $api_key = $keys['sizzle']['api_key'];

        $service_id = $input['service'] ?? null;
        $link = $input['link'] ?? '';
        $link = SanitizationService::sanitizeUrl($link);
        $quantity = $input['quantity'] ?? null;
        $network = $input['network'] ?? null;
        $action = $input['action'] ?? null;
        $region = $input['region'] ?? null;
        $quality = $input['quality'] ?? null;
        $name = $input['name'] ?? null;
        
        // Fetch services and match the selected service
        $services = self::fetchServices($api_url, $api_key);
        $matched  = array_filter($services, fn($s) => $s['service'] == $service_id);
        $matchedService = reset($matched);

        if (!$matchedService) {
            Response::error('Service not found', 404);
        }

        // Extract rate and quantity range
        $rate = (float) $matchedService['rate'];
        $min  = (int) $matchedService['min'];
        $max  = (int) $matchedService['max'];

        // Check if the quantity is within the valid range
        if ($quantity < $min || $quantity > $max) {
            Response::error('Invalid quantity range', 422);
        }

        // Calculate base cost from Sizzle rate
        $base_cost = ($rate * $quantity) / 1000;

        $service_rate = PlatformService::getSetting('service_rate', 5);

        // Add service markup
        $markup_percent = (float) $service_rate;
        $markup_amount = ($markup_percent / 100) * $base_cost;
        $cost = $base_cost + $markup_amount;

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
        if ($balance < $cost) {
            Response::error('Insufficient balance', 402);
        }

        // Start transaction to ensure atomicity of the balance update and order creation
        $conn->begin_transaction();

        try {
            // Submit order to external API
            $orderData = self::submitOrder($api_url, $api_key, $service_id, $link, $quantity, $user_id);

            if (!isset($orderData['order'])) {
                throw new Exception('Order failed with provider');
            }

            // Order details
            $order_ref = $orderData['order'];
            // $order_status = $orderData['status'];
            $order_status = 'pending';
            $date = gmdate('Y-m-d H:i:s');

            // Store order in db
            $insert = $conn->prepare("
                INSERT INTO orders (user_id, order_ref, service_id, network, action, region, quality, name, link, amount, status, date)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $insert->bind_param(
                "sssssssssdss", 
                $user_id, $order_ref, $service_id, $network, $action, $region, $quality, $name, $link, $cost, $order_status, $date
            );

            if (!$insert->execute()) {
                throw new Exception('Failed to store order');
            }

            // Deduct user balance 
            $new_balance = $balance - $cost;
            $update = $conn->prepare("UPDATE users SET balance = ? WHERE id = ?");
            $update->bind_param("di", $new_balance, $user_id);

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
            $insert = $conn->prepare("INSERT INTO payments (user_id, amount, order_ref, method, network, type, status, date) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $method = "order";
            $type = "debit";
            $status = "success";
            $date = gmdate('Y-m-d H:i:s');
            $insert->bind_param("sdssssss", $user_id, $cost, $order_ref, $method, $network, $type, $status, $date);
            $insert->execute();

            // Commit the transaction
            $conn->commit();

            // Fetch and return orders
            $orders = OrderService::getUserOrders($user_id, true);
            return Response::success([
                'orders' => $orders,
                'balance' => $updatedBalance
            ]);
            

        } catch (Exception $e) {
            // Rollback the transaction in case of an error
            $conn->rollback();
            Response::error('Something went wrong. Please try again', 500);
        }

        exit;
    }

    private static function fetchServices($url, $key)
    {
        $data = [
            "key"    => $key,
            "action" => "services"
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/x-www-form-urlencoded",
            "Accept: application/json",
            "User-Agent: Mozilla/5.0"
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        //(CHECK API KEY VALIDITY HERE LATER**************************************)//

        $decoded = json_decode($response, true) ?? [];
        if(empty($decoded)) {
            Response::error('Service unavailable', 503);
            return;
        }
        return $decoded ;
    }

    private static function submitOrder($url, $key, $service_id, $link, $quantity, $user_id)
    {
        $data = [
            "key"     => $key,
            "action"  => "add",
            "service" => $service_id,
            "link"    => $link,
            "quantity"=> $quantity
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/x-www-form-urlencoded",
            "Accept: application/json",
            "User-Agent: Mozilla/5.0"
        ]);

        $response = curl_exec($ch);
        curl_close($ch);
        
        $data = json_decode($response, true);

        // Case 1: Invalid API Key
        if (isset($data['error']) && strpos($data['error'], 'API is Disable') !== false) {
            $msg = "Sizzle API: Admin API Key is Disabled or Invalid. Order failed for service: $service_id, quantity: $quantity, link: $link || And USER: $user_id";
            Notify::log($msg);
            Response::error('Out of service error', 503);
        }

        // Case 2: Invalid service ID
        if (isset($data['error']) && strpos($data['error'], 'Service ID does not exist') !== false) {
            Response::error('Invalid service ID', 404);
        }

        // Case 3: Below/Above quantity range
        if (isset($data['error']) && strpos($data['error'], 'Your ordered Quantity') !== false) {
            Response::error('Invalid quantity range', 422);
        }

        // Case 4: Admin has insufficient balance
        if (isset($data['error']) && strpos($data['error'], "enough funds in your wallet") !== false) {
            $msg = "Sizzle API: Admin has insufficient balance. Order failed for service: $service_id, quantity: $quantity, link: $link || And USER: $user_id";
            Notify::log($msg);
            Response::error('Out of service error', 503);
        }

        // Success
        if (isset($data['status']) && $data['status'] === 'success') {
            return json_decode($response, true);
        }

        Response::error('Something went wrong. Please try again', 500);
    }

    public static function getUserTotalSpent($user_id)
    {
        $conn = Database::getConnection();

        // Get total orders
        $sqlOrders = "SELECT COUNT(*) AS total_orders FROM orders WHERE user_id = ?";
        $stmtOrders = $conn->prepare($sqlOrders);
        if (!$stmtOrders) {
            Response::error('Failed to get total orders', 500);
        }
        $stmtOrders->bind_param("s", $user_id);
        $stmtOrders->execute();
        $ordersResult = $stmtOrders->get_result()->fetch_assoc();
        $totalOrders = intval($ordersResult['total_orders']);

        // Get total spent from successful orders (completed)
        $sqlSpent = "SELECT COALESCE(SUM(amount), 0) AS total_spent FROM orders WHERE user_id = ? AND status = 'completed'";
        $stmtSpent = $conn->prepare($sqlSpent);
        if (!$stmtSpent) {
            Response::error('Failed to get total spent', 500);
        }
        $stmtSpent->bind_param("s", $user_id);
        $stmtSpent->execute();
        $spentResult = $stmtSpent->get_result()->fetch_assoc();
        $totalSpent = floatval($spentResult['total_spent']);

        // Get counts per status
        $statusCounts = [];
        $statuses = ['pending', 'completed', 'partial', 'failed'];

        foreach ($statuses as $status) {
            $sql = "SELECT COUNT(*) AS count FROM orders WHERE user_id = ? AND status = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ss", $user_id, $status);
            $stmt->execute();
            $res = $stmt->get_result()->fetch_assoc();
            $statusCounts[$status] = intval($res['count']);
        }

        Response::success([
            'total_orders' => $totalOrders,
            'total_spent' => $totalSpent,
            'status_breakdown' => $statusCounts
        ]);
    }

    public static function userSearchOrders($user_id, $searchTerm)
    {
        $conn = Database::getConnection();

        $likeTerm = "%" . $searchTerm . "%";

        $query = "
            SELECT id, order_ref, service_id, network, action, region, quality, name, link, amount, status
            FROM orders
            WHERE user_id = ?
            AND (
                order_ref LIKE ? OR
                network LIKE ? OR
                status LIKE ? OR
                action LIKE ?
            )
        ";

        $stmt = $conn->prepare($query);
        if (!$stmt) {
            // Response::error("Prepare failed: " . $conn->error, 500);
            return;
        }

        $stmt->bind_param("issss", $user_id, $likeTerm, $likeTerm, $likeTerm, $likeTerm);
        $stmt->execute();

        $result = $stmt->get_result();
        $orders = [];

        while ($row = $result->fetch_assoc()) {
            $orders[] = $row;
        }

        Response::success($orders);

        
    }

    // ADMIN METHODS
    public static function getAdminOrderStats()
    {
        $conn = Database::getConnection();

        // Total orders
        $stmt = $conn->query("SELECT COUNT(*) AS total_orders FROM orders");
        $totalOrders = intval($stmt->fetch_assoc()['total_orders']);

        // Total completed orders
        $stmt = $conn->query("SELECT COUNT(*) AS total_orders FROM orders WHERE status = 'completed'");
        $totalCompletedOrders = intval($stmt->fetch_assoc()['total_orders']);

        // Total spent (Completed orders)
        $stmt = $conn->query("SELECT COALESCE(SUM(amount), 0) AS total_spent FROM orders WHERE status = 'completed'");
        $totalSpent = floatval($stmt->fetch_assoc()['total_spent']);

        // Status breakdown
        $statuses = ['pending', 'completed','partial', 'failed'];
        $statusCounts = [];
        foreach ($statuses as $status) {
            $stmt = $conn->prepare("SELECT COUNT(*) AS count FROM orders WHERE status = ?");
            $stmt->bind_param("s", $status);
            $stmt->execute();
            $res = $stmt->get_result()->fetch_assoc();
            $statusCounts[$status] = intval($res['count']);
        }

        // Orders today
        $todayQuery = "
            SELECT COUNT(*) AS orders_today, COALESCE(SUM(amount), 0) AS spent_today
            FROM orders
            WHERE date >= UTC_TIMESTAMP() - INTERVAL 1 DAY
            AND status = 'completed'
        ";
        $todayResult = $conn->query($todayQuery)->fetch_assoc();
        $ordersToday = intval($todayResult['orders_today']);
        $spentToday = floatval($todayResult['spent_today']);

        // Orders this week
        $weekQuery = "
            SELECT COUNT(*) AS orders_week, COALESCE(SUM(amount), 0) AS spent_week
            FROM orders
            WHERE date >= UTC_TIMESTAMP() - INTERVAL 7 DAY
            AND status = 'completed'
        ";
        $weekResult = $conn->query($weekQuery)->fetch_assoc();
        $ordersThisWeek = intval($weekResult['orders_week']);
        $spentThisWeek = floatval($weekResult['spent_week']);

        // Orders this month
        $monthQuery = "
            SELECT COUNT(*) AS orders_month, COALESCE(SUM(amount), 0) AS spent_month
            FROM orders
            WHERE date >= UTC_TIMESTAMP() - INTERVAL 30 DAY
            AND status = 'completed'
        ";
        $monthResult = $conn->query($monthQuery)->fetch_assoc();
        $ordersThisMonth = intval($monthResult['orders_month']);
        $spentThisMonth = floatval($monthResult['spent_month']);

        Response::success([
            'total_orders'       => $totalOrders,
            'totalCompletedOrders'       => $totalCompletedOrders,
            'total_spent'        => $totalSpent,
            'status_breakdown'   => $statusCounts,
            'orders_today'       => $ordersToday,
            'spent_today'        => $spentToday,
            'orders_this_week'   => $ordersThisWeek,
            'spent_this_week'    => $spentThisWeek,
            'orders_this_month'  => $ordersThisMonth,
            'spent_this_month'   => $spentThisMonth
        ]);
    }

    public static function getAllOrders() {
        $conn = Database::getConnection();
        $stmt = $conn->prepare("
            SELECT 
                o.id AS order_id, o.order_ref, o.service_id, o.network, o.action, o.region, o.quality, o.name, o.link, o.amount, o.status, o.date,
                u.id AS user_id, u.fname, u.lname, u.email
            FROM 
                orders o
            INNER JOIN 
                users u ON o.user_id = u.id
        ");
        $stmt->execute();
        $result = $stmt->get_result();
        $orders = [];
        
        while ($row = $result->fetch_assoc()) {
            $orders[] = $row;
        }

        // if (empty($orders)) {
        //     Response::error('No orders found', 404);       No need for error when fetching all
        // }

        // Total orders
        $stmtTotal = $conn->query("SELECT COUNT(*) AS total_orders FROM orders");
        $total_orders_count = intval($stmtTotal->fetch_assoc()['total_orders']);

        Response::success([
            'total_orders'     => $orders,
            'total_orders_count' => $total_orders_count
        ]);
    }

    public static function getOrderById($id) {
        $conn = Database::getConnection();
        $stmt = $conn->prepare("
            SELECT 
                o.id AS order_id, o.order_ref, o.service_id, o.network, o.action, o.region, o.quality, o.name, o.link, o.amount, o.status, o.date,
                u.id AS user_id, u.fname, u.lname, u.email
            FROM 
                orders o
            INNER JOIN 
                users u ON o.user_id = u.id
        WHERE o.id = ?");
        $stmt->bind_param("s", $id);
        $stmt->execute();
        if (!$stmt) {
            // Not returning here .... remember to check when using to return error abeg (probably no result check)
            Response::error('Could not get order', 500);
        }

        $result = $stmt->get_result()->fetch_assoc();

        if (!$result) {
            Response::error('Order not found', 404);
        }

        return $result;
    }

    public static function deleteOrder($id) {
        $conn = Database::getConnection();

        // Check if order exists
        $stmt = $conn->prepare("SELECT id FROM orders WHERE id = ?");
        $stmt->bind_param("s", $id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();

        if (!$result) {
            Response::error('Order not found', 404);
        }

        $stmt = $conn->prepare("DELETE FROM orders WHERE id = ?");
        $stmt->bind_param("s", $id);

        if ($stmt->execute()) {
            Response::success("Order deleted successfully.");
        } else {
            Response::error("Failed to delete order.", 500);
        }
    }

    public static function searchOrdersByRef($searchTerm)
    {
        $conn = Database::getConnection();

        $likeTerm = "%" . $searchTerm . "%";

        $query = "
            SELECT 
                o.id AS order_id, o.order_ref, o.service_id, o.network, o.action, o.region, o.quality, o.name, o.link, o.amount, o.status, o.date,
                u.id AS user_id, u.fname, u.lname, u.email
            FROM 
                orders o
            INNER JOIN 
                users u ON o.user_id = u.id
            WHERE (
                o.order_ref LIKE ? OR
                o.network LIKE ? OR
                o.action LIKE ? OR
                o.region LIKE ? OR
                o.quality LIKE ? OR
                o.status LIKE ?
            )
        ";

        $stmt = $conn->prepare($query);
        if (!$stmt) {
            // Response::error("Prepare failed: " . $conn->error, 500);
            return;
        }

        $stmt->bind_param("ssssss", $likeTerm, $likeTerm, $likeTerm, $likeTerm, $likeTerm, $likeTerm);
        $stmt->execute();

        $result = $stmt->get_result();
        $orders = [];

        while ($row = $result->fetch_assoc()) {
            $orders[] = $row;
        }

        Response::success($orders);
    }
}



?>