<?php
/**
 * Database Configuration
 *
 * Configuration settings for MySQL database connection.
 * Uses environment variables for security.
 *
 * @package TradingPortfolio\Database
 * @version 0.3.0-MySQL
 */

return [
    // Database driver
    "driver" => "mysql",

    // Connection settings
    "host" => getenv("DB_HOST") ?: "localhost",
    "port" => getenv("DB_PORT") ?: 3306,
    "database" => getenv("DB_NAME") ?: "trading_portfolio",
    "username" => getenv("DB_USER") ?: "poRtUsR25",
    "password" => getenv("DB_PASS") ?: "#55Sv83gt?d9dI796h",

    // Character set
    "charset" => "utf8mb4",
    "collation" => "utf8mb4_unicode_ci",

    // PDO options
    "options" => [
        // Exception mode for errors
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,

        // Fetch mode: associative arrays
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,

        // Disable emulated prepares for better security
        PDO::ATTR_EMULATE_PREPARES => false,

        // Enable persistent connections (connection pooling)
        PDO::ATTR_PERSISTENT => true,

        // Set timeout (in seconds)
        PDO::ATTR_TIMEOUT => 5,

        // Convert numeric values to strings (for DECIMAL precision)
        PDO::ATTR_STRINGIFY_FETCHES => false,
    ],
];
