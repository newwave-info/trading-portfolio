            <div id="performance" class="view hidden">
                <div class="mb-6 sm:mb-10">
                    <h1 class="text-[18px] sm:text-[20px] font-medium text-primary tracking-tight">Performance & Flussi Progressivi</h1>
                </div>

                <!-- Performance Metrics -->
                <?php
                // Calcola metriche da snapshots
                $perf_1w = ["pct" => "-", "value" => "-", "raw_pct" => null];
                $perf_1m = ["pct" => "-", "value" => "-", "raw_pct" => null];
                $perf_3m = ["pct" => "-", "value" => "-", "raw_pct" => null];

                // Snapshots dal DB (YTD) caricati in data/portfolio_data.php
                $snapshots = $snapshots ?? [];
                $monthly_performance = $monthly_performance ?? [];

                if (!empty($snapshots)) {
                    // Ordina per data crescente per sicurezza
                    usort(
                        $snapshots,
                        fn($a, $b) => strcmp(
                            $a["snapshot_date"],
                            $b["snapshot_date"]
                        )
                    );

                    $first = $snapshots[0];
                    $current = end($snapshots);
                    $currentValue = (float) $current["total_market_value"];

                    // Helper per formattazione
                    $formatPerf = function ($fromValue) use ($currentValue) {
                        $change = $currentValue - $fromValue;
                        $pct =
                            $fromValue > 0 ? ($change / $fromValue) * 100 : 0;
                        return [
                            "pct" =>
                                ($pct >= 0 ? "+" : "") .
                                number_format($pct, 2, ",", ".") .
                                "%",
                            "value" =>
                                "€" .
                                number_format($fromValue / 1000, 1, ",", ".") .
                                "k",
                            "raw_pct" => $pct,
                        ];
                    };

                    // Ultimi 7 giorni (7 giorni fa o primo snapshot)
                    $oneWeekAgo = date("Y-m-d", strtotime("-7 days"));
                    $snap1w = null;
                    foreach ($snapshots as $s) {
                        if ($s["snapshot_date"] >= $oneWeekAgo) {
                            $snap1w = $s;
                            break;
                        }
                    }
                    if (!$snap1w) {
                        $snap1w = $first;
                    }
                    if ($snap1w) {
                        $value1w = (float) $snap1w["total_market_value"];
                        $perf_1w = $formatPerf($value1w);
                    }

                    // Ultimi 30 giorni (30 giorni fa o primo snapshot)
                    $oneMonthAgo = date("Y-m-d", strtotime("-30 days"));
                    $snap1m = null;
                    foreach ($snapshots as $s) {
                        if ($s["snapshot_date"] >= $oneMonthAgo) {
                            $snap1m = $s;
                            break;
                        }
                    }
                    if (!$snap1m) {
                        $snap1m = $first;
                    }
                    if ($snap1m) {
                        $value1m = (float) $snap1m["total_market_value"];
                        $perf_1m = $formatPerf($value1m);
                    }

                    // Ultimi 90 giorni (90 giorni fa o primo snapshot)
                    $threeMonthsAgo = date("Y-m-d", strtotime("-90 days"));
                    $snap3m = null;
                    foreach ($snapshots as $s) {
                        if ($s["snapshot_date"] >= $threeMonthsAgo) {
                            $snap3m = $s;
                            break;
                        }
                    }
                    if (!$snap3m) {
                        $snap3m = $first;
                    }
                    if ($snap3m) {
                        $value3m = (float) $snap3m["total_market_value"];
                        $perf_3m = $formatPerf($value3m);
                    }
                }

                // Fallback/override usando monthly_performance se i pct sono null
                $computeFromMonthly = function (int $monthsBack) use (
                    $monthly_performance
                ) {
                    $mp = $monthly_performance;
                    if (empty($mp)) {
                        return null;
                    }
                    // Mantieni ordine cronologico (assumiamo già ordinato, altrimenti ordina per month index se disponibile)
                    $count = count($mp);
                    $currentIdx = $count - 1;
                    $prevIdx = $currentIdx - $monthsBack;
                    if ($prevIdx < 0) {
                        return null;
                    }
                    $currVal = (float) ($mp[$currentIdx]["value"] ?? 0);
                    $prevVal = (float) ($mp[$prevIdx]["value"] ?? 0);
                    if ($prevVal <= 0) {
                        return null;
                    }
                    $pct = (($currVal - $prevVal) / $prevVal) * 100;
                    return [
                        "pct" =>
                            ($pct >= 0 ? "+" : "") .
                            number_format($pct, 2, ",", ".") .
                            "%",
                        "value" =>
                            "€" .
                            number_format($prevVal / 1000, 1, ",", ".") .
                            "k",
                        "raw_pct" => $pct,
                    ];
                };

                // Se non abbiamo pct utili dagli snapshot, prova a calcolare dai dati mensili
                if (
                    $perf_1m["raw_pct"] === null &&
                    ($fallback = $computeFromMonthly(1))
                ) {
                    $perf_1m = $fallback;
                }
                if (
                    $perf_3m["raw_pct"] === null &&
                    ($fallback = $computeFromMonthly(3))
                ) {
                    $perf_3m = $fallback;
                }
                ?>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6 mb-4">
                    <div class="widget-card widget-purple p-6">
                        <div class="flex items-center gap-2 mb-3">
                            <i class="fa-solid fa-bolt text-purple"></i>
                            <span class="text-[11px] font-medium text-gray-600 uppercase tracking-wider">Ultimi 7 Giorni</span>
                        </div>
                        <div class="text-2xl font-bold text-positive mb-1"><?php echo $perf_1w[
                            "pct"
                        ]; ?></div>
                        <div class="text-[11px] text-gray-500">vs <?php echo $perf_1w[
                            "value"
                        ]; ?></div>
                    </div>

                    <div class="widget-card widget-purple p-6">
                        <div class="flex items-center gap-2 mb-3">
                            <i class="fa-solid fa-chart-area text-purple"></i>
                            <span class="text-[11px] font-medium text-gray-600 uppercase tracking-wider">Ultimi 30 Giorni</span>
                        </div>
                        <div class="text-2xl font-bold text-positive mb-1"><?php echo $perf_1m[
                            "pct"
                        ]; ?></div>
                        <div class="text-[11px] text-gray-500">vs <?php echo $perf_1m[
                            "value"
                        ]; ?></div>
                    </div>

                    <div class="widget-card widget-purple p-6">
                        <div class="flex items-center gap-2 mb-3">
                            <i class="fa-solid fa-arrow-trend-up text-purple"></i>
                            <span class="text-[11px] font-medium text-gray-600 uppercase tracking-wider">Ultimi 90 Giorni</span>
                        </div>
                        <div class="text-2xl font-bold text-positive mb-1"><?php echo $perf_3m[
                            "pct"
                        ]; ?></div>
                        <div class="text-[11px] text-gray-500">vs <?php echo $perf_3m[
                            "value"
                        ]; ?></div>
                    </div>
                </div>

                <!-- Grafici Performance & Flussi -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
                    <div class="widget-card widget-purple p-6">
                        <div class="flex justify-between items-center mb-5 pb-4 border-b border-gray-200">
                            <div class="flex items-center gap-2">
                                <i class="fa-solid fa-calendar-days text-purple text-sm"></i>
                                <span class="text-[11px] font-medium text-gray-600 uppercase tracking-wider">Ultimi 7 Giorni</span>
                            </div>
                        </div>
                        <div class="relative h-[250px]">
                            <canvas id="valueOverTimeChart"></canvas>
                        </div>
                    </div>

                    <div class="widget-card widget-purple p-6">
                        <div class="flex justify-between items-center mb-5 pb-4 border-b border-gray-200">
                            <div class="flex items-center gap-2">
                                <i class="fa-solid fa-sack-dollar text-purple text-sm"></i>
                                <span class="text-[11px] font-medium text-gray-600 uppercase tracking-wider">Andamento Ultimi 30 Giorni</span>
                            </div>
                        </div>
                        <div class="relative h-[250px]">
                            <canvas id="cumulativeGainChart"></canvas>
                        </div>
                    </div>

                    <div class="widget-card widget-purple p-6">
                        <div class="flex justify-between items-center mb-5 pb-4 border-b border-gray-200">
                            <div class="flex items-center gap-2">
                                <i class="fa-solid fa-chart-area text-purple text-sm"></i>
                                <span class="text-[11px] font-medium text-gray-600 uppercase tracking-wider">Andamento Ultimi 90 Giorni</span>
                            </div>
                        </div>
                        <div class="relative h-[250px]">
                            <canvas id="performanceDetailChart"></canvas>
                        </div>
                    </div>
                </div>
                
                <!-- Tabella Storica Progressiva -->
                                <div class="mb-8 widget-card p-6">
                                    <div class="flex justify-between items-center mb-5 pb-4 border-b border-gray-200">
                                        <div class="flex items-center gap-2">
                                            <i class="fa-solid fa-table text-purple text-sm"></i>
                                            <span class="text-[11px] font-medium text-gray-600 uppercase tracking-wider">Storico Performance Giornaliero</span>
                                        </div>
                                    </div>
                                    <div class="overflow-x-auto">
                                        <table class="w-full text-sm sortable-table">
                                            <thead class="bg-gray-50 border-b border-gray-200">
                                                <tr>
                                                    <th class="px-4 py-3 text-left font-semibold text-gray-700 text-[11px] uppercase cursor-pointer" role="button">Data</th>
                                                    <th class="px-4 py-3 text-right font-semibold text-gray-700 text-[11px] uppercase cursor-pointer" role="button">Valore Portafoglio</th>
                                                    <th class="px-4 py-3 text-right font-semibold text-gray-700 text-[11px] uppercase cursor-pointer" role="button">Guadagno Cumulativo</th>
                                                    <th class="px-4 py-3 text-right font-semibold text-gray-700 text-[11px] uppercase cursor-pointer" role="button">Guadagno %</th>
                                                    <th class="px-4 py-3 text-right font-semibold text-gray-700 text-[11px] uppercase cursor-pointer" role="button">Posizioni Aperte</th>
                                                    <th class="px-4 py-3 text-right font-semibold text-gray-700 text-[11px] uppercase cursor-pointer" role="button">Day Change</th>
                                                    <th class="px-4 py-3 text-right font-semibold text-gray-700 text-[11px] uppercase cursor-pointer" role="button">Day Change %</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                // Carica storico performance dagli snapshot DB (ultimi 90)
                                                $history_data = [];
                                                $snapshotsDb = $snapshots ?? [];

                                                if (!empty($snapshotsDb)) {
                                                    // Ordina per data e prendi ultimi 90
                                                    usort(
                                                        $snapshotsDb,
                                                        fn($a, $b) => strcmp(
                                                            $a["snapshot_date"],
                                                            $b["snapshot_date"]
                                                        )
                                                    );
                                                    $snapshotsDb = array_slice(
                                                        $snapshotsDb,
                                                        -90
                                                    );

                                                    $prevValue = null;
                                                    $prevInvested =
                                                        $metadata[
                                                            "total_invested"
                                                        ];

                                                    foreach (
                                                        $snapshotsDb
                                                        as $snap
                                                    ) {
                                                        $value =
                                                            (float) $snap[
                                                                "total_market_value"
                                                            ];
                                                        $invested =
                                                            (float) ($snap[
                                                                "total_invested"
                                                            ] ?? $prevInvested);
                                                        $cumul_gain =
                                                            $value - $invested;
                                                        $gain_pct =
                                                            $invested > 0
                                                                ? ($cumul_gain /
                                                                        $invested) *
                                                                    100
                                                                : 0;

                                                        $day_change =
                                                            $prevValue !== null
                                                                ? $value -
                                                                    $prevValue
                                                                : 0;
                                                        $day_pct =
                                                            $prevValue !==
                                                                null &&
                                                            $prevValue > 0
                                                                ? ($day_change /
                                                                        $prevValue) *
                                                                    100
                                                                : 0;

                                                        $history_data[] = [
                                                            "date" => date(
                                                                "d/m/Y",
                                                                strtotime(
                                                                    $snap[
                                                                        "snapshot_date"
                                                                    ]
                                                                )
                                                            ),
                                                            "value" => $value,
                                                            "cumul_gain" => $cumul_gain,
                                                            "gain_pct" => $gain_pct,
                                                            "open_pos" =>
                                                                $snap[
                                                                    "total_holdings"
                                                                ] ??
                                                                ($metadata[
                                                                    "total_holdings"
                                                                ] ??
                                                                    count(
                                                                        $top_holdings
                                                                    )),
                                                            "day_change" => $day_change,
                                                            "day_pct" => $day_pct,
                                                            "raw_date" =>
                                                                $snap[
                                                                    "snapshot_date"
                                                                ],
                                                        ];

                                                        $prevValue = $value;
                                                        $prevInvested = $invested;
                                                    }
                                                }

                                                // Se nessun snapshot, mostra riga vuota
                                                if (empty($history_data)) {
                                                    echo '<tr><td colspan="7" class="px-4 py-8 text-center text-gray-500">Nessuno storico disponibile. Crea snapshot per visualizzare i dati.</td></tr>';
                                                } else {
                                                    foreach (
                                                        $history_data
                                                        as $row
                                                    ):
                                                        $day_is_positive =
                                                            $row[
                                                                "day_change"
                                                            ] >= 0; ?>
                                                <tr class="border-b border-gray-200 hover:bg-gray-50">
                                                    <td class="px-4 py-3 font-medium"><?php echo $row[
                                                        "date"
                                                    ]; ?></td>
                                                    <td class="px-4 py-3 text-right font-semibold">€<?php echo number_format(
                                                        $row["value"],
                                                        2,
                                                        ",",
                                                        "."
                                                    ); ?></td>
                                                    <td class="px-4 py-3 text-right text-positive font-semibold">€<?php echo number_format(
                                                        $row["cumul_gain"],
                                                        2,
                                                        ",",
                                                        "."
                                                    ); ?></td>
                                                    <td class="px-4 py-3 text-right text-positive font-semibold">+<?php echo number_format(
                                                        $row["gain_pct"],
                                                        2,
                                                        ",",
                                                        "."
                                                    ); ?>%</td>
                                                    <td class="px-4 py-3 text-right"><?php echo $row[
                                                        "open_pos"
                                                    ]; ?></td>
                                                    <td class="px-4 py-3 text-right <?php echo $day_is_positive
                                                        ? "text-positive"
                                                        : "text-negative"; ?> font-semibold">
                                                        <?php echo $day_is_positive
                                                            ? "+"
                                                            : ""; ?>€<?php echo number_format(
    $row["day_change"],
    2,
    ",",
    "."
); ?>
                                                    </td>
                                                    <td class="px-4 py-3 text-right <?php echo $day_is_positive
                                                        ? "text-positive"
                                                        : "text-negative"; ?> font-semibold">
                                                        <?php
                                                        echo $day_is_positive
                                                            ? "+"
                                                            : "";
                                                        echo number_format(
                                                            $row["day_pct"],
                                                            2,
                                                            ",",
                                                            "."
                                                        );
                                                        ?>%
                                                    </td>
                                                </tr>
                                                <?php
                                                    endforeach;
                                                }

// end if empty
?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                

                <!-- Charts Scripts with Dynamic Data -->
                <script>
                <?php
                // Prepara dati per i grafici da snapshots (per valore e %)
                $chart_labels_7d = $chart_values_7d = $chart_gain_pct_7d = [];
                $chart_labels_30d = $chart_values_30d = $chart_gain_pct_30d = [];
                $chart_labels_90d = $chart_values_90d = $chart_gain_pct_90d = [];

                if (!empty($history_data)) {
                    $date7 = date("Y-m-d", strtotime("-7 days"));
                    $date30 = date("Y-m-d", strtotime("-30 days"));
                    $date90 = date("Y-m-d", strtotime("-90 days"));

                    $hist7 = array_values(
                        array_filter(
                            $history_data,
                            fn($row) => ($row["raw_date"] ?? "") >= $date7
                        )
                    );
                    $hist30 = array_values(
                        array_filter(
                            $history_data,
                            fn($row) => ($row["raw_date"] ?? "") >= $date30
                        )
                    );
                    $hist90 = array_values(
                        array_filter(
                            $history_data,
                            fn($row) => ($row["raw_date"] ?? "") >= $date90
                        )
                    );

                    $chart_labels_7d = array_column($hist7, "date");
                    $chart_values_7d = array_column($hist7, "value");
                    $chart_gain_pct_7d = array_column($hist7, "gain_pct");

                    $chart_labels_30d = array_column($hist30, "date");
                    $chart_values_30d = array_column($hist30, "value");
                    $chart_gain_pct_30d = array_column($hist30, "gain_pct");

                    $chart_labels_90d = array_column($hist90, "date");
                    $chart_values_90d = array_column($hist90, "value");
                    $chart_gain_pct_90d = array_column($hist90, "gain_pct");
                }
                ?>

                // Wrap chart initialization in a function to be called when view becomes visible
                function initializePerformanceCharts() {
                    console.log('🎯 initializePerformanceCharts() called');

                    // Check if already initialized
                    if (window.performanceChartsInitialized) {
                        console.log('⚠️ Performance charts already initialized, skipping');
                        return;
                    }

                    console.log('📊 Starting chart initialization with ChartManager...');

                // Performance Detail Chart (Ultimi 90 giorni) - valore + %
                if (document.getElementById('performanceDetailChart')) {
                    try {
                        window.ChartManager.createPerformanceDetailChart(
                            'performanceDetailChart',
                            <?php echo json_encode($chart_labels_90d); ?>,
                            <?php echo json_encode($chart_values_90d); ?>,
                            <?php echo json_encode($chart_gain_pct_90d); ?>
                        );
                        initializedCharts.add('performanceDetailChart');
                        console.log('✅ Performance Detail Chart created');
                    } catch (error) {
                        console.error('Errore inizializzazione Performance Detail Chart:', error);
                    }
                }

                // Andamento 30 giorni - valore + %
                if (document.getElementById('cumulativeGainChart')) {
                    try {
                        window.ChartManager.createCumulativeGainChart(
                            'cumulativeGainChart',
                            <?php echo json_encode($chart_labels_30d); ?>,
                            <?php echo json_encode($chart_values_30d); ?>,
                            <?php echo json_encode($chart_gain_pct_30d); ?>
                        );
                        initializedCharts.add('cumulativeGainChart');
                        console.log('✅ Cumulative Gain Chart created');
                    } catch (error) {
                        console.error('Errore inizializzazione Cumulative Gain Chart:', error);
                    }
                }

                // Value Over Time Chart (Ultimi 7 giorni) - valore + %
                if (document.getElementById('valueOverTimeChart')) {
                    try {
                        window.ChartManager.createValueOverTimeChart(
                            'valueOverTimeChart',
                            <?php echo json_encode($chart_labels_7d); ?>,
                            <?php echo json_encode($chart_values_7d); ?>,
                            <?php echo json_encode($chart_gain_pct_7d); ?>
                        );
                        initializedCharts.add('valueOverTimeChart');
                        console.log('✅ Value Over Time Chart created');
                    } catch (error) {
                        console.error('Errore inizializzazione Value Over Time Chart:', error);
                    }
                }

                // Mark charts as initialized
                window.performanceChartsInitialized = true;
                console.log('✅ Performance charts initialized successfully');
            }

            // Initialize charts when performance view becomes visible
            // Observer to detect when #performance div becomes visible
            const performanceView = document.getElementById('performance');
            if (performanceView) {
                const observer = new MutationObserver((mutations) => {
                    mutations.forEach((mutation) => {
                        if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
                            const isVisible = !performanceView.classList.contains('hidden');
                            if (isVisible && !window.performanceChartsInitialized) {
                                console.log('👁️ Performance view is now visible, initializing charts...');
                                // Small delay to ensure DOM is fully rendered
                                setTimeout(() => {
                                    initializePerformanceCharts();
                                }, 100);
                            }
                        }
                    });
                });

                observer.observe(performanceView, {
                    attributes: true,
                    attributeFilter: ['class']
                });

                // Also try immediate initialization if view is already visible
                if (!performanceView.classList.contains('hidden')) {
                    console.log('👁️ Performance view already visible, initializing charts immediately...');
                    setTimeout(() => {
                        initializePerformanceCharts();
                    }, 100);
                }
            }
            </script>
            </div>

            <!-- View: Technical Analysis -->
