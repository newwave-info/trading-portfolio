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
    <script src="https://cdn.jsdelivr.net/npm/patternomaly@1.3.2/dist/patternomaly.min.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/styles.css">
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
    <script>
        // Funzioni base inline per evitare errori prima del caricamento di app.js
        function showView(viewId) {
            document.querySelectorAll('.view').forEach(v => v.classList.add('hidden'));
            const view = document.getElementById(viewId);
            if (view) view.classList.remove('hidden');
            document.querySelectorAll('.nav-item').forEach(item => {
                item.classList.remove('active', 'text-purple-600', 'font-medium');
                item.classList.add('text-gray-600');
            });
        }
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            if (sidebar && overlay) {
                sidebar.classList.toggle('-translate-x-full');
                overlay.classList.toggle('hidden');
            }
        }
        function toggleAccordion(targetId) {
            const content = document.getElementById(targetId);
            const button = document.querySelector(`[data-accordion-toggle="${targetId}"]`);
            if (content && button) {
                content.classList.toggle('hidden');
                const icon = button.querySelector('.accordion-icon');
                if (icon) icon.classList.toggle('rotate-180');
            }
        }
        console.log('Inline functions loaded');
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
            <button id="themeToggle" class="text-gray-500 hover:text-purple text-lg transition-colors" onclick="toggleTheme()" title="Cambia tema">
                <i class="fa-solid fa-moon"></i>
            </button>
            <button id="mobileMenuBtn" class="md:hidden text-primary text-xl" onclick="toggleSidebar()">
                <i class="fa-solid fa-bars"></i>
            </button>
        </div>
    </div>

    <!-- Overlay Mobile -->
    <div id="sidebarOverlay" class="fixed inset-0 bg-black/50 z-30 hidden md:hidden" onclick="toggleSidebar()"></div>

    <!-- Sidebar -->
    <div id="sidebar" class="w-[242px] bg-white border-r border-gray-200 fixed h-screen left-0 top-[60px] z-40 overflow-y-auto flex flex-col transform -translate-x-full md:translate-x-0 transition-transform duration-300">
        <div class="px-4 py-6 flex-1">
            <button class="w-full flex items-center text-[13px] font-semibold text-gray-400 px-2 py-2 pb-4 cursor-not-allowed transition-colors duration-200 border-b border-gray-200 btn-reset" disabled>
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-wallet text-[12px]"></i>
                    <span class="text-left">ETF Portfolio</span>
                </div>
            </button>
            <button class="w-full flex items-center text-[13px] font-semibold text-purple-600 px-2 py-2 pt-4 group transition-colors duration-200 btn-reset" data-accordion-toggle="portfolioMenu" onclick="toggleAccordion('portfolioMenu')">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-chart-line text-[12px] text-purple-600 transition-colors duration-200"></i>
                    <span class="text-purple-600 transition-colors duration-200 text-left">Portfolio Manager</span>
                </div>
                <i class="accordion-icon fa-solid fa-chevron-down text-[10px] text-purple-600 transition-transform duration-200 rotate-180 ml-auto"></i>
            </button>
            <div id="portfolioMenu" class="accordion-content mt-2">
                <div class="ml-3 border-l border-gray-200 space-y-1">
                    <div class="nav-item active flex items-center gap-2.5 pl-4 py-1.5 cursor-pointer text-[13px] text-purple-600 font-medium transition-colors duration-200 hover:text-purple-600" onclick="showView('dashboard'); toggleSidebar()">
                        <i class="fa-solid fa-gauge text-[11px] text-current"></i>
                        <span>Dashboard</span>
                    </div>
                    <div class="nav-item flex items-center gap-2.5 pl-4 py-1.5 cursor-pointer text-[13px] text-gray-600 transition-colors duration-200 hover:text-purple-600" onclick="showView('holdings'); toggleSidebar()">
                        <i class="fa-solid fa-list text-[11px] text-current"></i>
                        <span>Holdings</span>
                    </div>
                    <div class="nav-item flex items-center gap-2.5 pl-4 py-1.5 cursor-pointer text-[13px] text-gray-600 transition-colors duration-200 hover:text-purple-600" onclick="showView('performance'); toggleSidebar()">
                        <i class="fa-solid fa-chart-area text-[11px] text-current"></i>
                        <span>Performance</span>
                    </div>
                    <div class="nav-item flex items-center gap-2.5 pl-4 py-1.5 cursor-pointer text-[13px] text-gray-600 transition-colors duration-200 hover:text-purple-600" onclick="showView('technical'); toggleSidebar()">
                        <i class="fa-solid fa-magnifying-glass-chart text-[11px] text-current"></i>
                        <span>Analisi Tecnica</span>
                    </div>
                    <div class="nav-item flex items-center gap-2.5 pl-4 py-1.5 cursor-pointer text-[13px] text-gray-600 transition-colors duration-200 hover:text-purple-600" onclick="showView('dividends'); toggleSidebar()">
                        <i class="fa-solid fa-gift text-[11px] text-current"></i>
                        <span>Dividendi</span>
                    </div>
                    <div class="nav-item flex items-center gap-2.5 pl-4 py-1.5 cursor-pointer text-[13px] text-gray-600 transition-colors duration-200 hover:text-purple-600" onclick="showView('recommendations'); toggleSidebar()">
                        <i class="fa-solid fa-lightbulb text-[11px] text-current"></i>
                        <span>Raccomandazioni</span>
                    </div>
                    <div class="nav-item flex items-center gap-2.5 pl-4 py-1.5 cursor-pointer text-[13px] text-gray-600 transition-colors duration-200 hover:text-purple-600" onclick="showView('flows'); toggleSidebar()">
                        <i class="fa-solid fa-chart-simple text-[11px] text-current"></i>
                        <span>Flussi & Guadagni</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div id="mainContent" class="ml-0 md:ml-[242px] flex-1 flex flex-col overflow-hidden">
        <!-- Content -->
        <div class="flex-1 overflow-y-auto px-4 md:px-6 py-6 sm:py-10">

            <!-- View: Dashboard -->
            <div id="dashboard" class="view">
                <div class="mb-6 sm:mb-10">
                    <h1 class="text-[18px] sm:text-[20px] font-medium text-primary tracking-tight">Dashboard Overview</h1>
                </div>

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

        <!-- Info Banner -->
        <?php
        $portfolio_score = 74; // Calcolo dinamico in seguito
        $score_label = $portfolio_score >= 75 ? 'Ottimo' : ($portfolio_score >= 60 ? 'Buono' : 'Migliorabile');
        $banner_date = date('d/m/Y H:i', strtotime($metadata['last_update']));
        ?>
        <div class="mb-8 p-4 bg-gray-50 border border-gray-200 rounded">
            <div class="flex items-start gap-3">
                <i class="fa-solid fa-bullhorn text-gray-500 text-base mt-0.5"></i>
                <div class="flex-1">
                    <p class="text-[13px] text-gray-700">
                        <strong>Aggiornamento <?php echo $banner_date; ?>:</strong>
                        Portfolio con score <?php echo $portfolio_score; ?>/100 (<?php echo $score_label; ?>).
                        Performance attuale +<?php echo number_format($metadata['unrealized_pnl_pct'], 2, ',', '.'); ?>%.
                        Top performer: <?php echo htmlspecialchars($best_performer['ticker']); ?>
                        +<?php echo number_format($best_performer['pnl_percentage'], 2, ',', '.'); ?>%.
                    </p>
                </div>
            </div>
        </div>

        <!-- Score di Salute Portfolio -->
        <div class="mb-8">
            <div class="widget-card p-6">
                <h2 class="text-base font-semibold mb-4 text-primary">Salute Portafoglio</h2>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <div class="flex flex-col items-center justify-center">
                        <div class="text-5xl font-bold text-purple"><?php echo $portfolio_score; ?></div>
                        <div class="text-2xl font-semibold text-gray-600 mt-1">/100</div>
                        <div class="text-sm text-gray-500 mt-2 px-3 py-1 bg-purple-100 rounded-full">
                            <?php echo $score_label; ?>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <div>
                            <div class="text-xs text-gray-500 mb-1">Diversificazione:</div>
                            <span class="px-3 py-1 bg-success-light text-success-dark text-xs font-semibold rounded">Buona</span>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <div>
                            <div class="text-xs text-gray-500 mb-1">Performance:</div>
                            <span class="px-3 py-1 bg-success-light text-success-dark text-xs font-semibold rounded">Positiva</span>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <div>
                            <div class="text-xs text-gray-500 mb-1">Rischio:</div>
                            <span class="px-3 py-1 bg-warning-light text-warning text-xs font-semibold rounded">Moderato</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- AI Insights -->
        <div class="mb-8">
            <div class="widget-ai-insight px-6 py-4 transition-all duration-300">
                <div class="flex items-center gap-2.5 mb-3 pb-2.5 border-b border-blue-200/40">
                    <span class="badge-ai bg-purple text-white text-[9px] font-bold px-1.5 py-0.5 uppercase tracking-wide">AI Insight</span>
                    <span class="text-xs font-semibold text-primary uppercase tracking-wide">Riepilogo Portfolio <?php echo date('Y', strtotime($metadata['last_update'])); ?></span>
                </div>
                <div class="text-[13px] leading-relaxed text-gray-700">
                    <div class="pl-4 relative py-2 border-b border-dashed border-gray-300">
                        <span class="absolute left-0 text-danger font-bold">→</span>
                        Attenzione a <?php echo htmlspecialchars($worst_performer['ticker']); ?> con performance <?php echo number_format($worst_performer['pnl_percentage'], 2, ',', '.'); ?>%. Monitorare per eventuali azioni correttive.
                    </div>
                    <div class="pl-4 relative py-2 border-b border-dashed border-gray-300">
                        <span class="absolute left-0 text-purple font-bold">→</span>
                        Performance positiva (+<?php echo number_format($metadata['unrealized_pnl_pct'], 2, ',', '.'); ?>%) grazie alla forte crescita di <?php echo htmlspecialchars($best_performer['ticker']); ?> (+<?php echo number_format($best_performer['pnl_percentage'], 2, ',', '.'); ?>%).
                    </div>
                    <div class="pl-4 relative py-2">
                        <span class="absolute left-0 text-purple font-bold">→</span>
                        Portfolio bilanciato con <?php echo $holdings_count; ?> posizioni. Allocazione concentrata su global equity (<?php echo number_format($allocation_by_asset_class[0]['percentage'], 1, ',', '.'); ?>%).
                    </div>
                </div>
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

        <!-- Top 5 & Bottom 5 Performers -->
        <div class="mb-8">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Top 5 Performers -->
                <div class="widget-card p-6">
                    <div class="flex justify-between items-center mb-5 pb-4 border-b border-gray-200">
                        <span class="text-[11px] font-medium text-gray-600 uppercase tracking-wider">Top 5 Performer</span>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50 border-b border-gray-200">
                                <tr>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-700 text-[11px] uppercase">Ticker</th>
                                    <th class="px-4 py-3 text-right font-semibold text-gray-700 text-[11px] uppercase">Gain %</th>
                                    <th class="px-4 py-3 text-right font-semibold text-gray-700 text-[11px] uppercase">Gain €</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $sorted_holdings = $top_holdings;
                                usort($sorted_holdings, function($a, $b) {
                                    return $b['pnl_percentage'] - $a['pnl_percentage'];
                                });
                                $top_5 = array_slice($sorted_holdings, 0, min(5, count($sorted_holdings)));
                                foreach ($top_5 as $holding):
                                ?>
                                <tr class="border-b border-gray-200 hover:bg-gray-50">
                                    <td class="px-4 py-3 font-semibold text-purple"><?php echo htmlspecialchars($holding['ticker']); ?></td>
                                    <td class="px-4 py-3 text-right text-positive font-semibold">+<?php echo number_format($holding['pnl_percentage'], 2, ',', '.'); ?>%</td>
                                    <td class="px-4 py-3 text-right text-positive font-semibold">+€<?php echo number_format($holding['unrealized_pnl'], 2, ',', '.'); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Bottom 5 Performers -->
                <div class="widget-card p-6">
                    <div class="flex justify-between items-center mb-5 pb-4 border-b border-gray-200">
                        <span class="text-[11px] font-medium text-gray-600 uppercase tracking-wider">Bottom 5 Performer</span>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50 border-b border-gray-200">
                                <tr>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-700 text-[11px] uppercase">Ticker</th>
                                    <th class="px-4 py-3 text-right font-semibold text-gray-700 text-[11px] uppercase">Gain %</th>
                                    <th class="px-4 py-3 text-right font-semibold text-gray-700 text-[11px] uppercase">Gain €</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $bottom_5 = array_slice($sorted_holdings, -min(5, count($sorted_holdings)));
                                foreach ($bottom_5 as $holding):
                                    $is_negative = $holding['pnl_percentage'] < 0;
                                ?>
                                <tr class="border-b border-gray-200 hover:bg-gray-50">
                                    <td class="px-4 py-3 font-semibold text-purple"><?php echo htmlspecialchars($holding['ticker']); ?></td>
                                    <td class="px-4 py-3 text-right <?php echo $is_negative ? 'text-negative' : 'text-positive'; ?> font-semibold"><?php echo $is_negative ? '' : '+'; ?><?php echo number_format($holding['pnl_percentage'], 2, ',', '.'); ?>%</td>
                                    <td class="px-4 py-3 text-right <?php echo $is_negative ? 'text-negative' : 'text-positive'; ?> font-semibold"><?php echo $is_negative ? '' : '+'; ?>€<?php echo number_format($holding['unrealized_pnl'], 2, ',', '.'); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Breakdown per Tipo Strumento -->
        <div class="mb-8">
            <div class="widget-card p-6">
                <div class="flex justify-between items-center mb-5 pb-4 border-b border-gray-200">
                    <span class="text-[11px] font-medium text-gray-600 uppercase tracking-wider">Breakdown per Tipo</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700 text-[11px] uppercase">Tipo</th>
                                <th class="px-4 py-3 text-right font-semibold text-gray-700 text-[11px] uppercase">Valore €</th>
                                <th class="px-4 py-3 text-right font-semibold text-gray-700 text-[11px] uppercase">Percentuale</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Raggruppa holdings per tipo
                            $breakdown_by_type = [];
                            foreach ($top_holdings as $holding) {
                                // Per ora assumiamo tutti ETF, in futuro questa logica andrà nel backend
                                $type = 'ETF';
                                if (!isset($breakdown_by_type[$type])) {
                                    $breakdown_by_type[$type] = ['value' => 0, 'percentage' => 0];
                                }
                                $breakdown_by_type[$type]['value'] += $holding['market_value'];
                            }
                            // Calcola percentuali
                            foreach ($breakdown_by_type as $type => &$data) {
                                $data['percentage'] = ($data['value'] / $metadata['total_value']) * 100;
                            }
                            foreach ($breakdown_by_type as $type => $data):
                            ?>
                            <tr class="border-b border-gray-200 hover:bg-gray-50">
                                <td class="px-4 py-3 font-semibold"><?php echo htmlspecialchars($type); ?></td>
                                <td class="px-4 py-3 text-right font-semibold">€<?php echo number_format($data['value'], 2, ',', '.'); ?></td>
                                <td class="px-4 py-3 text-right">
                                    <span class="px-2 py-1 rounded text-[11px] bg-purple-100 text-purple-700 font-semibold">
                                        <?php echo number_format($data['percentage'], 2, ',', '.'); ?>%
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
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
            <!-- End Dashboard View -->

            <!-- View: Holdings -->
            <div id="holdings" class="view hidden">
                <div class="mb-6 sm:mb-10">
                    <h1 class="text-[18px] sm:text-[20px] font-medium text-primary tracking-tight">Holdings Dettagliate</h1>
                </div>

                <!-- Holdings Table -->
                <div class="widget-card p-6">
                    <div class="flex justify-between items-center mb-5 pb-4 border-b border-gray-200">
                        <span class="text-[11px] font-medium text-gray-600 uppercase tracking-wider">Tutte le Posizioni</span>
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
                                    <th class="px-4 py-3 text-right font-semibold text-gray-700 text-[11px] uppercase">P&L €</th>
                                    <th class="px-4 py-3 text-right font-semibold text-gray-700 text-[11px] uppercase">P&L %</th>
                                    <th class="px-4 py-3 text-right font-semibold text-gray-700 text-[11px] uppercase">Allocation</th>
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
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <div class="font-semibold text-positive">+<?php echo number_format($holding['pnl_percentage'], 2, ',', '.'); ?>%</div>
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <span class="px-2 py-1 rounded text-[11px] <?php echo $holding['drift'] < 0 ? 'bg-yellow-100 text-yellow-700' : 'bg-green-100 text-green-700'; ?>">
                                            <?php echo number_format($holding['current_allocation'], 2, ',', '.'); ?>%
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- View: Performance -->
            <div id="performance" class="view hidden">
                <div class="mb-6 sm:mb-10">
                    <h1 class="text-[18px] sm:text-[20px] font-medium text-primary tracking-tight">Performance Storica</h1>
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
                            <canvas id="performanceDetailChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Performance Metrics -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 mb-8">
                    <div class="widget-card widget-purple p-6">
                        <div class="flex items-center gap-2 mb-3">
                            <i class="fa-solid fa-chart-line text-purple"></i>
                            <span class="text-[11px] font-medium text-gray-600 uppercase tracking-wider">1 Mese</span>
                        </div>
                        <div class="text-2xl font-bold text-positive mb-1">+5.25%</div>
                        <div class="text-[11px] text-gray-500">vs €118.9k</div>
                    </div>

                    <div class="widget-card widget-purple p-6">
                        <div class="flex items-center gap-2 mb-3">
                            <i class="fa-solid fa-chart-area text-purple"></i>
                            <span class="text-[11px] font-medium text-gray-600 uppercase tracking-wider">3 Mesi</span>
                        </div>
                        <div class="text-2xl font-bold text-positive mb-1">+12.80%</div>
                        <div class="text-[11px] text-gray-500">vs €111.4k</div>
                    </div>

                    <div class="widget-card widget-purple p-6">
                        <div class="flex items-center gap-2 mb-3">
                            <i class="fa-solid fa-arrow-trend-up text-purple"></i>
                            <span class="text-[11px] font-medium text-gray-600 uppercase tracking-wider">YTD</span>
                        </div>
                        <div class="text-2xl font-bold text-positive mb-1">+25.75%</div>
                        <div class="text-[11px] text-gray-500">vs €100.0k</div>
                    </div>

                    <div class="widget-card widget-purple p-6">
                        <div class="flex items-center gap-2 mb-3">
                            <i class="fa-solid fa-trophy text-purple"></i>
                            <span class="text-[11px] font-medium text-gray-600 uppercase tracking-wider">Best Performer</span>
                        </div>
                        <div class="text-xl font-bold text-primary mb-1"><?php echo htmlspecialchars($best_performer['ticker']); ?></div>
                        <div class="text-[11px] text-positive">+<?php echo number_format($best_performer['pnl_percentage'], 2, ',', '.'); ?>%</div>
                    </div>
                </div>
            </div>

            <!-- View: Technical Analysis -->
            <div id="technical" class="view hidden">
                <div class="mb-6 sm:mb-10">
                    <h1 class="text-[18px] sm:text-[20px] font-medium text-primary tracking-tight">Analisi Tecnica</h1>
                </div>

                <!-- AI Insights per Technical -->
                <div class="mb-8">
                    <div class="widget-ai-insight px-6 py-4 transition-all duration-300">
                        <div class="flex items-center gap-2.5 mb-3 pb-2.5 border-b border-blue-200/40">
                            <span class="badge-ai bg-purple text-white text-[9px] font-bold px-1.5 py-0.5 uppercase tracking-wide">AI Insight</span>
                            <span class="text-xs font-semibold text-primary uppercase tracking-wide">Analisi Tecnica</span>
                        </div>
                        <div class="text-[13px] leading-relaxed text-gray-700">
                            <div class="pl-4 relative py-2 border-b border-dashed border-gray-300">
                                <span class="absolute left-0 text-purple font-bold">→</span>
                                Segnale BUY su <?php echo htmlspecialchars($technical_analysis[1]['ticker']); ?> con RSI <?php echo number_format($technical_analysis[1]['rsi'], 0); ?> e trend rialzista confermato.
                            </div>
                            <div class="pl-4 relative py-2">
                                <span class="absolute left-0 text-danger font-bold">→</span>
                                Attenzione a <?php echo htmlspecialchars($technical_analysis[2]['ticker']); ?> con RSI <?php echo number_format($technical_analysis[2]['rsi'], 0); ?> in zona neutrale. Monitorare per breakout.
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filters -->
                <div class="mb-6 flex justify-between items-center">
                    <div class="flex gap-2">
                        <button class="px-3 py-1 text-xs font-semibold rounded bg-purple text-white hover:bg-purple-dark transition-colors" data-filter="all">Tutti</button>
                        <button class="px-3 py-1 text-xs font-semibold rounded bg-gray-200 text-gray-700 hover:bg-gray-300 transition-colors" data-filter="BUY">BUY</button>
                        <button class="px-3 py-1 text-xs font-semibold rounded bg-gray-200 text-gray-700 hover:bg-gray-300 transition-colors" data-filter="HOLD">HOLD</button>
                        <button class="px-3 py-1 text-xs font-semibold rounded bg-gray-200 text-gray-700 hover:bg-gray-300 transition-colors" data-filter="WATCH">WATCH</button>
                    </div>
                    <button class="px-3 py-1 bg-purple text-white text-[11px] font-semibold rounded hover:bg-purple-dark transition-colors">
                        <i class="fa-solid fa-download mr-1"></i> Export CSV
                    </button>
                </div>

                <!-- Technical Table -->
                <div class="widget-card p-6">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50 border-b border-gray-200">
                                <tr>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-700 text-[11px] uppercase">Ticker</th>
                                    <th class="px-4 py-3 text-right font-semibold text-gray-700 text-[11px] uppercase">Prezzo</th>
                                    <th class="px-4 py-3 text-right font-semibold text-gray-700 text-[11px] uppercase">EMA9</th>
                                    <th class="px-4 py-3 text-right font-semibold text-gray-700 text-[11px] uppercase">EMA21</th>
                                    <th class="px-4 py-3 text-right font-semibold text-gray-700 text-[11px] uppercase">EMA50</th>
                                    <th class="px-4 py-3 text-right font-semibold text-gray-700 text-[11px] uppercase">EMA200</th>
                                    <th class="px-4 py-3 text-right font-semibold text-gray-700 text-[11px] uppercase">RSI</th>
                                    <th class="px-4 py-3 text-center font-semibold text-gray-700 text-[11px] uppercase">MACD</th>
                                    <th class="px-4 py-3 text-right font-semibold text-gray-700 text-[11px] uppercase">Score</th>
                                    <th class="px-4 py-3 text-center font-semibold text-gray-700 text-[11px] uppercase">Decisione</th>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-700 text-[11px] uppercase">Azione</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                foreach ($technical_analysis as $analysis):
                                    // Simula dati EMA (in produzione verranno dal backend)
                                    $ema9 = $analysis['price'] * (rand(98, 102) / 100);
                                    $ema21 = $analysis['sma_50'] * 0.98;
                                    $ema50 = $analysis['sma_50'];
                                    $ema200 = $analysis['sma_200'];
                                    $macd_signal = $analysis['signal'] === 'BUY' ? 'Positivo' : ($analysis['signal'] === 'WATCH' ? 'Neutrale' : 'Positivo Div');
                                    $tech_score = round($analysis['confidence'] * 100);
                                    $action = $analysis['signal'] === 'BUY' ? 'Accumula €' . number_format($analysis['price'] * 0.98, 2) : 'Hold + div';
                                ?>
                                <tr class="border-b border-gray-200 hover:bg-gray-50" data-signal="<?php echo $analysis['signal']; ?>">
                                    <td class="px-4 py-3 font-semibold text-purple"><?php echo htmlspecialchars($analysis['ticker']); ?></td>
                                    <td class="px-4 py-3 text-right font-medium">€<?php echo number_format($analysis['price'], 2, ',', '.'); ?></td>
                                    <td class="px-4 py-3 text-right text-xs">€<?php echo number_format($ema9, 2, ',', '.'); ?></td>
                                    <td class="px-4 py-3 text-right text-xs">€<?php echo number_format($ema21, 2, ',', '.'); ?></td>
                                    <td class="px-4 py-3 text-right text-xs">€<?php echo number_format($ema50, 2, ',', '.'); ?></td>
                                    <td class="px-4 py-3 text-right text-xs">€<?php echo number_format($ema200, 2, ',', '.'); ?></td>
                                    <td class="px-4 py-3 text-right font-semibold"><?php echo number_format($analysis['rsi'], 0); ?></td>
                                    <td class="px-4 py-3 text-center text-xs"><?php echo $macd_signal; ?></td>
                                    <td class="px-4 py-3 text-right font-bold text-purple"><?php echo $tech_score; ?></td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="px-2 py-1 rounded text-[11px] font-semibold <?php echo $analysis['signal'] === 'BUY' ? 'bg-green-100 text-green-700' : ($analysis['signal'] === 'WATCH' ? 'bg-yellow-100 text-yellow-700' : 'bg-blue-100 text-blue-700'); ?>">
                                            <?php echo $analysis['signal']; ?>
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-xs text-gray-600"><?php echo $action; ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- View: Dividends -->
            <div id="dividends" class="view hidden">
                <div class="mb-6 sm:mb-10">
                    <h1 class="text-[18px] sm:text-[20px] font-medium text-primary tracking-tight">Calendario Dividendi</h1>
                </div>

                <!-- Summary Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 mb-8">
                    <div class="widget-card widget-purple p-6">
                        <div class="text-[11px] text-gray-500 mb-2 uppercase tracking-wider">Totale 2025</div>
                        <div class="text-2xl font-bold text-primary">€<?php echo number_format($metadata['total_dividends'], 2, ',', '.'); ?></div>
                        <div class="text-[11px] text-positive mt-1"><?php echo count($dividends); ?> pagamenti</div>
                    </div>
                    <div class="widget-card p-6">
                        <div class="text-[11px] text-gray-500 mb-2 uppercase tracking-wider">Previsto 6 Mesi</div>
                        <div class="text-xl font-bold text-primary">€542.16</div>
                        <div class="text-[11px] text-gray-500 mt-1">Gen - Giu 2026</div>
                    </div>
                    <div class="widget-card p-6">
                        <div class="text-[11px] text-gray-500 mb-2 uppercase tracking-wider">Yield Medio Portfolio</div>
                        <div class="text-xl font-bold text-primary">2.8%</div>
                        <div class="text-[11px] text-gray-500 mt-1">Annualizzato</div>
                    </div>
                    <div class="widget-card p-6">
                        <div class="text-[11px] text-gray-500 mb-2 uppercase tracking-wider">Prossimo Stacco</div>
                        <div class="text-xl font-bold text-primary">15 Dic</div>
                        <div class="text-[11px] text-gray-500 mt-1">VWCE €30.18</div>
                    </div>
                </div>

                <!-- AI Insights per Dividends -->
                <div class="mb-8">
                    <div class="widget-ai-insight px-6 py-4 transition-all duration-300">
                        <div class="flex items-center gap-2.5 mb-3 pb-2.5 border-b border-blue-200/40">
                            <span class="badge-ai bg-purple text-white text-[9px] font-bold px-1.5 py-0.5 uppercase tracking-wide">AI Insight</span>
                            <span class="text-xs font-semibold text-primary uppercase tracking-wide">Analisi Dividendi</span>
                        </div>
                        <div class="text-[13px] leading-relaxed text-gray-700">
                            <div class="pl-4 relative py-2 border-b border-dashed border-gray-300">
                                <span class="absolute left-0 text-purple font-bold">→</span>
                                Portfolio con yield medio 2.8%, in linea con ETF diversificati. Focus su crescita capitale piuttosto che rendita.
                            </div>
                            <div class="pl-4 relative py-2">
                                <span class="absolute left-0 text-purple font-bold">→</span>
                                Attesi €542 nei prossimi 6 mesi. Concentrazione trimestrale su VWCE e VUSA, annuale su EIMI.
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Calendario Mensile -->
                <div class="mb-8">
                    <div class="widget-card p-6">
                        <div class="flex justify-between items-center mb-5 pb-4 border-b border-gray-200">
                            <span class="text-[11px] font-medium text-gray-600 uppercase tracking-wider">Calendario Prossimi 6 Mesi</span>
                        </div>
                        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                            <?php
                            $months_forecast = [
                                ['month' => 'Dicembre', 'year' => '2025', 'events' => 3, 'amount' => 90.54],
                                ['month' => 'Gennaio', 'year' => '2026', 'events' => 0, 'amount' => 0],
                                ['month' => 'Febbraio', 'year' => '2026', 'events' => 0, 'amount' => 0],
                                ['month' => 'Marzo', 'year' => '2026', 'events' => 3, 'amount' => 90.54],
                                ['month' => 'Aprile', 'year' => '2026', 'events' => 0, 'amount' => 0],
                                ['month' => 'Maggio', 'year' => '2026', 'events' => 0, 'amount' => 0],
                            ];
                            foreach ($months_forecast as $month):
                                $has_events = $month['events'] > 0;
                            ?>
                            <div class="p-4 <?php echo $has_events ? 'bg-purple-50 border-2 border-purple-300' : 'bg-gray-50 border border-gray-200'; ?> rounded">
                                <div class="font-semibold text-primary text-sm"><?php echo $month['month']; ?></div>
                                <div class="text-[10px] text-gray-500 mb-2"><?php echo $month['year']; ?></div>
                                <?php if ($has_events): ?>
                                    <div class="text-xs text-gray-600 mb-1"><?php echo $month['events']; ?> evento/i</div>
                                    <div class="text-sm font-bold text-positive">€<?php echo number_format($month['amount'], 2, ',', '.'); ?></div>
                                <?php else: ?>
                                    <div class="text-xs text-gray-400">Nessun evento</div>
                                <?php endif; ?>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- Asset Distributivi -->
                <div class="mb-8">
                    <div class="widget-card p-6">
                        <div class="flex justify-between items-center mb-5 pb-4 border-b border-gray-200">
                            <span class="text-[11px] font-medium text-gray-600 uppercase tracking-wider">Asset Distributivi</span>
                            <button class="px-3 py-1 bg-purple text-white text-[11px] font-semibold rounded hover:bg-purple-dark transition-colors">
                                <i class="fa-solid fa-download mr-1"></i> Export CSV
                            </button>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead class="bg-gray-50 border-b border-gray-200">
                                    <tr>
                                        <th class="px-4 py-3 text-left font-semibold text-gray-700 text-[11px] uppercase">Ticker</th>
                                        <th class="px-4 py-3 text-left font-semibold text-gray-700 text-[11px] uppercase">Nome</th>
                                        <th class="px-4 py-3 text-right font-semibold text-gray-700 text-[11px] uppercase">Yield %</th>
                                        <th class="px-4 py-3 text-center font-semibold text-gray-700 text-[11px] uppercase">Frequenza</th>
                                        <th class="px-4 py-3 text-right font-semibold text-gray-700 text-[11px] uppercase">Ultimo Pag.</th>
                                        <th class="px-4 py-3 text-right font-semibold text-gray-700 text-[11px] uppercase">Prossimo Stacco</th>
                                        <th class="px-4 py-3 text-right font-semibold text-gray-700 text-[11px] uppercase">Importo Atteso</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="border-b border-gray-200 hover:bg-gray-50">
                                        <td class="px-4 py-3 font-semibold text-purple">VWCE</td>
                                        <td class="px-4 py-3">Vanguard FTSE All-World</td>
                                        <td class="px-4 py-3 text-right font-semibold">2.1%</td>
                                        <td class="px-4 py-3 text-center text-xs">Trimestrale</td>
                                        <td class="px-4 py-3 text-right">17/09/2025</td>
                                        <td class="px-4 py-3 text-right font-medium">15/12/2025</td>
                                        <td class="px-4 py-3 text-right text-positive font-semibold">€30.18</td>
                                    </tr>
                                    <tr class="border-b border-gray-200 hover:bg-gray-50">
                                        <td class="px-4 py-3 font-semibold text-purple">VUSA</td>
                                        <td class="px-4 py-3">Vanguard S&P 500</td>
                                        <td class="px-4 py-3 text-right font-semibold">1.3%</td>
                                        <td class="px-4 py-3 text-center text-xs">Trimestrale</td>
                                        <td class="px-4 py-3 text-right">28/09/2025</td>
                                        <td class="px-4 py-3 text-right font-medium">20/12/2025</td>
                                        <td class="px-4 py-3 text-right text-positive font-semibold">€18.32</td>
                                    </tr>
                                    <tr class="border-b border-gray-200 hover:bg-gray-50">
                                        <td class="px-4 py-3 font-semibold text-purple">EIMI</td>
                                        <td class="px-4 py-3">iShares EM IMI</td>
                                        <td class="px-4 py-3 text-right font-semibold">3.9%</td>
                                        <td class="px-4 py-3 text-center text-xs">Annuale</td>
                                        <td class="px-4 py-3 text-right">12/03/2025</td>
                                        <td class="px-4 py-3 text-right font-medium">11/03/2026</td>
                                        <td class="px-4 py-3 text-right text-positive font-semibold">€42.04</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Dividends History -->
                <div class="mb-8">
                    <div class="widget-card p-6">
                        <div class="flex justify-between items-center mb-5 pb-4 border-b border-gray-200">
                            <span class="text-[11px] font-medium text-gray-600 uppercase tracking-wider">Storico Dividendi Ricevuti 2025</span>
                        </div>
                        <div class="space-y-3">
                            <?php foreach ($dividends as $div): ?>
                            <div class="flex justify-between items-center p-3 bg-gray-50 border border-gray-200 rounded">
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
                </div>

                <!-- Grafici Dividendi -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                    <div class="widget-card widget-purple p-6">
                        <div class="flex justify-between items-center mb-5 pb-4 border-b border-gray-200">
                            <span class="text-[11px] font-medium text-gray-600 uppercase tracking-wider">Dividendi Mensili 2025</span>
                        </div>
                        <div class="relative h-[300px]">
                            <canvas id="dividendsMonthlyChart"></canvas>
                        </div>
                    </div>
                    <div class="widget-card widget-purple p-6">
                        <div class="flex justify-between items-center mb-5 pb-4 border-b border-gray-200">
                            <span class="text-[11px] font-medium text-gray-600 uppercase tracking-wider">Rendita Cumulativa</span>
                        </div>
                        <div class="relative h-[300px]">
                            <canvas id="dividendsCumulativeChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- View: Recommendations -->
            <div id="recommendations" class="view hidden">
                <div class="mb-6 sm:mb-10">
                    <h1 class="text-[18px] sm:text-[20px] font-medium text-primary tracking-tight">Raccomandazioni & Piano Operativo</h1>
                </div>

                <!-- AI Insights per Recommendations -->
                <div class="mb-8">
                    <div class="widget-ai-insight px-6 py-4 transition-all duration-300">
                        <div class="flex items-center gap-2.5 mb-3 pb-2.5 border-b border-blue-200/40">
                            <span class="badge-ai bg-purple text-white text-[9px] font-bold px-1.5 py-0.5 uppercase tracking-wide">AI Insight</span>
                            <span class="text-xs font-semibold text-primary uppercase tracking-wide">Strategia Consigliata</span>
                        </div>
                        <div class="text-[13px] leading-relaxed text-gray-700">
                            <div class="pl-4 relative py-2 border-b border-dashed border-gray-300">
                                <span class="absolute left-0 text-purple font-bold">→</span>
                                Focus su accumulo ETF core (VWCE, VUSA) approfittando dei ribassi. Diversificare su emerging con EIMI.
                            </div>
                            <div class="pl-4 relative py-2">
                                <span class="absolute left-0 text-danger font-bold">→</span>
                                Evitare movimenti impulsivi. Mantenere DCA settimanale e attendere conferme tecniche per incrementi straordinari.
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Accordion Section: Azioni Immediate -->
                <div class="mb-6">
                    <div class="widget-card">
                        <button class="w-full px-6 py-4 flex items-center justify-between border-b border-gray-200 hover:bg-gray-50 transition-colors" onclick="toggleRecommendationAccordion('actionsSection')">
                            <div class="flex items-center gap-3">
                                <i class="fa-solid fa-bolt text-purple"></i>
                                <span class="font-semibold text-primary">Azioni Immediate</span>
                            </div>
                            <i class="fa-solid fa-chevron-down text-gray-400 transition-transform" id="actionsSection-icon"></i>
                        </button>
                        <div class="p-6 hidden" id="actionsSection">
                            <div class="overflow-x-auto">
                                <table class="w-full text-sm">
                                    <thead class="bg-gray-50 border-b border-gray-200">
                                        <tr>
                                            <th class="px-4 py-3 text-left font-semibold text-gray-700 text-[11px] uppercase">Priorità</th>
                                            <th class="px-4 py-3 text-left font-semibold text-gray-700 text-[11px] uppercase">Ticker</th>
                                            <th class="px-4 py-3 text-left font-semibold text-gray-700 text-[11px] uppercase">Azione</th>
                                            <th class="px-4 py-3 text-left font-semibold text-gray-700 text-[11px] uppercase">Motivazione</th>
                                            <th class="px-4 py-3 text-right font-semibold text-gray-700 text-[11px] uppercase">Target</th>
                                            <th class="px-4 py-3 text-right font-semibold text-gray-700 text-[11px] uppercase">Qty</th>
                                            <th class="px-4 py-3 text-right font-semibold text-gray-700 text-[11px] uppercase">Importo</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr class="border-b border-gray-200 hover:bg-gray-50">
                                            <td class="px-4 py-3"><span class="px-2 py-1 bg-red-100 text-red-700 text-xs font-bold rounded">ALTA</span></td>
                                            <td class="px-4 py-3 font-semibold text-purple">VWCE</td>
                                            <td class="px-4 py-3"><span class="px-2 py-1 bg-green-100 text-green-700 text-xs font-semibold rounded">ACCUMULA</span></td>
                                            <td class="px-4 py-3 text-xs">Momentum positivo, RSI 68, EMA rialzista</td>
                                            <td class="px-4 py-3 text-right">€115.80</td>
                                            <td class="px-4 py-3 text-right">2</td>
                                            <td class="px-4 py-3 text-right font-semibold">€231.60</td>
                                        </tr>
                                        <tr class="border-b border-gray-200 hover:bg-gray-50">
                                            <td class="px-4 py-3"><span class="px-2 py-1 bg-yellow-100 text-yellow-700 text-xs font-bold rounded">MEDIA</span></td>
                                            <td class="px-4 py-3 font-semibold text-purple">EIMI</td>
                                            <td class="px-4 py-3"><span class="px-2 py-1 bg-blue-100 text-blue-700 text-xs font-semibold rounded">HOLD</span></td>
                                            <td class="px-4 py-3 text-xs">Consolidamento, attendere breakout €30</td>
                                            <td class="px-4 py-3 text-right">€30.00</td>
                                            <td class="px-4 py-3 text-right">-</td>
                                            <td class="px-4 py-3 text-right font-semibold">-</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Accordion Section: Nuovi ETF Raccomandati -->
                <div class="mb-6">
                    <div class="widget-card">
                        <button class="w-full px-6 py-4 flex items-center justify-between border-b border-gray-200 hover:bg-gray-50 transition-colors" onclick="toggleRecommendationAccordion('newEtfsSection')">
                            <div class="flex items-center gap-3">
                                <i class="fa-solid fa-star text-purple"></i>
                                <span class="font-semibold text-primary">Nuovi ETF Raccomandati</span>
                                <span class="px-2 py-0.5 bg-green-100 text-green-700 text-xs font-semibold rounded">Zero Commissioni Fineco</span>
                            </div>
                            <i class="fa-solid fa-chevron-down text-gray-400 transition-transform" id="newEtfsSection-icon"></i>
                        </button>
                        <div class="p-6 hidden" id="newEtfsSection">
                            <div class="space-y-4">
                                <?php foreach ($opportunities as $opp): ?>
                                <div class="p-4 border border-gray-200 rounded bg-gray-50">
                                    <div class="flex justify-between items-start mb-3">
                                        <div>
                                            <div class="font-bold text-primary text-base"><?php echo htmlspecialchars($opp['ticker']); ?></div>
                                            <div class="text-sm text-gray-600 mt-1"><?php echo htmlspecialchars($opp['name']); ?></div>
                                        </div>
                                        <span class="px-2 py-1 rounded text-[11px] font-semibold <?php echo $opp['signal'] === 'BUY' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700'; ?>">
                                            <?php echo $opp['signal']; ?>
                                        </span>
                                    </div>
                                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-3 text-xs">
                                        <div><span class="text-gray-500">TER:</span> <strong><?php echo number_format($opp['expense_ratio'], 2, ',', '.'); ?>%</strong></div>
                                        <div><span class="text-gray-500">AUM:</span> <strong>€2.5B</strong></div>
                                        <div><span class="text-gray-500">Yield:</span> <strong><?php echo number_format($opp['yield'], 2, ',', '.'); ?>%</strong></div>
                                        <div><span class="text-gray-500">YTD:</span> <strong class="text-positive">+18.5%</strong></div>
                                    </div>
                                    <div class="text-xs text-gray-600 mb-3 p-3 bg-white border-l-2 border-purple">
                                        <i class="fa-solid fa-info-circle text-purple mr-1"></i>
                                        <?php echo htmlspecialchars($opp['reason']); ?>
                                    </div>
                                    <div class="flex gap-4 text-xs">
                                        <div><span class="text-gray-500">Entry Price:</span> <strong>€<?php echo number_format($opp['entry_price'], 2, ',', '.'); ?></strong></div>
                                        <div><span class="text-gray-500">Target:</span> <strong>€<?php echo number_format($opp['target_price'], 2, ',', '.'); ?></strong></div>
                                        <div><span class="text-gray-500">Upside:</span> <strong class="text-positive">+<?php echo number_format((($opp['target_price'] - $opp['entry_price']) / $opp['entry_price']) * 100, 1, ',', '.'); ?>%</strong></div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Accordion Section: Piano Operativo -->
                <div class="mb-6">
                    <div class="widget-card">
                        <button class="w-full px-6 py-4 flex items-center justify-between border-b border-gray-200 hover:bg-gray-50 transition-colors" onclick="toggleRecommendationAccordion('planSection')">
                            <div class="flex items-center gap-3">
                                <i class="fa-solid fa-calendar-days text-purple"></i>
                                <span class="font-semibold text-primary">Piano Operativo Temporale</span>
                            </div>
                            <i class="fa-solid fa-chevron-down text-gray-400 transition-transform" id="planSection-icon"></i>
                        </button>
                        <div class="p-6 hidden" id="planSection">
                            <div class="space-y-4">
                                <div class="p-4 border-l-4 border-purple bg-purple-50">
                                    <div class="font-semibold text-primary mb-2">Settimana 1-2: Fase Accumulo</div>
                                    <ul class="text-xs text-gray-700 space-y-1 ml-4">
                                        <li>• VWCE: incremento 2 quote se price ≤ €115.80</li>
                                        <li>• VUSA: monitorare per entry point sotto €110</li>
                                        <li>• Mantenere liquidità 15% per opportunità</li>
                                    </ul>
                                </div>
                                <div class="p-4 border-l-4 border-gray-400 bg-gray-50">
                                    <div class="font-semibold text-primary mb-2">Settimana 3-4: Fase Consolidamento</div>
                                    <ul class="text-xs text-gray-700 space-y-1 ml-4">
                                        <li>• EIMI: attendere breakout €30 per accumulo</li>
                                        <li>• Verificare stacco dividendi VWCE (15 Dic)</li>
                                        <li>• Valutare ribilanciamento se drift >5%</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Accordion Section: Avvertenze -->
                <div class="mb-6">
                    <div class="widget-card">
                        <button class="w-full px-6 py-4 flex items-center justify-between border-b border-gray-200 hover:bg-gray-50 transition-colors" onclick="toggleRecommendationAccordion('warningsSection')">
                            <div class="flex items-center gap-3">
                                <i class="fa-solid fa-triangle-exclamation text-danger"></i>
                                <span class="font-semibold text-primary">Avvertenze e Rischi</span>
                            </div>
                            <i class="fa-solid fa-chevron-down text-gray-400 transition-transform" id="warningsSection-icon"></i>
                        </button>
                        <div class="p-6 hidden" id="warningsSection">
                            <div class="space-y-4">
                                <div class="p-4 bg-red-50 border border-red-200 rounded">
                                    <div class="font-semibold text-danger mb-2">Rischi Macro</div>
                                    <p class="text-xs text-gray-700">Volatilità attesa per dicembre legata a decisioni Fed e BCE. Possibili correzioni 3-5% su equity globale.</p>
                                </div>
                                <div class="p-4 bg-yellow-50 border border-yellow-200 rounded">
                                    <div class="font-semibold text-warning mb-2">Rischi Portfolio</div>
                                    <p class="text-xs text-gray-700">Concentrazione su equity globale (87%). Considerare diversificazione su bond o commodities se risk appetite diminuisce.</p>
                                </div>
                                <div class="p-4 bg-blue-50 border border-blue-200 rounded">
                                    <div class="font-semibold text-primary mb-2">Note Operative</div>
                                    <p class="text-xs text-gray-700">DCA settimanale consigliato. Evitare lump sum oltre €500. Mantenere sempre 10-15% liquidità per opportunità.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- View: Flussi & Guadagni -->
            <div id="flows" class="view hidden">
                <div class="mb-6 sm:mb-10">
                    <h1 class="text-[18px] sm:text-[20px] font-medium text-primary tracking-tight">Flussi & Guadagni Progressivi</h1>
                </div>

                <!-- Info Metrics -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 mb-8">
                    <div class="widget-card p-6">
                        <div class="text-[11px] text-gray-500 mb-2 uppercase tracking-wider">Timeline Start</div>
                        <div class="text-xl font-bold text-primary">14 Nov 2025</div>
                        <div class="text-[11px] text-gray-500 mt-1">Primo utilizzo</div>
                    </div>
                    <div class="widget-card p-6">
                        <div class="text-[11px] text-gray-500 mb-2 uppercase tracking-wider">Days Tracked</div>
                        <div class="text-xl font-bold text-primary">5 giorni</div>
                        <div class="text-[11px] text-gray-500 mt-1">5 snapshot</div>
                    </div>
                    <div class="widget-card p-6">
                        <div class="text-[11px] text-gray-500 mb-2 uppercase tracking-wider">Last Update</div>
                        <div class="text-xl font-bold text-primary"><?php echo date('d M Y', strtotime($metadata['last_update'])); ?></div>
                        <div class="text-[11px] text-gray-500 mt-1"><?php echo date('H:i', strtotime($metadata['last_update'])); ?> CET</div>
                    </div>
                    <div class="widget-card p-6">
                        <div class="text-[11px] text-gray-500 mb-2 uppercase tracking-wider">Update Frequency</div>
                        <div class="text-xl font-bold text-primary">Settimanale</div>
                        <div class="text-[11px] text-gray-500 mt-1">Giovedi o manuale</div>
                    </div>
                </div>

                <!-- AI Insights per Flows -->
                <div class="mb-8">
                    <div class="widget-ai-insight px-6 py-4 transition-all duration-300">
                        <div class="flex items-center gap-2.5 mb-3 pb-2.5 border-b border-blue-200/40">
                            <span class="badge-ai bg-purple text-white text-[9px] font-bold px-1.5 py-0.5 uppercase tracking-wide">AI Insight</span>
                            <span class="text-xs font-semibold text-primary uppercase tracking-wide">Analisi Flussi</span>
                        </div>
                        <div class="text-[13px] leading-relaxed text-gray-700">
                            <div class="pl-4 relative py-2 border-b border-dashed border-gray-300">
                                <span class="absolute left-0 text-purple font-bold">→</span>
                                Portfolio in crescita costante con +<?php echo number_format($metadata['unrealized_pnl_pct'], 2, ',', '.'); ?>% da inizio tracking.
                            </div>
                            <div class="pl-4 relative py-2">
                                <span class="absolute left-0 text-purple font-bold">→</span>
                                Gain open €<?php echo number_format($metadata['unrealized_pnl'], 2, ',', '.'); ?> su posizioni attive. Nessuna posizione chiusa registrata.
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabella Storica -->
                <div class="mb-8 widget-card p-6">
                    <div class="flex justify-between items-center mb-5 pb-4 border-b border-gray-200">
                        <span class="text-[11px] font-medium text-gray-600 uppercase tracking-wider">Tabella Storica Progressiva</span>
                        <button class="px-3 py-1 bg-purple text-white text-[11px] font-semibold rounded hover:bg-purple-dark transition-colors">
                            <i class="fa-solid fa-download mr-1"></i> Export CSV
                        </button>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50 border-b border-gray-200">
                                <tr>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-700 text-[11px] uppercase">Data</th>
                                    <th class="px-4 py-3 text-right font-semibold text-gray-700 text-[11px] uppercase">Valore Portfolio</th>
                                    <th class="px-4 py-3 text-right font-semibold text-gray-700 text-[11px] uppercase">Guadagno Cumulativo</th>
                                    <th class="px-4 py-3 text-right font-semibold text-gray-700 text-[11px] uppercase">Guadagno %</th>
                                    <th class="px-4 py-3 text-right font-semibold text-gray-700 text-[11px] uppercase">Posizioni Aperte</th>
                                    <th class="px-4 py-3 text-right font-semibold text-gray-700 text-[11px] uppercase">Day Change</th>
                                    <th class="px-4 py-3 text-right font-semibold text-gray-700 text-[11px] uppercase">Day Change %</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                // Dati storici simulati - sostituire con dati reali dal database
                                $history_data = [
                                    ['date' => '14/11/2025', 'value' => 100000, 'cumul_gain' => 0, 'gain_pct' => 0, 'open_pos' => 5, 'day_change' => 0, 'day_pct' => 0],
                                    ['date' => '17/11/2025', 'value' => 102500, 'cumul_gain' => 2500, 'gain_pct' => 2.5, 'open_pos' => 5, 'day_change' => 2500, 'day_pct' => 2.5],
                                    ['date' => '20/11/2025', 'value' => 110000, 'cumul_gain' => 10000, 'gain_pct' => 10.0, 'open_pos' => 5, 'day_change' => 7500, 'day_pct' => 7.32],
                                    ['date' => '23/11/2025', 'value' => 118500, 'cumul_gain' => 18500, 'gain_pct' => 18.5, 'open_pos' => 5, 'day_change' => 8500, 'day_pct' => 7.73],
                                    ['date' => '24/11/2025', 'value' => 125750.50, 'cumul_gain' => 25750.50, 'gain_pct' => 25.75, 'open_pos' => 5, 'day_change' => 7250.50, 'day_pct' => 6.12],
                                ];
                                foreach ($history_data as $row):
                                    $day_is_positive = $row['day_change'] >= 0;
                                ?>
                                <tr class="border-b border-gray-200 hover:bg-gray-50">
                                    <td class="px-4 py-3 font-medium"><?php echo $row['date']; ?></td>
                                    <td class="px-4 py-3 text-right font-semibold">€<?php echo number_format($row['value'], 2, ',', '.'); ?></td>
                                    <td class="px-4 py-3 text-right text-positive font-semibold">€<?php echo number_format($row['cumul_gain'], 2, ',', '.'); ?></td>
                                    <td class="px-4 py-3 text-right text-positive font-semibold">+<?php echo number_format($row['gain_pct'], 2, ',', '.'); ?>%</td>
                                    <td class="px-4 py-3 text-right"><?php echo $row['open_pos']; ?></td>
                                    <td class="px-4 py-3 text-right <?php echo $day_is_positive ? 'text-positive' : 'text-negative'; ?> font-semibold">
                                        <?php echo $day_is_positive ? '+' : ''; ?>€<?php echo number_format($row['day_change'], 2, ',', '.'); ?>
                                    </td>
                                    <td class="px-4 py-3 text-right <?php echo $day_is_positive ? 'text-positive' : 'text-negative'; ?> font-semibold">
                                        <?php echo $day_is_positive ? '+' : ''; ?><?php echo number_format($row['day_pct'], 2, ',', '.'); ?>%
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Grafici -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                    <div class="widget-card widget-purple p-6">
                        <div class="flex justify-between items-center mb-5 pb-4 border-b border-gray-200">
                            <span class="text-[11px] font-medium text-gray-600 uppercase tracking-wider">Guadagno Cumulativo nel Tempo</span>
                        </div>
                        <div class="relative h-[300px]">
                            <canvas id="cumulativeGainChart"></canvas>
                        </div>
                    </div>
                    <div class="widget-card widget-purple p-6">
                        <div class="flex justify-between items-center mb-5 pb-4 border-b border-gray-200">
                            <span class="text-[11px] font-medium text-gray-600 uppercase tracking-wider">Valore Portfolio nel Tempo</span>
                        </div>
                        <div class="relative h-[300px]">
                            <canvas id="valueOverTimeChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Statistiche Performance -->
                <div class="mb-8">
                    <div class="text-[11px] font-medium text-gray-600 uppercase tracking-wider mb-4">Statistiche Performance</div>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
                        <div class="widget-card widget-purple p-6">
                            <div class="text-[11px] text-gray-500 mb-2 uppercase tracking-wider">Performance Since Start</div>
                            <div class="text-2xl font-bold text-positive">€25,750.50</div>
                            <div class="text-[11px] text-positive mt-1">+25.75%</div>
                        </div>
                        <div class="widget-card p-6">
                            <div class="text-[11px] text-gray-500 mb-2 uppercase tracking-wider">Best Week</div>
                            <div class="text-xl font-bold text-primary">€8,500.00</div>
                            <div class="text-[11px] text-gray-500 mt-1">23 Nov 2025</div>
                        </div>
                        <div class="widget-card p-6">
                            <div class="text-[11px] text-gray-500 mb-2 uppercase tracking-wider">Worst Week</div>
                            <div class="text-xl font-bold text-primary">€2,500.00</div>
                            <div class="text-[11px] text-gray-500 mt-1">17 Nov 2025</div>
                        </div>
                        <div class="widget-card p-6">
                            <div class="text-[11px] text-gray-500 mb-2 uppercase tracking-wider">Average Weekly Change</div>
                            <div class="text-xl font-bold text-primary">€6,437.63</div>
                            <div class="text-[11px] text-gray-500 mt-1">Volatility: 3.12%</div>
                        </div>
                    </div>
                </div>
            </div>

    </div>
</div>

<script>
        // Performance Chart
        const performanceCtxEl = document.getElementById('performanceChart');
        if (performanceCtxEl) {
            const performanceCtx = performanceCtxEl.getContext('2d');
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
        }

        // Allocation Chart
        const allocationCtxEl = document.getElementById('allocationChart');
        if (allocationCtxEl) {
            const allocationCtx = allocationCtxEl.getContext('2d');
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
        }
    </script>
    <script src="/assets/js/app.js?v=<?php echo time(); ?>"></script>
</body>
</html>
