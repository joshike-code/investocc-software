<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../core/response.php';
require_once __DIR__ . '/../utility/notify.php';

class BankAccountsService
{
    public static function getAllBankAccounts($getResult = false)
    {
        $conn = Database::getConnection();

        $sql = "SELECT id, account_name, bank_name, account_number 
                FROM bank_accounts";
        
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            Response::error('Failed to get bank accounts', 500);
        }

        $stmt->execute();
        $result = $stmt->get_result();

        $bank_accounts = [];
        while ($row = $result->fetch_assoc()) {
            $bank_accounts[] = $row;
        }

        $stmt->close();
        if($getResult === true) {
            return $bank_accounts;
            exit;
        }
        Response::success($bank_accounts);
    }

    public static function getBankAccountById($id)
    {
        $conn = Database::getConnection();
        $stmt = $conn->prepare("SELECT account_name, bank_name, account_number, date
            FROM bank_accounts 
            WHERE id = ?");
        $stmt->bind_param("s", $id);
        $stmt->execute();
        if (!$stmt) {
            // Not returning here .... remember to check when using to return error abeg (probably no execute check)
            Response::error('Could not get bank account', 500);
        }

        $result = $stmt->get_result()->fetch_assoc();

        if (!$result) {
            Response::error('Bank account not found', 404);
        }

        return $result;
    }

    public static function createBankAccount($input)
    {
        $conn = Database::getConnection();
        $stmt = $conn->prepare("INSERT INTO bank_accounts (account_name, bank_name, account_number, date) VALUES (?, ?, ?, ?)");
        if (!$stmt) {
            Response::error('Failed to bank account', 500);
        }

        $account_name = $input['account_name'] ?? null;
        $bank_name = $input['bank_name'] ?? null;
        $account_number = $input['account_number'] ?? null;
        $date = gmdate('Y-m-d H:i:s');

        $stmt->bind_param("ssss", $account_name, $bank_name, $account_number, $date);

        if ($stmt->execute()) {
            self::getAllBankAccounts();
        } else {
            Response::error('Failed to bank account', 500);
        }
    }

    public static function updateBankAccount($id, $input)
    {
        $conn = Database::getConnection();

        $account_name = $input['account_name'] ?? null;
        $bank_name = $input['bank_name'] ?? null;
        $account_number = $input['account_number'] ?? null;
        $date = gmdate('Y-m-d H:i:s');

        $stmt = $conn->prepare("UPDATE bank_accounts SET account_name = ?, bank_name = ?, account_number = ?, date = ? WHERE id = ?");
        $stmt->bind_param("sssss", $account_name, $bank_name, $account_number, $date, $id);

        if ($stmt->execute()) {
            self::getAllBankAccounts();
        } else {
            Response::error('Failed to update bank account', 500);
        }
    }

    public static function deleteBankAccount($id) {
        $conn = Database::getConnection();

        // Check if account exists
        $stmt = $conn->prepare("SELECT id FROM bank_accounts WHERE id = ?");
        $stmt->bind_param("s", $id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();

        if (!$result) {
            Response::error('Bank account not found', 404);
        }

        $stmt = $conn->prepare("DELETE FROM bank_accounts WHERE id = ?");
        $stmt->bind_param("s", $id);

        if ($stmt->execute()) {
            self::getAllBankAccounts();
        } else {
            Response::error("Failed to delete bank account.", 500);
        }
    }
}



?>