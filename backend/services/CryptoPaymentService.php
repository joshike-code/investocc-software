<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../core/response.php';
require_once __DIR__ . '/../services/PaymentWalletsService.php';
require_once __DIR__ . '/../services/ReferralService.php';

class CryptoPaymentService
{
    public static function createCryptoPayment(int $user_id, array $input) {
        $conn = Database::getConnection();
    
        $amount = $input['amount'] ?? null;
        $address = $input['address'] ?? null;
        $coin = $input['coin'] ?? null;
        $coin = strtolower($coin);
    
        // Validate amount
        if ($amount <= 0) {
          Response::error('Invalid amount provided.', 400);
        }
    
        // Validate that coin + address exists in allowed wallets
        $wallets = PaymentWalletsService::getAllWallets(true);
        $valid = false;

    
        if (is_array($wallets)) {
            foreach ($wallets as $wallet) {
                $walletCoin = strtolower($wallet['coin'] ?? '');
                $walletAddress = trim($wallet['address'] ?? '');
        
                if ($walletCoin === $coin && $walletAddress === $address) {
                    $valid = true;
                    break;
                }
            }
        }
    
        if (!$valid) {
          Response::error('Invalid coin or address. Please use one of the supported wallet addresses.', 400);
        }
    
        $tx_ref = uniqid("tx_");
        $date = gmdate('Y-m-d H:i:s');
    
        $stmt = $conn->prepare("INSERT INTO payments (user_id, amount, tx_ref, method, status, address, coin, date)
                                VALUES (?, ?, ?, 'crypto', 'pending', ?, ?, ?)");
    
        if (!$stmt) {
          Response::error('Crypto payment failed', 500);
        }
    
        $stmt->bind_param("idssss", $user_id, $amount, $tx_ref, $address, $coin, $date);
    
        if (!$stmt->execute()) {
          Response::error('Could not create crypto payment.', 500);
        }
    
        $stmt->close();
    
        return Response::success([
          'message' => 'Payment request submitted for approval',
          'amount' => $amount,
          'coin' => $coin,
          'tx_ref' => $tx_ref
        ]);
    }
      
      public static function updateCryptoPaymentStatus($id, $input)
    {
        $conn = Database::getConnection();

        $status = $input['status'] ?? null;
        $amount = $input['amount'] ?? null;
        if($status !== 'success' && $status !== 'failed') {
          Response::error('Invalid status values', 400);
        }

        $paymentStmt = $conn->prepare("SELECT user_id, tx_ref FROM payments WHERE id = ? AND method = 'crypto'");
        if(!$paymentStmt) {
          Response::error('Invalid selection', 400);
        }
        $paymentStmt->bind_param("s", $id);
        $paymentStmt->execute();
        $paymentResult = $paymentStmt->get_result();
        $payment = $paymentResult->fetch_assoc();


        $stmt = $conn->prepare("UPDATE payments SET status = ?, amount = ? WHERE id = ?");
        $stmt->bind_param("sds", $status, $amount, $id);

        if(!$stmt->execute()) {
          Response::error('Status update failed', 500);
        }

        // Update user balance
        if ($status === 'success') {
          $updateBalanceSql = "UPDATE users SET balance = balance + ? WHERE id = ?";
          $balanceStmt = $conn->prepare($updateBalanceSql);
          $balanceStmt->bind_param("ds", $amount, $payment['user_id']);
          $balanceStmt->execute();
          $balanceStmt->close();

          ReferralService::handleReferralBonus($payment['user_id'], $amount, $payment['tx_ref']);
        }

        Response::success([
          'message' => 'Status for user updated successfully',
          'status' => $status,
          'amount' => $amount,
          'id' => $id
        ]);
    }
}



?>