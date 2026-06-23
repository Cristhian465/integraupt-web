<?php

declare(strict_types=1);

namespace Config;

class Cors
{
    private static array $allowedOrigins = [
        'http://localhost:5173',
        'http://127.0.0.1:5173',
    ];

    public static function apply(): void
    {
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '';

        if (in_array($origin, self::$allowedOrigins, true)) {
            header("Access-Control-Allow-Origin: $origin");
        }

        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
        header('Access-Control-Allow-Credentials: true');
        header('Content-Type: application/json; charset=utf-8');

        // Preflight request
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(204);
            exit;
        }
    }
}
