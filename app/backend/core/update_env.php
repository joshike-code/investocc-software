<?php

require_once __DIR__ . '/../core/response.php';

function updateEnvValue($filePath, $key, $newValue) {
    if (!file_exists($filePath)) {
        Response::error('.env file not found', 404);
    }

    $lines = file($filePath, FILE_IGNORE_NEW_LINES);
    $found = false;

    foreach ($lines as &$line) {
        if (strpos(trim($line), "$key=") === 0) {
            $line = "$key=$newValue";
            $found = true;
            break;
        }
    }

    if (!$found) {
        // Append if key not found
        $lines[] = "$key=$newValue";
    }

    // Write back to file
    $result = file_put_contents($filePath, implode(PHP_EOL, $lines) . PHP_EOL);

    if ($result === false) {
        Response::error('Failed to write to .env file', 500);
    }

    return true;
}

// // Example usage
// $envFile = __DIR__ . '/../.env'; // Change path as needed
// $keyToUpdate = 'API_KEY';
// $newValue = 'new_api_key_123456';

// updateEnvValue($envFile, $keyToUpdate, $newValue);