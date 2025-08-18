<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../core/response.php';

class ExchangeService
{
    
    private static $cacheFile = __DIR__ . '/../cache/exchange_rate.json';
    private static $cacheDuration = 3600 * 1;

    public static function getUsdToNairaRate()
    {
        $keys = require __DIR__ . '/../config/keys.php';
        $api_key = $keys['exchangeratesapi']['api_key'];
        $apiUrl = "https://api.exchangeratesapi.io/v1/latest?access_key=$api_key&symbols=NGN,USD";

        if (file_exists(self::$cacheFile)) {
            $cached = json_decode(file_get_contents(self::$cacheFile), true);
            if (time() - $cached['timestamp'] < self::$cacheDuration) {
                Response::success(['rate' => $cached['rate'], 'source' => 'cache']);
                return;
            }
        }

        $response = file_get_contents($apiUrl);
        if (!$response) {
            Response::error('Failed to fetch exchange rate', 500);
        }

        $data = json_decode($response, true);

        if (!isset($data['rates']['USD'], $data['rates']['NGN'])) {
            Response::error('Invalid data from exchange API', 500);
        }

        $usdToNaira = $data['rates']['NGN'] / $data['rates']['USD'];

        $cacheData = [
            'rate' => round($usdToNaira, 2),
            'timestamp' => time()
        ];

        file_put_contents(self::$cacheFile, json_encode($cacheData));

        Response::success(['rate' => $cacheData['rate'], 'source' => 'api']);
    }
}



?>