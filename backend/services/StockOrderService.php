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

class StockOrderService
{
    public static function getUserOrders($user_id, $getResult = false)
    {
        $conn = Database::getConnection();

        $sql = "SELECT order_ref, stock, buy_price, shares, amount, status, date 
                FROM stock_orders 
                WHERE user_id = ? AND amount != '0.00'";
        
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

    public static function updateOrder(string $user_id, array $input)
    {
        Response::error('Updating existing order..', 500);
    }

    public static function createBuyOrder(string $user_id, array $input)
    {
        $conn = Database::getConnection();

        $amount = $input['amount'];
        $stock = $input['stock'];

        if($amount <= 0) {
            Response::error('Input amount to buy', 422);
        }

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

        // Find stock price
        $check = $conn->prepare("SELECT id, price FROM stocks WHERE trade_name = ?");
        $check->bind_param("s", $stock);
        $check->execute();
        
        $result = $check->get_result()->fetch_assoc();
        if (!$result) {
            Response::error('Stock does not exist', 404);
        }

        $stockPrice = $result['price'];

        $percentageCommission = PlatformService::getSetting('stock_commission', 0);
        $commission = ($percentageCommission / 100) * $amount;
        $orderAmount = $amount - $commission;
        $orderShares =  $orderAmount / $stockPrice;

        $order_ref = '';
        $status = 'bought';
        $date = gmdate('Y-m-d H:i:s');

        // Check if stock order exists
        $check = $conn->prepare("SELECT id, amount, order_ref FROM stock_orders WHERE stock = ? AND user_id = ?");
        $check->bind_param("ss", $stock, $user_id);
        $check->execute();
        $existingOrder = $check->get_result()->fetch_assoc();

        $conn->begin_transaction();

        try {
            if ($existingOrder) {
                // Updating Existing stock order
                $currentOrderAmount = $existingOrder['amount'];
                $currentOrderId = $existingOrder['id'];
                $order_ref = $existingOrder['order_ref'];
                $newOrderAmount = $currentOrderAmount + $orderAmount;
                $orderShares =  $newOrderAmount / $stockPrice;

                $stmt = $conn->prepare("UPDATE stock_orders SET amount = ?, shares =? WHERE id = ?");
                if (!$stmt) {
                    throw new Exception('Prepare failed');
                }
                $stmt->bind_param("dds", $newOrderAmount, $orderShares, $currentOrderId);
                if (!$stmt->execute()) {
                    throw new Exception('Failed to store order');
                }
                
            } else {
                // Inserting New stock order
                $order_ref = uniqid("od_");
                $stmt = $conn->prepare("INSERT INTO stock_orders (user_id, order_ref, stock, buy_price, shares, amount, commission, status, date) VALUES (?, ?, ?, ?, ?, ?, ?, ?,?)");
                if (!$stmt) {
                    throw new Exception('Prepare failed');
                }

                $stmt->bind_param("sssddddss", $user_id, $order_ref, $stock, $stockPrice, $orderShares, $orderAmount, $commission, $status, $date);

                if (!$stmt->execute()) {
                    throw new Exception('Failed to store order');
                }
            }

            // Deduct user balance 
            $new_balance = $balance - $amount;
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
            $insert = $conn->prepare("INSERT INTO payments (user_id, amount, order_ref, method, stock, type, status, date) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $method = "stockorder";
            $type = "debit";
            $status = "success";
            $date = gmdate('Y-m-d H:i:s');
            $insert->bind_param("sdssssss", $user_id, $amount, $order_ref, $method, $stock, $type, $status, $date);
            $insert->execute();

            // Commit the transaction
            $conn->commit();

            // Fetch and return orders
            $orders = StockOrderService::getUserOrders($user_id, true);
            return Response::success([
                'orders' => $orders,
                'balance' => $updatedBalance
            ]);
        } catch (Exception $e) {
            // Rollback the transaction in case of an error
            $conn->rollback();
            Response::error('Something went wrong. Please try again', 500);
        }
    }

    public static function createSellOrder(string $user_id, array $input)
    {
        $conn = Database::getConnection();

        $sellShares = $input['shares'];
        $stock = $input['stock'];
        if($sellShares <= 0) {
            Response::error('Input shares to sell', 422);
        }

       // Check if stock order exists
        $check = $conn->prepare("SELECT id, amount, shares, order_ref FROM stock_orders WHERE stock = ? AND user_id = ?");
        $check->bind_param("ss", $stock, $user_id);
        $check->execute();
        $order = $check->get_result()->fetch_assoc();
        if(!$order) {
            Response::error('Stock Order does not exist', 404);
        };

        $currentShares = $order['shares'];
        $currentOrderId = $order['id'];
        $order_ref = $order['order_ref'];
        if($sellShares > $currentShares) {
            Response::error('Not enough shares', 402);
        }

        // Find stock price
        $check = $conn->prepare("SELECT id, price FROM stocks WHERE trade_name = ?");
        $check->bind_param("s", $stock);
        $check->execute();
        
        $result = $check->get_result()->fetch_assoc();
        if (!$result) {
            Response::error('Stock does not exist', 404);
        }
        $stockPrice = $result['price'];

        $remainingShares = $currentShares - $sellShares;
        $remainingAmount = $remainingShares * $stockPrice;

        $percentageCommission = PlatformService::getSetting('stock_commission', 0);
        $sellAmount = $sellShares * $stockPrice;
        $commission = ($percentageCommission / 100) * $sellAmount;
        $orderAmount = $sellAmount - $commission;

        $conn->begin_transaction();

        try {
            $stmt = $conn->prepare("UPDATE stock_orders SET amount = ?, shares =? WHERE id = ?");
            if (!$stmt) {
                throw new Exception('Prepare failed');
            }
            $stmt->bind_param("dds", $remainingAmount, $remainingShares, $currentOrderId);
            if (!$stmt->execute()) {
                throw new Exception('Failed to update order');
            }

            // Update user balance 
            $update = $conn->prepare("UPDATE users SET balance = balance + ? WHERE id = ?");
            $update->bind_param("di", $orderAmount, $user_id);

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
            $insert = $conn->prepare("INSERT INTO payments (user_id, amount, order_ref, method, stock, type, status, date) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $method = "stockorder";
            $type = "credit";
            $status = "success";
            $date = gmdate('Y-m-d H:i:s');
            $insert->bind_param("sdssssss", $user_id, $orderAmount, $order_ref, $method, $stock, $type, $status, $date);
            $insert->execute();

            // Commit the transaction
            $conn->commit();

            // Fetch and return orders
            $orders = StockOrderService::getUserOrders($user_id, true);
            return Response::success([
                'orders' => $orders,
                'balance' => $updatedBalance
            ]);
        } catch (Exception $e) {
            // Rollback the transaction in case of an error
            $conn->rollback();
            Response::error('Something went wrong. Please try again', 500);
        }
    }

    public static function userSearchOrders($user_id, $searchTerm)
    {
        $conn = Database::getConnection();

        $likeTerm = "%" . $searchTerm . "%";

        $query = "
            SELECT id, order_ref, stock, buy_price, shares, amount, status, date 
            FROM stock_orders
            WHERE user_id = ?
            AND (
                order_ref LIKE ? OR
                stock LIKE ? OR
                status LIKE ? OR
                date LIKE ?
            )
            AND amount != '0.00'
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
    public static function getAllOrders() {
        $conn = Database::getConnection();
        $stmt = $conn->prepare("
            SELECT 
                o.id AS order_id, o.order_ref, o.stock, o.buy_price, o.shares, o.amount, o.status, o.date,
                u.id AS user_id, u.fname, u.lname, u.email
            FROM 
                stock_orders o
            INNER JOIN 
                users u ON o.user_id = u.id
            WHERE
                amount != '0.00'
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
        $stmtTotal = $conn->query("SELECT COUNT(*) AS total_orders FROM stock_orders WHERE amount != '0.00'");
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
                o.id AS order_id, o.order_ref, o.stock, o.buy_price, o.shares, o.amount, o.status, o.date,
                u.id AS user_id, u.fname, u.lname, u.email
            FROM 
                stock_orders o
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
        $stmt = $conn->prepare("SELECT id FROM stock_orders WHERE id = ?");
        $stmt->bind_param("s", $id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();

        if (!$result) {
            Response::error('Order not found', 404);
        }

        $stmt = $conn->prepare("DELETE FROM stock_orders WHERE id = ?");
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
                o.id AS order_id, o.order_ref, o.stock, o.buy_price, o.shares, o.amount, o.status, o.date,
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
            AND
                amount != '0.00'
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