<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../core/response.php';
require_once __DIR__ . '/../utility/notify.php';

class PaymentService
{
    public static function getUserPayments($user_id, $getResult = false)
    {
        $conn = Database::getConnection();

        // balance
        $userSql = "SELECT balance, ref_balance FROM users WHERE id = ?";
        $userStmt = $conn->prepare($userSql);
        if (!$userStmt) {
            Response::error('Failed to fetch user balance', 500);
        }
        $userStmt->bind_param("s", $user_id);
        $userStmt->execute();
        $userResult = $userStmt->get_result();
        $userData = $userResult->fetch_assoc();
        $balance = $userData['balance'] ?? 0.0;
        $ref_balance = $userData['ref_balance'] ?? 0.0;
        $userStmt->close();

        // payments
        $sql = "SELECT tx_ref, order_ref, method, coin, stock, plan, amount, type, status, date FROM payments WHERE user_id = ?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            Response::error('Failed to get payments', 500);
        }

        $stmt->bind_param("s", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        $payments = [];
        while ($row = $result->fetch_assoc()) {
            $payments[] = $row;
        }

        $stmt->close();

        $response = [
            'balance' => (float)$balance,
            'ref_balance' => (float)$ref_balance,
            'payments' => $payments
        ];

        return $response;
    }

    public static function searchUserPayments($user_id, $searchTerm)
    {
        $conn = Database::getConnection();

        $likeTerm = "%" . $searchTerm . "%";

        $query = "
            SELECT tx_ref, order_ref, method, coin, stock, plan, amount, type, status, date
            FROM payments
            WHERE user_id = ?
            AND (
                tx_ref LIKE ? OR
                method LIKE ? OR
                status LIKE ?
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
        $payments = [];

        while ($row = $result->fetch_assoc()) {
            $payments[] = $row;
        }

        Response::success($payments);
    }


    //Admin methods
    public static function getAllPayments() {
        $conn = Database::getConnection();
        $stmt = $conn->prepare("
            SELECT 
                p.id AS payment_id, p.tx_ref, p.method, p.amount, p.status, p.date,
                u.id AS user_id, u.fname, u.lname, u.email
            FROM 
                payments p
            INNER JOIN 
                users u ON p.user_id = u.id
        WHERE p.method != 'order'");
        $stmt->execute();
        $result = $stmt->get_result();
        $payments = [];
        
        while ($row = $result->fetch_assoc()) {
            $payments[] = $row;
        }

        // if (empty($payments)) {
        //     Response::error('No payments found', 404);     No need for error when fetching all
        // }

        // Total referral earnings
        $stmtTotal = $conn->query("SELECT COUNT(*) AS total_payments FROM payments WHERE method != 'order'");
        $total_payments_count = intval($stmtTotal->fetch_assoc()['total_payments']);

        Response::success([
            'total_payments'     => $payments,
            'total_payments_count' => $total_payments_count
        ]);
    }

    public static function getPaymentByID($id) {
        $conn = Database::getConnection();
        $stmt = $conn->prepare("
            SELECT 
                p.id AS payment_id, p.tx_ref, p.method, p.coin, p.address, p.bank_name, p.account_number, p.amount, p.status, p.date,
                u.id AS user_id, u.fname, u.lname, u.email
            FROM 
                payments p
            INNER JOIN 
                users u ON p.user_id = u.id
        WHERE p.method != 'order' AND p.id = ?");
        $stmt->bind_param("s", $id);
        $stmt->execute();
        if (!$stmt) {
            // Not returning here .... remember to check when using to return error abeg (probably no result check)
            Response::error('Could not get payment', 500);
        }

        $result = $stmt->get_result()->fetch_assoc();

        if (!$result) {
            Response::error('Payment not found', 404);
        }

        return $result;
    }
    
    public static function searchPaymentsByRef($searchTerm)
    {

    $conn = Database::getConnection();

        $likeTerm = "%" . $searchTerm . "%";

        $query = "
            SELECT 
                p.id AS payment_id, p.tx_ref, p.method, p.amount, p.status, p.date,
                u.id AS user_id, u.fname, u.lname, u.email
            FROM 
                payments p
            INNER JOIN 
                users u ON p.user_id = u.id
            WHERE (
                p.tx_ref LIKE ? OR
                p.method LIKE ? OR
                p.status LIKE ?
            )
            AND p.method != 'order'
        ";

        $stmt = $conn->prepare($query);
        if (!$stmt) {
            // Response::error("Prepare failed: " . $conn->error, 500);
            return;
        }

        $stmt->bind_param("sss", $likeTerm, $likeTerm, $likeTerm);
        $stmt->execute();

        $result = $stmt->get_result();
        $payments = [];

        while ($row = $result->fetch_assoc()) {
            $payments[] = $row;
        }

        Response::success($payments);
    }
}



?>