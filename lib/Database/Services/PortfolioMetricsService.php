<?php
/**
 * Portfolio Metrics Service
 *
 * Ricalcola tabelle derivate (allocations, snapshots, monthly_performance)
 * partendo dagli holdings correnti (v_holdings_enriched).
 */

require_once __DIR__ . '/../Repositories/HoldingRepository.php';
require_once __DIR__ . '/../Repositories/PortfolioRepository.php';
require_once __DIR__ . '/../Repositories/SnapshotRepository.php';

class PortfolioMetricsService
{
    const DEFAULT_PORTFOLIO_ID = 1;

    private $db;
    private $holdingRepo;
    private $portfolioRepo;
    private $snapshotRepo;

    public function __construct(DatabaseManager $db)
    {
        $this->db = $db;
        $this->holdingRepo = new HoldingRepository($db);
        $this->portfolioRepo = new PortfolioRepository($db);
        $this->snapshotRepo = new SnapshotRepository($db);
    }

    /**
     * Recalculate allocations, snapshot (today) and monthly performance.
     *
     * @param int|null $portfolioId
     * @return array summary
     */
    public function recalculate($portfolioId = null)
    {
        $portfolioId = $portfolioId ?: self::DEFAULT_PORTFOLIO_ID;

        // Holdings enriched (include invested/market_value/pnl)
        $holdings = $this->holdingRepo->getEnrichedHoldings($portfolioId);
        if (empty($holdings)) {
            return ['success' => false, 'message' => 'Nessun holding attivo'];
        }

        // Totali
        $totalInvested = array_sum(array_column($holdings, 'invested'));
        $totalMarket = array_sum(array_column($holdings, 'market_value'));
        $totalPnl = array_sum(array_column($holdings, 'pnl'));
        $totalPnlPct = $totalInvested > 0 ? ($totalPnl / $totalInvested) * 100 : 0;
        $totalHoldings = count($holdings);

        // Allocation per asset class
        $allocations = $this->buildAllocations($holdings, $totalMarket);
        $this->portfolioRepo->updateAllocations($allocations, $portfolioId);

        // Snapshot odierno (upsert) con holdings
        $snapshot = [
            'date' => date('Y-m-d'),
            'total_invested' => $totalInvested,
            'total_market_value' => $totalMarket,
            'total_pnl' => $totalPnl,
            'total_pnl_pct' => $totalPnlPct,
            'total_dividends_received' => 0,
            'metadata' => [
                'holdings_count' => $totalHoldings
            ]
        ];
        $this->snapshotRepo->upsertWithHoldings($snapshot, $holdings, $portfolioId);

        // Monthly performance (YTD) basata sugli snapshot DB
        $year = (int) date('Y');
        $monthly = $this->buildMonthlyPerformanceFromSnapshots($year, $portfolioId);
        $this->portfolioRepo->updateMonthlyPerformance($monthly, $year, $portfolioId);

        // Aggiorna timestamp portfolio per riflettere l'ultimo enrichment
        $this->portfolioRepo->updateLastUpdate($portfolioId);

        return [
            'success' => true,
            'allocations' => count($allocations),
            'snapshot' => $snapshot['date'],
            'monthly_records' => count($monthly)
        ];
    }

    /**
     * Public helper to recalc everything for a portfolio
     *
     * @param int $portfolioId
     * @return array
     */
    public function recalculateAllForPortfolio(int $portfolioId): array
    {
        return $this->recalculate($portfolioId);
    }

    private function buildAllocations(array $holdings, float $totalMarket): array
    {
        $agg = [];
        foreach ($holdings as $h) {
            $class = $h['asset_class'] ?? 'Unknown';
            if (!isset($agg[$class])) {
                $agg[$class] = 0;
            }
            $agg[$class] += $h['market_value'];
        }

        $allocations = [];
        foreach ($agg as $class => $value) {
            $pct = $totalMarket > 0 ? ($value / $totalMarket) * 100 : 0;
            $allocations[] = [
                'asset_class' => $class,
                'market_value' => $value,
                'percentage' => $pct
            ];
        }

        return $allocations;
    }

    private function buildMonthlyPerformanceFromSnapshots(int $year, int $portfolioId): array
    {
        $snapshots = $this->snapshotRepo->getYearToDate($year, $portfolioId);
        if (empty($snapshots)) {
            return [];
        }

        // Prendi ultimo snapshot di ogni mese
        $byMonth = [];
        foreach ($snapshots as $snap) {
            $monthKey = date('Y-m', strtotime($snap['snapshot_date']));
            if (!isset($byMonth[$monthKey]) || $snap['snapshot_date'] > $byMonth[$monthKey]['snapshot_date']) {
                $byMonth[$monthKey] = $snap;
            }
        }

        ksort($byMonth);

        $result = [];
        foreach ($byMonth as $monthKey => $snap) {
            $value = (float) $snap['total_market_value'];
            $invested = (float) $snap['total_invested'];
            $gain = $value - $invested;
            $gainPct = $invested > 0 ? ($gain / $invested) * 100 : 0;

            $monthNum = (int) date('n', strtotime($snap['snapshot_date']));
            $monthLabel = date('M', strtotime($snap['snapshot_date']));

            $result[] = [
                'month' => $monthNum,
                'month_label' => $monthLabel,
                'total_value' => $value,
                'total_invested' => $invested,
                'total_gain' => $gain,
                'gain_pct' => $gainPct
            ];
        }

        return $result;
    }
}
