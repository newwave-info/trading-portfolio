<?php
/**
 * Transaction Repository
 *
 * Handles transaction data operations (buy, sell, dividend, etc.).
 *
 * @package TradingPortfolio\Database\Repositories
 * @version 0.3.0-MySQL
 */

require_once __DIR__ . '/BaseRepository.php';

class TransactionRepository extends BaseRepository
{
    protected $table = 'transactions';

    /**
     * Default portfolio ID (single portfolio app)
     */
    const DEFAULT_PORTFOLIO_ID = 1;

    /**
     * Get all transactions for a portfolio
     *
     * @param int|null $portfolioId
     * @param string $orderBy Column to order by (default: transaction_date DESC)
     * @return array
     */
    public function getAll($portfolioId = null, $orderBy = 'transaction_date DESC')
    {
        $portfolioId = $portfolioId ?: self::DEFAULT_PORTFOLIO_ID;

        $sql = "
            SELECT *
            FROM transactions
            WHERE portfolio_id = ?
            ORDER BY $orderBy
        ";

        return $this->fetchAll($sql, [$portfolioId]);
    }

    /**
     * Get transactions by date range
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
            FROM transactions
            WHERE portfolio_id = ?
              AND transaction_date BETWEEN ? AND ?
            ORDER BY transaction_date DESC
        ";

        return $this->fetchAll($sql, [$portfolioId, $startDate, $endDate]);
    }

    /**
     * Get transactions by ticker
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
            FROM transactions
            WHERE portfolio_id = ? AND ticker = ?
            ORDER BY transaction_date DESC
        ";

        return $this->fetchAll($sql, [$portfolioId, $ticker]);
    }

    /**
     * Get transactions by type
     *
     * @param string $type (BUY, SELL, DIVIDEND, FEE, DEPOSIT, WITHDRAWAL)
     * @param int|null $portfolioId
     * @return array
     */
    public function getByType($type, $portfolioId = null)
    {
        $portfolioId = $portfolioId ?: self::DEFAULT_PORTFOLIO_ID;

        $sql = "
            SELECT *
            FROM transactions
            WHERE portfolio_id = ? AND type = ?
            ORDER BY transaction_date DESC
        ";

        return $this->fetchAll($sql, [$portfolioId, $type]);
    }

    /**
     * Get total invested amount by ticker (sum of BUY transactions)
     *
     * @param string $ticker
     * @param int|null $portfolioId
     * @return float
     */
    public function getTotalInvestedByTicker($ticker, $portfolioId = null)
    {
        $portfolioId = $portfolioId ?: self::DEFAULT_PORTFOLIO_ID;

        $sql = "
            SELECT SUM(amount + fees) as total
            FROM transactions
            WHERE portfolio_id = ? AND ticker = ? AND type = 'BUY'
        ";

        $result = $this->fetchOne($sql, [$portfolioId, $ticker]);
        return (float)($result['total'] ?? 0);
    }

    /**
     * Get complete transaction history
     *
     * @param int|null $portfolioId
     * @return array
     */
    public function getCompletedHistory($portfolioId = null)
    {
        $portfolioId = $portfolioId ?: self::DEFAULT_PORTFOLIO_ID;

        $sql = "
            SELECT
                transaction_date as date,
                type,
                ticker,
                quantity,
                quantity as quantity_change,
                price,
                amount,
                fees,
                notes,
                created_at as timestamp
            FROM transactions
            WHERE portfolio_id = ?
            ORDER BY transaction_date DESC, id DESC
        ";

        return $this->fetchAll($sql, [$portfolioId]);
    }

    /**
     * Create new transaction
     *
     * @param array $data Transaction data
     * @param int|null $portfolioId
     * @return int Transaction ID
     */
    public function createTransaction(array $data, $portfolioId = null)
    {
        $portfolioId = $portfolioId ?: self::DEFAULT_PORTFOLIO_ID;

        $transactionData = [
            'portfolio_id' => $portfolioId,
            'ticker' => $data['ticker'],
            'transaction_date' => $data['date'] ?? $data['transaction_date'] ?? date('Y-m-d'),
            'type' => $data['type'],
            'quantity' => $data['quantity'] ?? null,
            'price' => $data['price'] ?? null,
            'amount' => $data['amount'],
            'fees' => $data['fees'] ?? 0,
            'notes' => $data['notes'] ?? null
        ];

        return $this->create($transactionData);
    }

    /**
     * Log BUY/SELL transaction
     *
     * @param string $ticker
     * @param string $isin
     * @param float $quantity
     * @param float $price
     * @param string $type BUY|SELL
     * @param int|null $portfolioId
     * @return int
     */
    public function logTrade(string $ticker, float $quantity, float $price, string $type, $portfolioId = null)
    {
        $portfolioId = $portfolioId ?: self::DEFAULT_PORTFOLIO_ID;

        $amount = $quantity * $price;

        return $this->create([
            'portfolio_id' => $portfolioId,
            'ticker' => $ticker,
            'transaction_date' => date('Y-m-d'),
            'type' => strtoupper($type),
            'quantity' => $quantity,
            'price' => $price,
            'amount' => $amount,
            'fees' => 0,
            'notes' => 'Auto-log holdings update'
        ]);
    }

    /**
     * Log DIVIDEND transaction
     *
     * @param string $ticker
     * @param string $isin
     * @param float $amount
     * @param string|null $date
     * @param int|null $portfolioId
     * @return int
     */
    public function logDividend(string $ticker, float $amount, ?string $date = null, $portfolioId = null)
    {
        $portfolioId = $portfolioId ?: self::DEFAULT_PORTFOLIO_ID;

        return $this->create([
            'portfolio_id' => $portfolioId,
            'ticker' => $ticker,
            'transaction_date' => $date ?? date('Y-m-d'),
            'type' => 'DIVIDEND',
            'quantity' => null,
            'price' => null,
            'amount' => $amount,
            'fees' => 0,
            'notes' => 'Auto-log dividend'
        ]);
    }

    /**
     * Get transactions summary by year
     *
     * @param int $year
     * @param int|null $portfolioId
     * @return array
     */
    public function getSummaryByYear($year, $portfolioId = null)
    {
        $portfolioId = $portfolioId ?: self::DEFAULT_PORTFOLIO_ID;

        $sql = "
            SELECT
                type,
                COUNT(*) as count,
                SUM(amount) as total_amount,
                SUM(fees) as total_fees
            FROM transactions
            WHERE portfolio_id = ? AND YEAR(transaction_date) = ?
            GROUP BY type
            ORDER BY type
        ";

        return $this->fetchAll($sql, [$portfolioId, $year]);
    }

    /**
     * Get recent transactions
     *
     * @param int $limit
     * @param int|null $portfolioId
     * @return array
     */
    public function getRecent($limit = 10, $portfolioId = null)
    {
        $portfolioId = $portfolioId ?: self::DEFAULT_PORTFOLIO_ID;

        $sql = "
            SELECT *
            FROM transactions
            WHERE portfolio_id = ?
            ORDER BY transaction_date DESC, id DESC
            LIMIT ?
        ";

        return $this->fetchAll($sql, [$portfolioId, $limit]);
    }
}
