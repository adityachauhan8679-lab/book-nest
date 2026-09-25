<?php
/**
 * BookNest - Database Configuration & PDO Connection
 * College Project: BookNest Online Bookstore
 */

// Database Credentials (Standard XAMPP default is root with empty password)
define('DB_HOST', getenv('DB_HOST') ?: '127.0.0.1');
define('DB_PORT', getenv('DB_PORT') ?: '3306');
define('DB_NAME', getenv('DB_NAME') ?: 'booknest');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') !== false ? getenv('DB_PASS') : '');
define('DB_CHARSET', 'utf8mb4');

class Database {
    private static ?PDO $instance = null;
    private static bool $connectionFailed = false;
    private static ?string $errorMessage = null;

    /**
     * Get Singleton PDO connection instance
     */
    public static function getConnection(): ?PDO {
        if (self::$instance === null && !self::$connectionFailed) {
            $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . DB_CHARSET
            ];

            try {
                self::$instance = new PDO($dsn, DB_USER, DB_PASS, $options);
            } catch (PDOException $e) {
                self::$connectionFailed = true;
                self::$errorMessage = $e->getMessage();
                error_log("BookNest DB Connection Error: " . $e->getMessage());
            }
        }

        return self::$instance;
    }

    /**
     * Check if database connection is active
     */
    public static function isConnected(): bool {
        return self::getConnection() !== null;
    }

    /**
     * Get error message if connection failed
     */
    public static function getErrorMessage(): ?string {
        return self::$errorMessage;
    }

    /**
     * Helper to run prepared queries with parameters
     */
    public static function query(string $sql, array $params = []): PDOStatement {
        $pdo = self::getConnection();
        if (!$pdo) {
            throw new Exception("Database connection is not available.");
        }
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }
}
