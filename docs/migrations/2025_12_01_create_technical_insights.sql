-- Create table for AI-generated technical insights
CREATE TABLE IF NOT EXISTS technical_insights (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    portfolio_id INT UNSIGNED NOT NULL,
    isin VARCHAR(20) NULL,
    scope ENUM('portfolio', 'instrument') NOT NULL DEFAULT 'portfolio',
    model VARCHAR(100) NOT NULL DEFAULT 'openai',
    generated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    raw_input_snapshot JSON NULL,
    insight_json JSON NULL,
    insight_text TEXT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    INDEX idx_portfolio_scope (portfolio_id, scope),
    INDEX idx_isin (isin)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
