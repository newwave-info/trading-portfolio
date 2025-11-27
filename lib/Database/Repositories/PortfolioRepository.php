<?php
/**
 * Portfolio Repository
 *
 * Handles portfolio-level data operations including metadata, allocations, and performance.
 *
 * @package TradingPortfolio\Database\Repositories
 * @version 0.3.0-MySQL
 */

require_once __DIR__ . '/BaseRepository.php';

class PortfolioRepository extends BaseRepository
{
    protected $table = 'portfolios';

    /**
     * Default portfolio ID (single portfolio app)
     */
    const DEFAULT_PORTFOLIO_ID = 1;

    /**
     * Get portfolio metadata (uses v_portfolio_metadata VIEW)
     *
     * @param int|null $portfolioId
     * @return array
     */
    public function getMetadata($portfolioId = null)
    {
        $portfolioId = $portfolioId ?: self::DEFAULT_PORTFOLIO_ID;

        $sql = "SELECT * FROM v_portfolio_metadata WHERE portfolio_id = ?";
        $metadata = $this->fetchOne($sql, [$portfolioId]);

        if (!$metadata) {
            // Return empty metadata if portfolio doesn't exist
            return [
                'portfolio_name' => 'My Portfolio',
                'owner' => 'Owner',
                'total_holdings' => 0,
                'total_invested' => 0,
                'total_market_value' => 0,
                'total_pnl' => 0,
                'total_pnl_pct' => 0,
                'total_dividends_received' => 0,
                'last_update' => date('Y-m-d H:i:s')
            ];
        }

        // Format for compatibility with current application structure
        return [
            'portfolio_name' => $metadata['name'],
            'owner' => $metadata['owner'],
            'total_holdings' => (int)$metadata['total_holdings'],
            'total_invested' => (float)$metadata['total_invested'],
            'total_market_value' => (float)$metadata['total_market_value'],
            'total_pnl' => (float)$metadata['total_pnl'],
            'total_pnl_pct' => (float)$metadata['total_pnl_pct'],
            'total_dividends_received' => (float)$metadata['total_dividends_received'],
            'last_update' => $metadata['last_update']
        ];
    }

    /**
     * Get asset class allocations
     *
     * @param int|null $portfolioId
     * @return array
     */
    public function getAllocations($portfolioId = null)
    {
        $portfolioId = $portfolioId ?: self::DEFAULT_PORTFOLIO_ID;

        $sql = "
            SELECT
                asset_class,
                market_value,
                percentage
            FROM allocation_by_asset_class
            WHERE portfolio_id = ?
            ORDER BY market_value DESC
        ";

        $allocations = $this->fetchAll($sql, [$portfolioId]);

        // Format for compatibility
        return array_map(function($allocation) {
            return [
                'asset_class' => $allocation['asset_class'],
                'market_value' => (float)$allocation['market_value'],
                'percentage' => (float)$allocation['percentage']
            ];
        }, $allocations);
    }

    /**
     * Update asset class allocations
     *
     * @param array $allocations Array of [asset_class, market_value, percentage]
     * @param int|null $portfolioId
     * @return bool
     */
    public function updateAllocations(array $allocations, $portfolioId = null)
    {
        $portfolioId = $portfolioId ?: self::DEFAULT_PORTFOLIO_ID;

        try {
            $this->beginTransaction();

            // Delete existing allocations
            $this->execute(
                "DELETE FROM allocation_by_asset_class WHERE portfolio_id = ?",
                [$portfolioId]
            );

            // Insert new allocations
            foreach ($allocations as $allocation) {
                $this->execute(
                    "INSERT INTO allocation_by_asset_class
                     (portfolio_id, asset_class, market_value, percentage)
                     VALUES (?, ?, ?, ?)",
                    [
                        $portfolioId,
                        $allocation['asset_class'],
                        $allocation['market_value'],
                        $allocation['percentage']
                    ]
                );
            }

            $this->commit();
            return true;

        } catch (Exception $e) {
            $this->rollback();
            error_log("Failed to update allocations: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get monthly performance for a specific year
     *
     * @param int $year
     * @param int|null $portfolioId
     * @return array
     */
    public function getMonthlyPerformance($year, $portfolioId = null)
    {
        $portfolioId = $portfolioId ?: self::DEFAULT_PORTFOLIO_ID;

        $sql = "
            SELECT
                month,
                month_label,
                total_value,
                total_invested,
                total_gain,
                gain_pct
            FROM monthly_performance
            WHERE portfolio_id = ? AND year = ?
            ORDER BY month ASC
        ";

        $performance = $this->fetchAll($sql, [$portfolioId, $year]);

        // Format for compatibility
        return array_map(function($item) {
            return [
                'month' => $item['month_label'],
                'value' => (float)$item['total_value'],
                'invested' => (float)$item['total_invested'],
                'gain' => (float)$item['total_gain'],
                'gain_pct' => (float)$item['gain_pct']
            ];
        }, $performance);
    }

    /**
     * Update monthly performance data
     *
     * @param array $performanceData Array of monthly performance records
     * @param int $year
     * @param int|null $portfolioId
     * @return bool
     */
    public function updateMonthlyPerformance(array $performanceData, $year, $portfolioId = null)
    {
        $portfolioId = $portfolioId ?: self::DEFAULT_PORTFOLIO_ID;

        try {
            $this->beginTransaction();

            foreach ($performanceData as $data) {
                // Upsert: INSERT ... ON DUPLICATE KEY UPDATE
                $this->execute(
                    "INSERT INTO monthly_performance
                     (portfolio_id, year, month, month_label, total_value, total_invested, total_gain, gain_pct)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                     ON DUPLICATE KEY UPDATE
                        total_value = VALUES(total_value),
                        total_invested = VALUES(total_invested),
                        total_gain = VALUES(total_gain),
                        gain_pct = VALUES(gain_pct)",
                    [
                        $portfolioId,
                        $year,
                        $data['month'],
                        $data['month_label'],
                        $data['total_value'],
                        $data['total_invested'],
                        $data['total_gain'],
                        $data['gain_pct']
                    ]
                );
            }

            $this->commit();
            return true;

        } catch (Exception $e) {
            $this->rollback();
            error_log("Failed to update monthly performance: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update portfolio last_update timestamp
     *
     * @param int|null $portfolioId
     * @return bool
     */
    public function updateLastUpdate($portfolioId = null)
    {
        $portfolioId = $portfolioId ?: self::DEFAULT_PORTFOLIO_ID;

        return $this->execute(
            "UPDATE portfolios SET updated_at = NOW() WHERE id = ?",
            [$portfolioId]
        ) > 0;
    }

    /**
     * Get or create default portfolio
     *
     * @param string $name
     * @param string $owner
     * @return int Portfolio ID
     */
    public function getOrCreateDefault($name = 'My Portfolio', $owner = 'Owner')
    {
        // Check if default portfolio exists
        $portfolio = $this->find(self::DEFAULT_PORTFOLIO_ID);

        if (!$portfolio) {
            // Create default portfolio
            return $this->create([
                'name' => $name,
                'owner' => $owner
            ]);
        }

        return self::DEFAULT_PORTFOLIO_ID;
    }
}
