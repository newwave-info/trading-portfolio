<?php
/**
 * API Configuration
 *
 * Configuration settings for the Recommendations API
 */

return [
    // CORS Settings
    'cors' => [
        'allowed_origins' => [
            'http://localhost',
            'https://localhost',
            'https://your-domain.com',
            'https://trading-portfolio.example.com'
        ],
        'allowed_methods' => ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
        'allowed_headers' => ['Content-Type', 'Authorization', 'X-Requested-With'],
        'max_age' => 86400, // 24 hours
    ],

    // Rate Limiting
    'rate_limit' => [
        'enabled' => true,
        'max_requests' => 60, // per minute
        'window' => 60, // seconds
        'storage' => 'file', // 'file' or 'redis'
        'file_path' => __DIR__ . '/../logs/api_rate_limit.json',
        'redis_key_prefix' => 'api_rate_limit:'
    ],

    // Logging
    'logging' => [
        'enabled' => true,
        'file_path' => __DIR__ . '/../logs/api_recommendations.log',
        'level' => 'info', // debug, info, warning, error
        'max_size' => 10 * 1024 * 1024, // 10MB
        'rotate' => true,
    ],

    // Pagination
    'pagination' => [
        'default_per_page' => 20,
        'max_per_page' => 100,
        'default_order_by' => 'created_at',
        'default_order_dir' => 'DESC',
        'allowed_order_by' => ['created_at', 'confidence_score', 'urgency', 'trigger_price', 'expires_at']
    ],

    // Validation
    'validation' => [
        'strict_mode' => true,
        'allowed_types' => [
            'BUY_LIMIT', 'BUY_MARKET', 'SELL_PARTIAL', 'SELL_ALL',
            'SET_STOP_LOSS', 'SET_TAKE_PROFIT', 'REBALANCE'
        ],
        'allowed_urgency' => [
            'IMMEDIATO', 'QUESTA_SETTIMANA', 'PROSSIME_2_SETTIMANE', 'MONITORAGGIO'
        ],
        'allowed_status' => [
            'ACTIVE', 'EXECUTED', 'EXPIRED', 'IGNORED'
        ],
        'confidence_min' => 0,
        'confidence_max' => 100,
    ],

    // Security
    'security' => [
        'require_auth' => false, // Set to true for production
        'auth_header' => 'Authorization',
        'api_key_header' => 'X-API-Key',
        'allowed_ips' => [], // Empty array allows all IPs
        'blocked_ips' => [],
    ],

    // Alert Configuration
    'alerts' => [
        'email_enabled' => true,
        'telegram_enabled' => true,
        'sms_enabled' => false,
        'webhook_enabled' => false,
        'high_priority_threshold' => 80, // Confidence score threshold for high priority alerts
    ],

    // Notification Channels
    'notifications' => [
        'email' => [
            'from' => $_ENV['ALERT_EMAIL_FROM'] ?? 'noreply@trading-portfolio.local',
            'to' => $_ENV['ALERT_EMAIL_TO'] ?? 'admin@example.com',
            'smtp_host' => $_ENV['SMTP_HOST'] ?? 'localhost',
            'smtp_port' => $_ENV['SMTP_PORT'] ?? 587,
            'smtp_user' => $_ENV['SMTP_USER'] ?? '',
            'smtp_pass' => $_ENV['SMTP_PASS'] ?? '',
        ],
        'telegram' => [
            'bot_token' => $_ENV['TELEGRAM_BOT_TOKEN'] ?? '',
            'chat_id' => $_ENV['TELEGRAM_CHAT_ID'] ?? '',
        ],
    ],

    // Database
    'database' => [
        'default_portfolio_id' => 1,
        'connection_timeout' => 5, // seconds
        'query_timeout' => 30, // seconds
    ],

    // Performance
    'performance' => [
        'cache_enabled' => false,
        'cache_ttl' => 300, // 5 minutes
        'cache_key_prefix' => 'api_rec:',
        'compression_enabled' => true,
        'minify_json' => false,
    ]
];