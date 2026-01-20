<?php

/**
 * Database Connection
 * Uses environment variables from .env file
 */

require_once __DIR__ . "/config.php";

$dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME;
$username = DB_USER;
$password = DB_PASS;

try {
    $con = new PDO($dsn, $username, $password);
    $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $con->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Database connection error"
    ]);
    exit;
}
