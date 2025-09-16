<?php
/**
 * Configuration Service  
 * Handles system configuration validation and storage
 */

require_once __DIR__ . '/../core/response.php';

class ConfigService {
    
    private static $secretKey = 'INV_2024_SECRET_KEY_HASH';

    private static function getConfigDir() {
        $base = __DIR__ . '/../cache';
        $hash1 = substr(md5('sys_cache_dir'), 0, 8);  
        $hash2 = substr(sha1('tmp_storage'), 0, 12);   
        $hash3 = substr(md5('cfg_handler'), 0, 8);    
        $hash4 = substr(sha1('auth_token'), 0, 10);    
        
        return $base . '/' . $hash1 . '/' . $hash2 . '/' . $hash3 . '/' . $hash4;
    }

    private static function ensureConfigDirectory() {
        $configDir = self::getConfigDir();
        if (!file_exists($configDir)) {
            mkdir($configDir, 0755, true);
            
           
            $base = __DIR__ . '/../cache';
            $levels = explode('/', str_replace($base . '/', '', $configDir));
            $currentPath = $base;
            
            foreach ($levels as $level) {
                $currentPath .= '/' . $level;
                if (!file_exists($currentPath . '/.htaccess')) {
                    file_put_contents($currentPath . '/.htaccess', "Deny from all\n");
                }
                if (!file_exists($currentPath . '/index.html')) {
                    file_put_contents($currentPath . '/index.html', '<!-- System Cache --><html><head><title>Cache</title></head><body>Cache Directory</body></html>');
                }
               
                if (!file_exists($currentPath . '/cache.dat')) {
                    file_put_contents($currentPath . '/cache.dat', base64_encode('System cache data ' . time()));
                }
            }
        }
    }
    
    
    private static function getConfigFilePath($domain) {
        $hash1 = md5($domain . self::$secretKey);
        $hash2 = sha1($hash1 . 'cfg_salt');
        $hash3 = md5($hash2 . time());
        $fileName = substr($hash1, 0, 8) . '_' . substr($hash2, 0, 12) . '.tmp';
        
        return self::getConfigDir() . '/' . $fileName;
    }

    private static function getAllConfigFiles() {
        $configDir = self::getConfigDir();
        $files = [];
        if (is_dir($configDir)) {
            $handle = opendir($configDir);
            while (($file = readdir($handle)) !== false) {
                if (pathinfo($file, PATHINFO_EXTENSION) === 'tmp' && strpos($file, '_') !== false) {
                    $files[] = $configDir . '/' . $file;
                }
            }
            closedir($handle);
        }
        return $files;
    }

    public static function validateConfigurationKey($configKey, $domain, $deviceFingerprint = null) {
        self::ensureConfigDirectory();
        try {
            error_log("Validating config key for domain: $domain");
            
            $parts = explode('.', $configKey);
            
            if (count($parts) !== 3) {
                error_log("Invalid key format - parts count: " . count($parts));
                Response::error('Invalid configuration key format', 400);
            }
            
            list($headerEncoded, $payloadEncoded, $signatureEncoded) = $parts;
            
            $header = json_decode(base64_decode($headerEncoded), true);
            $payload = json_decode(base64_decode($payloadEncoded), true);
            
            if (!$header || !$payload) {
                Response::error('Invalid configuration data', 400);
            }

            if (!self::verifySignature($headerEncoded, $payloadEncoded, $signatureEncoded)) {
                error_log("Signature verification failed for domain: $domain");
                Response::error('Configuration signature verification failed', 401);
            }
            
            error_log("Signature verified, validating claims for domain: $domain");
            
            self::validateClaims($payload, $domain);
            
            self::storeConfiguration($configKey, $domain, $payload, $deviceFingerprint);
            
            $expiryDate = new DateTime($payload['exp']);
            $now = new DateTime();
            $daysRemaining = max(0, $expiryDate->diff($now)->days);
            
            return [
                'valid' => true,
                'expiresAt' => $payload['exp'],
                'daysRemaining' => $daysRemaining,
                'type' => $payload['type'] ?? 'standard',
                'domain' => $payload['domain'] ?? $domain
            ];
            
        } catch (Exception $e) {
            error_log("Config validation exception: " . $e->getMessage());
            Response::error('Configuration validation failed', 500);
        }
    }
    
    private static function verifySignature($header, $payload, $signature) {
        try {
            $combined = ($header . '.' . $payload) . self::$secretKey;
            $hash = 0;
            
            for ($i = 0; $i < strlen($combined); $i++) {
                $char = ord($combined[$i]);
                $hash = (($hash << 5) - $hash) + $char;
                $hash = $hash & 0xFFFFFFFF; // Keep as 32-bit integer
            }
            
            $expectedSignature = base64_encode(dechex(abs($hash)));
            $providedSignature = base64_decode($signature);
            
            return hash_equals($expectedSignature, $providedSignature);
        } catch (Exception $e) {
            return false;
        }
    }
    
  
    private static function validateClaims($payload, $domain) {
        error_log("Validating claims for domain: $domain, issuer: " . ($payload['iss'] ?? 'missing'));

        if (($payload['iss'] ?? '') !== 'investocc.com') {
            error_log("Invalid issuer: " . ($payload['iss'] ?? 'missing'));
            Response::error('Configuration issuer not recognized', 401);
        }
        
        if (!isset($payload['exp']) || empty($payload['exp'])) {
            error_log("Missing expiry date");
            Response::error('Configuration expiry date is missing', 400);
        }
        
        try {
            $expiryDate = new DateTime($payload['exp']);
            $now = new DateTime();
            
            error_log("Expiry check - Expiry: " . $payload['exp'] . ", Now: " . $now->format('c'));
            
            $expiryDate->setTimezone(new DateTimeZone('UTC'));
            $now->setTimezone(new DateTimeZone('UTC'));
            
            if ($expiryDate < $now) {
                error_log("License expired - Expiry UTC: " . $expiryDate->format('c') . ", Now UTC: " . $now->format('c'));
                Response::error('Configuration has expired', 401);
            }
        } catch (Exception $e) {
            error_log("DateTime parsing error: " . $e->getMessage());
            Response::error('Invalid configuration expiry date format', 400);
        }
        
    
        if (isset($payload['domain']) && $payload['domain'] !== null && $payload['domain'] !== $domain) {
            Response::error('Configuration not valid for this domain', 401);
        }
    }
   
    private static function storeConfiguration($configKey, $domain, $payload, $deviceFingerprint) {
        try {
           
            $configData = [
                'k' => base64_encode($configKey),
                'd' => $domain,
                'a' => time(), 
                'e' => $payload['exp'],
                't' => $payload['type'] ?? 'standard',
                'f' => $deviceFingerprint,
                'v' => ($payload['validation_count'] ?? 0) + 1, 
                'l' => time(),
                'trial_used' => ($payload['type'] ?? 'standard') === 'trial' ? true : false
            ];
            
            
            $possibleFiles = [];
            for ($i = 0; $i < 5; $i++) {
                $hash1 = md5($domain . self::$secretKey . $i);
                $hash2 = sha1($hash1 . 'cfg_salt_' . $i);
                $fileName = substr($hash1, 0, 8) . '_' . substr($hash2, 0, 12) . '.tmp';
                $possibleFiles[] = self::getConfigDir() . '/' . $fileName;
            }
            
        
            $configFile = $possibleFiles[array_rand($possibleFiles)];
            
        
            $encodedData = base64_encode(json_encode($configData));
            $obfuscatedData = str_rot13($encodedData); 
            
            file_put_contents($configFile, $obfuscatedData);
            
            
            self::createDecoyFiles($domain);
            
        } catch (Exception $e) {
            error_log("Config storage error: " . $e->getMessage());
        }
    }
    

    private static function createDecoyFiles($domain) {
        for ($i = 0; $i < 3; $i++) {
            $decoyHash = md5($domain . 'decoy_' . $i . time());
            $decoyFile = self::getConfigDir() . '/' . substr($decoyHash, 0, 8) . '_' . substr($decoyHash, 8, 12) . '.tmp';
            
            $decoyData = [
                'cache_id' => uniqid(),
                'timestamp' => time(),
                'data' => base64_encode('system_cache_' . $i),
                'checksum' => md5('decoy_data_' . $i)
            ];
            
            $encodedDecoy = base64_encode(json_encode($decoyData));
            file_put_contents($decoyFile, str_rot13($encodedDecoy));
        }
    }
    

    public static function getConfigurationStatus($domain) {
        if (empty($domain)) {
            Response::error('Domain is required', 400);
        }
        
        try {
            self::ensureConfigDirectory();
            $configFiles = self::getAllConfigFiles();
            $trialEverUsed = false;
            $hasActiveLicense = false;
            $activeLicenseData = null;
            
            foreach ($configFiles as $file) {
                $data = self::loadConfigFromFile($file);
                
                if (!$data || isset($data['cache_id']) || !isset($data['d'])) {
                    continue;
                }
                
                if ($data['d'] === $domain) {
                    $wasTrialLicense = (isset($data['trial_used']) && $data['trial_used'] === true) || 
                                      (isset($data['t']) && $data['t'] === 'trial');
                    
                    if ($wasTrialLicense) {
                        $trialEverUsed = true;
                    }
                    
                    if (!isset($data['e']) || empty($data['e'])) {
                        continue;
                    }
                    
                    $expiryDate = new DateTime($data['e']);
                    $now = new DateTime();
                    
                    if ($expiryDate > $now) {
                        $hasActiveLicense = true;
                        $activeLicenseData = $data;
                        break;
                    }
                }
            }

            if ($hasActiveLicense && $activeLicenseData) {
                $expiryDate = new DateTime($activeLicenseData['e']);
                $now = new DateTime();
                $daysRemaining = $expiryDate->diff($now)->days;
                
                return [
                    'hasActiveLicense' => true,
                    'trialAlreadyUsed' => $trialEverUsed,
                    'licenseKey' => base64_decode($activeLicenseData['k']),
                    'expiresAt' => $activeLicenseData['e'],
                    'daysRemaining' => $daysRemaining,
                    'type' => $activeLicenseData['t'] ?? 'standard',
                    'activatedAt' => date('c', $activeLicenseData['a'])
                ];
            }
            
            if ($trialEverUsed) {
                return [
                    'hasActiveLicense' => false,
                    'trialAlreadyUsed' => true,
                    'message' => 'Trial configuration has expired'
                ];
            } else {
                return [
                    'hasActiveLicense' => false,
                    'trialAlreadyUsed' => false,
                    'message' => 'No active configuration found for this domain'
                ];
            }
            
        } catch (Exception $e) {
            error_log("Config status error: " . $e->getMessage());
            Response::error('Unable to check configuration status', 500);
        }
    }
    

    private static function loadConfigFromFile($filePath) {
        try {
            if (!file_exists($filePath)) {
                return null;
            }
            
            $obfuscatedData = file_get_contents($filePath);
            $encodedData = str_rot13($obfuscatedData);
            $jsonData = base64_decode($encodedData);
            
            return json_decode($jsonData, true);
        } catch (Exception $e) {
            return null;
        }
    }
    

    public static function verifySystemConfiguration($domain = null, $checksum = null) {
        try {
            $systemValid = true;
            $configCount = 0;
            
            if ($domain) {
                $status = self::getConfigurationStatus($domain);
                $systemValid = $status['hasActiveLicense'] ?? false;
                $configCount = $systemValid ? 1 : 0;
            } else {
                // Check all configurations
                $configFiles = self::getAllConfigFiles();
                
                foreach ($configFiles as $file) {
                    $data = self::loadConfigFromFile($file);
                    if ($data && isset($data['e']) && !empty($data['e'])) {
                        try {
                            $expiryDate = new DateTime($data['e']);
                            if ($expiryDate > new DateTime()) {
                                $configCount++;
                            }
                        } catch (Exception $dateException) {
                            // Skip invalid date entries
                            continue;
                        }
                    }
                }
            }
            
            return [
                'valid' => $systemValid,
                'configurationCount' => $configCount,
                'systemIntegrity' => 'verified',
                'timestamp' => date('c')
            ];
            
        } catch (Exception $e) {
            error_log("System verification error: " . $e->getMessage());
            Response::error('System verification failed', 500);
        }
    }
}
?>
