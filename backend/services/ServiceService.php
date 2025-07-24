<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../core/response.php';
require_once __DIR__ . '/../services/PlatformService.php';

class ServiceService
{
    private static $cacheFile = __DIR__ . '/../cache/services.json';
    private static $cacheDuration = 3600 * 800;

    public static function getApiServices()
    {
        $service_rate = PlatformService::getSetting('service_rate', 5);

        // Try reading from cache
        if (file_exists(self::$cacheFile)) {
            $cached = json_decode(file_get_contents(self::$cacheFile), true);

            if (
                is_array($cached) &&
                isset($cached['timestamp'], $cached['services']) &&
                (time() - $cached['timestamp'] < self::$cacheDuration)
            ) {
                // Return cached data with adjusted rates
                $services = self::applyMarkup($cached['services'], $service_rate);
                return ['services' => $services, 'source' => 'cache'];
            } else {
                // delete invalid cache
                @unlink(self::$cacheFile);
            }
        }

        // Fetch from API
        $keys = require __DIR__ . '/../config/keys.php';
        $api_url = $keys['sizzle']['api_url'];
        $data = [
            "key"    => $keys['sizzle']['api_key'],
            "action" => "services"
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $api_url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/x-www-form-urlencoded",
            "Accept: application/json",
            "User-Agent: Mozilla/5.0"
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        if (!$response) {
            Response::error('Failed to fetch services from API', 500);
            return;
        }

        $decoded = json_decode($response, true);

        if (!is_array($decoded)) {
            Response::error('Invalid response from API', 500);
            return;
        }

        // Cache it
        file_put_contents(self::$cacheFile, json_encode([
            'timestamp' => time(),
            'services' => $decoded
        ]));

        // Return with markup applied
        $services = self::applyMarkup($decoded, $service_rate);
        return ['services' => $services, 'source' => 'api'];
    }

    private static function applyMarkup(array $services, float $rate)
    {
        foreach ($services as &$service) {
            if (isset($service['rate'])) {
                $service['rate'] = round($service['rate'] * (1 + $rate / 100), 2);
            }
        }
        return $services;
    }
}



?>