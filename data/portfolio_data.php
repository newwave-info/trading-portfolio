<?php
// ============================================
// PORTFOLIO DATA - Caricato da JSON
// ============================================
// Questo file carica i dati da portfolio.json usando PortfolioManager
// Mantiene retrocompatibilità con le variabili utilizzate nelle viste

require_once __DIR__ . '/../lib/PortfolioManager.php';

try {
    $portfolioManager = new PortfolioManager();
    $portfolioData = $portfolioManager->getData();

    // Metadata del portafoglio
    $metadata = $portfolioData['metadata'];

    // Top holdings (tutti gli holdings dal JSON)
    $top_holdings = $portfolioData['holdings'];

    // Storico performance - Caricato da snapshots se disponibili
    $snapshotsPath = __DIR__ . '/snapshots.json';
    if (file_exists($snapshotsPath)) {
        $snapshotsData = json_decode(file_get_contents($snapshotsPath), true);
        $snapshots = $snapshotsData['snapshots'] ?? [];

        // Genera monthly_performance da snapshots (ultimi 12 mesi)
        if (!empty($snapshots)) {
            $byMonth = [];
            foreach ($snapshots as $snap) {
                $month = date('M', strtotime($snap['date']));
                $year = date('Y', strtotime($snap['date']));
                $key = $year . '-' . $month;

                // Prendi l'ultimo snapshot di ogni mese
                if (!isset($byMonth[$key]) || $snap['date'] > $byMonth[$key]['date']) {
                    $byMonth[$key] = [
                        'month' => $month,
                        'value' => $snap['metadata']['total_value'],
                        'date' => $snap['date']
                    ];
                }
            }

            // Ordina per data e prendi ultimi 12 mesi
            usort($byMonth, fn($a, $b) => $a['date'] <=> $b['date']);
            $monthly_performance = array_slice(array_map(fn($item) => [
                'month' => $item['month'],
                'value' => $item['value']
            ], $byMonth), -12);
        } else {
            // Fallback a dati da portfolio.json se no snapshots
            $monthly_performance = $portfolioData['monthly_performance'];
        }
    } else {
        // Fallback a dati da portfolio.json
        $monthly_performance = $portfolioData['monthly_performance'];
    }

    // Analisi tecnica - Caricata da JSON (popolato da n8n workflow)
    $technical_analysis = [];
    $technicalPath = __DIR__ . '/technical_analysis.json';
    if (file_exists($technicalPath)) {
        $technicalData = json_decode(file_get_contents($technicalPath), true);
        $technical_analysis = $technicalData['analysis'] ?? [];
    }

    // Allocazione per asset class
    $allocation_by_asset_class = $portfolioData['allocation_by_asset_class'];

    // Dividendi ricevuti
    $dividends = $portfolioData['dividends'];

    // Opportunità ETF - Caricata da JSON (popolato da n8n workflow)
    $opportunities = [];
    $opportunitiesPath = __DIR__ . '/opportunities.json';
    if (file_exists($opportunitiesPath)) {
        $opportunitiesData = json_decode(file_get_contents($opportunitiesPath), true);
        $opportunities = $opportunitiesData['opportunities'] ?? [];
    }

    // Dashboard Insights - Caricata da JSON (AI analysis)
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

    // Recommendations - Caricata da JSON (AI analysis)
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

    // Dividends Calendar - Caricata da JSON (from API or n8n)
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

    // Calcoli per dashboard
    $holdings_count = count($top_holdings);
    $best_performer = $top_holdings[0] ?? null;
    $worst_performer = end($top_holdings) ?: null;

} catch (Exception $e) {
    // Fallback in caso di errore
    error_log("Error loading portfolio data: " . $e->getMessage());

    // Dati di fallback minimali
    $metadata = [
        'portfolio_name' => 'Portafoglio ETF',
        'owner' => 'User',
        'last_update' => date('Y-m-d'),
        'base_currency' => 'EUR',
        'total_value' => 0,
        'total_invested' => 0,
        'unrealized_pnl' => 0,
        'unrealized_pnl_pct' => 0,
        'realized_pnl' => 0,
        'total_dividends' => 0
    ];

    $top_holdings = [];
    $monthly_performance = [];
    $technical_analysis = [];
    $allocation_by_asset_class = [];
    $dividends = [];
    $opportunities = [];
    $holdings_count = 0;
    $best_performer = null;
    $worst_performer = null;
}
