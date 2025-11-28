-- ============================================================================
-- Trading Portfolio - MySQL Database Schema
-- Version: 0.3.0-MySQL
-- Created: 2025-11-27
-- ============================================================================

-- Drop existing database if exists (use with caution in production)
-- DROP DATABASE IF EXISTS trading_portfolio;

-- Create database
CREATE DATABASE IF NOT EXISTS trading_portfolio
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE trading_portfolio;

-- ============================================================================
-- TABLE 1: portfolios
-- Core portfolio metadata
-- ============================================================================
CREATE TABLE IF NOT EXISTS portfolios (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    owner VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Portfolio metadata';

-- ============================================================================
-- TABLE 2: holdings
-- Individual holdings (ETFs, stocks, bonds, etc.)
-- ============================================================================
CREATE TABLE IF NOT EXISTS holdings (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    portfolio_id INT UNSIGNED NOT NULL,
    ticker VARCHAR(20) NOT NULL,
    name VARCHAR(255) NOT NULL,
    asset_class ENUM('ETF', 'Stock', 'Bond', 'Cash', 'Other') NOT NULL,
    quantity DECIMAL(12, 6) NOT NULL,
    avg_price DECIMAL(12, 4) NOT NULL,
    current_price DECIMAL(12, 4) NULL,
    price_source VARCHAR(50) NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (portfolio_id) REFERENCES portfolios(id) ON DELETE CASCADE,
    UNIQUE KEY unique_portfolio_ticker (portfolio_id, ticker),
    INDEX idx_ticker (ticker),
    INDEX idx_asset_class (asset_class),
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Holdings with current and historical prices';

-- ============================================================================
-- TABLE 3: transactions
-- All buy/sell/dividend transactions
-- ============================================================================
CREATE TABLE IF NOT EXISTS transactions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    portfolio_id INT UNSIGNED NOT NULL,
    ticker VARCHAR(20) NOT NULL,
    transaction_date DATE NOT NULL,
    type ENUM('BUY', 'SELL', 'DIVIDEND', 'FEE', 'DEPOSIT', 'WITHDRAWAL') NOT NULL,
    quantity DECIMAL(12, 6) NULL,
    price DECIMAL(12, 4) NULL,
    amount DECIMAL(12, 2) NOT NULL,
    fees DECIMAL(12, 2) DEFAULT 0,
    notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (portfolio_id) REFERENCES portfolios(id) ON DELETE CASCADE,
    INDEX idx_portfolio_date (portfolio_id, transaction_date),
    INDEX idx_ticker (ticker),
    INDEX idx_type (type),
    INDEX idx_date (transaction_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Complete transaction history';

-- ============================================================================
-- TABLE 4: dividend_payments
-- Dividend payments (forecast and received)
-- ============================================================================
CREATE TABLE IF NOT EXISTS dividend_payments (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    portfolio_id INT UNSIGNED NOT NULL,
    ticker VARCHAR(20) NOT NULL,
    ex_date DATE NOT NULL,
    payment_date DATE NULL,
    amount_per_share DECIMAL(12, 6) NOT NULL,
    total_amount DECIMAL(12, 2) NOT NULL,
    quantity DECIMAL(12, 6) NOT NULL,
    status ENUM('FORECAST', 'RECEIVED') DEFAULT 'FORECAST',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (portfolio_id) REFERENCES portfolios(id) ON DELETE CASCADE,
    INDEX idx_portfolio_date (portfolio_id, ex_date),
    INDEX idx_ticker (ticker),
    INDEX idx_status (status),
    INDEX idx_payment_date (payment_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Dividend payments calendar';

-- ============================================================================
-- TABLE 5: snapshots
-- Daily portfolio snapshots
-- ============================================================================
CREATE TABLE IF NOT EXISTS snapshots (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    portfolio_id INT UNSIGNED NOT NULL,
    snapshot_date DATE NOT NULL,
    total_invested DECIMAL(12, 2) NOT NULL,
    total_market_value DECIMAL(12, 2) NOT NULL,
    total_pnl DECIMAL(12, 2) NOT NULL,
    total_pnl_pct DECIMAL(8, 4) NOT NULL,
    total_dividends_received DECIMAL(12, 2) DEFAULT 0,
    metadata JSON NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (portfolio_id) REFERENCES portfolios(id) ON DELETE CASCADE,
    UNIQUE KEY unique_portfolio_snapshot (portfolio_id, snapshot_date),
    INDEX idx_date (snapshot_date),
    INDEX idx_portfolio_date (portfolio_id, snapshot_date DESC)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Daily portfolio value snapshots';

-- ============================================================================
-- TABLE 6: snapshot_holdings
-- Holdings state within each snapshot
-- ============================================================================
CREATE TABLE IF NOT EXISTS snapshot_holdings (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    snapshot_id INT UNSIGNED NOT NULL,
    ticker VARCHAR(20) NOT NULL,
    quantity DECIMAL(12, 6) NOT NULL,
    avg_price DECIMAL(12, 4) NOT NULL,
    current_price DECIMAL(12, 4) NOT NULL,
    market_value DECIMAL(12, 2) NOT NULL,
    invested DECIMAL(12, 2) NOT NULL,
    pnl DECIMAL(12, 2) NOT NULL,
    pnl_pct DECIMAL(8, 4) NOT NULL,

    FOREIGN KEY (snapshot_id) REFERENCES snapshots(id) ON DELETE CASCADE,
    INDEX idx_snapshot (snapshot_id),
    INDEX idx_ticker (ticker)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Holdings details within each snapshot';

-- ============================================================================
-- TABLE 7: allocation_by_asset_class
-- Asset class allocation breakdown
-- ============================================================================
CREATE TABLE IF NOT EXISTS allocation_by_asset_class (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    portfolio_id INT UNSIGNED NOT NULL,
    asset_class VARCHAR(50) NOT NULL,
    market_value DECIMAL(12, 2) NOT NULL,
    percentage DECIMAL(8, 4) NOT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (portfolio_id) REFERENCES portfolios(id) ON DELETE CASCADE,
    UNIQUE KEY unique_portfolio_class (portfolio_id, asset_class)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Asset class allocation percentages';

-- ============================================================================
-- TABLE 8: monthly_performance
-- Monthly portfolio performance metrics
-- ============================================================================
CREATE TABLE IF NOT EXISTS monthly_performance (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    portfolio_id INT UNSIGNED NOT NULL,
    year INT NOT NULL,
    month INT NOT NULL,
    month_label VARCHAR(10) NOT NULL,
    total_value DECIMAL(12, 2) NOT NULL,
    total_invested DECIMAL(12, 2) NOT NULL,
    total_gain DECIMAL(12, 2) NOT NULL,
    gain_pct DECIMAL(8, 4) NOT NULL,

    FOREIGN KEY (portfolio_id) REFERENCES portfolios(id) ON DELETE CASCADE,
    UNIQUE KEY unique_portfolio_month (portfolio_id, year, month),
    INDEX idx_portfolio_year (portfolio_id, year, month)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Monthly performance aggregates';

-- ============================================================================
-- TABLE 9: metadata_cache
-- Cache for computed values (optional)
-- ============================================================================
CREATE TABLE IF NOT EXISTS metadata_cache (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    portfolio_id INT UNSIGNED NOT NULL,
    cache_key VARCHAR(100) NOT NULL,
    cache_value TEXT NOT NULL,
    expires_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (portfolio_id) REFERENCES portfolios(id) ON DELETE CASCADE,
    UNIQUE KEY unique_portfolio_key (portfolio_id, cache_key),
    INDEX idx_expires (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Cache for computed metadata';

-- ============================================================================
-- TABLE 10: cron_logs
-- Cron job execution logs
-- ============================================================================
CREATE TABLE IF NOT EXISTS cron_logs (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    portfolio_id INT UNSIGNED NULL,
    job_type VARCHAR(50) NOT NULL,
    status ENUM('SUCCESS', 'ERROR', 'RUNNING') NOT NULL,
    message TEXT NULL,
    executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (portfolio_id) REFERENCES portfolios(id) ON DELETE SET NULL,
    INDEX idx_job_type (job_type),
    INDEX idx_executed (executed_at DESC)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Cron job execution history';

-- ============================================================================
-- VIEW 3: v_dividends_enriched
-- Dividendi con quantità effettive alla data di stacco (se disponibile snapshot)
-- ============================================================================
CREATE OR REPLACE VIEW v_dividends_enriched AS
WITH ranked AS (
    SELECT
        dp.id AS dp_id,
        dp.portfolio_id,
        dp.ticker,
        dp.ex_date,
        sh.quantity,
        s.snapshot_date,
        ROW_NUMBER() OVER (PARTITION BY dp.id ORDER BY s.snapshot_date DESC) AS rn
    FROM dividend_payments dp
    JOIN snapshots s
        ON s.portfolio_id = dp.portfolio_id
        AND s.snapshot_date <= dp.ex_date
    JOIN snapshot_holdings sh
        ON sh.snapshot_id = s.id
        AND sh.ticker = dp.ticker
)
SELECT
    dp.*,
    r.snapshot_date AS snapshot_date_used,
    CASE WHEN r.rn = 1 THEN r.quantity ELSE NULL END AS snapshot_quantity,
    COALESCE(CASE WHEN r.rn = 1 THEN r.quantity END, dp.quantity) AS quantity_at_ex_date,
    dp.amount_per_share * COALESCE(CASE WHEN r.rn = 1 THEN r.quantity END, dp.quantity) AS paid_amount,
    CASE WHEN r.rn = 1 THEN 1 ELSE 0 END AS owned_on_snapshot
FROM dividend_payments dp
LEFT JOIN ranked r
    ON r.dp_id = dp.id
    AND r.rn = 1;

-- ============================================================================
-- VIEW 1: v_holdings_enriched
-- Real-time computed values for current holdings
-- ============================================================================
CREATE OR REPLACE VIEW v_holdings_enriched AS
SELECT
    h.id,
    h.portfolio_id,
    h.ticker,
    h.name,
    h.asset_class,
    h.quantity,
    h.avg_price,
    h.current_price,
    h.price_source,
    (h.quantity * h.avg_price) AS invested,
    (h.quantity * COALESCE(h.current_price, h.avg_price)) AS market_value,
    (h.quantity * COALESCE(h.current_price, h.avg_price)) - (h.quantity * h.avg_price) AS pnl,
    CASE
        WHEN h.avg_price > 0 THEN
            (((COALESCE(h.current_price, h.avg_price) - h.avg_price) / h.avg_price) * 100)
        ELSE 0
    END AS pnl_pct,
    h.is_active,
    h.updated_at
FROM holdings h
WHERE h.is_active = TRUE;

-- ============================================================================
-- VIEW 2: v_portfolio_metadata
-- Real-time portfolio summary metadata
-- ============================================================================
CREATE OR REPLACE VIEW v_portfolio_metadata AS
SELECT
    p.id AS portfolio_id,
    p.name,
    p.owner,
    COUNT(DISTINCT h.ticker) AS total_holdings,
    COALESCE(SUM(h.quantity * h.avg_price), 0) AS total_invested,
    COALESCE(SUM(h.quantity * COALESCE(h.current_price, h.avg_price)), 0) AS total_market_value,
    COALESCE(SUM((h.quantity * COALESCE(h.current_price, h.avg_price)) - (h.quantity * h.avg_price)), 0) AS total_pnl,
    CASE
        WHEN COALESCE(SUM(h.quantity * h.avg_price), 0) > 0 THEN
            ((COALESCE(SUM(h.quantity * COALESCE(h.current_price, h.avg_price)), 0) - COALESCE(SUM(h.quantity * h.avg_price), 0)) / SUM(h.quantity * h.avg_price) * 100)
        ELSE 0
    END AS total_pnl_pct,
    COALESCE(
        (SELECT SUM(total_amount)
         FROM dividend_payments dp
         WHERE dp.portfolio_id = p.id AND dp.status = 'RECEIVED'),
        0
    ) AS total_dividends_received,
    p.updated_at AS last_update
FROM portfolios p
LEFT JOIN holdings h ON h.portfolio_id = p.id AND h.is_active = TRUE
GROUP BY p.id, p.name, p.owner, p.updated_at;

-- ============================================================================
-- MySQL User Setup (execute separately with appropriate privileges)
-- ============================================================================
-- CREATE USER IF NOT EXISTS 'portfolio_user'@'localhost'
--     IDENTIFIED BY 'secure_password_here';
--
-- GRANT SELECT, INSERT, UPDATE, DELETE, CREATE VIEW
--     ON trading_portfolio.*
--     TO 'portfolio_user'@'localhost';
--
-- FLUSH PRIVILEGES;

-- ============================================================================
-- Verification Queries
-- ============================================================================
-- Show all tables
-- SHOW TABLES;

-- Show table structures
-- DESCRIBE portfolios;
-- DESCRIBE holdings;
-- DESCRIBE transactions;
-- DESCRIBE dividend_payments;
-- DESCRIBE snapshots;
-- DESCRIBE snapshot_holdings;
-- DESCRIBE allocation_by_asset_class;
-- DESCRIBE monthly_performance;
-- DESCRIBE metadata_cache;
-- DESCRIBE cron_logs;

-- Test VIEWs
-- SELECT * FROM v_holdings_enriched;
-- SELECT * FROM v_portfolio_metadata;

-- ============================================================================
-- Schema Creation Complete
-- ============================================================================
