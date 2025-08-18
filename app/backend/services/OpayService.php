<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../core/response.php';
require_once __DIR__ . '/../services/ReferralService.php';

class OpayService
{

    public static function createOpayPayment(int $user_id, array $input)
    {
        $amount = $input['amount'] ?? null;
        $email = $input['email'] ?? '';
        if (!$amount || $amount <= 0) {
            Response::error('Invalid amount', 400);
        }

        $conn = Database::getConnection();
        $tx_ref = uniqid("tx_");
        $date = gmdate('Y-m-d H:i:s');

        $stmt = $conn->prepare("INSERT INTO payments (user_id, amount, tx_ref, method, status, date) VALUES (?, ?, ?, 'opay', 'pending', ?)");
        $stmt->bind_param("idss", $user_id, $amount, $tx_ref, $date);
        if (!$stmt->execute()) {
            Response::error('Could not create payment', 500);
        }

        $keys = require(__DIR__ . '/../config/keys.php');
        $publicKey = $keys['opay']['public_key'];
        $merchantId = $keys['opay']['merchant_id'];
        $webhook = $keys['opay']['webhook'];
        $platformName = $keys['platform']['name'];
        $platformUrl = $keys['platform']['url'];

        $payload = [
            'country' => 'NG',
            'reference' => $tx_ref,
            'amount' => [
                'total' => $amount * 100,
                'currency' => 'NGN',
            ],
            'returnUrl' => $platformUrl,
            'cancelUrl' => $platformUrl,
            'callbackUrl' => $webhook,
            'expireAt' => 300,
            'userInfo' => [
                'userEmail' => $email,
                'userId' => $user_id
            ],
            'product' => [
                'name' => $platformName,
                'description' => 'Top up'
            ]
        ];

        $headers = [
            'Authorization: Bearer ' . $publicKey,
            'MerchantId: ' . $merchantId,
            'Content-Type: application/json',
            'Accept: application/json',
        ];

        $ch = curl_init('https://sandboxapi.opaycheckout.com/api/v1/international/cashier/create');
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $res = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);

        if ($err) {
            Response::error("Curl error: $err", 500);
        }

        $resData = json_decode($res, true);
        if ($resData['code'] !== '00000') {
            Response::error("OPay error: " . $resData['message'], 500);
        }

        $cashierUrl = $resData['data']['cashierUrl'];
        Response::success(['url' => $cashierUrl, 'tx_ref' => $tx_ref]);
    }

    public static function handleOpayWebhook()
    {
        $input = file_get_contents("php://input");
        $data = json_decode($input, true);

        if (!$data || ($data['status'] ?? '') !== 'SUCCESS') {
            Response::error('Invalid webhook data', 400);
            return;
        }

        $tx_ref = $data['reference'] ?? null;
        $amount = ($data['amount'] ?? 0) / 100;
        $transaction_id = $data['transactionId'] ?? null;
        $date = gmdate('Y-m-d H:i:s');

        if (!$tx_ref || !$transaction_id) {
            Response::error('Missing required data', 400);
            return;
        }

        $conn = Database::getConnection();

        $stmt = $conn->prepare("SELECT id, user_id, amount, status FROM payments WHERE tx_ref = ? LIMIT 1");
        $stmt->bind_param("s", $tx_ref);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            Response::success('Transaction not found');
            return;
        }

        $payment = $result->fetch_assoc();
        $stmt->close();

        if ($payment['status'] !== 'pending' && $payment['status'] !== 'cancelled') {
            Response::success('Transaction already processed');
            return;
        }

        $conn->begin_transaction();

        try {
            $update = $conn->prepare("UPDATE payments SET status = 'success', date = ? WHERE id = ?");
            $update->bind_param("si", $date, $payment['id']);
            $update->execute();
            $update->close();

            $credit = $conn->prepare("UPDATE users SET balance = balance + ? WHERE id = ?");
            $credit->bind_param("di", $payment['amount'], $payment['user_id']);
            $credit->execute();
            $credit->close();

            ReferralService::handleReferralBonus($payment['user_id'], $payment['amount'], $tx_ref);

            $conn->commit();
            Response::success('Webhook processed');
        } catch (Exception $e) {
            $conn->rollback();
            Response::error("Error: " . $e->getMessage(), 500);
        }
    }
}