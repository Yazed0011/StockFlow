<?php

/**
 * Authentication Middleware
 * Validates JWT token and prepares user data
 */

require_once __DIR__ . "/jwt.php";

/* ===== جلب الهيدر ===== */
$headers = getallheaders();
$authHeader = $headers['Authorization'] ?? '';

if (!str_starts_with($authHeader, 'Bearer ')) {
    http_response_code(401);
    header("Content-Type: application/json");
    echo json_encode([
        "success" => false,
        "message" => "Unauthorized"
    ]);
    exit;
}

/* ===== استخراج التوكن ===== */
$token = substr($authHeader, 7);

/* ===== التحقق من JWT ===== */
$decoded = validateAccessToken($token, $JWT_SECRET, $JWT_ALGO);

if (!$decoded) {
    http_response_code(401);
    header("Content-Type: application/json");
    echo json_encode([
        "success" => false,
        "message" => "Invalid or expired token"
    ]);
    exit;
}

/* ===== تجهيز المستخدم ===== */
$user = [
    "id"    => (int) ($decoded->sub ?? 0),
    "name"  => $decoded->user->name  ?? null,
    "email" => $decoded->user->email ?? null,
    "admin" => (int) ($decoded->user->admin ?? 0),
];
