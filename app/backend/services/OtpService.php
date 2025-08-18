<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../core/response.php';
require_once __DIR__ . '/../core/jwt_utils.php';

class OtpService
{
    public static function generateOtp(string $email): string
    {
        $conn = Database::getConnection();
        $otp = str_pad(random_int(0, 9999), 4, '0', STR_PAD_LEFT);
        $expires_at = date('Y-m-d H:i:s', time() + 1800); // 30 mins

        $stmt = $conn->prepare("REPLACE INTO user_otp (email, otp, expires_at) VALUES (?, ?, ?)");
        if (!$stmt) Response::error('OTP generation failed', 500);
        $stmt->bind_param("sss", $email, $otp, $expires_at);
        $stmt->execute();

        return $otp;
    }

    public static function validateOtp(string $email, string $otp): bool
    {
        $conn = Database::getConnection();

        // Clear expired OTPs
        $cleanup = $conn->prepare("DELETE FROM user_otp WHERE expires_at < NOW()");
        if ($cleanup) {
            $cleanup->execute();
            $cleanup->close();
        }

        // Now check if the OTP exists and is still valid
        $stmt = $conn->prepare("SELECT * FROM user_otp WHERE email = ? AND otp = ? AND expires_at > NOW()");
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("ss", $email, $otp);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $stmt->close();
            return true;
        } else {
            $stmt->close();
            return false;
        }
    }
}



?>