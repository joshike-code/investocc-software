<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../core/response.php';
require_once __DIR__ . '/../services/ReferralService.php';
require_once __DIR__ . '/../core/jwt_utils.php';
require_once __DIR__ . '/../services/PlatformService.php';

class SafehavenService
{

    private static function getClientData(): array
    {
        $keys = require __DIR__ . '/../config/keys.php';
        return $keys['safehaven'];
    }

    private static function getAccessToken(): string
    {
        $safehaven = self::getClientData();
        $clientId = $safehaven['client_id'];

        $now = time();
        $payload = [
            'iss' => 'https://sudo.africa',
            'sub' => $clientId,
            'aud' => $safehaven['api_host'],
            'iat' => $now,
            'exp' => $now + 3600
        ];

        $secret_key = $safehaven['secret_key'];
        $algorithm = "RS256";
        $jwt = generate_jwt($payload, $secret_key, $algorithm);

        $res = self::httpPost($safehaven['api_host'].'/oauth2/token', [
            'client_id' => $clientId,
            'client_assertion' => $jwt,
            'client_assertion_type' => 'urn:ietf:params:oauth:client-assertion-type:jwt-bearer',
            'grant_type' => 'client_credentials'
        ]);

        if (!isset($res['access_token'])) {
            Response::error('Failed to get access token from Safehaven', 500);
        }

        return $res['access_token'];
    }

    public static function createVirtualAccount(string $user_id, array $input)
    {

        $conn = Database::getConnection();
        $tx_ref = uniqid("tx_");
        $amount = $input['amount'];
        $date = gmdate('Y-m-d H:i:s');

        $stmt = $conn->prepare("INSERT INTO payments (user_id, amount, tx_ref, method, status, date)
                                VALUES (?, ?, ?, 'safehaven', 'pending', ?)");
        $stmt->bind_param("idss", $user_id, $amount, $tx_ref, $date);
        if (!$stmt->execute()) {
            Response::error('Could not create payment', 500);
        }

        $accessToken = self::getAccessToken();
        $keys = require __DIR__ . '/../config/keys.php';
        $clientId = $keys['safehaven']['client_id'];

        $payload = [
            'validFor' => 1800,
            'callbackUrl' => $keys['safehaven']['webhook'],
            'settlementAccount' => [
                'bankCode' => $keys['safehaven']['bank_code'],  //090286
                'accountNumber' => $keys['safehaven']['account_number']
            ],
            'amountControl' => 'OverPayment',
            'amount' => $amount,
            'externalReference' => $tx_ref
        ];

        $res = self::httpPost($keys['safehaven']['api_host'].'/virtual-accounts', $payload, [
            'Authorization: Bearer ' . $accessToken,
            'ClientID: ' . $clientId
        ]);

        if (isset($res['statusCode']) && $res['statusCode'] === 200) {
            $percentage_add = PlatformService::getSetting('safehavenpay_percent', 0.5);
            $payamount = $res['data']['amount'];
            $additionalamount = ($percentage_add / 100) * $payamount;
            $amountWithFee = $payamount + $additionalamount;
            Response::success([
                'amount' => $amountWithFee,
                'accountNumber' => $res['data']['accountNumber'],
                'accountName' => $res['data']['accountName'],
                'expiryDate' => $res['data']['expiryDate'],
            ]);
        }

        error_log("ERROR " . json_encode($res));
        Response::error('Failed to create virtual account', 500);
    }

    public static function handleSafehavenWebhook()
    {
        $payload = json_decode(file_get_contents("php://input"), true);
        // file_put_contents(__DIR__ . '/../utility/log/safehaven.log', json_encode($payload) . "[".date("Y-m-d H:i:s")."]", FILE_APPEND);

        if (!$payload || !isset($payload['data']['status'])) {
            Response::error('Invalid data', 400);
        }

        if (strtolower($payload['data']['status']) !== 'completed') {
            Response::success("Ignored non-success event");
        }

        // Process payment
        $tx_ref = $payload['data']['externalReference'];
        $status = strtolower($payload['data']['status']);
        $date = gmdate('Y-m-d H:i:s');

        $amountWithFee = (float)$payload['data']['amount'];
        $percentage_add = PlatformService::getSetting('safehavenpay_percent', 0.5);
        $amount = $amountWithFee / (1 + ($percentage_add / 100)); //Raw amount without added fee

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

        $status = 'success';
        $conn->begin_transaction();

        try {
            // Update payment status
            $update = $conn->prepare("UPDATE payments SET amount=?, status = ?, date = ? WHERE id = ?");
            $update->bind_param("dssi", $amount, $status, $date, $payment['id']);
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

    private static function httpPost(string $url, array $data, array $headers = [])
    {
        $headers[] = 'Content-Type: application/json';
        $ch = curl_init($url);

        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => $headers,
        ]);

        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            Response::error('Curl error: ' . curl_error($ch), 500);
        }

        curl_close($ch);
        return json_decode($response, true);
    }
}