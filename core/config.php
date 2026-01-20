<?php

/**
 * Configuration File
 * Centralized configuration management using .env file
 */

require_once __DIR__ . "/../vendor/autoload.php";

use Dotenv\Dotenv;

// Load .env file from project root
$dotenv = Dotenv::createImmutable(__DIR__ . "/..");
$dotenv->load();

// Database Configuration
define('DB_HOST', $_ENV['DB_HOST'] ?? 'localhost');
define('DB_NAME', $_ENV['DB_NAME'] ?? 'StokFlow');
define('DB_USER', $_ENV['DB_USER'] ?? 'root');
define('DB_PASS', $_ENV['DB_PASS'] ?? '');

// JWT Configuration
define('JWT_SECRET', $_ENV['JWT_SECRET'] ?? null);
define('JWT_ALGO', $_ENV['JWT_ALGO'] ?? 'HS256');
define('JWT_EXP_SECONDS', (int) ($_ENV['JWT_EXP_SECONDS'] ?? 3600));

// Validate required configuration
if (empty(constant('JWT_SECRET'))) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "JWT_SECRET is not configured in .env file"
    ]);
    exit;
}
