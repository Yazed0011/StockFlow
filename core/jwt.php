<?php

/**
 * JWT Token Management
 * Handles token generation and validation
 */

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;

require_once __DIR__ . "/config.php";

// Use constants from config.php
$JWT_SECRET = JWT_SECRET;
$JWT_ALGO   = JWT_ALGO;
$JWT_EXP    = JWT_EXP_SECONDS;
/**
 * @param array $user
 * @param string $JWT_SECRET
 * @param string $JWT_ALGO
 * @param int $expSeconds
 * @return string
 */
function generateAccessToken(array $user, string $JWT_SECRET, string $JWT_ALGO = 'HS256', int $expSeconds = 3600): string
{
    $now = time();
    $payload = [
        'iss' => 'StockFlow-API',
        'iat' => $now,
        'exp' => $now + $expSeconds,
        // استخدام sub كبساطة: معرف المُستخدم
        'sub' => (string) ($user['id'] ?? ''),
        // معلومات إضافية في claim مخصص
        'user' => [
            'name'  => $user['name']  ?? null,
            'email' => $user['email'] ?? null,
            'admin' => $user['admin'] ?? 0,
        ],
    ];

    return JWT::encode($payload, $JWT_SECRET, $JWT_ALGO);
}

/**
 * @return string
 */
function refreshToken(int $bytes = 32): string
{
    // 32 bytes -> 64 hex chars
    return bin2hex(random_bytes($bytes));
}

/**
 * يحقق التوكن ويُرجع الكلايمز كـ object أو false عند الفشل
 *
 * @param string $token
 * @param string $JWT_SECRET
 * @param string $JWT_ALGO
 * @return object|false
 */
function validateAccessToken(string $token, string $JWT_SECRET, string $JWT_ALGO = 'HS256')
{
    try {
        // فك التوكن والتحقق من التوقيع والصلاحية
        $decoded = JWT::decode($token, new Key($JWT_SECRET, $JWT_ALGO));
        return $decoded;
    } catch (ExpiredException $e) {
        // توكن منتهي
        return false;
    } catch (SignatureInvalidException $e) {
        // توقيع غير صحيح
        return false;
    } catch (\UnexpectedValueException $e) {
        return false;
    } catch (\Exception $e) {
        // خطأ عام
        return false;
    }
}
