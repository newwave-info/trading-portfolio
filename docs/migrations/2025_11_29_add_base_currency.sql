-- ============================================================================
-- Migration: add base_currency on portfolios + expose it downstream
-- ============================================================================

ALTER TABLE portfolios
    ADD COLUMN base_currency CHAR(3) NOT NULL DEFAULT 'EUR' AFTER owner;

UPDATE portfolios
    SET base_currency = 'EUR'
    WHERE base_currency IS NULL;

CREATE OR REPLACE VIEW v_portfolio_metadata AS
SELECT
    p.id AS portfolio_id,
    p.name,
    p.owner,
    p.base_currency,
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
GROUP BY p.id, p.name, p.owner, p.base_currency, p.updated_at;

-- Migration complete
