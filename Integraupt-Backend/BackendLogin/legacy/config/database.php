<?php

declare(strict_types=1);

namespace Config;

use PDO;
use PDOException;

class Database
{
    private static ?PDO $connection = null;

    private static string $host     = 'localhost';
    private static string $dbname   = 'sisintupt';
    private static string $username = 'root';
    private static string $password = '';
    private static string $charset  = 'utf8mb4';

    public static function getConnection(): PDO
    {
        if (self::$connection === null) {
            $dsn = sprintf(
                'mysql:host=%s;dbname=%s;charset=%s',
                self::$host,
                self::$dbname,
                self::$charset
            );

            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];

            try {
                self::$connection = new PDO($dsn, self::$username, self::$password, $options);
            } catch (PDOException $e) {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Error de conexión a la base de datos.']);
                exit;
            }
        }

        return self::$connection;
    }
}
