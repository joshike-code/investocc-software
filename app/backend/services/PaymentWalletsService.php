<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../core/response.php';
require_once __DIR__ . '/../utility/notify.php';

class PaymentWalletsService
{
    public static function getAllWallets($getResult = false)
    {
        $conn = Database::getConnection();

        $sql = "SELECT id, coin, network, address 
                FROM payment_wallets";
        
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            Response::error('Failed to get payment_wallets', 500);
        }

        $stmt->execute();
        $result = $stmt->get_result();

        $wallets = [];
        while ($row = $result->fetch_assoc()) {
            $wallets[] = $row;
        }

        $stmt->close();
        if($getResult === true) {
            return $wallets;
            exit;
        }
        Response::success($wallets);
    }

    public static function getWalletById($id)
    {
        $conn = Database::getConnection();
        $stmt = $conn->prepare("SELECT coin, network, address, date
            FROM payment_wallets 
            WHERE id = ?");
        $stmt->bind_param("s", $id);
        $stmt->execute();
        if (!$stmt) {
            // Not returning here .... remember to check when using to return error abeg (probably no execute check)
            Response::error('Could not get wallet', 500);
        }

        $result = $stmt->get_result()->fetch_assoc();

        if (!$result) {
            Response::error('Wallet not found', 404);
        }

        return $result;
    }

    public static function createWallet($input)
    {
        $conn = Database::getConnection();
        $stmt = $conn->prepare("INSERT INTO payment_wallets (coin, network, address, date) VALUES (?, ?, ?, ?)");
        if (!$stmt) {
            Response::error('Failed to create wallet', 500);
        }

        $coin = $input['coin'] ?? null;
        $network = $input['network'] ?? null;
        $address = $input['address'] ?? null;
        $date = gmdate('Y-m-d H:i:s');

        $stmt->bind_param("ssss", $coin, $network, $address, $date);

        if ($stmt->execute()) {
            self::getAllWallets();
        } else {
            Response::error('Failed to create wallet', 500);
        }
    }

    public static function updateWallet($id, $input)
    {
        $conn = Database::getConnection();

        $coin = $input['coin'] ?? null;
        $network = $input['network'] ?? null;
        $address = $input['address'] ?? null;
        $date = gmdate('Y-m-d H:i:s');

        $stmt = $conn->prepare("UPDATE payment_wallets SET coin = ?, network = ?, address = ?, date = ? WHERE id = ?");
        $stmt->bind_param("sssss", $coin, $network, $address, $date, $id);

        if ($stmt->execute()) {
            self::getAllWallets();
        } else {
            Response::error('Failed to update wallet', 500);
        }
    }

    public static function deleteWallet($id) {
        $conn = Database::getConnection();

        // Check if wallet exists
        $stmt = $conn->prepare("SELECT id FROM payment_wallets WHERE id = ?");
        $stmt->bind_param("s", $id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();

        if (!$result) {
            Response::error('Wallet not found', 404);
        }

        $stmt = $conn->prepare("DELETE FROM payment_wallets WHERE id = ?");
        $stmt->bind_param("s", $id);

        if ($stmt->execute()) {
            self::getAllWallets();
        } else {
            Response::error("Failed to delete Wallet.", 500);
        }
    }
}



?>