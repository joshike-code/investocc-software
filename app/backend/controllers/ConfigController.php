<?php

require_once __DIR__ . '/../services/ConfigService.php';
require_once __DIR__ . '/../core/response.php';

class ConfigController {

    public static function validateConfiguration() {
        $input = json_decode(file_get_contents('php://input'), true);
        
        $configKey = $input['licenseKey'] ?? null;
        $domain = $input['domain'] ?? null;
        $deviceFingerprint = $input['deviceFingerprint'] ?? null;
        
        $result = ConfigService::validateConfigurationKey($configKey, $domain, $deviceFingerprint);
        Response::success($result);
    }

    public static function getConfigurationStatus($domain) {
        $status = ConfigService::getConfigurationStatus($domain);
        Response::success($status);
    }

    public static function verifyConfiguration() {
        $input = json_decode(file_get_contents('php://input'), true);
        
        $domain = $input['domain'] ?? null;
        $checksum = $input['checksum'] ?? null;
        
        $result = ConfigService::verifySystemConfiguration($domain, $checksum);
        Response::success($result);
    }
}
?>
