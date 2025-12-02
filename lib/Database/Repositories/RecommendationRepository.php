<?php
/**
 * Recommendation Repository
 *
 * Handles recommendation data operations for trading signals.
 *
 * @package TradingPortfolio\Database\Repositories
 * @version 1.0.0
 */

require_once __DIR__ . '/BaseRepository.php';

class RecommendationRepository extends BaseRepository
{
    protected $table = 'recommendations';

    const DEFAULT_PORTFOLIO_ID = 1;

    /**
     * Get active recommendations with enriched holding data
     *
     * @param int|null $portfolioId
     * @param string|null $urgency Filter by urgency level
     * @return array
     */
    public function getActiveRecommendations($portfolioId = null, $urgency = null)
    {
        $portfolioId = $portfolioId ?: self::DEFAULT_PORTFOLIO_ID;

        $sql = "
            SELECT
                r.*,
                h.ticker,
                h.name,
                h.current_price,
                h.asset_class,
                h.role,
                h.target_allocation_pct,
                h.quantity as current_quantity,
                h.avg_price,
                DATEDIFF(r.expires_at, NOW()) as days_to_expire,
                CASE
                    WHEN r.type IN ('BUY_LIMIT', 'BUY_MARKET') THEN
                        ROUND((r.quantity * r.trigger_price) / (SELECT SUM(quantity * current_price) FROM holdings WHERE portfolio_id = r.portfolio_id) * 100, 2)
                    WHEN r.type IN ('SELL_PARTIAL') THEN
                        ROUND((r.quantity * h.current_price) / (SELECT SUM(quantity * current_price) FROM holdings WHERE portfolio_id = r.portfolio_id) * 100, 2)
                    ELSE 0
                END as allocation_impact_pct
            FROM recommendations r
            LEFT JOIN holdings h ON r.holding_id = h.id
            WHERE r.portfolio_id = ?
                AND r.status = 'ACTIVE'
                AND (r.expires_at IS NULL OR r.expires_at > NOW())
        ";

        $params = [$portfolioId];

        if ($urgency) {
            $sql .= " AND r.urgency = ?";
            $params[] = $urgency;
        }

        $sql .= "
            ORDER BY
                CASE r.urgency
                    WHEN 'IMMEDIATO' THEN 1
                    WHEN 'QUESTA_SETTIMANA' THEN 2
                    WHEN 'PROSSIME_2_SETTIMANE' THEN 3
                    WHEN 'MONITORAGGIO' THEN 4
                END,
                r.confidence_score DESC,
                r.created_at DESC
        ";

        return $this->fetchAll($sql, $params);
    }

    /**
     * Get recommendations by holding and type
     *
     * @param int $holdingId
     * @param string|null $type
     * @param string|null $status
     * @return array
     */
    public function getByHolding($holdingId, $type = null, $status = null)
    {
        $sql = "SELECT * FROM recommendations WHERE holding_id = ?";
        $params = [$holdingId];

        if ($type) {
            $sql .= " AND type = ?";
            $params[] = $type;
        }

        if ($status) {
            $sql .= " AND status = ?";
            $params[] = $status;
        }

        $sql .= " ORDER BY created_at DESC";

        return $this->fetchAll($sql, $params);
    }

    /**
     * Get recent recommendations with performance tracking
     *
     * @param int|null $portfolioId
     * @param int $daysLookback
     * @return array
     */
    public function getRecentRecommendations($portfolioId = null, $daysLookback = 30)
    {
        $portfolioId = $portfolioId ?: self::DEFAULT_PORTFOLIO_ID;

        $sql = "
            SELECT
                r.*,
                h.ticker,
                h.name,
                h.current_price,
                CASE
                    WHEN r.type IN ('BUY_LIMIT', 'BUY_MARKET') THEN
                        (h.current_price - r.executed_price) / r.executed_price * 100
                    WHEN r.type IN ('SELL_PARTIAL', 'SELL_ALL') THEN
                        (r.executed_price - h.avg_price) / h.avg_price * 100
                    ELSE NULL
                END as realized_pnl_pct,
                CASE
                    WHEN h.current_price <= r.stop_loss THEN 1
                    ELSE 0
                END as hit_stop,
                CASE
                    WHEN h.current_price >= r.take_profit THEN 1
                    ELSE 0
                END as hit_tp,
                CASE
                    WHEN r.type IN ('BUY_LIMIT', 'BUY_MARKET')
                         AND h.current_price > r.executed_price THEN 1
                    WHEN r.type IN ('SELL_PARTIAL', 'SELL_ALL')
                         AND h.current_price < r.executed_price THEN 1
                    ELSE 0
                END as is_success
            FROM recommendations r
            LEFT JOIN holdings h ON r.holding_id = h.id
            WHERE r.portfolio_id = ?
                AND r.created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
            ORDER BY r.created_at DESC
        ";

        return $this->fetchAll($sql, [$portfolioId, $daysLookback]);
    }

    /**
     * Get expired recommendations that need cleanup
     *
     * @param int|null $portfolioId
     * @return array
     */
    public function getExpiredRecommendations($portfolioId = null)
    {
        $portfolioId = $portfolioId ?: self::DEFAULT_PORTFOLIO_ID;

        $sql = "
            SELECT id, holding_id, type, status, expires_at
            FROM recommendations
            WHERE portfolio_id = ?
                AND status = 'ACTIVE'
                AND expires_at IS NOT NULL
                AND expires_at < NOW()
        ";

        return $this->fetchAll($sql, [$portfolioId]);
    }

    /**
     * Check for duplicate recommendations
     *
     * @param int $holdingId
     * @param string $type
     * @param int $hoursLookback
     * @return bool
     */
    public function hasRecentRecommendation($holdingId, $type, $hoursLookback = 48)
    {
        $sql = "
            SELECT 1
            FROM recommendations
            WHERE holding_id = ?
                AND type = ?
                AND status IN ('ACTIVE', 'EXECUTED')
                AND created_at >= DATE_SUB(NOW(), INTERVAL ? HOUR)
            LIMIT 1
        ";

        return $this->fetchOne($sql, [$holdingId, $type, $hoursLookback]) !== null;
    }

    /**
     * Update recommendation status
     *
     * @param int $id
     * @param string $status
     * @param array $additionalData
     * @return bool
     */
    public function updateStatus($id, $status, array $additionalData = [])
    {
        $data = array_merge(['status' => $status], $additionalData);

        if ($status === 'EXECUTED' && !isset($data['executed_at'])) {
            $data['executed_at'] = date('Y-m-d H:i:s');
        }

        return $this->update($id, $data);
    }

    /**
     * Bulk expire old recommendations
     *
     * @param int|null $portfolioId
     * @return int Affected rows
     */
    public function expireOldRecommendations($portfolioId = null)
    {
        $portfolioId = $portfolioId ?: self::DEFAULT_PORTFOLIO_ID;

        $sql = "
            UPDATE recommendations
            SET status = 'EXPIRED'
            WHERE portfolio_id = ?
                AND status = 'ACTIVE'
                AND expires_at IS NOT NULL
                AND expires_at < NOW()
        ";

        return $this->execute($sql, [$portfolioId]);
    }

    /**
     * Get recommendations statistics
     *
     * @param int|null $portfolioId
     * @return array
     */
    public function getStatistics($portfolioId = null)
    {
        $portfolioId = $portfolioId ?: self::DEFAULT_PORTFOLIO_ID;

        $sql = "
            SELECT
                COUNT(*) as total_count,
                SUM(CASE WHEN status = 'ACTIVE' THEN 1 ELSE 0 END) as active_count,
                SUM(CASE WHEN status = 'EXECUTED' THEN 1 ELSE 0 END) as executed_count,
                SUM(CASE WHEN status = 'EXPIRED' THEN 1 ELSE 0 END) as expired_count,
                SUM(CASE WHEN status = 'IGNORED' THEN 1 ELSE 0 END) as ignored_count,
                AVG(CASE WHEN status = 'EXECUTED' THEN confidence_score END) as avg_confidence_executed,
                AVG(CASE WHEN status = 'ACTIVE' THEN confidence_score END) as avg_confidence_active,
                COUNT(DISTINCT DATE(created_at)) as active_days
            FROM recommendations
            WHERE portfolio_id = ?
        ";

        return $this->fetchOne($sql, [$portfolioId]);
    }

    /**
     * Get filtered recommendations with pagination and sorting
     *
     * @param int $portfolioId
     * @param array $filters Array of filters [status, holding_id, urgency]
     * @param int $page Page number (1-based)
     * @param int $perPage Items per page
     * @param string $orderBy Sort column
     * @param string $orderDir Sort direction (ASC/DESC)
     * @return array ['data' => [], 'total' => int]
     */
    public function getFilteredRecommendations($portfolioId, array $filters = [], $page = 1, $perPage = 20, $orderBy = 'created_at', $orderDir = 'DESC')
    {
        // Base query
        $sql = "
            SELECT SQL_CALC_FOUND_ROWS
                r.*,
                h.ticker,
                h.name,
                h.current_price,
                h.asset_class,
                h.role,
                h.target_allocation_pct,
                DATEDIFF(r.expires_at, NOW()) as days_to_expire
            FROM recommendations r
            LEFT JOIN holdings h ON r.holding_id = h.id
            WHERE r.portfolio_id = ?
        ";

        $params = [$portfolioId];
        $conditions = [];

        // Applica filtri
        if (isset($filters['status'])) {
            $conditions[] = "r.status = ?";
            $params[] = $filters['status'];
        }

        if (isset($filters['holding_id'])) {
            $conditions[] = "r.holding_id = ?";
            $params[] = $filters['holding_id'];
        }

        if (isset($filters['urgency'])) {
            $conditions[] = "r.urgency = ?";
            $params[] = $filters['urgency'];
        }

        // Aggiungi condizioni WHERE
        if (!empty($conditions)) {
            $sql .= " AND " . implode(" AND ", $conditions);
        }

        // Ordinamento
        $allowedOrderBy = ['created_at', 'confidence_score', 'urgency', 'trigger_price', 'expires_at'];
        if (in_array($orderBy, $allowedOrderBy)) {
            $sql .= " ORDER BY r.$orderBy $orderDir";
        }

        // Paginazione
        $offset = ($page - 1) * $perPage;
        $sql .= " LIMIT $offset, $perPage";

        // Esegui query principale
        $data = $this->fetchAll($sql, $params);

        // Ottieni il totale (SQL_CALC_FOUND_ROWS)
        $totalResult = $this->fetchOne("SELECT FOUND_ROWS() as total");
        $total = (int)($totalResult['total'] ?? 0);

        return [
            'data' => $data,
            'total' => $total
        ];
    }

    /**
     * Find recommendation by ID with holding data
     *
     * @param int $id
     * @param int|null $portfolioId
     * @return array|null
     */
    public function findById($id, $portfolioId = null)
    {
        $portfolioId = $portfolioId ?: self::DEFAULT_PORTFOLIO_ID;

        $sql = "
            SELECT
                r.*,
                h.ticker,
                h.name,
                h.current_price,
                h.asset_class,
                h.role,
                h.target_allocation_pct,
                h.quantity as current_quantity,
                h.avg_price
            FROM recommendations r
            LEFT JOIN holdings h ON r.holding_id = h.id
            WHERE r.id = ? AND r.portfolio_id = ?
            LIMIT 1
        ";

        return $this->fetchOne($sql, [$id, $portfolioId]);
    }

    /**
     * Soft delete recommendation
     *
     * @param int $id
     * @param int|null $portfolioId
     * @return bool
     */
    public function softDelete($id, $portfolioId = null)
    {
        $portfolioId = $portfolioId ?: self::DEFAULT_PORTFOLIO_ID;

        $sql = "UPDATE recommendations
                SET is_active = 0, updated_at = NOW()
                WHERE id = ? AND portfolio_id = ? AND is_active = 1";

        return $this->execute($sql, [$id, $portfolioId]) > 0;
    }

    /**
     * Soft delete recommendation by setting is_active = 0
     *
     * @param int $id
     * @param int|null $portfolioId
     * @return bool
     */
    public function delete($id, $portfolioId = null)
    {
        return $this->softDelete($id, $portfolioId);
    }

    /**
     * Log user action on recommendation
     *
     * @param int $recommendationId
     * @param string $action
     * @param string|null $notes
     * @return int Action ID
     */
    public function logAction($recommendationId, $action, $notes = null)
    {
        $data = [
            'recommendation_id' => $recommendationId,
            'action' => $action,
            'notes' => $notes
        ];

        return $this->createInTable('recommendation_actions', $data);
    }

    /**
     * Helper method to create in specific table
     *
     * @param string $table
     * @param array $data
     * @return int
     */
    protected function createInTable($table, array $data)
    {
        $columns = array_keys($data);
        $placeholders = array_fill(0, count($columns), '?');

        $sql = sprintf(
            "INSERT INTO %s (%s) VALUES (%s)",
            $table,
            implode(', ', $columns),
            implode(', ', $placeholders)
        );

        $this->execute($sql, array_values($data));
        return (int)$this->db->lastInsertId();
    }
}