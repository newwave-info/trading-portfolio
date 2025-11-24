<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ETF Portfolio Manager - Dashboard</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/styles.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#1e293b',
                        secondary: '#334155',
                        accent: '#3b82f6',
                        positive: '#10b981',
                        success: '#10b981',
                        'success-light': '#d1fae5',
                        'success-dark': '#059669',
                        danger: '#ef4444',
                        'danger-light': '#fee2e2',
                        'danger-dark': '#dc2626',
                        purple: '#8b5cf6',
                        'purple-light': '#ede9fe',
                        'purple-dark': '#7c3aed',
                        warning: '#f59e0b',
                        'warning-light': '#fef3c7',
                        negative: '#ef4444',
                    }
                }
            }
        }
    </script>
</head>
<body class="flex flex-col h-screen overflow-hidden bg-gray-50">
    <?php
    // ============================================
    // DATI STATICI ESEMPIO - PORTFOLIO ETF
    // ============================================

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

    // Storic performance (ultimi 12 mesi)
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
            'updated_at' => '2025-11-24 18:00'
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
            'updated_at' => '2025-11-24 18:00'
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
            'updated_at' => '2025-11-24 18:00'
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
            'type' => '_distribution'
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
            'updated_at' => '2025-11-23'
        ]
    ];

    // Calcoli per dashboard
    $holdings_count = count($top_holdings);
    $best_performer = $top_holdings[0]; // IWDA ha il miglior rendimento
    $worst_performer = $top_holdings[3]; // EIMI ha rendimento più basso
    ?>

    <!-- Top Header -->
    <div class="h-[60px] bg-white border-b border-gray-200 px-6 flex items-center justify-between z-50 shrink-0">
        <div>
            <h2 class="text-[18px] font-semibold text-primary"><?php echo htmlspecialchars($metadata['portfolio_name']); ?></h2>
            <p class="text-[11px] text-gray-500"><?php echo htmlspecialchars($metadata['owner']); ?></p>
        </div>
        <div class="flex items-center gap-4">
            <div class="text-[11px] text-gray-500 hidden sm:block">
                Ultimo aggiornamento: <strong class="text-gray-700"><?php echo htmlspecialchars($metadata['last_update']); ?></strong>
            </div>
            <button id="themeToggle" class="text-gray-500 hover:text-purple text-lg transition-colors" title="Cambia tema">
                <i class="fa-solid fa-moon"></i>
            </button>
            <button id="mobileMenuBtn" class="md:hidden text-primary text-xl">
                <i class="fa-solid fa-bars"></i>
            </button>
        </div>
    </div>

    <!-- Main Content -->
    <div class="flex-1 overflow-y-auto px-4 md:px-6 py-6 sm:py-10">
        <!-- Cards Overview -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 mb-8">
            <div class="widget-card widget-purple p-6">
                <div class="flex items-center gap-2 mb-3">
                    <i class="fa-solid fa-wallet text-purple"></i>
                    <span class="text-[11px] font-medium text-gray-600 uppercase tracking-wider">Valore Totale</span>
                </div>
                <div class="text-2xl font-bold text-primary mb-1">€<?php echo number_format($metadata['total_value'], 2, ',', '.'); ?></div>
                <div class="text-[11px] text-gray-500">Investito: €<?php echo number_format($metadata['total_invested'], 2, ',', '.'); ?></div>
            </div>

            <div class="widget-card widget-purple p-6">
                <div class="flex items-center gap-2 mb-3">
                    <i class="fa-solid fa-chart-line text-purple"></i>
                    <span class="text-[11px] font-medium text-gray-600 uppercase tracking-wider">P&L Non Realizzato</span>
                </div>
                <div class="text-2xl font-bold text-positive mb-1">€<?php echo number_format($metadata['unrealized_pnl'], 2, ',', '.'); ?></div>
                <div class="text-[11px] text-gray-500">+<?php echo number_format($metadata['unrealized_pnl_pct'], 2, ',', '.'); ?>%</div>
            </div>

            <div class="widget-card widget-purple p-6">
                <div class="flex items-center gap-2 mb-3">
                    <i class="fa-solid fa-coins text-purple"></i>
                    <span class="text-[11px] font-medium text-gray-600 uppercase tracking-wider">Dividendi Totali</span>
                </div>
                <div class="text-2xl font-bold text-primary mb-1">€<?php echo number_format($metadata['total_dividends'], 2, ',', '.'); ?></div>
                <div class="text-[11px] text-gray-500"><?php echo count($dividends); ?> pagamenti</div>
            </div>

            <div class="widget-card widget-purple p-6">
                <div class="flex items-center gap-2 mb-3">
                    <i class="fa-solid fa-boxes-stacked text-purple"></i>
                    <span class="text-[11px] font-medium text-gray-600 uppercase tracking-wider">Posizioni</span>
                </div>
                <div class="text-2xl font-bold text-primary mb-1"><?php echo $holdings_count; ?></div>
                <div class="text-[11px] text-gray-500">ETF attivi</div>
            </div>
        </div>

        <!-- Performance Chart -->
        <div class="mb-8">
            <div class="widget-card widget-purple p-6">
                <div class="flex justify-between items-center mb-5 pb-4 border-b border-gray-200">
                    <div class="flex items-center gap-2">
                        <span class="text-[11px] font-medium text-gray-600 uppercase tracking-wider">Andamento Portafoglio</span>
                    </div>
                </div>
                <div class="relative h-[300px]">
                    <canvas id="performanceChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Top Holdings Table -->
        <div class="mb-8">
            <div class="widget-card p-6">
                <div class="flex justify-between items-center mb-5 pb-4 border-b border-gray-200">
                    <span class="text-[11px] font-medium text-gray-600 uppercase tracking-wider">Top Holdings</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700 text-[11px] uppercase">Ticker</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700 text-[11px] uppercase">Nome</th>
                                <th class="px-4 py-3 text-right font-semibold text-gray-700 text-[11px] uppercase">Quantità</th>
                                <th class="px-4 py-3 text-right font-semibold text-gray-700 text-[11px] uppercase">Prezzo Medio</th>
                                <th class="px-4 py-3 text-right font-semibold text-gray-700 text-[11px] uppercase">Prezzo Attuale</th>
                                <th class="px-4 py-3 text-right font-semibold text-gray-700 text-[11px] uppercase">Valore</th>
                                <th class="px-4 py-3 text-right font-semibold text-gray-700 text-[11px] uppercase">P&L</th>
                                <th class="px-4 py-3 text-right font-semibold text-gray-700 text-[11px] uppercase">Drift</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($top_holdings as $holding): ?>
                            <tr class="border-b border-gray-200 hover:bg-gray-50">
                                <td class="px-4 py-3 font-semibold text-purple"><?php echo htmlspecialchars($holding['ticker']); ?></td>
                                <td class="px-4 py-3">
                                    <div class="font-medium text-gray-800"><?php echo htmlspecialchars($holding['name']); ?></div>
                                    <div class="text-[11px] text-gray-500"><?php echo htmlspecialchars($holding['asset_class']); ?></div>
                                </td>
                                <td class="px-4 py-3 text-right"><?php echo number_format($holding['quantity'], 2, ',', '.'); ?></td>
                                <td class="px-4 py-3 text-right">€<?php echo number_format($holding['avg_price'], 2, ',', '.'); ?></td>
                                <td class="px-4 py-3 text-right">€<?php echo number_format($holding['current_price'], 2, ',', '.'); ?></td>
                                <td class="px-4 py-3 text-right font-semibold">€<?php echo number_format($holding['market_value'], 2, ',', '.'); ?></td>
                                <td class="px-4 py-3 text-right">
                                    <div class="font-semibold text-positive">€<?php echo number_format($holding['unrealized_pnl'], 2, ',', '.'); ?></div>
                                    <div class="text-[11px] text-positive">+<?php echo number_format($holding['pnl_percentage'], 2, ',', '.'); ?>%</div>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <span class="px-2 py-1 rounded text-[11px] <?php echo $holding['drift'] < 0 ? 'bg-yellow-100 text-yellow-700' : 'bg-green-100 text-green-700'; ?>">
                                        <?php echo number_format($holding['drift'], 2, ',', '.'); ?>%
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Allocation & Analysis -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <div class="widget-card widget-purple p-6">
                <div class="flex justify-between items-center mb-5 pb-4 border-b border-gray-200">
                    <div class="flex items-center gap-2">
                        <span class="text-[11px] font-medium text-gray-600 uppercase tracking-wider">Allocazione per Asset Class</span>
                    </div>
                </div>
                <div class="relative h-[250px]">
                    <canvas id="allocationChart"></canvas>
                </div>
            </div>

            <div class="widget-card widget-purple p-6">
                <div class="flex justify-between items-center mb-5 pb-4 border-b border-gray-200">
                    <div class="flex items-center gap-2">
                        <span class="text-[11px] font-medium text-gray-600 uppercase tracking-wider">Analisi Tecnica</span>
                    </div>
                </div>
                <div class="space-y-4">
                    <?php foreach ($technical_analysis as $analysis): ?>
                    <div class="p-4 bg-gray-50 border border-gray-200">
                        <div class="flex justify-between items-start mb-2">
                            <div>
                                <div class="font-semibold text-primary"><?php echo htmlspecialchars($analysis['ticker']); ?></div>
                                <div class="text-[11px] text-gray-500">RSI: <?php echo number_format($analysis['rsi'], 1); ?></div>
                            </div>
                            <span class="px-2 py-1 rounded text-[11px] font-semibold <?php echo $analysis['signal'] === 'BUY' ? 'bg-green-100 text-green-700' : ($analysis['signal'] === 'SELL' ? 'bg-red-100 text-red-700' : 'bg-blue-100 text-blue-700'); ?>">
                                <?php echo $analysis['signal']; ?>
                            </span>
                        </div>
                        <div class="flex gap-4 text-[11px]">
                            <span class="<?php echo $analysis['change_1d'] >= 0 ? 'text-positive' : 'text-negative'; ?>">
                                1D: <?php echo ($analysis['change_1d'] >= 0 ? '+' : ''); ?><?php echo number_format($analysis['change_1d'], 2, ',', '.'); ?>%
                            </span>
                            <span class="<?php echo $analysis['change_1m'] >= 0 ? 'text-positive' : 'text-negative'; ?>">
                                1M: <?php echo ($analysis['change_1m'] >= 0 ? '+' : ''); ?><?php echo number_format($analysis['change_1m'], 2, ',', '.'); ?>%
                            </span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Dividends & Opportunities -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <div class="widget-card p-6">
                <div class="flex justify-between items-center mb-5 pb-4 border-b border-gray-200">
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-gift text-purple"></i>
                        <span class="text-[11px] font-medium text-gray-600 uppercase tracking-wider">Dividendi Ricevuti (2025)</span>
                    </div>
                </div>
                <div class="space-y-3">
                    <?php foreach ($dividends as $div): ?>
                    <div class="flex justify-between items-center p-3 bg-gray-50 border border-gray-200">
                        <div>
                            <div class="font-semibold text-primary text-sm"><?php echo htmlspecialchars($div['ticker']); ?></div>
                            <div class="text-[11px] text-gray-500"><?php echo date('d/m/Y', strtotime($div['pay_date'])); ?></div>
                        </div>
                        <div class="text-right">
                            <div class="font-bold text-positive">€<?php echo number_format($div['amount'], 2, ',', '.'); ?></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="widget-card p-6">
                <div class="flex justify-between items-center mb-5 pb-4 border-b border-gray-200">
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-search text-purple"></i>
                        <span class="text-[11px] font-medium text-gray-600 uppercase tracking-wider">Opportunità n8n</span>
                    </div>
                </div>
                <div class="space-y-3">
                    <?php foreach ($opportunities as $opp): ?>
                    <div class="p-4 bg-gradient-to-r from-purple/5 to-purple/10 border border-purple/20">
                        <div class="flex justify-between items-start mb-2">
                            <div>
                                <div class="font-semibold text-primary"><?php echo htmlspecialchars($opp['ticker']); ?></div>
                                <div class="text-[11px] text-gray-600"><?php echo htmlspecialchars($opp['name']); ?></div>
                            </div>
                            <span class="px-2 py-1 rounded text-[11px] font-semibold <?php echo $opp['signal'] === 'BUY' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700'; ?>">
                                <?php echo $opp['signal']; ?>
                            </span>
                        </div>
                        <div class="text-[11px] text-gray-600 mt-2">
                            <i class="fa-solid fa-lightbulb text-yellow-500"></i> <?php echo htmlspecialchars($opp['reason']); ?>
                        </div>
                        <div class="flex gap-3 mt-2 text-[11px]">
                            <span class="text-gray-500">Yield: <strong><?php echo number_format($opp['yield'], 2, ',', '.'); ?>%</strong></span>
                            <span class="text-gray-500">TER: <strong><?php echo number_format($opp['expense_ratio'], 2, ',', '.'); ?>%</strong></span>
                            <span class="px-1 py-0.5 bg-green-100 text-green-700 rounded text-[10px]"><?php echo $opp['commission_profile']; ?></span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

    </div>

    <script src="assets/js/app.js"></script>
    <script>
        // Performance Chart
        const performanceCtx = document.getElementById('performanceChart').getContext('2d');
        new Chart(performanceCtx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode(array_column($monthly_performance, 'month')); ?>,
                datasets: [{
                    label: 'Valore Portafoglio',
                    data: <?php echo json_encode(array_column($monthly_performance, 'value')); ?>,
                    borderColor: '#8b5cf6',
                    backgroundColor: 'rgba(139, 92, 246, 0.1)',
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: false,
                        ticks: {
                            callback: function(value) {
                                return '€' + value.toLocaleString('it-IT');
                            }
                        }
                    }
                }
            }
        });

        // Allocation Chart
        const allocationCtx = document.getElementById('allocationChart').getContext('2d');
        new Chart(allocationCtx, {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode(array_column($allocation_by_asset_class, 'class')); ?>,
                datasets: [{
                    data: <?php echo json_encode(array_column($allocation_by_asset_class, 'percentage')); ?>,
                    backgroundColor: [
                        '#8b5cf6',
                        '#3b82f6',
                        '#10b981',
                        '#f59e0b',
                        '#ef4444'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    </script>
</body>
</html>
