<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../core/response.php';
require_once __DIR__ . '/../services/PlatformService.php';

class StockService
{

    public static function getStockCategories(): array {
        return array (
            array ( 'name' => 'The Moneystart portfolio', 'id' => 'moneystart' ),
            array ( 'name' => 'Motorsport: Formula One Sponsors', 'id' => 'motorsport' ),
            array ( 'name' => 'Inverse ETFs', 'id' => 'inverse-etfs' ),
            array ( 'name' => 'Gold ETFs', 'id' => 'gold-etfs' ),
            array ( 'name' => 'The Magnificient 7', 'id' => 'magnificient-7' ),
            array ( 'name' => 'AI Stocks to Watch in 2025', 'id' => 'ai-2025' ),
            array ( 'name' => 'Bond ETFs', 'id' => 'bond-etfs' ),
            array ( 'name' => 'Leveraged Inverse ETFs', 'id' => 'leveraged-etfs' ),
            array ( 'name' => 'Warren Buffets Portfolio', 'id' => 'warren-buffet' ),
            array ( 'name' => 'Metaverse Stocks', 'id' => 'metaverse' ),
            array ( 'name' => 'European ETFs', 'id' => 'european-etfs' ),
            array ( 'name' => 'Cheap stocks to Buy Now', 'id' => 'cheap-to-buy' ),
            array ( 'name' => 'Energy Stocks', 'id' => 'energy-stocks' ),
            array ( 'name' => 'Best Performing ETFs: 2024', 'id' => 'best-performing-2024' ),
            array ( 'name' => 'REITs: Real Estate Investment Trusts', 'id' => 'reits' ),
            array ( 'name' => 'Real Estate', 'id' => 'real-estate' ),
            array ( 'name' => 'Bank ETFs', 'id' => 'bank-etfs' ),
            array ( 'name' => 'Fixed Income ETFs', 'id' => 'fixed-income-etfs' ),
            array ( 'name' => 'Biggest Companies in the World', 'id' => 'biggest-companies' ),
            array ( 'name' => 'Invest in Sports', 'id' => 'invest-sports' ),
            array ( 'name' => 'Real Estate ETFs', 'id' => 'real-estate-etfs' ),
            array ( 'name' => "Warren Buffet's Top 6 Stocks", 'id' => 'warren-buffet' ),
            array ( 'name' => "Charlie Munger's Top 10 Stocks", 'id' => 'charlie-munger' ),
            array ( 'name' => "Carl Icahn's Top 12 Stocks", 'id' => 'carl-icahn' ),
            array ( 'name' => "Ken Griffin's Top 12 Stocks", 'id' => 'ken-griffin' ),
            array ( 'name' => "Bill Ackman's Top 6 Stocks", 'id' => 'bill-ackman' ),
            array ( 'name' => "Bill Gates' Top 20 Stocks", 'id' => 'bill-gate' ),
            array ( 'name' => 'Most Bought Stocks', 'id' => 'most-bought' ),
            array ( 'name' => 'Defensive ETFs', 'id' => 'defensive-etfs' ),
            array ( 'name' => 'Agriculture', 'id' => 'agriculture' ),
            array ( 'name' => 'Big Stable Companies', 'id' => 'big-stable' ),
            array ( 'name' => 'Oil & Gas Stocks', 'id' => 'oil-gas-stocks' ),
            array ( 'name' => 'Expert Picks: Ross Gerber', 'id' => 'ross-gerber' ),
            array ( 'name' => 'Expert Picks: Emmet Savage', 'id' => 'emmet-savage' ),
            array ( 'name' => 'Oil & Gas ETFs', 'id' => 'oil-gas-etfs' ),
            array ( 'name' => 'Manufacturing', 'id' => 'manufacturing' ),
            array ( 'name' => 'The Weed Industry', 'id' => 'weed-industry' ),
            array ( 'name' => 'Big Bank Energy', 'id' => 'big-bank-energy' ),
            array ( 'name' => 'Investing Starterpack', 'id' => 'investing-starterpack' ),
            array ( 'name' => 'Self-Driving Car Stocks', 'id' => 'self-driving' ),
            array ( 'name' => 'Vroom: The EV Industry', 'id' => 'vroom' ),
            array ( 'name' => "Rory's Picks", 'id' => 'rory' ),
            array ( 'name' => 'Dividend Stocks', 'id' => 'dividend-stocks' ),
            array ( 'name' => 'All Stocks', 'id' => 'all-stocks' ),
            array ( 'name' => 'Most Popular', 'id' => 'most-popular' ),
            array ( 'name' => 'Technology', 'id' => 'technology' ),
            array ( 'name' => 'Health', 'id' => 'health' ),
            array ( 'name' => 'Transportation', 'id' => 'transportation' ),
            array ( 'name' => 'Food', 'id' => 'food' ),
            array ( 'name' => 'Retail', 'id' => 'retail' ),
            array ( 'name' => 'Entertainment', 'id' => 'entertainment' ),
            array ( 'name' => 'ETFs', 'id' => 'etfs' ),
            array ( 'name' => 'Currency ETFs', 'id' => 'currency-etfs' ),
            // array ( 'name' => 'Bitcoin ETFs', 'id' => 'bitcoin-etfs' ),
            array ( 'name' => 'Halal Stocks & ETFs', 'id' => 'halal' ),
            array ( 'name' => 'Commodities', 'id' => 'commodities' ),
        );
    }

    public static function getAllStocks()
    {
        $conn = Database::getConnection();

        $sql = "SELECT id, name, trade_name, price, today_percent, today_p_l, one_week_percent, one_week_p_l, last_update FROM stocks";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            Response::error('Failed to get stocks', 500);
        }

        $stmt->execute();
        $result = $stmt->get_result();

        $stocks = [];
        while ($row = $result->fetch_assoc()) {
            $stocks[] = $row;
        }

        $stmt->close();

        return $stocks;
    }

    public static function getStocksByCategory($category)
    {
        $conn = Database::getConnection();

        $sql = "SELECT id, name, trade_name, price, description, about, sector, categories, last_update
                FROM stocks
                WHERE JSON_CONTAINS(categories, ?)";

        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            Response::error('Failed to prepare category filter', 500);
        }

        $jsonCategory = json_encode([$category]);

        $stmt->bind_param("s", $jsonCategory);

        $stmt->execute();
        $result = $stmt->get_result();

        $stocks = [];
        while ($row = $result->fetch_assoc()) {
            $stocks[] = $row;
        }

        $stmt->close();

        return $stocks;
    }

    public static function getStockById($id)
    {
        $conn = Database::getConnection();
        $stmt = $conn->prepare("SELECT id, name, trade_name, price, today_percent, today_p_l, one_week_percent, one_week_p_l, open, high, low, month_high, month_low, volume, market_cap, description, about, sector, categories, last_update
            FROM stocks 
            WHERE id = ?");
        $stmt->bind_param("s", $id);
        $stmt->execute();
        if (!$stmt) {
            Response::error('Could not get stock', 500);
        }

        $result = $stmt->get_result()->fetch_assoc();

        if (!$result) {
            Response::error('Stock not found', 404);
        }

        return $result;
    }

    public static function searchStocks($searchTerm)
    {
        $conn = Database::getConnection();

        $likeTerm = "%" . $searchTerm . "%";

        $query = "
            SELECT id, name, trade_name, price, description, about, sector, categories, last_update
            FROM stocks
            WHERE (
                name LIKE ? OR
                trade_name LIKE ? OR
                sector LIKE ? OR
                categories LIKE ?
            )
        ";

        $stmt = $conn->prepare($query);
        if (!$stmt) {
            // Response::error("Prepare failed: " . $conn->error, 500);
            return;
        }

        $stmt->bind_param("ssss", $likeTerm, $likeTerm, $likeTerm, $likeTerm);
        $stmt->execute();

        $result = $stmt->get_result();
        $stocks = [];

        while ($row = $result->fetch_assoc()) {
            $stocks[] = $row;
        }

        return $stocks; 
    }
}



?>