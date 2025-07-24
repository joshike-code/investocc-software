<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../core/response.php';
require_once __DIR__ . '/../services/PaymentService.php';
require_once __DIR__ . '/../services/ReferralService.php';

class WithdrawService
{

    public static function getPendingWithdrawals() {
        $conn = Database::getConnection();
        $stmt = $conn->prepare("
            SELECT 
                w.id AS withdrawal_id, w.payment_ref, w.amount, w.coin, w.bank_name, w.date,
                u.id AS user_id, u.fname, u.lname, u.email
            FROM 
                pending_withdrawals w
            INNER JOIN 
                users u ON w.user_id = u.id
        ");
        $stmt->execute();
        $result = $stmt->get_result();
        $pending_withdrawals = [];
        
        while ($row = $result->fetch_assoc()) {
            $pending_withdrawals[] = $row;
        }

        // Total pending withdrawals count
        $stmtTotal = $conn->query("SELECT COUNT(*) AS pending_withdrawals_count FROM pending_withdrawals");
        $pending_withdrawals_count = intval($stmtTotal->fetch_assoc()['pending_withdrawals_count']);

        Response::success([
            'pending_withdrawals'     => $pending_withdrawals,
            'pending_withdrawals_count' => $pending_withdrawals_count
        ]);
    }

    public static function getPendingWithdrawalById($id) {
        $conn = Database::getConnection();
        $stmt = $conn->prepare("
            SELECT 
                w.id AS withdrawal_id, w.payment_ref, w.amount, w.coin, w.network, w.address, w.bank_name, w.account_name, w.account_number, w.date,
                u.id AS user_id, u.fname, u.lname, u.email
            FROM 
                pending_withdrawals w
            INNER JOIN 
                users u ON w.user_id = u.id
        WHERE w.id = ?");
        $stmt->bind_param("s", $id);
        $stmt->execute();
        if (!$stmt) {
            Response::error('Could not get pending withdrawals', 500);
        }

        $result = $stmt->get_result()->fetch_assoc();

        if (!$result) {
            Response::error('Pending withdrawals not found', 404);
        }

        return $result;
    }

    public static function createWithdrawal(int $user_id, array $input, string $method) {
        $conn = Database::getConnection();
    
        $amount = $input['amount'] ?? null;
        $bank_name = $input['bank_name'] ?? null;
        $account_number = $input['account_number'] ?? null;
        $account_name = $input['account_name'] ?? null;
        $coin = $input['coin'] ?? null;
        $network = $input['network'] ?? null;
        $address = $input['address'] ?? null;

        $tx_ref = uniqid("tx_");
        $date = gmdate('Y-m-d H:i:s');
    
        // Validate amount
        if ($amount <= 0) {
          Response::error('Invalid amount provided.', 400);
        }

        // Check if pending withdrawals exists for user
        $check = $conn->prepare("SELECT user_id FROM pending_withdrawals WHERE user_id = ?");
        $check->bind_param("s", $user_id);
        $check->execute();
        $pending_withdrawal = $check->get_result()->fetch_assoc();
        if($pending_withdrawal) {
            Response::error('Pending withdrawal exists for user', 400);
        };

        // Fetch user balance
        $stmt = $conn->prepare("SELECT balance FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();

        if (!$user) {
            Response::error('User not found', 404);
        }

        // Ensure the balance is a float and check if the user has enough balance
        $balance = (float) $user['balance'];
        if ($balance < $amount) {
            Response::error('Insufficient balance', 402);
        }
    
        $conn->begin_transaction();

        try {
            $stmt = $conn->prepare("INSERT INTO pending_withdrawals (user_id, payment_ref, amount, coin, network, address, bank_name, account_name, account_number, date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            if (!$stmt) {
                throw new Exception('Prepare failed');
            }
            $stmt->bind_param("ssdsssssss", $user_id, $tx_ref, $amount, $coin, $network, $address, $bank_name, $account_name, $account_number, $date);
            if (!$stmt->execute()) {
                throw new Exception('Failed to create pending withdrawal');
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
            $insert = $conn->prepare("INSERT INTO payments (user_id, amount, tx_ref, method, coin, address, bank_name, account_number, type, status, date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $method = "withdrawal";
            $type = "debit";
            $status = "pending";
            $insert->bind_param("sdsssssssss", $user_id, $amount, $tx_ref, $method, $coin, $address, $bank_name, $account_number, $type, $status, $date);
            $insert->execute();

            // Commit the transaction
            $conn->commit();

            // Fetch and return payment data
            $payments = PaymentService::getUserPayments($user_id);
            return Response::success([
                'payments' => $payments,
                'balance' => $updatedBalance
            ]);
        } catch (Exception $e) {
            // Rollback the transaction in case of an error
            $conn->rollback();
            error_log("ERROR " . $e);
            Response::error('Something went wrong. Please try again', 500);
        }
    }

    public static function approveWithdrawal($id) {
        $conn = Database::getConnection();

        // Find pending payment
        $check = $conn->prepare("SELECT user_id, amount, payment_ref FROM pending_withdrawals WHERE id = ?");
        $check->bind_param("s", $id);
        if (!$check->execute()) {
            Response::error('Failed to fetch pending withdrawal', 500);
        }
        $pending_withdrawal = $check->get_result()->fetch_assoc();
        if(!$pending_withdrawal) {
            Response::error('Pending withdrawal does not exist', 400);
        };

        $payment_ref = $pending_withdrawal['payment_ref'];

        $conn->begin_transaction();

        try {
            //Delete pending withdrawal
            $stmt = $conn->prepare("DELETE FROM pending_withdrawals WHERE id = ?");
            $stmt->bind_param("s", $id);

            if (!$stmt->execute()) {
                throw new Exception('Failed to delete pending withdrawal');
            };

            // Update payment status
            $status = 'success';
            $stmt = $conn->prepare("UPDATE payments SET status = ? WHERE tx_ref = ?");
            $stmt->bind_param("ss", $status, $payment_ref);

            if(!$stmt->execute()) {
                throw new Exception('Status update failed');
            }

            // Commit the transaction
            $conn->commit();

            Response::success('Withdrawal approved');

        } catch (Exception $e) {
            // Rollback the transaction in case of an error
            $conn->rollback();
            error_log("ERROR " . $e);
            Response::error("An error occured: $e", 500);
        }
    }

    public static function declineWithdrawal($id) {
        $conn = Database::getConnection();

        // Find pending payment
        $check = $conn->prepare("SELECT user_id, amount, payment_ref FROM pending_withdrawals WHERE id = ?");
        $check->bind_param("s", $id);
        if (!$check->execute()) {
            Response::error('Failed to fetch pending withdrawal', 500);
        }
        $pending_withdrawal = $check->get_result()->fetch_assoc();
        if(!$pending_withdrawal) {
            Response::error('Pending withdrawal does not exist', 400);
        };

        $amount = $pending_withdrawal['amount'];
        $user_id = $pending_withdrawal['user_id'];
        $payment_ref = $pending_withdrawal['payment_ref'];

        $conn->begin_transaction();

        try {
            // Update user balance 
            $update = $conn->prepare("UPDATE users SET balance = balance + ? WHERE id = ?");
            $update->bind_param("di", $amount, $user_id);

            if (!$update->execute()) {
                throw new Exception('Failed to update balance');
            }

            //Delete pending withdrawal
            $stmt = $conn->prepare("DELETE FROM pending_withdrawals WHERE id = ?");
            $stmt->bind_param("s", $id);

            if (!$stmt->execute()) {
                throw new Exception('Failed to delete pending withdrawal');
            };

            // Update payment status
            $status = 'cancelled';
            $stmt = $conn->prepare("UPDATE payments SET status = ? WHERE tx_ref = ?");
            $stmt->bind_param("ss", $status, $payment_ref);

            if(!$stmt->execute()) {
                throw new Exception('Status update failed');
            }

            // Commit the transaction
            $conn->commit();

            Response::success('Withdrawal declined');

        } catch (Exception $e) {
            // Rollback the transaction in case of an error
            $conn->rollback();
            error_log("ERROR " . $e);
            Response::error("An error occured: $e", 500);
        }
    }
}



?>