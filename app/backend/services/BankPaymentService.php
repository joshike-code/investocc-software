<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../core/response.php';
require_once __DIR__ . '/../services/BankAccountsService.php';
require_once __DIR__ . '/../services/ReferralService.php';

class BankPaymentService
{
    public static function createBankPayment(int $user_id, array $input) {
        $conn = Database::getConnection();
    
        $amount = $input['amount'] ?? null;
        $bank_name = $input['bank_name'] ?? null;
        $account_number = $input['account_number'] ?? null;
        $bank_name = strtolower($bank_name);
    
        // Validate amount
        if ($amount <= 0) {
          Response::error('Invalid amount provided.', 400);
        }
    
        // Validate that account_number + bank_name exists in allowed bank accounts
        $admin_accounts = BankAccountsService::getAllBankAccounts(true);
        $valid = false;

    
        if (is_array($admin_accounts)) {
            foreach ($admin_accounts as $admin_account) {
                $admin_bank_name = strtolower($admin_account['bank_name'] ?? '');
                $admin_account_number = trim($admin_account['account_number'] ?? '');
        
                if ($admin_bank_name === $bank_name && $admin_account_number === $account_number) {
                    $valid = true;
                    break;
                }
            }
        }
    
        if (!$valid) {
          Response::error('Invalid bank account. Please use one of the supported bank accounts.', 400);
        }
    
        $tx_ref = uniqid("tx_");
        $date = gmdate('Y-m-d H:i:s');
    
        $stmt = $conn->prepare("INSERT INTO payments (user_id, amount, tx_ref, method, status, bank_name, account_number, date)
                                VALUES (?, ?, ?, 'bank', 'pending', ?, ?, ?)");
    
        if (!$stmt) {
          Response::error('Bank payment failed', 500);
        }
    
        $stmt->bind_param("idssss", $user_id, $amount, $tx_ref, $bank_name, $account_number, $date);
    
        if (!$stmt->execute()) {
          Response::error('Could not create bank payment.', 500);
        }
    
        $stmt->close();
    
        return Response::success([
          'message' => 'Payment request submitted for approval',
          'amount' => $amount,
          'bank_name' => $bank_name,
          'tx_ref' => $tx_ref
        ]);
    }
      
      public static function updateBankPaymentStatus($id, $input)
    {
        $conn = Database::getConnection();

        $status = $input['status'] ?? null;
        $amount = $input['amount'] ?? null;
        if($status !== 'success' && $status !== 'failed') {
          Response::error('Invalid status values', 400);
        }

        $paymentStmt = $conn->prepare("SELECT user_id, tx_ref FROM payments WHERE id = ? AND method = 'bank'");
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