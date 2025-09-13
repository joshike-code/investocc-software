<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../core/response.php';

class FinnhubService
{
    private static $apiKey;
    private static $baseUrl = 'https://finnhub.io/api/v1';

    private static function getApiKey()
    {
        if (self::$apiKey === null) {
            $keys = require __DIR__ . '/../config/keys.php';
            self::$apiKey = $keys['finnhub']['api_key'];
        }
        return self::$apiKey;
    }

    /**
     * Make HTTP request to Finnhub API
     */
    private static function makeRequest($endpoint, $params = [])
    {
        $apiKey = self::getApiKey();
        if (empty($apiKey)) {
            error_log("Finnhub API key not configured");
            return null;
        }

        $params['token'] = $apiKey;
        $url = self::$baseUrl . $endpoint . '?' . http_build_query($params);

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_USERAGENT => 'Investocc Backend/1.0',
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 3
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            error_log("Finnhub API cURL error: " . $error);
            return null;
        }

        if ($httpCode !== 200) {
            error_log("Finnhub API HTTP error: " . $httpCode . " - " . $response);
            return null;
        }

        $data = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log("Finnhub API JSON decode error: " . json_last_error_msg());
            return null;
        }

        return $data;
    }

    /**
     * Get real-time quote for a stock symbol
     */
    public static function getQuote($symbol)
    {
        return self::makeRequest('/quote', ['symbol' => $symbol]);
    }

    /**
     * Get multiple quotes in batch (more efficient)
     */
    public static function getMultipleQuotes($symbols)
    {
        $quotes = [];
        foreach ($symbols as $symbol) {
            $quote = self::getQuote($symbol);
            if ($quote !== null) {
                $quotes[$symbol] = $quote;
            }
            // Add small delay to respect rate limits (60 calls/minute for free tier)
            usleep(100000); // 0.1 second delay
        }
        return $quotes;
    }

    /**
     * Get company profile information
     */
    public static function getCompanyProfile($symbol)
    {
        return self::makeRequest('/stock/profile2', ['symbol' => $symbol]);
    }

    /**
     * Calculate percentage change
     */
    public static function calculatePercentageChange($current, $previous)
    {
        if ($previous == 0) return 0;
        return (($current - $previous) / $previous) * 100;
    }

    /**
     * Format Finnhub quote data for your database structure
     */
    public static function formatQuoteData($symbol, $quoteData)
    {
        if (!$quoteData || !isset($quoteData['c'])) {
            return null;
        }

        $current = $quoteData['c'];  // Current price
        $previousClose = $quoteData['pc']; // Previous close
        $open = $quoteData['o'];     // Open price
        $high = $quoteData['h'];     // High price
        $low = $quoteData['l'];      // Low price

        $todayChange = $current - $previousClose;
        $todayPercent = self::calculatePercentageChange($current, $previousClose);

        return [
            'trade_name' => $symbol,
            'price' => round($current, 2),
            'today_percent' => round($todayPercent, 2),
            'today_p_l' => round($todayChange, 2),
            'open' => round($open, 2),
            'high' => round($high, 2),
            'low' => round($low, 2),
            'last_update' => date('Y-m-d H:i:s')
        ];
    }

    /**
     * Update stock prices in database
     */
    public static function updateStockPrices($symbols = null)
    {
        $conn = Database::getConnection();
        
        // Get all symbols if none provided
        if ($symbols === null) {
            $stmt = $conn->prepare("SELECT DISTINCT trade_name FROM stocks WHERE trade_name IS NOT NULL AND trade_name != ''");
            $stmt->execute();
            $result = $stmt->get_result();
            
            $symbols = [];
            while ($row = $result->fetch_assoc()) {
                $symbols[] = $row['trade_name'];
            }
        }

        if (empty($symbols)) {
            error_log("No symbols found to update");
            return false;
        }

        $updatedCount = 0;
        $failedSymbols = [];

        foreach ($symbols as $symbol) {
            try {
                // Use getCachedQuote with 0 minutes to force fresh data and update cache
                $quoteData = self::getCachedQuote($symbol, 0);
                
                if ($quoteData && isset($quoteData['c'])) {
                    $formattedData = self::formatQuoteData($symbol, $quoteData);
                    
                    if ($formattedData) {
                        // Update stock in database
                        $updateStmt = $conn->prepare("
                            UPDATE stocks 
                            SET price = ?, 
                                today_percent = ?, 
                                today_p_l = ?, 
                                open = ?, 
                                high = ?, 
                                low = ?, 
                                last_update = ? 
                            WHERE trade_name = ?
                        ");
                        
                        $updateStmt->bind_param(
                            "ddddddss",
                            $formattedData['price'],
                            $formattedData['today_percent'],
                            $formattedData['today_p_l'],
                            $formattedData['open'],
                            $formattedData['high'],
                            $formattedData['low'],
                            $formattedData['last_update'],
                            $symbol
                        );
                        
                        if ($updateStmt->execute()) {
                            $updatedCount++;
                            error_log("Updated stock: $symbol - Price: {$formattedData['price']}");
                        } else {
                            $failedSymbols[] = $symbol;
                            error_log("Failed to update stock in DB: $symbol");
                        }
                    }
                } else {
                    $failedSymbols[] = $symbol;
                    error_log("Failed to get quote for: $symbol");
                }
                
                // Rate limiting - free tier allows 60 calls per minute
                usleep(1100000); // 1.1 second delay between calls
                
            } catch (Exception $e) {
                $failedSymbols[] = $symbol;
                error_log("Exception updating stock $symbol: " . $e->getMessage());
            }
        }

        error_log("Stock update completed. Updated: $updatedCount, Failed: " . count($failedSymbols));
        
        return [
            'updated' => $updatedCount,
            'failed' => $failedSymbols,
            'total' => count($symbols)
        ];
    }

    /**
     * Cache stock data to avoid excessive API calls
     */
    public static function getCachedQuote($symbol, $maxAgeMinutes = 60)
    {
        $cacheFile = __DIR__ . '/../cache/stock_' . $symbol . '.json';
        
        if (file_exists($cacheFile)) {
            $cacheData = json_decode(file_get_contents($cacheFile), true);
            
            if ($cacheData && isset($cacheData['timestamp'])) {
                $cacheAge = time() - $cacheData['timestamp'];
                
                // Return cached data if still fresh
                if ($cacheAge < ($maxAgeMinutes * 60)) {
                    return $cacheData['data'];
                }
            }
        }
        
        // Get fresh data from API
        $freshData = self::getQuote($symbol);
        
        if ($freshData) {
            // Save to cache
            $cacheData = [
                'timestamp' => time(),
                'data' => $freshData
            ];
            
            // Ensure cache directory exists
            $cacheDir = dirname($cacheFile);
            if (!is_dir($cacheDir)) {
                mkdir($cacheDir, 0755, true);
            }
            
            file_put_contents($cacheFile, json_encode($cacheData));
        }
        
        return $freshData;
    }
}

?>
