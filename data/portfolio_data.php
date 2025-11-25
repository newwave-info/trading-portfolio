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

    // Storico performance (ultimi 12 mesi)
    $monthly_performance = $portfolioData['monthly_performance'];

    // Analisi tecnica (placeholder - verrà popolato da n8n)
    $technical_analysis = [
        [
            'isin' => 'IE00B3RBWM25',
            'ticker' => 'IWDA',
            'signal' => 'HOLD',
            'confidence' => 0.78,
            'price' => 89.45,
            'change_1d' => 0.85,
            'change_1m' => 3.25,
            'change_3m' => 8.15,
            'volatility' => 12.45,
            'volume' => 2540000,
            'sma_50' => 87.20,
            'sma_200' => 82.15,
            'rsi' => 62.30,
            'updated_at' => '2025-11-24 18:00',
            'reasoning' => 'Prezzo sopra tutte le medie mobili con RSI in zona neutrale (62). Trend rialzista confermato ma momentum in raffreddamento. Consigliato mantenere posizione e attendere consolidamento.'
        ],
        [
            'isin' => 'IE00B1FZC250',
            'ticker' => 'IUSA',
            'signal' => 'BUY',
            'confidence' => 0.72,
            'price' => 442.15,
            'change_1d' => 1.25,
            'change_1m' => 5.80,
            'change_3m' => 12.35,
            'volatility' => 15.20,
            'volume' => 1850000,
            'sma_50' => 435.20,
            'sma_200' => 418.75,
            'rsi' => 68.50,
            'updated_at' => '2025-11-24 18:00',
            'reasoning' => 'Forte momentum rialzista con RSI 68.5 e prezzo sopra SMA50/200. Volumi in crescita confermano interesse. Golden cross recente tra EMA50 e EMA200. Opportunità di accumulo su debolezza.'
        ],
        [
            'isin' => 'IE00BF5JHG71',
            'ticker' => 'EIMI',
            'signal' => 'WATCH',
            'confidence' => 0.45,
            'price' => 32.10,
            'change_1d' => -0.45,
            'change_1m' => 2.15,
            'change_3m' => 5.85,
            'volatility' => 18.75,
            'volume' => 965000,
            'sma_50' => 31.85,
            'sma_200' => 30.20,
            'rsi' => 55.20,
            'updated_at' => '2025-11-24 18:00',
            'reasoning' => 'Segnali contrastanti: prezzo sopra medie ma volumi deboli. RSI neutrale a 55. Alta volatilità (18.75%) richiede cautela. Attendere breakout confermato sopra 32.50 o supporto a 31.20.'
        ]
    ];

    // Allocazione per asset class
    $allocation_by_asset_class = $portfolioData['allocation_by_asset_class'];

    // Dividendi ricevuti
    $dividends = $portfolioData['dividends'];

    // Opportunità da workflow n8n (placeholder)
    $opportunities = [
        [
            'isin' => 'IE00B53S7W95',
            'ticker' => 'IBCI',
            'name' => 'iShares Core Euro Corp Bond UCITS ETF',
            'signal' => 'BUY',
            'confidence' => 0.82,
            'commission_profile' => 'ZERO',
            'reason' => 'Bonds Europe with zero commission on Fineco',
            'yield' => 3.45,
            'expense_ratio' => 0.40,
            'entry_price' => 125.50,
            'target_price' => 135.00,
            'updated_at' => '2025-11-23'
        ],
        [
            'isin' => 'IE00B4XGL253',
            'ticker' => 'IEAG',
            'name' => 'iShares Core € Govt Bond UCITS ETF',
            'signal' => 'WATCH',
            'confidence' => 0.65,
            'commission_profile' => 'ZERO',
            'reason' => 'Euro Government Bonds, good for diversification',
            'yield' => 2.85,
            'expense_ratio' => 0.20,
            'entry_price' => 110.00,
            'target_price' => 118.00,
            'updated_at' => '2025-11-23'
        ]
    ];

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
