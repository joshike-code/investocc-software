<?php
require_once __DIR__ . '/env-loader.php';
require_once __DIR__ . '/../core/response.php';

loadEnv(__DIR__ . '/../.env');

if(!file_exists(__DIR__ . '/../.env')) {
    Response::error('env file not found', 500);
}

return [
    'system' => [
        'host_link' => $_ENV['HOST_LINK'] ?? getenv('HOST_LINK'),
    ],
    'platform' => [
        'name' => $_ENV['PLATFORM_NAME'] ?? getenv('PLATFORM_NAME'),
        'url' => $_ENV['PLATFORM_URL'] ?? getenv('PLATFORM_URL'),
        'address' => $_ENV['PLATFORM_ADDRESS'] ?? getenv('PLATFORM_ADDRESS'),
        'supportmail' => $_ENV['PLATFORM_SUPPORT_MAIL'] ?? getenv('PLATFORM_SUPPORT_MAIL')
    ],
    'db' => [
        'host' => $_ENV['DB_HOST'] ?? getenv('DB_HOST'),
        'port' => $_ENV['DB_HOST'] ?? getenv('DB_PORT'),
        'name' => $_ENV['DB_NAME'] ?? getenv('DB_NAME'),
        'username' => $_ENV['DB_USERNAME'] ?? getenv('DB_USERNAME'),
        'password' => $_ENV['DB_PASSWORD'] ?? getenv('DB_PASSWORD'),
    ],
    'jwt' => [
        'secret_key' => $_ENV['JWT_SECRET_KEY'] ?? getenv('JWT_SECRET_KEY')
    ],
    'degiant' => [
        'passkey' => $_ENV['DEGIANT_PASSKEY'] ?? getenv('DEGIANT_PASSKEY')
    ],
    'exchangeratesapi' => [
        'api_key' => $_ENV['EXCHANGE_RATES_API_KEY'] ?? getenv('EXCHANGE_RATES_API_KEY')
    ],
    'finnhub' => [
        'api_key' => $_ENV['FINNHUB_API_KEY'] ?? getenv('FINNHUB_API_KEY')
    ],
    'phpmailer' => [
        'host' => $_ENV['PHPMAILER_HOST'] ?? getenv('PHPMAILER_HOST'),
        'username' => $_ENV['PHPMAILER_USERNAME'] ?? getenv('PHPMAILER_USERNAME'),
        'from' => $_ENV['PHPMAILER_FROM'] ?? getenv('PHPMAILER_FROM'),
        'password' => $_ENV['PHPMAILER_PASSWORD'] ?? getenv('PHPMAILER_PASSWORD'),
        'auth' => filter_var($_ENV['PHPMAILER_AUTH'] ?? getenv('PHPMAILER_AUTH'), FILTER_VALIDATE_BOOLEAN),
        'security' => $_ENV['PHPMAILER_SECURITY'] ?? getenv('PHPMAILER_SECURITY'),
        'port' => $_ENV['PHPMAILER_PORT'] ?? getenv('PHPMAILER_PORT')
    ]
];
