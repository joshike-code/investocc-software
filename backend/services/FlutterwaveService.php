<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../core/response.php';
require_once __DIR__ . '/../services/ReferralService.php';

class FlutterwaveService
{
    public static function createFlutterwavePayment(int $user_id, array $input) {
        $amount = $input['amount'] ?? null;

        $conn = Database::getConnection();
        $tx_ref = uniqid("tx_");
        $date = gmdate('Y-m-d H:i:s');

        $stmt = $conn->prepare("INSERT INTO payments (user_id, amount, tx_ref, method, status, date)
                                VALUES (?, ?, ?, 'flutterwave', 'pending', ?)");
        $stmt->bind_param("idss", $user_id, $amount, $tx_ref, $date);
        if (!$stmt->execute()) {
            Response::error('Could not create payment', 500);
        }
        $keys = require(__DIR__ . '/../config/keys.php');

        return Response::success([
            'tx_ref' => $tx_ref,
            'amount' => $amount,
            'public_key' => $keys['flutterwave']['public_key']
        ]);
    }

    public static function verifyFlutterwaveTransaction(string $transaction_id)
    {
        $conn = Database::getConnection();

        $keys = require(__DIR__ . '/../config/keys.php');
        $flutterwaveSecretKey = $keys['flutterwave']['secret_key'];
        $verifyUrl = "https://api.flutterwave.com/v3/transactions/$transaction_id/verify";

        $ch = curl_init($verifyUrl);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                "Authorization: Bearer $flutterwaveSecretKey",
                "Content-Type: application/json"
            ]
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            Response::error('Verification failed from Flutterwave', 500);
        }

        $data = json_decode($response, true);
        if (!$data || !isset($data['data'])) {
            Response::error('Invalid response from Flutterwave', 500);
        }

        $tx_ref = $data['data']['tx_ref'];
        $amount = $data['data']['amount'];
        $status = strtolower($data['data']['status']);
        $date = gmdate('Y-m-d H:i:s');

        // My ENUMS
        if($status === 'pending') {
            $status = 'pending';
        } elseif($status === 'successful') {
            $status = 'success';
        } else {
            $status = 'failed';
        };

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
        }

        // Compare amount
        if ((float)$amount < (float)$payment['amount']) {
            Response::error("Amount mismatch", 400);
        }

        $conn->begin_transaction();

        try {
            // Update payment status
            $updateSql = "UPDATE payments SET status = ?, date = ?, WHERE id = ?";
            $updateStmt = $conn->prepare($updateSql);
            $updateStmt->bind_param("ssi", $status, $date, $payment['id']);
            $updateStmt->execute();
            $updateStmt->close();

            // Update user balance
            if ($status === 'successful') {
                $updateBalanceSql = "UPDATE users SET balance = balance + ? WHERE id = ?";
                $balanceStmt = $conn->prepare($updateBalanceSql);
                $balanceStmt->bind_param("ds", $payment['amount'], $payment['user_id']);
                $balanceStmt->execute();
                $balanceStmt->close();

                ReferralService::handleReferralBonus($payment['user_id'], $payment['amount'], $tx_ref);
            }

            $conn->commit();
            Response::success("Payment verified and balance updated");

        } catch (Exception $e) {
            $conn->rollback();
            Response::error("Transaction failed: " . $e->getMessage(), 500);
        }
    }

    public static function handleFlutterwaveWebhook()
    {
        $payload = json_decode(file_get_contents("php://input"), true);
        $conn = Database::getConnection();

        if (
            !isset($payload['data']['status'], $payload['data']['tx_ref'], $payload['data']['amount']) ||
            strtolower($payload['data']['status']) !== 'successful'
        ) {
            Response::success("Webhook received but status not successful");
        }

        $tx_ref = $payload['data']['tx_ref'];
        $amount = (float)$payload['data']['amount'];
        $status = strtolower($payload['data']['status']);
        $date = gmdate('Y-m-d H:i:s');

        $stmt = $conn->prepare("SELECT id, user_id, amount, status FROM payments WHERE tx_ref = ? LIMIT 1");
        $stmt->bind_param("s", $tx_ref);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            Response::success("Payment not found for tx_ref: $tx_ref");
        }

        $payment = $result->fetch_assoc();
        $stmt->close();

        if ($payment['status'] !== 'pending') {
            Response::success("Payment already processed");
        }

        // amount match
        // if ($amount < (float)$payment['amount']) {
        //     Response::success("Amount mismatch on webhook");
        // }

        $conn->begin_transaction();

        try {
            // Update payment record
            $updatePayment = $conn->prepare("UPDATE payments SET status = ?, date = ?, WHERE id = ?");
            $updatePayment->bind_param("ssi", $status, $date, $payment['id']);
            $updatePayment->execute();
            $updatePayment->close();

            // Credit user balance
            $creditBalance = $conn->prepare("UPDATE users SET balance = balance + ? WHERE id = ?");
            $creditBalance->bind_param("ds", $payment['amount'], $payment['user_id']);
            $creditBalance->execute();
            $creditBalance->close();

            $conn->commit();
            Response::success("Webhook: Payment processed successfully");

            ReferralService::handleReferralBonus($payment['user_id'], $payment['amount'], $tx_ref);

        } catch (Exception $e) {
            $conn->rollback();
            Response::error("Webhook error: " . $e->getMessage(), 500);
        }
    }
        
}



?>