<?php

/**
 * Environment Synchronization Service
 * 
 * Synchronizes .env file with .env-example by adding missing keys
 * while preserving existing values and maintaining file structure.
 */
class EnvSyncService
{
    private static $envPath;
    private static $examplePath;
    
    /**
     * Initialize paths
     */
    private static function init($baseDir = null)
    {
        $baseDir = $baseDir ?? __DIR__ . '/..';
        self::$envPath = $baseDir . '/.env';
        self::$examplePath = $baseDir . '/.env-example';
    }
    
    /**
     * Synchronize .env with .env-example
     * 
     * @param string|null $baseDir Base directory path (defaults to parent of this file)
     * @return array Result with status and message
     */
    public static function syncEnvironmentFiles($baseDir = null)
    {
        try {
            self::init($baseDir);
            
            // Check if files exist
            if (!file_exists(self::$envPath)) {
                return [
                    'status' => 'error',
                    'message' => '.env file not found',
                    'details' => 'Path: ' . self::$envPath
                ];
            }
            
            if (!file_exists(self::$examplePath)) {
                return [
                    'status' => 'error',
                    'message' => '.env-example file not found',
                    'details' => 'Path: ' . self::$examplePath
                ];
            }
            
            // Parse both files
            $envVars = self::parseEnvFile(self::$envPath);           // Current .env (target)
            $exampleVars = self::parseEnvFile(self::$examplePath);   // .env-example (source of truth)
            
            // Find keys that exist in .env-example but are missing from .env
            // NOTE: We ONLY update .env, NEVER modify .env-example
            $missingKeys = [];
            $addedKeys = [];
            
            foreach ($exampleVars as $key => $exampleData) {
                if (!array_key_exists($key, $envVars)) {
                    $missingKeys[$key] = $exampleData;
                }
            }
            
            // If no missing keys, return success
            if (empty($missingKeys)) {
                return [
                    'status' => 'success',
                    'message' => 'Environment files are synchronized',
                    'added_keys' => [],
                    'total_keys' => count($envVars)
                ];
            }
            
            // Append missing keys to .env file
            $envContent = file_get_contents(self::$envPath);
            
            // Ensure file ends with newline
            if (!empty($envContent) && substr($envContent, -1) !== "\n") {
                $envContent .= "\n";
            }
            
            // Group missing keys by section
            $sections = self::groupKeysBySection($missingKeys);
            
            foreach ($sections as $sectionName => $sectionKeys) {
                // Add section header if it doesn't exist
                if (!empty($sectionName) && strpos($envContent, "# $sectionName") === false) {
                    $envContent .= "\n# $sectionName\n";
                }
                
                // Add each key
                foreach ($sectionKeys as $key => $data) {
                    $value = $data['value'];
                    $comment = !empty($data['comment']) ? "  #{$data['comment']}" : '';
                    
                    $envContent .= "$key=$value$comment\n";
                    $addedKeys[] = $key;
                }
            }
            
            // Write updated content back to .env
            if (file_put_contents(self::$envPath, $envContent) === false) {
                return [
                    'status' => 'error',
                    'message' => 'Failed to write to .env file',
                    'details' => 'Check file permissions for: ' . self::$envPath
                ];
            }
            
            return [
                'status' => 'success',
                'message' => 'Environment synchronized successfully',
                'added_keys' => $addedKeys,
                'total_added' => count($addedKeys),
                'total_keys' => count($envVars) + count($addedKeys)
            ];
            
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Environment synchronization failed',
                'details' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Parse .env file and extract key-value pairs with metadata
     */
    private static function parseEnvFile($filePath)
    {
        $content = file_get_contents($filePath);
        $lines = explode("\n", $content);
        
        $vars = [];
        $currentSection = '';
        
        foreach ($lines as $line) {
            $line = trim($line);
            
            // Skip empty lines
            if (empty($line)) {
                continue;
            }
            
            // Extract section headers
            if (preg_match('/^#\s*(.+)\s*Configuration/', $line, $matches)) {
                $currentSection = $matches[1];
                continue;
            }
            
            // Skip other comments that aren't inline
            if (strpos($line, '#') === 0) {
                continue;
            }
            
            // Parse key=value pairs
            if (preg_match('/^([A-Z_][A-Z0-9_]*)=(.*)$/', $line, $matches)) {
                $key = $matches[1];
                $fullValue = $matches[2];
                
                // Separate value from inline comment
                $value = '';
                $comment = '';
                
                if (preg_match('/^([^#]*?)(\s*#(.*))?$/', $fullValue, $valueMatches)) {
                    $value = trim($valueMatches[1]);
                    $comment = isset($valueMatches[3]) ? trim($valueMatches[3]) : '';
                }
                
                $vars[$key] = [
                    'value' => $value,
                    'comment' => $comment,
                    'section' => $currentSection
                ];
            }
        }
        
        return $vars;
    }
    
    /**
     * Group keys by their section
     */
    private static function groupKeysBySection($keys)
    {
        $sections = [];
        
        foreach ($keys as $key => $data) {
            $section = $data['section'] ?? 'Other';
            $sections[$section][$key] = $data;
        }
        
        return $sections;
    }
    
    /**
     * Get a summary of environment variables
     */
    public static function getEnvSummary($baseDir = null)
    {
        try {
            self::init($baseDir);
            
            if (!file_exists(self::$envPath) || !file_exists(self::$examplePath)) {
                return [
                    'status' => 'error',
                    'message' => 'Environment files not found'
                ];
            }
            
            $envVars = self::parseEnvFile(self::$envPath);
            $exampleVars = self::parseEnvFile(self::$examplePath);
            
            $missing = array_diff_key($exampleVars, $envVars);
            $extra = array_diff_key($envVars, $exampleVars);
            
            return [
                'status' => 'success',
                'env_keys' => count($envVars),
                'example_keys' => count($exampleVars),
                'missing_keys' => array_keys($missing),
                'extra_keys' => array_keys($extra),
                'is_synchronized' => empty($missing)
            ];
            
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }
}
