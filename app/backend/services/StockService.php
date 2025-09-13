<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../core/response.php';
require_once __DIR__ . '/../services/PlatformService.php';
require_once __DIR__ . '/../services/FinnhubService.php';

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



    /**
     * Get stock by ID (database only)
     */
    public static function getStockByIdFromDb($id)
    {
        return self::getStockById($id);
    }

    /**
     * Get stocks by category (database only)
     */
    public static function getStocksByCategoryFromDb($category)
    {
        return self::getStocksByCategory($category);
    }

    /**
     * Search stocks (database only)
     */
    public static function searchStocksFromDb($searchTerm)
    {
        return self::searchStocks($searchTerm);
    }

    /**
     * Manually update all stock prices from Finnhub
     */
    public static function updateAllStockPrices()
    {
        $result = FinnhubService::updateStockPrices();
        return $result;
    }

    /**
     * Update specific stocks
     */
    public static function updateSpecificStocks($symbols)
    {
        $result = FinnhubService::updateStockPrices($symbols);
        return $result;
    }

    /**
     * Smart update - only update stocks with stale cache (configurable frequency)
     */
    public static function smartUpdateStockPrices()
    {
        $conn = Database::getConnection();
        
        // Get update frequency from config
        // $keys = require __DIR__ . '/../config/keys.php';
        // $updateFrequency = $keys['finnhub']['update_frequency'];
        $updateFrequency = 1800;

        // Get all symbols
        $stmt = $conn->prepare("SELECT DISTINCT trade_name FROM stocks WHERE trade_name IS NOT NULL AND trade_name != ''");
        $stmt->execute();
        $result = $stmt->get_result();
        
        $allSymbols = [];
        while ($row = $result->fetch_assoc()) {
            $allSymbols[] = $row['trade_name'];
        }
        
        // Filter symbols that need updating
        $symbolsToUpdate = [];
        foreach ($allSymbols as $symbol) {
            $cacheFile = __DIR__ . '/../cache/stock_' . $symbol . '.json';
            
            if (!file_exists($cacheFile)) {
                $symbolsToUpdate[] = $symbol;
                continue;
            }
            
            $cacheData = json_decode(file_get_contents($cacheFile), true);
            if (!$cacheData || !isset($cacheData['timestamp'])) {
                $symbolsToUpdate[] = $symbol;
                continue;
            }
            
            $cacheAge = time() - $cacheData['timestamp'];
            // Only update if cache is older than configured frequency
            if ($cacheAge >= $updateFrequency) {
                $symbolsToUpdate[] = $symbol;
            }
        }
        
        if (empty($symbolsToUpdate)) {
            $frequencyMinutes = round($updateFrequency / 60, 1);
            return [
                'message' => "All stock data is fresh (less than {$frequencyMinutes} minutes old)",
                'updated' => 0,
                'skipped' => count($allSymbols),
                'total' => count($allSymbols),
                'update_frequency' => $updateFrequency,
                'next_update_needed' => date('Y-m-d H:i:s', time() + $updateFrequency)
            ];
        }
        
        $result = FinnhubService::updateStockPrices($symbolsToUpdate);
        $result['skipped'] = count($allSymbols) - count($symbolsToUpdate);
        $result['message'] = "Updated {$result['updated']} stocks, skipped {$result['skipped']} with fresh cache";
        $result['update_frequency'] = $updateFrequency;
        
        return $result;
    }

    /**
     * Get current market status (open/closed)
     * Based on US stock market hours (NYSE/NASDAQ)
     */
    public static function getMarketStatus()
    {
        // Set timezone to Eastern Time (where US stock markets operate)
        $easternTime = new DateTime('now', new DateTimeZone('America/New_York'));
        
        $currentTime = $easternTime->format('H:i');
        $currentDay = $easternTime->format('N'); // 1=Monday, 7=Sunday
        $currentDate = $easternTime->format('Y-m-d');
        
        // US market holidays (major ones that affect stock trading)
        $holidays = self::getMarketHolidays($easternTime->format('Y'));
        
        // Check if today is a holiday
        if (in_array($currentDate, $holidays)) {
            return [
                'is_open' => false,
                'status' => 'closed',
                'reason' => 'Market Holiday',
                'next_open' => self::getNextMarketOpen($easternTime),
                'timezone' => 'America/New_York',
                'current_time' => $easternTime->format('Y-m-d H:i:s T')
            ];
        }
        
        // Check if it's weekend (Saturday=6, Sunday=7)
        if ($currentDay >= 6) {
            return [
                'is_open' => false,
                'status' => 'closed',
                'reason' => 'Weekend',
                'next_open' => self::getNextMarketOpen($easternTime),
                'timezone' => 'America/New_York',
                'current_time' => $easternTime->format('Y-m-d H:i:s T')
            ];
        }
        
        // Regular market hours: 9:30 AM - 4:00 PM ET (Monday-Friday)
        $marketOpen = '09:30';
        $marketClose = '16:00';
        
        if ($currentTime >= $marketOpen && $currentTime < $marketClose) {
            // Market is open
            $closeTime = clone $easternTime;
            $closeTime->setTime(16, 0, 0);
            
            return [
                'is_open' => true,
                'status' => 'open',
                'reason' => 'Regular Trading Hours',
                'closes_at' => $closeTime->format('Y-m-d H:i:s T'),
                'time_until_close' => self::getTimeUntilClose($easternTime),
                'timezone' => 'America/New_York',
                'current_time' => $easternTime->format('Y-m-d H:i:s T')
            ];
        } else {
            // Market is closed (before open or after close)
            $reason = $currentTime < $marketOpen ? 'Before Market Hours' : 'After Market Hours';
            
            return [
                'is_open' => false,
                'status' => 'closed',
                'reason' => $reason,
                'next_open' => self::getNextMarketOpen($easternTime),
                'timezone' => 'America/New_York',
                'current_time' => $easternTime->format('Y-m-d H:i:s T')
            ];
        }
    }

    /**
     * Get major US market holidays for a given year
     */
    private static function getMarketHolidays($year)
    {
        $holidays = [];
        
        // New Year's Day (observed on Monday if falls on Sunday)
        $newYear = new DateTime("$year-01-01");
        if ($newYear->format('N') == 7) { // Sunday
            $newYear->modify('+1 day');
        }
        $holidays[] = $newYear->format('Y-m-d');
        
        // Martin Luther King Jr. Day (3rd Monday in January)
        $mlkDay = new DateTime("third monday of january $year");
        $holidays[] = $mlkDay->format('Y-m-d');
        
        // Presidents Day (3rd Monday in February)
        $presidentsDay = new DateTime("third monday of february $year");
        $holidays[] = $presidentsDay->format('Y-m-d');
        
        // Good Friday (Friday before Easter)
        $easter = new DateTime("$year-03-21");
        $easter->modify('+' . (easter_days($year)) . ' days');
        $goodFriday = clone $easter;
        $goodFriday->modify('-2 days');
        $holidays[] = $goodFriday->format('Y-m-d');
        
        // Memorial Day (last Monday in May)
        $memorialDay = new DateTime("last monday of may $year");
        $holidays[] = $memorialDay->format('Y-m-d');
        
        // Juneteenth (June 19th, observed on Monday if falls on Sunday)
        $juneteenth = new DateTime("$year-06-19");
        if ($juneteenth->format('N') == 7) { // Sunday
            $juneteenth->modify('+1 day');
        }
        $holidays[] = $juneteenth->format('Y-m-d');
        
        // Independence Day (July 4th, observed on Monday if falls on Sunday)
        $july4th = new DateTime("$year-07-04");
        if ($july4th->format('N') == 7) { // Sunday
            $july4th->modify('+1 day');
        }
        $holidays[] = $july4th->format('Y-m-d');
        
        // Labor Day (1st Monday in September)
        $laborDay = new DateTime("first monday of september $year");
        $holidays[] = $laborDay->format('Y-m-d');
        
        // Thanksgiving Day (4th Thursday in November)
        $thanksgiving = new DateTime("fourth thursday of november $year");
        $holidays[] = $thanksgiving->format('Y-m-d');
        
        // Christmas Day (December 25th, observed on Monday if falls on Sunday)
        $christmas = new DateTime("$year-12-25");
        if ($christmas->format('N') == 7) { // Sunday
            $christmas->modify('+1 day');
        }
        $holidays[] = $christmas->format('Y-m-d');
        
        return $holidays;
    }

    /**
     * Calculate when the market will next open
     */
    private static function getNextMarketOpen($currentTime)
    {
        $nextOpen = clone $currentTime;
        
        // If it's Friday after hours or weekend, next open is Monday
        if ($currentTime->format('N') == 5 && $currentTime->format('H:i') >= '16:00') {
            // Friday after close
            $nextOpen->modify('next monday 09:30');
        } elseif ($currentTime->format('N') >= 6) {
            // Weekend
            $nextOpen->modify('next monday 09:30');
        } else {
            // Weekday - check if before or after hours
            if ($currentTime->format('H:i') < '09:30') {
                // Before market opens today
                $nextOpen->setTime(9, 30, 0);
            } else {
                // After market closes, next open is tomorrow (or Monday if Friday)
                if ($currentTime->format('N') == 5) {
                    $nextOpen->modify('next monday 09:30');
                } else {
                    $nextOpen->modify('+1 day')->setTime(9, 30, 0);
                }
            }
        }
        
        // Check if next open day is a holiday
        $holidays = self::getMarketHolidays($nextOpen->format('Y'));
        while (in_array($nextOpen->format('Y-m-d'), $holidays)) {
            $nextOpen->modify('+1 day');
            if ($nextOpen->format('N') >= 6) {
                $nextOpen->modify('next monday');
            }
            $nextOpen->setTime(9, 30, 0);
        }
        
        return $nextOpen->format('Y-m-d H:i:s T');
    }

    /**
     * Calculate time remaining until market close
     */
    private static function getTimeUntilClose($currentTime)
    {
        $closeTime = clone $currentTime;
        $closeTime->setTime(16, 0, 0);
        
        $diff = $closeTime->getTimestamp() - $currentTime->getTimestamp();
        
        if ($diff <= 0) {
            return '00:00:00';
        }
        
        $hours = floor($diff / 3600);
        $minutes = floor(($diff % 3600) / 60);
        $seconds = $diff % 60;
        
        return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
    }
}



?>