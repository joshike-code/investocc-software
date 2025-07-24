<?php  
ini_set('display_errors', 1); 
ini_set('display_startup_errors', 1); 
error_reporting(E_ALL);

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../core/response.php';
require_once __DIR__ . '/../services/ReferralService.php';

class PaystackService {
    public static function createPaystackPayment(int $user_id, array $input) {
        $amount = $input['amount'] ?? null;
        if (!$amount || $amount <= 0) {
            Response::error('Invalid amount', 400);
        }

        $conn = Database::getConnection();
        $tx_ref = uniqid("tx_");
        $date = gmdate('Y-m-d H:i:s');

        $stmt = $conn->prepare("INSERT INTO payments (user_id, amount, tx_ref, method, status, date) VALUES (?, ?, ?, 'paystack', 'pending', ?)");
        $stmt->bind_param("idss", $user_id, $amount, $tx_ref, $date);
        if (!$stmt->execute()) {
            Response::error('Could not create payment', 500);
        }

        $keys = require(__DIR__ . '/../config/keys.php');
        $paystackKey = $keys['paystack']['public_key'];

        Response::success([
            'tx_ref' => $tx_ref,
            'amount' => $amount,
            'public_key' => $paystackKey
        ]);
    }

    public static function updatePaystackTransaction($tx_ref) {
        // This method updates cancelled payments only

        $conn = Database::getConnection();

        //Fetch pending payment
        $sql = "SELECT id, user_id, amount, status FROM payments WHERE tx_ref = ? LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $tx_ref);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            Response::error("Payment record not found", 404);
        }

        $payment = $result->fetch_assoc();
        $stmt->close();

        if ($payment['status'] !== 'pending') {
            Response::success("Payment already processed");
        };

        // Update payment status
        $status = 'cancelled';
        $date = gmdate('Y-m-d H:i:s');

        $stmt = $conn->prepare("UPDATE payments SET status = ?, date = ? WHERE tx_ref = ?");
        $stmt->bind_param("sss", $status, $date, $tx_ref);

        if ($stmt->execute()) {
            Response::success('Payment cancelled');
            $stmt->close();
        } else {
            Response::error('Failed to update wallet', 500);
        }
    }

    public static function handlePaystackWebhook() {
        $input = file_get_contents("php://input");
        $payload = json_decode($input, true);

        if (!isset($payload['event']) || $payload['event'] !== 'charge.success') {
            Response::success("Ignored non-success event");
        }

        $data = $payload['data'] ?? null;
        if (!$data || !isset($data['reference'], $data['amount'])) {
            Response::error("Invalid webhook payload", 400);
        }

        $tx_ref = $data['reference'];
        $amount = $data['amount'] / 100; // Convert from kobo
        $status = 'success';
        $date = gmdate('Y-m-d H:i:s');

        $conn = Database::getConnection();

        // Check if tx_ref exists
        $stmt = $conn->prepare("SELECT id, user_id, amount, status FROM payments WHERE tx_ref = ? LIMIT 1");
        $stmt->bind_param("s", $tx_ref);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            Response::success("Transaction not recognized: $tx_ref");
        }

        $payment = $result->fetch_assoc();
        $stmt->close();

        if ($payment['status'] !== 'pending' && $payment['status'] !== 'cancelled') {
            Response::success("Transaction already processed");
        }

        $conn->begin_transaction();

        try {
            // Update payment status
            $update = $conn->prepare("UPDATE payments SET status = ?, date = ? WHERE id = ?");
            $update->bind_param("ssi", $status, $date, $payment['id']);
            $update->execute();
            $update->close();

            // Credit user balance
            $credit = $conn->prepare("UPDATE users SET balance = balance + ? WHERE id = ?");
            $credit->bind_param("di", $amount, $payment['user_id']);
            $credit->execute();
            $credit->close();

            // Handle referral bonus
            ReferralService::handleReferralBonus($payment['user_id'], $amount, $tx_ref);

            $conn->commit();
            Response::success("Webhook processed: user credited");
        } catch (Exception $e) {
            $conn->rollback();
            Response::error("Webhook error: " . $e->getMessage(), 500);
        }
    }
}