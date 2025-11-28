<?php
// ============================================
// PORTFOLIO DATA - Caricato da MySQL
// ============================================
// Questo file carica i dati dal database MySQL usando i Repository
// Mantiene retrocompatibilità con le variabili utilizzate nelle viste

require_once __DIR__ . '/../lib/Database/DatabaseManager.php';
require_once __DIR__ . '/../lib/Database/Repositories/PortfolioRepository.php';
require_once __DIR__ . '/../lib/Database/Repositories/HoldingRepository.php';
require_once __DIR__ . '/../lib/Database/Repositories/DividendRepository.php';
require_once __DIR__ . '/../lib/Database/Repositories/SnapshotRepository.php';
require_once __DIR__ . '/../lib/Database/Repositories/TransactionRepository.php';

try {
    // Initialize database and repositories
    $db = DatabaseManager::getInstance();
    $portfolioRepo = new PortfolioRepository($db);
    $holdingRepo = new HoldingRepository($db);
    $dividendRepo = new DividendRepository($db);
    $snapshotRepo = new SnapshotRepository($db);
    $transactionRepo = new TransactionRepository($db);

    // ============================================
    // METADATA DEL PORTAFOGLIO (da VIEW)
    // ============================================
    $metadata_raw = $portfolioRepo->getMetadata();

    // Mappa i nomi delle chiavi per compatibilità con le view
    $metadata = [
        'portfolio_name' => $metadata_raw['portfolio_name'],
        'owner' => $metadata_raw['owner'],
        'last_update' => $metadata_raw['last_update'],
        'total_holdings' => $metadata_raw['total_holdings'],
        'total_invested' => $metadata_raw['total_invested'],
        'total_value' => $metadata_raw['total_market_value'], // Compatibilità
        'total_market_value' => $metadata_raw['total_market_value'],
        'unrealized_pnl' => $metadata_raw['total_pnl'], // Compatibilità
        'total_pnl' => $metadata_raw['total_pnl'],
        'unrealized_pnl_pct' => $metadata_raw['total_pnl_pct'], // Compatibilità
        'total_pnl_pct' => $metadata_raw['total_pnl_pct'],
        'total_dividends' => $metadata_raw['total_dividends_received'], // Compatibilità
        'total_dividends_received' => $metadata_raw['total_dividends_received'],
        'realized_pnl' => 0, // TODO: calcolare da transazioni
        'cash_balance' => 0,
        'base_currency' => 'EUR'
    ];

    // ============================================
    // TOP HOLDINGS (da VIEW con computed values)
    // ============================================
    $holdings_raw = $holdingRepo->getEnrichedHoldings();

    // Calcola il totale per le percentuali di allocazione
    $total_market_value = array_sum(array_column($holdings_raw, 'market_value'));

    // Mappa i nomi delle chiavi per compatibilità con le view
    $top_holdings = array_map(function($holding) use ($total_market_value) {
        // Calcola allocazione corrente
        $current_allocation = $total_market_value > 0
            ? ($holding['market_value'] / $total_market_value) * 100
            : 0;

        return [
            'id' => $holding['id'],
            'ticker' => $holding['ticker'],
            'isin' => '', // Non disponibile nel DB, campo opzionale
            'name' => $holding['name'],
            'asset_class' => $holding['asset_class'],
            'quantity' => $holding['quantity'],
            'avg_price' => $holding['avg_price'],
            'current_price' => $holding['current_price'],
            'price_source' => $holding['price_source'],
            'invested_value' => $holding['invested'],
            'market_value' => $holding['market_value'],
            'unrealized_pnl' => $holding['pnl'],
            'pnl_percentage' => $holding['pnl_pct'],
            'current_allocation' => $current_allocation,
            'target_allocation' => 0, // TODO: aggiungere campo al DB
            'drift' => $current_allocation - 0, // drift = current - target
            'updated_at' => $holding['updated_at'],
            // Campi extra per compatibilità
            'sector' => '',
            'market' => '',
            'instrument_type' => $holding['asset_class'],
            'currency' => 'EUR',
            'dividend_yield' => 0,
            'expense_ratio' => 0,
            'distributor' => '',
            'notes' => ''
        ];
    }, $holdings_raw);

    // Riordina per market_value decrescente
    usort($top_holdings, fn($a, $b) => $b['market_value'] <=> $a['market_value']);

    // ============================================
    // STORICO PERFORMANCE - Da snapshots
    // ============================================
    $currentYear = date('Y');
    $monthly_performance = $portfolioRepo->getMonthlyPerformance($currentYear);

    // Se non ci sono dati, prendi dagli snapshot
    if (empty($monthly_performance)) {
        $yearSnapshots = $snapshotRepo->getMonthlySnapshots($currentYear);

        $monthly_performance = array_map(function($snap) {
            return [
                'month' => date('M', strtotime($snap['snapshot_date'])),
                'value' => (float)$snap['total_market_value'],
                'invested' => (float)$snap['total_invested'],
                'gain' => (float)$snap['total_market_value'] - (float)$snap['total_invested'],
                'gain_pct' => ($snap['total_invested'] ?? 0) > 0
                    ? ((($snap['total_market_value'] - $snap['total_invested']) / $snap['total_invested']) * 100)
                    : 0
            ];
        }, $yearSnapshots);
    }

    // ============================================
    // SNAPSHOTS (storico giornaliero) - per Performance tab
    // ============================================
    $snapshots = $snapshotRepo->getYearToDate($currentYear);

    // Fallback: se non ci sono snapshot in DB, crea uno snapshot sintetico con i metadati correnti
    if (empty($snapshots) && !empty($metadata)) {
        $snapshots = [[
            'snapshot_date' => date('Y-m-d'),
            'total_invested' => $metadata['total_invested'] ?? 0,
            'total_market_value' => $metadata['total_market_value'] ?? 0,
            'total_pnl' => $metadata['total_pnl'] ?? 0,
            'total_pnl_pct' => $metadata['total_pnl_pct'] ?? 0,
            'total_dividends_received' => $metadata['total_dividends_received'] ?? 0,
            'total_holdings' => $metadata['total_holdings'] ?? 0,
        ]];
    }

    // Fallback monthly_performance se ancora vuoto: usa lo snapshot corrente/metadati
    if (empty($monthly_performance) && !empty($snapshots)) {
        $latest = end($snapshots);
        $monthly_performance = [[
            'month' => date('M'),
            'value' => (float) $latest['total_market_value'],
            'invested' => (float) ($latest['total_invested'] ?? $metadata['total_invested'] ?? 0),
            'gain' => (float) $latest['total_market_value'] - (float) ($latest['total_invested'] ?? $metadata['total_invested'] ?? 0),
            'gain_pct' => ($latest['total_invested'] ?? $metadata['total_invested'] ?? 0) > 0
                ? ((($latest['total_market_value'] - ($latest['total_invested'] ?? $metadata['total_invested'] ?? 0)) / ($latest['total_invested'] ?? $metadata['total_invested'] ?? 0)) * 100)
                : 0
        ]];
    }

    // ============================================
    // ALLOCAZIONE PER ASSET CLASS
    // ============================================
    $allocation_by_asset_class = $portfolioRepo->getAllocations();

    // ============================================
    // DIVIDENDI
    // ============================================
    $dividends = $dividendRepo->getAll();

    // ============================================
    // TRANSAZIONI (BUY/SELL/DIVIDEND)
    // ============================================
    $transactions = $transactionRepo->getCompletedHistory();

    // ============================================
    // ANALISI TECNICA - Caricata da JSON (popolato da n8n workflow)
    // ============================================
    $technical_analysis = [];
    $technicalPath = __DIR__ . '/technical_analysis.json';
    if (file_exists($technicalPath)) {
        $technicalData = json_decode(file_get_contents($technicalPath), true);
        $technical_analysis = $technicalData['analysis'] ?? [];
    }

    // ============================================
    // OPPORTUNITÀ ETF - Caricata da JSON (popolato da n8n workflow)
    // ============================================
    $opportunities = [];
    $opportunitiesPath = __DIR__ . '/opportunities.json';
    if (file_exists($opportunitiesPath)) {
        $opportunitiesData = json_decode(file_get_contents($opportunitiesPath), true);
        $opportunities = $opportunitiesData['opportunities'] ?? [];
    }

    // ============================================
    // DASHBOARD INSIGHTS - Caricata da JSON (AI analysis)
    // ============================================
    $dashboardInsights = [
        'portfolio_health' => [
            'score' => null,
            'score_label' => '-',
            'diversification' => ['label' => '-', 'status' => 'neutral'],
            'performance' => ['label' => '-', 'status' => 'neutral'],
            'risk' => ['label' => '-', 'status' => 'neutral']
        ],
        'ai_insights' => ['summary_title' => 'Riepilogo Portafoglio', 'insights' => []]
    ];
    $dashboardInsightsPath = __DIR__ . '/dashboard_insights.json';
    if (file_exists($dashboardInsightsPath)) {
        $data = json_decode(file_get_contents($dashboardInsightsPath), true);
        if (isset($data['portfolio_health'])) {
            $dashboardInsights['portfolio_health'] = $data['portfolio_health'];
        }
        if (isset($data['ai_insights'])) {
            $dashboardInsights['ai_insights'] = $data['ai_insights'];
        }
    }

    // ============================================
    // RECOMMENDATIONS - Caricata da JSON (AI analysis)
    // ============================================
    $recommendations = [
        'immediate_actions' => [],
        'operational_plan' => [],
        'warnings_risks' => []
    ];
    $recommendationsPath = __DIR__ . '/recommendations.json';
    if (file_exists($recommendationsPath)) {
        $data = json_decode(file_get_contents($recommendationsPath), true);
        $recommendations['immediate_actions'] = $data['immediate_actions'] ?? [];
        $recommendations['operational_plan'] = $data['operational_plan'] ?? [];
        $recommendations['warnings_risks'] = $data['warnings_risks'] ?? [];
    }

    // ============================================
    // DIVIDENDS CALENDAR - Caricata da JSON (from API or n8n)
    // ============================================
    $dividends_calendar_data = [
        'forecast_6m' => ['total_amount' => null, 'period' => '-'],
        'portfolio_yield' => null,
        'next_dividend' => ['date' => '-', 'ticker' => '-', 'amount' => null],
        'monthly_forecast' => [],
        'distributing_assets' => [],
        'ai_insight' => '-'
    ];
    $dividendsCalendarPath = __DIR__ . '/dividends_calendar.json';
    if (file_exists($dividendsCalendarPath)) {
        $data = json_decode(file_get_contents($dividendsCalendarPath), true);
        $dividends_calendar_data['forecast_6m'] = $data['forecast_6m'] ?? $dividends_calendar_data['forecast_6m'];
        $dividends_calendar_data['portfolio_yield'] = $data['portfolio_yield'] ?? null;
        $dividends_calendar_data['next_dividend'] = $data['next_dividend'] ?? $dividends_calendar_data['next_dividend'];
        $dividends_calendar_data['monthly_forecast'] = $data['monthly_forecast'] ?? [];
        $dividends_calendar_data['distributing_assets'] = $data['distributing_assets'] ?? [];
        $dividends_calendar_data['ai_insight'] = $data['ai_insight'] ?? '-';
    }

    // ============================================
    // CALCOLI PER DASHBOARD
    // ============================================
    $holdings_count = count($top_holdings);

    // Best/worst performer basato su pnl_percentage
    $best_performer = null;
    $worst_performer = null;

    if (!empty($top_holdings)) {
        $sorted_by_pnl = $top_holdings;
        usort($sorted_by_pnl, fn($a, $b) => $b['pnl_percentage'] <=> $a['pnl_percentage']);

        $best_performer = $sorted_by_pnl[0];
        $worst_performer = end($sorted_by_pnl);
    }

} catch (Exception $e) {
    // Fallback in caso di errore
    error_log("Error loading portfolio data from MySQL: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());

    // Dati di fallback minimali
    $metadata = [
        'portfolio_name' => 'Portafoglio ETF',
        'owner' => 'User',
        'last_update' => date('Y-m-d H:i:s'),
        'total_holdings' => 0,
        'total_invested' => 0,
        'total_value' => 0,
        'total_market_value' => 0,
        'unrealized_pnl' => 0,
        'total_pnl' => 0,
        'unrealized_pnl_pct' => 0,
        'total_pnl_pct' => 0,
        'total_dividends' => 0,
        'total_dividends_received' => 0,
        'realized_pnl' => 0,
        'cash_balance' => 0,
        'base_currency' => 'EUR'
    ];

    $top_holdings = [];
    $monthly_performance = [];
    $technical_analysis = [];
    $allocation_by_asset_class = [];
    $dividends = [];
    $transactions = [];
    $opportunities = [];
    $dashboardInsights = [
        'portfolio_health' => [
            'score' => null,
            'score_label' => '-',
            'diversification' => ['label' => '-', 'status' => 'neutral'],
            'performance' => ['label' => '-', 'status' => 'neutral'],
            'risk' => ['label' => '-', 'status' => 'neutral']
        ],
        'ai_insights' => ['summary_title' => 'Riepilogo Portafoglio', 'insights' => []]
    ];
    $recommendations = [
        'immediate_actions' => [],
        'operational_plan' => [],
        'warnings_risks' => []
    ];
    $dividends_calendar_data = [
        'forecast_6m' => ['total_amount' => null, 'period' => '-'],
        'portfolio_yield' => null,
        'next_dividend' => ['date' => '-', 'ticker' => '-', 'amount' => null],
        'monthly_forecast' => [],
        'distributing_assets' => [],
        'ai_insight' => '-'
    ];
    $holdings_count = 0;
    $best_performer = null;
    $worst_performer = null;
}
