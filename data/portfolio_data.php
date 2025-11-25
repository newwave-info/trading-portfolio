<?php
// ============================================
// DATI STATICI ESEMPIO - PORTAFOGLIO ETF
// ============================================
// Questo file contiene dati di esempio mockati.
// In produzione, questi dati saranno recuperati dal database.

// Metadata del portafoglio
$metadata = [
    'portfolio_name' => 'Portafoglio ETF Personale',
    'owner' => 'Mario Rossi',
    'last_update' => '2025-11-24',
    'base_currency' => 'EUR',
    'total_value' => 125750.50,
    'total_invested' => 100000.00,
    'unrealized_pnl' => 25750.50,
    'unrealized_pnl_pct' => 25.75,
    'realized_pnl' => 3250.75,
    'total_dividends' => 1850.25
];

// Top holdings
$top_holdings = [
    [
        'isin' => 'IE00B3RBWM25',
        'ticker' => 'IWDA',
        'name' => 'iShares Core MSCI World UCITS ETF',
        'asset_class' => 'Equity Global',
        'sector' => 'Global',
        'quantity' => 250.50,
        'avg_price' => 75.20,
        'current_price' => 89.45,
        'market_value' => 22412.25,
        'invested_value' => 18837.60,
        'unrealized_pnl' => 3574.65,
        'pnl_percentage' => 18.98,
        'target_allocation' => 25.00,
        'current_allocation' => 17.83,
        'drift' => -7.17,
        'dividend_yield' => 1.85
    ],
    [
        'isin' => 'IE00B4L5Y983',
        'ticker' => 'IWDA-L',
        'name' => 'iShares Core MSCI World UCITS ETF (Dist)',
        'asset_class' => 'Equity Global',
        'sector' => 'Global',
        'quantity' => 180.25,
        'avg_price' => 76.50,
        'current_price' => 89.45,
        'market_value' => 16126.36,
        'invested_value' => 13789.13,
        'unrealized_pnl' => 2337.23,
        'pnl_percentage' => 16.95,
        'target_allocation' => 20.00,
        'current_allocation' => 12.83,
        'drift' => -7.17,
        'dividend_yield' => 1.82
    ],
    [
        'isin' => 'IE00B1FZC250',
        'ticker' => 'IUSA',
        'name' => 'iShares Core S&P 500 UCITS ETF',
        'asset_class' => 'Equity US',
        'sector' => 'US Large Cap',
        'quantity' => 95.75,
        'avg_price' => 385.20,
        'current_price' => 442.15,
        'market_value' => 42339.16,
        'invested_value' => 36893.18,
        'unrealized_pnl' => 5445.98,
        'pnl_percentage' => 14.76,
        'target_allocation' => 30.00,
        'current_allocation' => 33.68,
        'drift' => 3.68,
        'dividend_yield' => 1.32
    ],
    [
        'isin' => 'IE00BF5JHG71',
        'ticker' => 'EIMI',
        'name' => 'iShares Core MSCI Emerging Markets IMI',
        'asset_class' => 'Equity Emerging',
        'sector' => 'Emerging Markets',
        'quantity' => 320.00,
        'avg_price' => 28.75,
        'current_price' => 32.10,
        'market_value' => 10272.00,
        'invested_value' => 9200.00,
        'unrealized_pnl' => 1072.00,
        'pnl_percentage' => 11.65,
        'target_allocation' => 10.00,
        'current_allocation' => 8.17,
        'drift' => -1.83,
        'dividend_yield' => 2.45
    ],
    [
        'isin' => 'IE00B53L3W79',
        'ticker' => 'IWMO',
        'name' => 'iShares Core MSCI World UCITS ETF (EUR)',
        'asset_class' => 'Equity Global',
        'sector' => 'Global',
        'quantity' => 145.50,
        'avg_price' => 68.90,
        'current_price' => 77.25,
        'market_value' => 11240.88,
        'invested_value' => 10028.95,
        'unrealized_pnl' => 1211.93,
        'pnl_percentage' => 12.08,
        'target_allocation' => 15.00,
        'current_allocation' => 8.94,
        'drift' => -6.06,
        'dividend_yield' => 1.88
    ]
];

// Storico performance (ultimi 12 mesi)
$monthly_performance = [
    ['month' => '2024-12', 'value' => 98500.00, 'invested' => 100000.00],
    ['month' => '2025-01', 'value' => 101200.50, 'invested' => 100000.00],
    ['month' => '2025-02', 'value' => 103800.75, 'invested' => 100000.00],
    ['month' => '2025-03', 'value' => 105900.25, 'invested' => 100000.00],
    ['month' => '2025-04', 'value' => 107500.00, 'invested' => 100000.00],
    ['month' => '2025-05', 'value' => 109800.50, 'invested' => 100000.00],
    ['month' => '2025-06', 'value' => 111200.75, 'invested' => 100000.00],
    ['month' => '2025-07', 'value' => 112900.25, 'invested' => 100000.00],
    ['month' => '2025-08', 'value' => 114500.00, 'invested' => 100000.00],
    ['month' => '2025-09', 'value' => 116800.50, 'invested' => 100000.00],
    ['month' => '2025-10', 'value' => 118900.75, 'invested' => 100000.00],
    ['month' => '2025-11', 'value' => 125750.50, 'invested' => 100000.00]
];

// Analisi tecnica (segnali recenti)
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
$allocation_by_asset_class = [
    ['class' => 'Equity Global', 'percentage' => 39.60, 'value' => 49779.49],
    ['class' => 'Equity US', 'percentage' => 33.68, 'value' => 42339.16],
    ['class' => 'Equity Emerging', 'percentage' => 8.17, 'value' => 10272.00],
    ['class' => 'Bonds', 'percentage' => 12.50, 'value' => 15721.31],
    ['class' => 'Commodities', 'percentage' => 6.05, 'value' => 7638.54]
];

// Dividendi ricevuti
$dividends = [
    [
        'isin' => 'IE00B3RBWM25',
        'ticker' => 'IWDA',
        'amount' => 385.50,
        'ex_date' => '2025-09-15',
        'pay_date' => '2025-10-15',
        'type' => 'distribution'
    ],
    [
        'isin' => 'IE00B1FZC250',
        'ticker' => 'IUSA',
        'amount' => 520.75,
        'ex_date' => '2025-09-20',
        'pay_date' => '2025-10-10',
        'type' => 'distribution'
    ],
    [
        'isin' => 'IE00BF5JHG71',
        'ticker' => 'EIMI',
        'amount' => 185.25,
        'ex_date' => '2025-08-31',
        'pay_date' => '2025-09-30',
        'type' => 'distribution'
    ],
    [
        'isin' => 'IE00B4L5Y983',
        'ticker' => 'IWDA-L',
        'amount' => 298.40,
        'ex_date' => '2025-09-15',
        'pay_date' => '2025-10-05',
        'type' => 'distribution'
    ],
    [
        'isin' => 'IE00B53L3W79',
        'ticker' => 'IWMO',
        'amount' => 155.80,
        'ex_date' => '2025-09-10',
        'pay_date' => '2025-10-01',
        'type' => 'distribution'
    ]
];

// Opportunità da workflow n8n
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
$best_performer = $top_holdings[0]; // IWDA ha il miglior rendimento
$worst_performer = $top_holdings[3]; // EIMI ha rendimento più basso
