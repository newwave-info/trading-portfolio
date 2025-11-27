<?php
/**
 * Database Manager - Singleton PDO Wrapper
 *
 * Manages database connections with connection pooling and transaction support.
 *
 * @package TradingPortfolio\Database
 * @version 0.3.0-MySQL
 */

class DatabaseManager
{
    /**
     * Singleton instance
     */
    private static $instance = null;

    /**
     * PDO connection
     */
    private $pdo;

    /**
     * Database configuration
     */
    private $config;

    /**
     * Private constructor to prevent direct instantiation
     */
    private function __construct()
    {
        $this->loadEnvironment();
        $this->loadConfig();
        $this->connect();
    }

    /**
     * Load environment variables from .env file
     */
    private function loadEnvironment()
    {
        $envFile = __DIR__ . '/../../.env';

        if (file_exists($envFile)) {
            $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

            foreach ($lines as $line) {
                // Skip comments
                if (strpos(trim($line), '#') === 0) {
                    continue;
                }

                // Parse KEY=VALUE
                if (strpos($line, '=') !== false) {
                    list($key, $value) = explode('=', $line, 2);
                    $key = trim($key);
                    $value = trim($value);

                    // Set environment variable if not already set
                    if (!getenv($key)) {
                        putenv("$key=$value");
                    }
                }
            }
        }
    }

    /**
     * Load database configuration
     */
    private function loadConfig()
    {
        $this->config = require __DIR__ . '/config.php';
    }

    /**
     * Create database connection
     *
     * @throws PDOException
     */
    private function connect()
    {
        try {
            $dsn = sprintf(
                "%s:host=%s;port=%s;dbname=%s;charset=%s",
                $this->config['driver'],
                $this->config['host'],
                $this->config['port'],
                $this->config['database'],
                $this->config['charset']
            );

            $this->pdo = new PDO(
                $dsn,
                $this->config['username'],
                $this->config['password'],
                $this->config['options']
            );

            // Set character set
            $this->pdo->exec("SET NAMES '{$this->config['charset']}' COLLATE '{$this->config['collation']}'");

        } catch (PDOException $e) {
            error_log("Database connection failed: " . $e->getMessage());
            throw new RuntimeException("Unable to connect to database: " . $e->getMessage());
        }
    }

    /**
     * Get singleton instance
     *
     * @return DatabaseManager
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Get PDO connection
     *
     * @return PDO
     */
    public function getConnection()
    {
        // Verify connection is alive
        if (!$this->isConnected()) {
            $this->connect();
        }

        return $this->pdo;
    }

    /**
     * Check if connection is alive
     *
     * @return bool
     */
    private function isConnected()
    {
        try {
            $this->pdo->query('SELECT 1');
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Begin transaction
     *
     * @return bool
     */
    public function beginTransaction()
    {
        try {
            return $this->pdo->beginTransaction();
        } catch (PDOException $e) {
            error_log("Failed to begin transaction: " . $e->getMessage());
            throw new RuntimeException("Transaction failed: " . $e->getMessage());
        }
    }

    /**
     * Commit transaction
     *
     * @return bool
     */
    public function commit()
    {
        try {
            return $this->pdo->commit();
        } catch (PDOException $e) {
            error_log("Failed to commit transaction: " . $e->getMessage());
            throw new RuntimeException("Commit failed: " . $e->getMessage());
        }
    }

    /**
     * Rollback transaction
     *
     * @return bool
     */
    public function rollback()
    {
        try {
            if ($this->pdo->inTransaction()) {
                return $this->pdo->rollBack();
            }
            return true;
        } catch (PDOException $e) {
            error_log("Failed to rollback transaction: " . $e->getMessage());
            throw new RuntimeException("Rollback failed: " . $e->getMessage());
        }
    }

    /**
     * Get last insert ID
     *
     * @param string|null $name Sequence name (for PostgreSQL)
     * @return string
     */
    public function lastInsertId($name = null)
    {
        return $this->pdo->lastInsertId($name);
    }

    /**
     * Execute a query and return statement
     *
     * @param string $sql SQL query
     * @param array $params Query parameters
     * @return PDOStatement
     * @throws PDOException
     */
    public function query($sql, array $params = [])
    {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            error_log("Query failed: " . $e->getMessage() . " | SQL: " . $sql);
            throw $e;
        }
    }

    /**
     * Execute an INSERT/UPDATE/DELETE query and return affected rows
     *
     * @param string $sql SQL query
     * @param array $params Query parameters
     * @return int Number of affected rows
     */
    public function execute($sql, array $params = [])
    {
        $stmt = $this->query($sql, $params);
        return $stmt->rowCount();
    }

    /**
     * Fetch single row
     *
     * @param string $sql SQL query
     * @param array $params Query parameters
     * @return array|null
     */
    public function fetchOne($sql, array $params = [])
    {
        $stmt = $this->query($sql, $params);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Fetch all rows
     *
     * @param string $sql SQL query
     * @param array $params Query parameters
     * @return array
     */
    public function fetchAll($sql, array $params = [])
    {
        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll();
    }

    /**
     * Fetch single column value
     *
     * @param string $sql SQL query
     * @param array $params Query parameters
     * @return mixed
     */
    public function fetchColumn($sql, array $params = [])
    {
        $stmt = $this->query($sql, $params);
        return $stmt->fetchColumn();
    }

    /**
     * Prevent cloning of singleton
     */
    private function __clone() {}

    /**
     * Prevent unserialization of singleton
     */
    public function __wakeup()
    {
        throw new RuntimeException("Cannot unserialize singleton");
    }

    /**
     * Close connection on destruct
     */
    public function __destruct()
    {
        $this->pdo = null;
    }
}
