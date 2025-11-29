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
            SELECT
                id,
                ticker,
                name,
                asset_class,
                quantity,
                avg_price,
                current_price,
                price_source,
                invested,
                market_value,
                pnl,
                pnl_pct,
                updated_at
            FROM v_holdings_enriched
            WHERE portfolio_id = ?
            ORDER BY market_value DESC
        ";

        $holdings = $this->fetchAll($sql, [$portfolioId]);

        // Format for compatibility with current application
        return array_map(function($holding) {
            return [
                'id' => (int)$holding['id'],
                'ticker' => $holding['ticker'],
                'name' => $holding['name'],
                'asset_class' => $holding['asset_class'],
                'quantity' => (float)$holding['quantity'],
                'avg_price' => (float)$holding['avg_price'],
                'current_price' => $holding['current_price'] ? (float)$holding['current_price'] : null,
                'price_source' => $holding['price_source'],
                'invested' => (float)$holding['invested'],
                'market_value' => (float)$holding['market_value'],
                'pnl' => (float)$holding['pnl'],
                'pnl_pct' => (float)$holding['pnl_pct'],
                'updated_at' => $holding['updated_at']
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
