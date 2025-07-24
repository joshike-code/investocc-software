<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../core/response.php';
require_once __DIR__ . '/../core/jwt_utils.php';
require_once __DIR__ . '/../services/PlatformService.php';

class ReferralService {
    public static function handleReferralBonus(string $payer_id, float $amount, string $payment_id): void
    {
        $conn = Database::getConnection();

        // Get referrer ID from payer
        $stmt = $conn->prepare("SELECT referred_by FROM users WHERE id = ?");
        $stmt->bind_param("s", $payer_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $referrer_code = $result['referred_by'] ?? null;

        if (!$referrer_code) return; // No referrer, skip bonus

        // Get referrer ID using the ref_code
        $stmt = $conn->prepare("SELECT id FROM users WHERE ref_code = ?");
        $stmt->bind_param("s", $referrer_code);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        if (!$result) return;

        $referrer_id = $result['id'];

        // Get referral percentage from platform settings
        $percentage = PlatformService::getSetting('referral_percentage', 5);
        if ($percentage <= 0) return;

        $bonus = ($percentage / 100) * $amount;

        // Credit referrer balance
        $update = $conn->prepare("UPDATE users SET ref_balance = ref_balance + ? WHERE id = ?");
        $update->bind_param("ds", $bonus, $referrer_id);
        $update->execute();

        // Log to referrals table
        $insert = $conn->prepare("INSERT INTO referrals (id, referrer_id, referred_user_id, amount, percentage, payment_id, description, date) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $log_id = uniqid('ref_', true);

        $desc = "Referral bonus from $payer_id";
        $date = gmdate('Y-m-d H:i:s');

        $insert->bind_param("sssddsss", $log_id, $referrer_id, $payer_id, $bonus, $percentage, $payment_id, $desc, $date);
        $insert->execute();
    }

    public static function convertReferralBonusToBalance(string $user_id): void
    {
        $conn = Database::getConnection();

        $stmt = $conn->prepare("SELECT ref_balance FROM users WHERE id = ?");
        $stmt->bind_param("s", $user_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();

        if (!$result) {
            Response::error('User not found', 404);
        }

        $refBalance = floatval($result['ref_balance']);

        // Get minimum referral conversion amount
        $minAmount = PlatformService::getSetting('min_withdrawal', 1);

        if ($refBalance < $minAmount) {
            Response::error("You need at least ₦$minAmount in referral balance to convert to main balance.", 400);
        }

        // Begin transaction
        $conn->begin_transaction();

        try {
            // Add to main balance
            $update = $conn->prepare("UPDATE users SET balance = balance + ?, ref_balance = 0 WHERE id = ?");
            $update->bind_param("ds", $refBalance, $user_id);
            $update->execute();
           
            // Log to payments table
            $insert = $conn->prepare("INSERT INTO payments (user_id, amount, tx_ref, method, status, date) VALUES (?, ?, ?, ?, ?, ?)");
            $tx_ref = uniqid("tx_");
            $method = "referral";
            $status = "success";
            $date = gmdate('Y-m-d H:i:s');
            $insert->bind_param("sdssss", $user_id, $refBalance, $tx_ref, $method, $status, $date);
            $insert->execute();

            $conn->commit();
            Response::success(['message' => 'Referral bonus moved to main balance', 'amount' => $refBalance]);

        } catch (Exception $e) {
            $conn->rollback();
            Response::error('Failed to move referral bonus to balance', 500);
        }
    }

    //we want the referrer here not referrer_user(referee)
    public static function getAllReferralEarnings() {
        $conn = Database::getConnection();
        $stmt = $conn->prepare("
            SELECT 
                r.id AS ref_earn_id, r.referrer_id, r.referred_user_id, r.amount, r.percentage, r.payment_id, r.description, r.date,
                u.id AS user_id, u.fname, u.lname, u.email
            FROM 
                referrals r
            INNER JOIN 
                users u ON r.referrer_id = u.id
        ");
        $stmt->execute();
        $result = $stmt->get_result();
        $referrals = [];
        
        while ($row = $result->fetch_assoc()) {
            $referrals[] = $row;
        }

        // if (empty($referrals)) {
        //     Response::error('No referrals earnings found', 404);     No need for error when fetching all
        // }

        // Total referral earnings
        $stmtTotal = $conn->query("SELECT COUNT(*) AS total_referrals FROM referrals");
        $total_referrals_count = intval($stmtTotal->fetch_assoc()['total_referrals']);

        Response::success([
            'total_referrals'     => $referrals,
            'total_referrals_count' => $total_referrals_count
        ]);
    }
}

?>