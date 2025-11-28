<?php
/**
 * Dividend Repository
 *
 * Handles dividend payment data operations (forecast and received).
 *
 * @package TradingPortfolio\Database\Repositories
 * @version 0.3.0-MySQL
 */

require_once __DIR__ . '/BaseRepository.php';

class DividendRepository extends BaseRepository
{
    protected $table = 'dividend_payments';

    /**
     * Default portfolio ID (single portfolio app)
     */
    const DEFAULT_PORTFOLIO_ID = 1;

    /**
     * Get monthly dividend data for a specific year
     *
     * @param int $year
     * @param int|null $portfolioId
     * @return array Array with 'received' and 'forecast' per month
     */
    public function getMonthlyData($year, $portfolioId = null)
    {
        $portfolioId = $portfolioId ?: self::DEFAULT_PORTFOLIO_ID;

        // Inizializza mesi
        $months = [];
        for ($m = 1; $m <= 12; $m++) {
            $months[$m] = [
                'month' => $m,
                'month_label' => date('M', mktime(0, 0, 0, $m, 1)),
                'received' => 0,
                'forecast' => 0,
                'total' => 0
            ];
        }

        // Usa la view v_dividends_enriched per importi effettivi
        $sqlReceived = "
            SELECT paid_amount, payment_date, ex_date
            FROM v_dividends_enriched
            WHERE portfolio_id = ? AND status = 'RECEIVED' AND ex_date BETWEEN ? AND ?
        ";
        $received = $this->fetchAll($sqlReceived, [$portfolioId, "$year-01-01", "$year-12-31"]);
        foreach ($received as $div) {
            $date = $div['payment_date'] ?: $div['ex_date'];
            $month = (int)date('n', strtotime($date));
            $months[$month]['received'] += (float)$div['paid_amount'];
        }

        $sqlForecast = "
            SELECT total_amount, payment_date, ex_date
            FROM dividend_payments
            WHERE portfolio_id = ? AND status = 'FORECAST' AND ex_date BETWEEN ? AND ?
        ";
        $forecast = $this->fetchAll($sqlForecast, [$portfolioId, "$year-01-01", "$year-12-31"]);
        foreach ($forecast as $div) {
            $date = $div['payment_date'] ?: $div['ex_date'];
            $month = (int)date('n', strtotime($date));
            $months[$month]['forecast'] += (float)$div['total_amount'];
        }

        foreach ($months as $m => $data) {
            $months[$m]['total'] = $data['received'] + $data['forecast'];
        }

        return array_values($months);
    }

    /**
     * Get received dividends by date range
     *
     * @param string $startDate
     * @param string $endDate
     * @param int|null $portfolioId
     * @return array
     */
    public function getReceived($startDate, $endDate, $portfolioId = null)
    {
        $portfolioId = $portfolioId ?: self::DEFAULT_PORTFOLIO_ID;

        $sql = "
            SELECT *
            FROM v_dividends_enriched
            WHERE portfolio_id = ?
              AND status = 'RECEIVED'
              AND ex_date BETWEEN ? AND ?
            ORDER BY ex_date DESC
        ";

        return $this->fetchAll($sql, [$portfolioId, $startDate, $endDate]);
    }

    /**
     * Get forecast dividends by date range
     *
     * @param string $startDate
     * @param string $endDate
     * @param int|null $portfolioId
     * @return array
     */
    public function getForecast($startDate, $endDate, $portfolioId = null)
    {
        $portfolioId = $portfolioId ?: self::DEFAULT_PORTFOLIO_ID;

        $sql = "
            SELECT *
            FROM dividend_payments
            WHERE portfolio_id = ?
              AND status = 'FORECAST'
              AND ex_date BETWEEN ? AND ?
            ORDER BY ex_date ASC
        ";

        return $this->fetchAll($sql, [$portfolioId, $startDate, $endDate]);
    }

    /**
     * Get all dividends (received + forecast)
     *
     * @param int|null $portfolioId
     * @param int|null $limit
     * @return array
     */
    public function getAll($portfolioId = null, $limit = null)
    {
        $portfolioId = $portfolioId ?: self::DEFAULT_PORTFOLIO_ID;

        $sql = "
            SELECT *
            FROM v_dividends_enriched
            WHERE portfolio_id = ?
            ORDER BY ex_date DESC
        ";

        if ($limit) {
            $sql .= " LIMIT " . (int)$limit;
        }

        return $this->fetchAll($sql, [$portfolioId]);
    }

    /**
     * Get dividends by ticker
     *
     * @param string $ticker
     * @param int|null $portfolioId
     * @return array
     */
    public function getByTicker($ticker, $portfolioId = null)
    {
        $portfolioId = $portfolioId ?: self::DEFAULT_PORTFOLIO_ID;

        $sql = "
            SELECT *
            FROM dividend_payments
            WHERE portfolio_id = ? AND ticker = ?
            ORDER BY ex_date DESC
        ";

        return $this->fetchAll($sql, [$portfolioId, $ticker]);
    }

    /**
     * Mark dividend as received (used by dividends-payout.php)
     *
     * @param int $id Dividend payment ID
     * @return bool
     */
    public function markAsReceived($id)
    {
        $sql = "
            UPDATE dividend_payments
            SET status = 'RECEIVED', payment_date = CURDATE(), updated_at = NOW()
            WHERE id = ?
        ";

        return $this->execute($sql, [$id]) > 0;
    }

    /**
     * Mark multiple dividends as received
     *
     * @param array $ids Array of dividend payment IDs
     * @return bool
     */
    public function markMultipleAsReceived(array $ids)
    {
        if (empty($ids)) {
            return false;
        }

        try {
            $this->beginTransaction();

            foreach ($ids as $id) {
                $this->markAsReceived($id);
            }

            $this->commit();
            return true;

        } catch (Exception $e) {
            $this->rollback();
            error_log("Failed to mark dividends as received: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get total received dividends
     *
     * @param int|null $portfolioId
     * @return float
     */
    public function getTotalReceived($portfolioId = null)
    {
        $portfolioId = $portfolioId ?: self::DEFAULT_PORTFOLIO_ID;

        $sql = "
            SELECT SUM(total_amount) as total
            FROM dividend_payments
            WHERE portfolio_id = ? AND status = 'RECEIVED'
        ";

        $result = $this->fetchOne($sql, [$portfolioId]);
        return (float)($result['total'] ?? 0);
    }

    /**
     * Get total forecast dividends
     *
     * @param int|null $portfolioId
     * @return float
     */
    public function getTotalForecast($portfolioId = null)
    {
        $portfolioId = $portfolioId ?: self::DEFAULT_PORTFOLIO_ID;

        $sql = "
            SELECT SUM(total_amount) as total
            FROM dividend_payments
            WHERE portfolio_id = ? AND status = 'FORECAST'
        ";

        $result = $this->fetchOne($sql, [$portfolioId]);
        return (float)($result['total'] ?? 0);
    }

    /**
     * Create new dividend payment
     *
     * @param array $data Dividend data
     * @param int|null $portfolioId
     * @return int Dividend ID
     */
    public function createDividend(array $data, $portfolioId = null)
    {
        $portfolioId = $portfolioId ?: self::DEFAULT_PORTFOLIO_ID;

        $dividendData = [
            'portfolio_id' => $portfolioId,
            'ticker' => $data['ticker'],
            'ex_date' => $data['ex_date'],
            'payment_date' => $data['payment_date'] ?? null,
            'amount_per_share' => $data['amount_per_share'],
            'total_amount' => $data['total_amount'],
            'quantity' => $data['quantity'],
            'status' => $data['status'] ?? 'FORECAST'
        ];

        return $this->create($dividendData);
    }

    /**
     * Get upcoming dividends (next 30 days)
     *
     * @param int $days
     * @param int|null $portfolioId
     * @return array
     */
    public function getUpcoming($days = 30, $portfolioId = null)
    {
        $portfolioId = $portfolioId ?: self::DEFAULT_PORTFOLIO_ID;

        $endDate = date('Y-m-d', strtotime("+$days days"));

        $sql = "
            SELECT *
            FROM dividend_payments
            WHERE portfolio_id = ?
              AND status = 'FORECAST'
              AND ex_date BETWEEN CURDATE() AND ?
            ORDER BY ex_date ASC
        ";

        return $this->fetchAll($sql, [$portfolioId, $endDate]);
    }

    /**
     * Get dividend yield by ticker
     *
     * @param string $ticker
     * @param float $currentPrice
     * @param int|null $portfolioId
     * @return float Annual dividend yield percentage
     */
    public function getDividendYield($ticker, $currentPrice, $portfolioId = null)
    {
        $portfolioId = $portfolioId ?: self::DEFAULT_PORTFOLIO_ID;

        if ($currentPrice <= 0) {
            return 0;
        }

        // Get last 12 months of dividends
        $startDate = date('Y-m-d', strtotime('-12 months'));
        $endDate = date('Y-m-d');

        $sql = "
            SELECT SUM(amount_per_share) as annual_dividend
            FROM dividend_payments
            WHERE portfolio_id = ?
              AND ticker = ?
              AND status = 'RECEIVED'
              AND ex_date BETWEEN ? AND ?
        ";

        $result = $this->fetchOne($sql, [$portfolioId, $ticker, $startDate, $endDate]);
        $annualDividend = (float)($result['annual_dividend'] ?? 0);

        return ($annualDividend / $currentPrice) * 100;
    }
}
