<?php
/**
 * Snapshot Repository
 *
 * Handles daily portfolio snapshot operations.
 *
 * @package TradingPortfolio\Database\Repositories
 * @version 0.3.0-MySQL
 */

require_once __DIR__ . '/BaseRepository.php';

class SnapshotRepository extends BaseRepository
{
    protected $table = 'snapshots';

    /**
     * Default portfolio ID (single portfolio app)
     */
    const DEFAULT_PORTFOLIO_ID = 1;

    /**
     * Get latest snapshot
     *
     * @param int|null $portfolioId
     * @return array|null
     */
    public function getLatest($portfolioId = null)
    {
        $portfolioId = $portfolioId ?: self::DEFAULT_PORTFOLIO_ID;

        $sql = "
            SELECT *
            FROM snapshots
            WHERE portfolio_id = ?
            ORDER BY snapshot_date DESC
            LIMIT 1
        ";

        return $this->fetchOne($sql, [$portfolioId]);
    }

    /**
     * Get snapshots by date range
     *
     * @param string $startDate
     * @param string $endDate
     * @param int|null $portfolioId
     * @return array
     */
    public function getByDateRange($startDate, $endDate, $portfolioId = null)
    {
        $portfolioId = $portfolioId ?: self::DEFAULT_PORTFOLIO_ID;

        $sql = "
            SELECT *
            FROM snapshots
            WHERE portfolio_id = ?
              AND snapshot_date BETWEEN ? AND ?
            ORDER BY snapshot_date ASC
        ";

        return $this->fetchAll($sql, [$portfolioId, $startDate, $endDate]);
    }

    /**
     * Get daily snapshots (last N days)
     *
     * @param int $days
     * @param int|null $portfolioId
     * @return array
     */
    public function getDailySnapshots($days = 5, $portfolioId = null)
    {
        $portfolioId = $portfolioId ?: self::DEFAULT_PORTFOLIO_ID;

        $sql = "
            SELECT *
            FROM snapshots
            WHERE portfolio_id = ?
            ORDER BY snapshot_date DESC
            LIMIT ?
        ";

        $snapshots = $this->fetchAll($sql, [$portfolioId, $days]);

        // Reverse to get chronological order
        return array_reverse($snapshots);
    }

    /**
     * Get snapshot by specific date
     *
     * @param string $date
     * @param int|null $portfolioId
     * @return array|null
     */
    public function getByDate($date, $portfolioId = null)
    {
        $portfolioId = $portfolioId ?: self::DEFAULT_PORTFOLIO_ID;

        $sql = "
            SELECT *
            FROM snapshots
            WHERE portfolio_id = ? AND snapshot_date = ?
            LIMIT 1
        ";

        return $this->fetchOne($sql, [$portfolioId, $date]);
    }

    /**
     * Create snapshot with holdings
     *
     * @param array $snapshot Snapshot data
     * @param array $holdings Array of holdings data
     * @param int|null $portfolioId
     * @return int Snapshot ID
     */
    public function createWithHoldings(array $snapshot, array $holdings, $portfolioId = null)
    {
        $portfolioId = $portfolioId ?: self::DEFAULT_PORTFOLIO_ID;

        try {
            $this->beginTransaction();

            // Create snapshot
            $snapshotData = [
                'portfolio_id' => $portfolioId,
                'snapshot_date' => $snapshot['date'] ?? date('Y-m-d'),
                'total_invested' => $snapshot['total_invested'],
                'total_market_value' => $snapshot['total_market_value'],
                'total_pnl' => $snapshot['total_pnl'],
                'total_pnl_pct' => $snapshot['total_pnl_pct'],
                'total_dividends_received' => $snapshot['total_dividends_received'] ?? 0,
                'metadata' => isset($snapshot['metadata']) ? json_encode($snapshot['metadata']) : null
            ];

            $snapshotId = $this->create($snapshotData);

            // Insert holdings for this snapshot
            foreach ($holdings as $holding) {
                $this->execute(
                    "INSERT INTO snapshot_holdings
                     (snapshot_id, ticker, quantity, avg_price, current_price, market_value, invested, pnl, pnl_pct)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)",
                    [
                        $snapshotId,
                        $holding['ticker'],
                        $holding['quantity'],
                        $holding['avg_price'],
                        $holding['current_price'],
                        $holding['market_value'],
                        $holding['invested'],
                        $holding['pnl'],
                        $holding['pnl_pct']
                    ]
                );
            }

            $this->commit();
            return $snapshotId;

        } catch (Exception $e) {
            $this->rollback();
            error_log("Failed to create snapshot: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get snapshot holdings
     *
     * @param int $snapshotId
     * @return array
     */
    public function getSnapshotHoldings($snapshotId)
    {
        $sql = "
            SELECT *
            FROM snapshot_holdings
            WHERE snapshot_id = ?
            ORDER BY market_value DESC
        ";

        return $this->fetchAll($sql, [$snapshotId]);
    }

    /**
     * Get monthly snapshots (last day of each month)
     *
     * @param int $year
     * @param int|null $portfolioId
     * @return array
     */
    public function getMonthlySnapshots($year, $portfolioId = null)
    {
        $portfolioId = $portfolioId ?: self::DEFAULT_PORTFOLIO_ID;

        $sql = "
            SELECT
                MONTH(snapshot_date) as month,
                MAX(snapshot_date) as snapshot_date,
                total_invested,
                total_market_value,
                total_pnl,
                total_pnl_pct
            FROM snapshots
            WHERE portfolio_id = ? AND YEAR(snapshot_date) = ?
            GROUP BY YEAR(snapshot_date), MONTH(snapshot_date)
            ORDER BY snapshot_date ASC
        ";

        return $this->fetchAll($sql, [$portfolioId, $year]);
    }

    /**
     * Get year-to-date snapshots
     *
     * @param int $year
     * @param int|null $portfolioId
     * @return array
     */
    public function getYearToDate($year, $portfolioId = null)
    {
        $startDate = "$year-01-01";
        $endDate = date('Y-m-d');

        return $this->getByDateRange($startDate, $endDate, $portfolioId);
    }

    /**
     * Delete old snapshots (for cleanup, if needed)
     *
     * @param string $beforeDate
     * @param int|null $portfolioId
     * @return int Number of deleted snapshots
     */
    public function deleteOldSnapshots($beforeDate, $portfolioId = null)
    {
        $portfolioId = $portfolioId ?: self::DEFAULT_PORTFOLIO_ID;

        $sql = "
            DELETE FROM snapshots
            WHERE portfolio_id = ? AND snapshot_date < ?
        ";

        return $this->execute($sql, [$portfolioId, $beforeDate]);
    }

    /**
     * Check if snapshot exists for date
     *
     * @param string $date
     * @param int|null $portfolioId
     * @return bool
     */
    public function existsForDate($date, $portfolioId = null)
    {
        $portfolioId = $portfolioId ?: self::DEFAULT_PORTFOLIO_ID;

        $sql = "
            SELECT 1
            FROM snapshots
            WHERE portfolio_id = ? AND snapshot_date = ?
            LIMIT 1
        ";

        return $this->db->fetchColumn($sql, [$portfolioId, $date]) !== false;
    }

    /**
     * Get performance comparison between two dates
     *
     * @param string $startDate
     * @param string $endDate
     * @param int|null $portfolioId
     * @return array|null
     */
    public function getPerformanceComparison($startDate, $endDate, $portfolioId = null)
    {
        $portfolioId = $portfolioId ?: self::DEFAULT_PORTFOLIO_ID;

        $start = $this->getByDate($startDate, $portfolioId);
        $end = $this->getByDate($endDate, $portfolioId);

        if (!$start || !$end) {
            return null;
        }

        $valueChange = $end['total_market_value'] - $start['total_market_value'];
        $pnlChange = $end['total_pnl'] - $start['total_pnl'];
        $pctChange = $start['total_market_value'] > 0
            ? (($valueChange / $start['total_market_value']) * 100)
            : 0;

        return [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'start_value' => (float)$start['total_market_value'],
            'end_value' => (float)$end['total_market_value'],
            'value_change' => (float)$valueChange,
            'pnl_change' => (float)$pnlChange,
            'pct_change' => (float)$pctChange
        ];
    }

    /**
     * Get all snapshots count
     *
     * @param int|null $portfolioId
     * @return int
     */
    public function getCount($portfolioId = null)
    {
        $portfolioId = $portfolioId ?: self::DEFAULT_PORTFOLIO_ID;

        return $this->count(['portfolio_id' => $portfolioId]);
    }

    /**
     * Upsert snapshot for a date and replace snapshot_holdings.
     *
     * @param array $snapshot Snapshot data (date, totals, metadata)
     * @param array $holdings Holdings enriched array
     * @param int|null $portfolioId
     * @return int Snapshot ID
     * @throws Exception
     */
    public function upsertWithHoldings(array $snapshot, array $holdings, $portfolioId = null)
    {
        $portfolioId = $portfolioId ?: self::DEFAULT_PORTFOLIO_ID;
        $date = $snapshot['date'] ?? date('Y-m-d');

        try {
            $this->beginTransaction();

            $existing = $this->getByDate($date, $portfolioId);

            if ($existing) {
                $snapshotId = (int) $existing['id'];

                // Update snapshot totals
                $this->execute(
                    "UPDATE snapshots
                     SET total_invested = ?, total_market_value = ?, total_pnl = ?, total_pnl_pct = ?, total_dividends_received = ?, metadata = ?
                     WHERE id = ?",
                    [
                        $snapshot['total_invested'],
                        $snapshot['total_market_value'],
                        $snapshot['total_pnl'],
                        $snapshot['total_pnl_pct'],
                        $snapshot['total_dividends_received'] ?? 0,
                        isset($snapshot['metadata']) ? json_encode($snapshot['metadata']) : null,
                        $snapshotId
                    ]
                );

                // Replace holdings
                $this->execute("DELETE FROM snapshot_holdings WHERE snapshot_id = ?", [$snapshotId]);
            } else {
                $snapshotData = [
                    'portfolio_id' => $portfolioId,
                    'snapshot_date' => $date,
                    'total_invested' => $snapshot['total_invested'],
                    'total_market_value' => $snapshot['total_market_value'],
                    'total_pnl' => $snapshot['total_pnl'],
                    'total_pnl_pct' => $snapshot['total_pnl_pct'],
                    'total_dividends_received' => $snapshot['total_dividends_received'] ?? 0,
                    'metadata' => isset($snapshot['metadata']) ? json_encode($snapshot['metadata']) : null
                ];

                $snapshotId = $this->create($snapshotData);
            }

            // Insert holdings for this snapshot
            foreach ($holdings as $holding) {
                $this->execute(
                    "INSERT INTO snapshot_holdings
                     (snapshot_id, ticker, quantity, avg_price, current_price, market_value, invested, pnl, pnl_pct)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)",
                    [
                        $snapshotId,
                        $holding['ticker'],
                        $holding['quantity'],
                        $holding['avg_price'],
                        $holding['current_price'] ?? $holding['avg_price'],
                        $holding['market_value'],
                        $holding['invested'],
                        $holding['pnl'],
                        $holding['pnl_pct']
                    ]
                );
            }

            $this->commit();
            return $snapshotId;

        } catch (Exception $e) {
            $this->rollback();
            throw $e;
        }
    }
}
