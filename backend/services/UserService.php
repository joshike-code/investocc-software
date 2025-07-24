<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../core/response.php';
require_once __DIR__ . '/../core/jwt_utils.php';
require_once __DIR__ . '/../services/OtpService.php';
require_once __DIR__ . '/../services/MailService.php';

class UserService
{
    public static function loginUser(string $email, string $password, string $type) {
        try {

            if (empty($email) || empty($password)) {
                Response::error('Email and password are required', 400);
            }
        
            $conn = Database::getConnection();
            $stmt = $conn->prepare("SELECT id, password, role, permissions FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
        
            // If the user is not found
            if ($result->num_rows === 0) {
                Response::error('Invalid email', 401);
                exit;
            }
        
            // Fetch the user data
            $user = $result->fetch_assoc();

            // Verify the password
            if (!password_verify($password, $user['password'])) {
                Response::error('Invalid password', 401);
                exit;
            }
        
            $token = generate_jwt(['user_id' => $user['id'], 'role' => $user['role'], 'permissions' => $user['permissions'], 'exp' => time() + 3600], 'base');
            Response::success(['token' => $token]);
        
        } catch (Exception $e) {
            Response::error('An error occurred', 500);
        }
    }

    public static function preRegisterUser(array $input)
    {
        $conn = Database::getConnection();
        $email = $input['email'];

        // Check if email exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            Response::error("Email already exists", 400);
        }

        // Generate OTP
        $otp = OtpService::generateOtp($email);

        // Response::success(['message' => 'OTP sent', 'otp' => $otp]);
        if (!MailService::sendOtpEmail($email, $otp, 'register')) {
            Response::error('Failed to send OTP email', 500);
        }
        Response::success('OTP Sent');
    }

    public static function registerUser(array $input)
    {
        session_start();
        $conn = Database::getConnection();

        $email = $input['email'];
        $otp = $input['otp'];

        $validateOtp = OtpService::validateOtp($email, $otp);
        if(!$validateOtp) {
            Response::error('invalid otp', 401);
        }

        $id = uniqid('usr_', true);
        $password = password_hash($input['password'], PASSWORD_DEFAULT);
        $fname = $input['fname'];
        $lname = $input['lname'];
        $role = 'user';

        // Generate unique referral code
        $ref_code = strtoupper(substr(bin2hex(random_bytes(4)), 0, 8));

        $referred_by = $input['ref'] ?? null;
        if ($referred_by) {
            $checkRef = $conn->prepare("SELECT id FROM users WHERE ref_code = ?");
            $checkRef->bind_param("s", $referred_by);
            $checkRef->execute();
            $checkResult = $checkRef->get_result();
        
            if ($checkResult->num_rows === 0) {
                // No need to make noise
                // Response::error("Invalid referral code provided", 400);
                $referred_by = null;
            }
        }

        $stmt = $conn->prepare("INSERT INTO users (id, email, password, fname, lname, role, ref_code, referred_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        if (!$stmt) {
            Response::error('error: prepare failed', 500);
        }

        $stmt->bind_param("ssssssss", $id, $email, $password, $fname, $lname, $role, $ref_code, $referred_by);

        if ($stmt->execute()) {
            unset($_SESSION['verified_email']);
            return true;
        } else {
            Response::error('Registration failed. Email may already be in use.', 400);
        }
    }

    //Forgot Password 1
    public static function checkEmail(array $input) {
        $email = $input['email'] ?? null;
        if (!$email) {
            Response::error('No email provided', 400);
        }
    
        $conn = Database::getConnection();
        $stmt = $conn->prepare("SELECT id, password, role FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
    
        // If the user is found
        if ($result) {
            // Generate OTP
            $otp = OtpService::generateOtp($email);
            
            if (!MailService::sendOtpEmail($email, $otp, 'forgot-password')) {
                Response::error('Failed to send OTP email', 500);
            }
            Response::success('OTP Sent');
        } else {
            Response::error('Invalid email', 401);
        }

        Response::error('Something went wrong', 500);
    }

    //Forgot Password 2
    public static function createNewPassword($input, $action)
    {
        
        $otp = $input['otp'] ?? null;
        $email = $input['email'] ?? null;
        $password = $input['password'] ?? null;
        if($action === 'confirm') {
            $validateOtp = OtpService::validateOtp($email, $otp);
            if($validateOtp) {
                Response::success('OTP validated');
            } else {
                Response::error('Invalid OTP', 401);
            }
        }

        $validateOtp = OtpService::validateOtp($email, $otp); //Validate again to block smart guys hahahahaah
        if(!$validateOtp) {
            Response::error('Invalid OTP', 401);
        }
        $conn = Database::getConnection();


        $hashed = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
        $stmt->bind_param("ss", $hashed, $email);

        if ($stmt->execute()) {
            Response::success(['message' => 'Password updated']);
        } else {
            Response::error('Password update failed', 500);
        }
    }

    public static function getUserById($id)
    {
        $conn = Database::getConnection();
        $stmt = $conn->prepare("SELECT email, fname, lname, phone, country, balance, ref_balance, ref_code, permissions, date_registered
            FROM users 
            WHERE id = ?");
        $stmt->bind_param("s", $id);
        $stmt->execute();
        if (!$stmt) {
            Response::error('Could not get user', 500);
        }

        $result = $stmt->get_result()->fetch_assoc();

        if (!$result) {
            Response::error('User not found', 404);
        }

        return $result;
    }

    // Admin methods
    public static function getAllUsers($role = 'user') {
        $conn = Database::getConnection();
        $stmt = $conn->prepare("SELECT id, fname, lname, email, ref_balance, balance, permissions, date_registered
                FROM users
                WHERE role = ?");
        $stmt->bind_param("s", $role);
        $stmt->execute();
        $result = $stmt->get_result();
        $users = [];
        
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }

        // if (empty($users)) {
        //     Response::error('No users found', 404);   No need for error when fetching all
        // }

        // Total referral earnings
        $stmtTotal = $conn->query("SELECT COUNT(*) AS total_users FROM users WHERE role = '$role'");
        $total_users_count = intval($stmtTotal->fetch_assoc()['total_users']);

        Response::success([
            'total_users'     => $users,
            'total_users_count' => $total_users_count
        ]);
    }

    public static function deleteUser($user_id, $role = 'user') {
        $conn = Database::getConnection();

        // Check if user exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE id = ? AND role = ?");
        $stmt->bind_param("ss", $user_id, $role);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();

        if (!$result) {
            Response::error('User not found', 404);
        }

        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("s", $user_id);

        if ($stmt->execute()) {
            Response::success("User deleted successfully.");
        } else {
            Response::error("Failed to delete user.", 500);
        }
    }

    public static function getAdminUserStats()
    {
        $conn = Database::getConnection();

        // Total users
        $stmtTotal = $conn->query("SELECT COUNT(*) AS total_users FROM users WHERE role = 'user'");
        $totalUsers = intval($stmtTotal->fetch_assoc()['total_users']);

        // Total referrals
        $stmtReferrals = $conn->query("SELECT COUNT(*) AS total_referrals FROM users WHERE referred_by IS NOT NULL");
        $totalReferrals = intval($stmtReferrals->fetch_assoc()['total_referrals']);

        Response::success([
            'total_users'     => $totalUsers,
            'total_referrals' => $totalReferrals
        ]);
    }

    public static function updateUserProfile($user_id, $input)
    {
        $conn = Database::getConnection();

        $fname = $input['fname'];
        $lname = $input['lname'];
        $email = $input['email'];
        $phone = $input['phone'] ?? null;
        $permissions = $input['permissions'] ?? null;
        if($permissions !== null) {
            $permissions = json_encode($permissions);
        };

        // Check email exists for another user
        $check = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $check->bind_param("ss", $email, $user_id);
        $check->execute();
        
        $checkResult = $check->get_result();

        if ($checkResult->num_rows > 0) {
            Response::error('Email already in use by another user', 400);
        }

        $stmt = $conn->prepare("UPDATE users SET fname = ?, lname = ?, email = ?, phone = ?, permissions = ? WHERE id = ?");
        $stmt->bind_param("ssssss", $fname, $lname, $email, $phone, $permissions, $user_id);

        if ($stmt->execute()) {
            $userData = self::getUserById($user_id);
            Response::success($userData);
        } else {
            Response::error('Profile update failed', 500);
        }
    }

    public static function createAdmin($input)
    {
        $conn = Database::getConnection();

        $fname = $input['fname'];
        $lname = $input['lname'];
        $email = $input['email'];
        $phone = $input['phone'] ?? null;
        $password = password_hash($input['password'], PASSWORD_DEFAULT);
        $permissions = json_encode($input['permissions']);

        $role = 'admin';
        $ref_code = strtoupper(substr(bin2hex(random_bytes(4)), 0, 8));
        $referred_by = null;

        // Check email exists for another user
        $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        
        $checkResult = $check->get_result();

        if ($checkResult->num_rows > 0) {
            Response::error('Email already in use by another user', 400);
        }

        $stmt = $conn->prepare("INSERT INTO users (email, password, fname, lname, phone, role, permissions, ref_code, referred_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        if (!$stmt) {
            Response::error('error: prepare failed', 500);
        }

        $stmt->bind_param("sssssssss", $email, $password, $fname, $lname, $phone, $role, $permissions, $ref_code, $referred_by);

        if ($stmt->execute()) {
            Response::success("Admin added successfully");
        } else {
            Response::error('Profile update failed', 500);
        }
    }

    public static function updateUserPassword($user_id, $oldPassword, $newPassword)
    {
        $conn = Database::getConnection();

        $stmt = $conn->prepare("SELECT id, password FROM users WHERE id = ?");
        $stmt->bind_param("s", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        // Verify the password
        if (!password_verify($oldPassword, $user['password'])) {
            Response::error('Invalid password', 400);
            exit;
        }

        $hashed = password_hash($newPassword, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->bind_param("ss", $hashed, $user_id);

        if ($stmt->execute()) {
            Response::success(['message' => 'Password updated']);
        } else {
            Response::error('Password update failed', 500);
        }
    }

    public static function getUserReferralCount($user_id) {
        $conn = Database::getConnection();
    
        // Get the ref_code of the user
        $stmt = $conn->prepare("SELECT ref_code FROM users WHERE id = ?");
        $stmt->bind_param("s", $user_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
    
        if (!$result || !isset($result['ref_code'])) {
            Response::error('User not found or has no referral code', 404);
        }
    
        $ref_code = $result['ref_code'];
    
        // Count how many users used this ref_code
        $countStmt = $conn->prepare("SELECT COUNT(*) as count FROM users WHERE referred_by = ?");
        $countStmt->bind_param("s", $ref_code);
        $countStmt->execute();
        $countResult = $countStmt->get_result()->fetch_assoc();

        return $countResult;
    }

    public static function searchUsersByEmail($searchTerm)
    {
        $conn = Database::getConnection();

        $likeTerm = "%" . $searchTerm . "%";

        $query = "
            SELECT id, fname, lname, email, ref_balance, balance, permissions, date_registered
            FROM users
            WHERE (
                email LIKE ? OR
                fname LIKE ? OR
                lname LIKE ?
            )
        ";

        $stmt = $conn->prepare($query);
        if (!$stmt) {
            // Response::error("Prepare failed: " . $conn->error, 500);
            return;
        }

        $stmt->bind_param("sss", $likeTerm, $likeTerm, $likeTerm);
        $stmt->execute();

        $result = $stmt->get_result();
        $users = [];

        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }

        Response::success($users);

       
    }

}



?>