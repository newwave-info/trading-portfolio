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
require_once __DIR__ . '/../lib/Database/Repositories/DividendEnrichedRepository.php';
require_once __DIR__ . '/../lib/Database/Repositories/SnapshotRepository.php';
require_once __DIR__ . '/../lib/Database/Repositories/TransactionRepository.php';

try {
    // Initialize database and repositories
    $db = DatabaseManager::getInstance();
    $portfolioRepo = new PortfolioRepository($db);
    $holdingRepo = new HoldingRepository($db);
    $dividendRepo = new DividendRepository($db);
    $dividendEnrichedRepo = new DividendEnrichedRepository($db);
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
            'sector' => $holding['sector'] ?? '',
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
            // Range prezzi (52w, giornalieri)
            'fifty_two_week_high' => $holding['fifty_two_week_high'] ?? 0,
            'fifty_two_week_low' => $holding['fifty_two_week_low'] ?? 0,
            'day_high' => $holding['day_high'] ?? 0,
            'day_low' => $holding['day_low'] ?? 0,
            // Performance percentuali
            'ytd_change_percent' => $holding['ytd_change_percent'] ?? 0,
            'one_month_change_percent' => $holding['one_month_change_percent'] ?? 0,
            'three_month_change_percent' => $holding['three_month_change_percent'] ?? 0,
            'one_year_change_percent' => $holding['one_year_change_percent'] ?? 0,
            // Dividendi
            'dividend_yield' => $holding['dividend_yield'] ?? 0,
            'annual_dividend' => $holding['annual_dividend'] ?? 0,
            'dividend_frequency' => $holding['dividend_frequency'] ?? '-',
            'has_dividends' => $holding['has_dividends'] ?? false,
            'total_dividends_5y' => $holding['total_dividends_5y'] ?? 0,
            // Volume e mercato
            'volume' => $holding['volume'] ?? 0,
            'exchange' => $holding['exchange'] ?? '',
            'first_trade_date' => $holding['first_trade_date'] ?? 0,
            // Campi extra per compatibilità
            'market' => $holding['exchange'] ?? '',
            'instrument_type' => $holding['asset_class'],
            'currency' => 'EUR',
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
    // Dividendi (storico + forecast) normalizzati per compatibilità vista
    $dividends_raw = $dividendEnrichedRepo->findAll();
    $dividends = array_map(function($div) {
        $payDate = $div['payment_date'] ?? $div['ex_date'];
        return [
            'id' => $div['id'],
            'ticker' => $div['ticker'],
            'status' => $div['status'],
            'ex_date' => $div['ex_date'],
            'payment_date' => $div['payment_date'],
            'date' => $payDate, // compatibilità legacy
            'amount_per_share' => (float)$div['amount_per_share'],
            'quantity' => (float)$div['quantity_at_ex_date'],
            'amount' => (float)$div['paid_amount'],
            'pay_date' => $payDate, // compatibilità legacy
            'owned_on_snapshot' => (int)$div['owned_on_snapshot'],
            'snapshot_date_used' => $div['snapshot_date_used']
        ];
    }, $dividends_raw);

    // ==========================
    // DIVIDENDS CALENDAR (DB-first)
    // ==========================
    $dividends_calendar_data = [
        'forecast_6m' => ['total_amount' => null, 'period' => '-'], // user-facing: 12 mesi ma manteniamo la chiave per compatibilità
        'portfolio_yield' => null,
        'next_dividend' => ['date' => '-', 'ticker' => '-', 'amount' => null],
        'monthly_forecast' => [],
        'distributing_assets' => [],
        'ai_insight' => '-' // lasciato a JSON esterno per ora
    ];

    $today = date('Y-m-d');
    $forecastHorizonEnd = date('Y-m-d', strtotime('+12 months'));
    $twelveMonthsLater = date('Y-m-d', strtotime('+12 months'));
    $twelveMonthsAgo = date('Y-m-d', strtotime('-12 months'));

    // Helper per calcolare total_amount se mancante
    $computeTotalAmount = function(array $div): float {
        $total = $div['total_amount'] ?? null;
        if ($total !== null) {
            return (float) $total;
        }
        $qty = $div['quantity_at_ex_date'] ?? $div['quantity'] ?? 0;
        $aps = $div['amount_per_share'] ?? 0;
        return (float) ($qty * $aps);
    };

    // Forecast prossimi 12 mesi (chiave primaria: ex_date, payment_date dato secondario)
    $forecast = $dividendRepo->getForecast($today, $forecastHorizonEnd);
    if (!empty($forecast)) {
        $sum = 0;
        foreach ($forecast as $div) {
            $sum += $computeTotalAmount($div);
        }

        // Periodo es: "12/2025 - 11/2026"
        $months = array_map(function($div) {
            $date = $div['ex_date'] ?? $div['payment_date'];
            return date('m/Y', strtotime($date));
        }, $forecast);
        $period = reset($months) . ' - ' . end($months);

        $dividends_calendar_data['forecast_6m'] = [ // manteniamo la chiave ma è 12 mesi
            'total_amount' => (float) $sum,
            'period' => $period
        ];
    }

    // Next dividend (FORECAST più vicino, solo date future) basato su ex_date
    $nextDividend = null;
    foreach ($forecast as $div) {
        $date = $div['ex_date'] ?? $div['payment_date'];
        if ($date >= $today && ($nextDividend === null || $date < $nextDividend['date'])) {
            $nextDividend = [
                'date' => $date,
                'ticker' => $div['ticker'],
                'amount' => $computeTotalAmount($div)
            ];
        }
    }
    if ($nextDividend) {
        $dividends_calendar_data['next_dividend'] = $nextDividend;
    }

    // Monthly forecast (prossimi 12 mesi, raggruppo per mese con anno, includo pay_date)
    if (!empty($forecast)) {
        $byMonth = [];
        foreach ($forecast as $div) {
            $date = $div['ex_date'] ?? $div['payment_date'];
            $payDate = $div['payment_date'] ?? $div['ex_date'];
            $monthKey = date('Y-m', strtotime($date));
            if (!isset($byMonth[$monthKey])) {
                // Nomi mese in italiano
                $italianMonths = [
                    'January' => 'Gennaio', 'February' => 'Febbraio', 'March' => 'Marzo',
                    'April' => 'Aprile', 'May' => 'Maggio', 'June' => 'Giugno',
                    'July' => 'Luglio', 'August' => 'Agosto', 'September' => 'Settembre',
                    'October' => 'Ottobre', 'November' => 'Novembre', 'December' => 'Dicembre'
                ];
                $monthEn = date('F', strtotime($date));
                $monthIt = $italianMonths[$monthEn] ?? $monthEn;

            $byMonth[$monthKey] = [
                'month' => $monthIt, // mese esteso italiano
                'year' => date('Y', strtotime($date)),
                'month_year' => $monthKey,
                'label' => $monthIt . ' ' . date('Y', strtotime($date)),
                'amount' => 0,
                'payment_dates' => []
            ];
        }
        $byMonth[$monthKey]['amount'] += $computeTotalAmount($div);
        if ($payDate) {
            $byMonth[$monthKey]['payment_dates'][] = $payDate;
        }
    }
    // Ordina per mese
        ksort($byMonth);
        $dividends_calendar_data['monthly_forecast'] = array_values($byMonth);
    }

    // Portfolio yield (ultimi 12 mesi ricevuti / valore portafoglio)
    $received12m = $dividendRepo->getReceived($twelveMonthsAgo, $today);
    $receivedTotal = array_sum(array_column($received12m, 'paid_amount'));
    $totalMarketValue = $metadata['total_market_value'] ?? 0;
    if ($totalMarketValue > 0) {
        $dividends_calendar_data['portfolio_yield'] = ($receivedTotal / $totalMarketValue) * 100;
    }

    // Distributing assets: per ticker, annual_amount (forecast 12m) e yield calcolato sul market_value
    if (!empty($holdings_raw)) {
        // Mappa holdings per ticker per recuperare market_value e name
        $holdingMap = [];
        foreach ($holdings_raw as $h) {
            $holdingMap[$h['ticker']] = $h;
        }

        // Forecast 12 mesi per ticker
        $forecast12m = $dividendRepo->getForecast($today, $twelveMonthsLater);
        $byTicker = [];
        foreach ($forecast12m as $div) {
            $ticker = $div['ticker'];
            if (!isset($byTicker[$ticker])) {
                $byTicker[$ticker] = [
                    'ticker' => $ticker,
                    'annual_amount' => 0,
                    'next_div_date' => null,
                    'next_payment_date' => null,
                    'last_payment_date' => null,
                    'next_amount' => null
                ];
            }
            $amount = $computeTotalAmount($div);
            $byTicker[$ticker]['annual_amount'] += $amount;

            $date = $div['ex_date'] ?? $div['payment_date'];
            if ($byTicker[$ticker]['next_div_date'] === null || $date < $byTicker[$ticker]['next_div_date']) {
                $byTicker[$ticker]['next_div_date'] = $date;
                $byTicker[$ticker]['next_amount'] = $amount;
            }

            // Payment date tracking
            $payDate = $div['payment_date'] ?? null;
            if ($payDate) {
                if ($byTicker[$ticker]['next_payment_date'] === null || $payDate < $byTicker[$ticker]['next_payment_date']) {
                    $byTicker[$ticker]['next_payment_date'] = $payDate;
                }
                // Last payment (most recent past)
                $today = date('Y-m-d');
                if ($payDate <= $today) {
                    if ($byTicker[$ticker]['last_payment_date'] === null || $payDate > $byTicker[$ticker]['last_payment_date']) {
                        $byTicker[$ticker]['last_payment_date'] = $payDate;
                    }
                }
            }
        }

        // Costruisci lista con yield
        $totalAnnualAll = 0;
        foreach ($byTicker as $ticker => $info) {
            $holding = $holdingMap[$ticker] ?? null;
            if (!$holding) {
                continue;
            }
            $marketValue = $holding['market_value'] ?? 0;
            $yieldPct = $marketValue > 0 ? ($info['annual_amount'] / $marketValue) * 100 : null;
            $totalAnnualAll += $info['annual_amount'];

            $dividends_calendar_data['distributing_assets'][] = [
                'ticker' => $ticker,
                'name' => $holding['name'] ?? $ticker,
                'dividend_yield' => $yieldPct,
                'annual_amount' => $info['annual_amount'],
                'frequency' => 'N/A',
                'last_div_date' => null, // manteniamo per compatibilità, ma non usato
                'last_payment_date' => $info['last_payment_date'],
                'next_div_date' => $info['next_div_date'],
                'next_payment_date' => $info['next_payment_date'],
                'next_amount' => $info['next_amount']
            ];
        }

        // Yield di portafoglio stimato su base annuale (forecast 12 mesi)
        if (($metadata['total_market_value'] ?? 0) > 0 && $totalAnnualAll > 0) {
            $dividends_calendar_data['portfolio_yield'] = ($totalAnnualAll / $metadata['total_market_value']) * 100;
        }
    }

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
    // DIVIDENDS CALENDAR - AI insight ancora da JSON (solo ai_insight fallback)
    // Se presente un file con ai_insight, lo innestiamo
    $dividendsCalendarPath = __DIR__ . '/dividends_calendar.json';
    if (file_exists($dividendsCalendarPath)) {
        $data = json_decode(file_get_contents($dividendsCalendarPath), true);
        if (isset($data['ai_insight'])) {
            $dividends_calendar_data['ai_insight'] = $data['ai_insight'];
        }
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
