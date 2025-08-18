<?php
require_once __DIR__ . '/core/response.php';
require_once __DIR__ . '/vendor/phinx-autoload.php';

use Phinx\Config\Config;
use Phinx\Migration\Manager;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\StreamOutput;

// Load config
$configArray = include __DIR__ . '/phinx.php';

// Create config object
$config = new Config($configArray);

// Fake input and output for CLI simulation
$input = new ArrayInput([]);
$output = new StreamOutput(fopen('php://output', 'w'));

// Create migration manager
$manager = new Manager($config, $input, $output);

try {
    $manager->migrate('development');

    // Return if included
    if (php_sapi_name() !== 'cli' && basename(__FILE__) !== basename($_SERVER['SCRIPT_FILENAME'])) {
        return [
            'status' => 'success'
        ];
    }

    // If run directly in browser
    echo "Migrations ran successfully.";
} catch (Exception $e) {
    if (php_sapi_name() !== 'cli' && basename(__FILE__) !== basename($_SERVER['SCRIPT_FILENAME'])) {
        return [
            'status' => 'error',
            'error' => $e->getMessage()
        ];
    }

    // If run directly in browser
    echo "Migration failed: " . $e->getMessage();
}