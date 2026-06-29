<?php

namespace App\Libraries;

use PDO;
use PDOException;

class Database
{
    private static ?PDO $instance = null;

    /**
     * Returns a singleton PDO connection instance.
     * Reads credentials from .env file at project root.
     */
    public static function getConnection(): PDO
    {
        if (self::$instance === null) {
            // Load .env file
            $envPath = dirname(__DIR__, 2) . '/.env';
            if (file_exists($envPath)) {
                $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                foreach ($lines as $line) {
                    if (str_starts_with(trim($line), '#')) continue;
                    if (str_contains($line, '=')) {
                        [$key, $value] = explode('=', $line, 2);
                        $_ENV[trim($key)] = trim($value);
                    }
                }
            }

            $host    = $_ENV['DB_HOST'] ?? getenv('DB_HOST') ?? 'localhost';
            $port    = $_ENV['DB_PORT'] ?? getenv('DB_PORT') ?? '3306';
            $dbname  = $_ENV['DB_NAME'] ?? getenv('DB_NAME') ?? 'kampusin_db';
            $user    = $_ENV['DB_USER'] ?? getenv('DB_USER') ?? 'root';
            $pass    = $_ENV['DB_PASS'] ?? getenv('DB_PASS') ?? '';

            $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";

            try {
                self::$instance = new PDO($dsn, $user, $pass, [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]);
            } catch (PDOException $e) {
                // In production, log this — never expose connection strings
                error_log('Database connection failed: ' . $e->getMessage());
                http_response_code(500);
                die('<h1>Koneksi database gagal. Hubungi administrator.</h1>');
            }
        }

        return self::$instance;
    }

    // Prevent instantiation and cloning
    private function __construct() {}
    private function __clone() {}
}
