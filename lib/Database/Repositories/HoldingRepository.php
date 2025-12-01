<?php
/**
 * Holding Repository
 *
 * Handles holdings data operations including enriched view with computed values.
 *
 * @package TradingPortfolio\Database\Repositories
 * @version 0.3.0-MySQL
 */

require_once __DIR__ . '/BaseRepository.php';

class HoldingRepository extends BaseRepository
{
    protected $table = 'holdings';

    /**
     * Default portfolio ID (single portfolio app)
     */
    const DEFAULT_PORTFOLIO_ID = 1;

    /**
     * Get enriched holdings (uses v_holdings_enriched VIEW with computed values)
     *
     * @param int|null $portfolioId
     * @return array
     */
    public function getEnrichedHoldings($portfolioId = null)
    {
        $portfolioId = $portfolioId ?: self::DEFAULT_PORTFOLIO_ID;

        $sql = "
            SELECT *
            FROM v_holdings_enriched
            WHERE portfolio_id = ? AND is_active = 1
            ORDER BY market_value DESC
        ";

        $holdings = $this->fetchAll($sql, [$portfolioId]);

        return array_map(function($holding) {
            return [
                'id' => (int)$holding['id'],
                'ticker' => $holding['ticker'],
                'name' => $holding['name'],
                'asset_class' => $holding['asset_class'],
                'sector' => $holding['sector'],
                'isin' => $holding['isin'] ?? null,
                'quantity' => (float)$holding['quantity'],
                'avg_price' => (float)$holding['avg_price'],
                'current_price' => $holding['current_price'] !== null ? (float)$holding['current_price'] : null,
                'price_source' => $holding['price_source'],
                'dividend_yield' => $holding['dividend_yield'] !== null ? (float)$holding['dividend_yield'] : null,
                'annual_dividend' => $holding['annual_dividend'] !== null ? (float)$holding['annual_dividend'] : null,
                'dividend_frequency' => $holding['dividend_frequency'],
                'has_dividends' => (bool)$holding['has_dividends'],
                'total_dividends_5y' => $holding['total_dividends_5y'] !== null ? (int)$holding['total_dividends_5y'] : null,
                'fifty_two_week_high' => $holding['fifty_two_week_high'] !== null ? (float)$holding['fifty_two_week_high'] : null,
                'fifty_two_week_low' => $holding['fifty_two_week_low'] !== null ? (float)$holding['fifty_two_week_low'] : null,
                'ytd_change_percent' => $holding['ytd_change_percent'] !== null ? (float)$holding['ytd_change_percent'] : 0,
                'one_month_change_percent' => $holding['one_month_change_percent'] !== null ? (float)$holding['one_month_change_percent'] : 0,
                'three_month_change_percent' => $holding['three_month_change_percent'] !== null ? (float)$holding['three_month_change_percent'] : 0,
                'one_year_change_percent' => $holding['one_year_change_percent'] !== null ? (float)$holding['one_year_change_percent'] : 0,
                'previous_close' => $holding['previous_close'] !== null ? (float)$holding['previous_close'] : null,
                'day_high' => $holding['day_high'] !== null ? (float)$holding['day_high'] : null,
                'day_low' => $holding['day_low'] !== null ? (float)$holding['day_low'] : null,
                'volume' => $holding['volume'] !== null ? (int)$holding['volume'] : null,
                'exchange' => $holding['exchange'],
                'first_trade_date' => $holding['first_trade_date'] !== null ? (int)$holding['first_trade_date'] : null,
                'ema9' => $holding['ema9'] !== null ? (float)$holding['ema9'] : null,
                'ema21' => $holding['ema21'] !== null ? (float)$holding['ema21'] : null,
                'ema50' => $holding['ema50'] !== null ? (float)$holding['ema50'] : null,
                'ema200' => $holding['ema200'] !== null ? (float)$holding['ema200'] : null,
                'rsi14' => $holding['rsi14'] !== null ? (float)$holding['rsi14'] : null,
                'macd_value' => $holding['macd_value'] !== null ? (float)$holding['macd_value'] : null,
                'macd_signal' => $holding['macd_signal'] !== null ? (float)$holding['macd_signal'] : null,
                'macd_hist' => $holding['macd_hist'] !== null ? (float)$holding['macd_hist'] : null,
                'hist_vol_30d' => $holding['hist_vol_30d'] !== null ? (float)$holding['hist_vol_30d'] : null,
                'atr14_pct' => $holding['atr14_pct'] !== null ? (float)$holding['atr14_pct'] : null,
                'range_1y_percentile' => $holding['range_1y_percentile'] !== null ? (float)$holding['range_1y_percentile'] : null,
                'bb_percent_b' => $holding['bb_percent_b'] !== null ? (float)$holding['bb_percent_b'] : null,
                'atr14' => $holding['atr14'] !== null ? (float)$holding['atr14'] : null,
                'hist_vol_90d' => $holding['hist_vol_90d'] !== null ? (float)$holding['hist_vol_90d'] : null,
                'range_1m_min' => $holding['range_1m_min'] !== null ? (float)$holding['range_1m_min'] : null,
                'range_1m_max' => $holding['range_1m_max'] !== null ? (float)$holding['range_1m_max'] : null,
                'range_1m_percentile' => $holding['range_1m_percentile'] !== null ? (float)$holding['range_1m_percentile'] : null,
                'range_3m_min' => $holding['range_3m_min'] !== null ? (float)$holding['range_3m_min'] : null,
                'range_3m_max' => $holding['range_3m_max'] !== null ? (float)$holding['range_3m_max'] : null,
                'range_3m_percentile' => $holding['range_3m_percentile'] !== null ? (float)$holding['range_3m_percentile'] : null,
                'range_6m_min' => $holding['range_6m_min'] !== null ? (float)$holding['range_6m_min'] : null,
                'range_6m_max' => $holding['range_6m_max'] !== null ? (float)$holding['range_6m_max'] : null,
                'range_6m_percentile' => $holding['range_6m_percentile'] !== null ? (float)$holding['range_6m_percentile'] : null,
                'range_1y_min' => $holding['range_1y_min'] !== null ? (float)$holding['range_1y_min'] : null,
                'range_1y_max' => $holding['range_1y_max'] !== null ? (float)$holding['range_1y_max'] : null,
                'fib_low' => $holding['fib_low'] !== null ? (float)$holding['fib_low'] : null,
                'fib_high' => $holding['fib_high'] !== null ? (float)$holding['fib_high'] : null,
                'fib_23_6' => $holding['fib_23_6'] !== null ? (float)$holding['fib_23_6'] : null,
                'fib_38_2' => $holding['fib_38_2'] !== null ? (float)$holding['fib_38_2'] : null,
                'fib_50_0' => $holding['fib_50_0'] !== null ? (float)$holding['fib_50_0'] : null,
                'fib_61_8' => $holding['fib_61_8'] !== null ? (float)$holding['fib_61_8'] : null,
                'fib_78_6' => $holding['fib_78_6'] !== null ? (float)$holding['fib_78_6'] : null,
                'fib_23_6_dist_pct' => $holding['fib_23_6_dist_pct'] !== null ? (float)$holding['fib_23_6_dist_pct'] : null,
                'fib_38_2_dist_pct' => $holding['fib_38_2_dist_pct'] !== null ? (float)$holding['fib_38_2_dist_pct'] : null,
                'fib_50_0_dist_pct' => $holding['fib_50_0_dist_pct'] !== null ? (float)$holding['fib_50_0_dist_pct'] : null,
                'fib_61_8_dist_pct' => $holding['fib_61_8_dist_pct'] !== null ? (float)$holding['fib_61_8_dist_pct'] : null,
                'fib_78_6_dist_pct' => $holding['fib_78_6_dist_pct'] !== null ? (float)$holding['fib_78_6_dist_pct'] : null,
                'bb_middle' => $holding['bb_middle'] !== null ? (float)$holding['bb_middle'] : null,
                'bb_upper' => $holding['bb_upper'] !== null ? (float)$holding['bb_upper'] : null,
                'bb_lower' => $holding['bb_lower'] !== null ? (float)$holding['bb_lower'] : null,
                'bb_width_pct' => $holding['bb_width_pct'] !== null ? (float)$holding['bb_width_pct'] : null,
                'invested' => (float)$holding['invested'],
                'market_value' => (float)$holding['market_value'],
                'pnl' => (float)$holding['pnl'],
                'pnl_pct' => (float)$holding['pnl_pct'],
                'updated_at' => $holding['updated_at'],
                'is_active' => (int)($holding['is_active'] ?? 1)
            ];
        }, $holdings);
    }

    /**
     * Get active holdings (without computed values)
     *
     * @param int|null $portfolioId
     * @return array
     */
    public function getActiveHoldings($portfolioId = null)
    {
        $portfolioId = $portfolioId ?: self::DEFAULT_PORTFOLIO_ID;

        $sql = "
            SELECT *
            FROM holdings
            WHERE portfolio_id = ? AND is_active = 1
            ORDER BY ticker ASC
        ";

        return $this->fetchAll($sql, [$portfolioId]);
    }

    /**
     * Find holding by ISIN for a given portfolio
     *
     * @param string $isin
     * @param int|null $portfolioId
     * @return array|null
     */
    public function findByIsin(string $isin, $portfolioId = null): ?array
    {
        $portfolioId = $portfolioId ?: self::DEFAULT_PORTFOLIO_ID;

        $sql = "
            SELECT *
            FROM holdings
            WHERE portfolio_id = ? AND isin = ? AND is_active = 1
            LIMIT 1
        ";

        return $this->fetchOne($sql, [$portfolioId, $isin]);
    }

    /**
     * Update arbitrary fields on a holding (by ISIN)
     *
     * @param int $portfolioId
     * @param string $isin
     * @param array $fields
     * @return bool
     */
    public function updateFieldsByIsin(int $portfolioId, string $isin, array $fields): bool
    {
        if (empty($fields)) {
            return true;
        }

        $setParts = [];
        $params = [];
        foreach ($fields as $column => $value) {
            $setParts[] = "{$column} = ?";
            $params[] = $value;
        }
        $params[] = $portfolioId;
        $params[] = $isin;

        $sql = sprintf(
            "UPDATE holdings SET %s, updated_at = NOW() WHERE portfolio_id = ? AND isin = ? AND is_active = 1",
            implode(', ', $setParts)
        );

        return $this->execute($sql, $params) > 0;
    }

    /**
     * Find holding by ticker
     *
     * @param string $ticker
     * @param int|null $portfolioId
     * @return array|null
     */
    public function findByTicker($ticker, $portfolioId = null)
    {
        $portfolioId = $portfolioId ?: self::DEFAULT_PORTFOLIO_ID;

        $sql = "
            SELECT *
            FROM holdings
            WHERE portfolio_id = ? AND ticker = ? AND is_active = 1
            LIMIT 1
        ";

        return $this->fetchOne($sql, [$portfolioId, $ticker]);
    }

    /**
     * Update holding price (used by n8n webhook)
     *
     * @param string $ticker
     * @param float $price
     * @param string|null $source
     * @param int|null $portfolioId
     * @return bool
     */
    public function updatePrice($ticker, $price, $source = null, $portfolioId = null)
    {
        $portfolioId = $portfolioId ?: self::DEFAULT_PORTFOLIO_ID;

        $sql = "
            UPDATE holdings
            SET current_price = ?, price_source = ?, updated_at = NOW()
            WHERE portfolio_id = ? AND ticker = ? AND is_active = 1
        ";

        return $this->execute($sql, [$price, $source, $portfolioId, $ticker]) > 0;
    }

    /**
     * Bulk update prices (used by n8n webhook for multiple holdings)
     *
     * @param array $priceUpdates Array of [ticker, price, source]
     * @param int|null $portfolioId
     * @return array Results [success => bool, updated => int, failed => array]
     */
    public function bulkUpdatePrices(array $priceUpdates, $portfolioId = null)
    {
        $portfolioId = $portfolioId ?: self::DEFAULT_PORTFOLIO_ID;

        $updated = 0;
        $failed = [];

        try {
            $this->beginTransaction();

            foreach ($priceUpdates as $update) {
                $ticker = $update['ticker'] ?? $update['symbol'] ?? null;
                $price = $update['price'] ?? $update['current_price'] ?? null;
                $source = $update['source'] ?? $update['price_source'] ?? 'YahooFinance_v8';

                if (!$ticker || !$price) {
                    $failed[] = [
                        'ticker' => $ticker,
                        'reason' => 'Missing ticker or price'
                    ];
                    continue;
                }

                $result = $this->updatePrice($ticker, $price, $source, $portfolioId);

                if ($result) {
                    $updated++;
                } else {
                    $failed[] = [
                        'ticker' => $ticker,
                        'reason' => 'Update failed (ticker not found or not active)'
                    ];
                }
            }

            $this->commit();

            return [
                'success' => true,
                'updated' => $updated,
                'failed' => $failed,
                'timestamp' => date('Y-m-d H:i:s')
            ];

        } catch (Exception $e) {
            $this->rollback();
            error_log("Bulk price update failed: " . $e->getMessage());

            return [
                'success' => false,
                'updated' => 0,
                'failed' => $failed,
                'error' => $e->getMessage(),
                'timestamp' => date('Y-m-d H:i:s')
            ];
        }
    }

    /**
     * Create new holding
     *
     * @param array $data Holding data
     * @param int|null $portfolioId
     * @return int Holding ID
     */
    public function createHolding(array $data, $portfolioId = null)
    {
        $portfolioId = $portfolioId ?: self::DEFAULT_PORTFOLIO_ID;

        $holdingData = [
            'portfolio_id' => $portfolioId,
            'ticker' => $data['ticker'],
            'isin' => $data['isin'] ?? $data['ticker'] ?? null,
            'name' => $data['name'],
            'asset_class' => $data['asset_class'] ?? 'ETF',
            'quantity' => $data['quantity'],
            'avg_price' => $data['avg_price'],
            'current_price' => $data['current_price'] ?? null,
            'price_source' => $data['price_source'] ?? null,
            'is_active' => $data['is_active'] ?? true
        ];

        return $this->create($holdingData);
    }

    /**
     * Update holding quantity and average price
     *
     * @param string $ticker
     * @param float $quantity
     * @param float $avgPrice
     * @param int|null $portfolioId
     * @return bool
     */
    public function updateQuantityAndPrice($ticker, $quantity, $avgPrice, $portfolioId = null)
    {
        $portfolioId = $portfolioId ?: self::DEFAULT_PORTFOLIO_ID;

        $sql = "
            UPDATE holdings
            SET quantity = ?, avg_price = ?, updated_at = NOW()
            WHERE portfolio_id = ? AND ticker = ? AND is_active = 1
        ";

        return $this->execute($sql, [$quantity, $avgPrice, $portfolioId, $ticker]) > 0;
    }

    /**
     * Soft delete holding (set is_active = 0)
     *
     * @param string $ticker
     * @param int|null $portfolioId
     * @return bool
     */
    public function softDelete($ticker, $portfolioId = null)
    {
        $portfolioId = $portfolioId ?: self::DEFAULT_PORTFOLIO_ID;

        $sql = "
            UPDATE holdings
            SET is_active = 0, updated_at = NOW()
            WHERE portfolio_id = ? AND ticker = ?
        ";

        return $this->execute($sql, [$portfolioId, $ticker]) > 0;
    }

    /**
     * Soft delete holding by ISIN
     *
     * @param string $isin
     * @param int|null $portfolioId
     * @return bool
     */
    public function softDeleteByIsin(string $isin, $portfolioId = null): bool
    {
        $portfolioId = $portfolioId ?: self::DEFAULT_PORTFOLIO_ID;

        $sql = "
            UPDATE holdings
            SET is_active = 0, updated_at = NOW()
            WHERE portfolio_id = ? AND isin = ?
        ";

        return $this->execute($sql, [$portfolioId, $isin]) > 0;
    }

    /**
     * Hard delete holding by ticker
     *
     * @param string $ticker
     * @param int|null $portfolioId
     * @return bool
     */
    public function hardDeleteByTicker(string $ticker, $portfolioId = null): bool
    {
        $portfolioId = $portfolioId ?: self::DEFAULT_PORTFOLIO_ID;

        $sql = "DELETE FROM holdings WHERE portfolio_id = ? AND ticker = ? LIMIT 1";
        return $this->execute($sql, [$portfolioId, $ticker]) > 0;
    }

    /**
     * Hard delete holding by ISIN
     *
     * @param string $isin
     * @param int|null $portfolioId
     * @return bool
     */
    public function hardDeleteByIsin(string $isin, $portfolioId = null): bool
    {
        $portfolioId = $portfolioId ?: self::DEFAULT_PORTFOLIO_ID;

        $sql = "DELETE FROM holdings WHERE portfolio_id = ? AND isin = ? LIMIT 1";
        return $this->execute($sql, [$portfolioId, $isin]) > 0;
    }

    /**
     * Restore soft-deleted holding
     *
     * @param string $ticker
     * @param int|null $portfolioId
     * @return bool
     */
    public function restore($ticker, $portfolioId = null)
    {
        $portfolioId = $portfolioId ?: self::DEFAULT_PORTFOLIO_ID;

        $sql = "
            UPDATE holdings
            SET is_active = 1, updated_at = NOW()
            WHERE portfolio_id = ? AND ticker = ?
        ";

        return $this->execute($sql, [$portfolioId, $ticker]) > 0;
    }

    /**
     * Get holdings grouped by asset class
     *
     * @param int|null $portfolioId
     * @return array
     */
    public function getByAssetClass($portfolioId = null)
    {
        $portfolioId = $portfolioId ?: self::DEFAULT_PORTFOLIO_ID;

        $sql = "
            SELECT
                asset_class,
                COUNT(*) as count,
                SUM(quantity * COALESCE(current_price, avg_price)) as total_value
            FROM holdings
            WHERE portfolio_id = ? AND is_active = 1
            GROUP BY asset_class
            ORDER BY total_value DESC
        ";

        return $this->fetchAll($sql, [$portfolioId]);
    }

    /**
     * Get total portfolio value
     *
     * @param int|null $portfolioId
     * @return float
     */
    public function getTotalValue($portfolioId = null)
    {
        $portfolioId = $portfolioId ?: self::DEFAULT_PORTFOLIO_ID;

        $sql = "
            SELECT SUM(quantity * COALESCE(current_price, avg_price)) as total
            FROM holdings
            WHERE portfolio_id = ? AND is_active = 1
        ";

        $result = $this->fetchOne($sql, [$portfolioId]);
        return (float)($result['total'] ?? 0);
    }

    /**
     * Get total invested amount
     *
     * @param int|null $portfolioId
     * @return float
     */
    public function getTotalInvested($portfolioId = null)
    {
        $portfolioId = $portfolioId ?: self::DEFAULT_PORTFOLIO_ID;

        $sql = "
            SELECT SUM(quantity * avg_price) as total
            FROM holdings
            WHERE portfolio_id = ? AND is_active = 1
        ";

        $result = $this->fetchOne($sql, [$portfolioId]);
        return (float)($result['total'] ?? 0);
    }
}
