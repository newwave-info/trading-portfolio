            <div id="performance" class="view hidden">
                <div class="mb-6 sm:mb-10">
                    <h1 class="text-[18px] sm:text-[20px] font-medium text-primary tracking-tight">Performance & Flussi Progressivi</h1>
                </div>

                <!-- Performance Metrics -->
                <?php
                // Calcola metriche da snapshots
                $perf_1m = ["pct" => "-", "value" => "-"];
                $perf_3m = ["pct" => "-", "value" => "-"];
                $perf_ytd = ["pct" => "-", "value" => "-"];

                // Snapshots dal DB (YTD) caricati in data/portfolio_data.php
                $snapshots = $snapshots ?? [];

                if (!empty($snapshots)) {
                    // Ordina per data crescente per sicurezza
                    usort($snapshots, fn($a, $b) => strcmp($a["snapshot_date"], $b["snapshot_date"]));

                    $first = $snapshots[0];
                    $current = end($snapshots);
                    $currentValue = (float) $current["total_market_value"];

                    // Helper per formattazione
                    $formatPerf = function($fromValue) use ($currentValue) {
                        $change = $currentValue - $fromValue;
                        $pct = $fromValue > 0 ? ($change / $fromValue) * 100 : 0;
                        return [
                            "pct" => ($pct >= 0 ? "+" : "") . number_format($pct, 2, ",", ".") . "%",
                            "value" => "€" . number_format($fromValue / 1000, 1, ",", ".") . "k",
                        ];
                    };

                    // 1 Mese (30 giorni fa o primo snapshot)
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

                    // 3 Mesi (90 giorni fa o primo snapshot)
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

                    // YTD (inizio anno o primo snapshot)
                    $ytdStart = date("Y") . "-01-01";
                    $snapYtd = null;
                    foreach ($snapshots as $s) {
                        if ($s["snapshot_date"] >= $ytdStart) {
                            $snapYtd = $s;
                            break;
                        }
                    }
                    if (!$snapYtd) {
                        $snapYtd = $first;
                    }
                    if ($snapYtd) {
                        $valueYtd = (float) $snapYtd["total_market_value"];
                        $perf_ytd = $formatPerf($valueYtd);
                    }
                }
                ?>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 mb-8">
                    <div class="widget-card widget-purple p-6">
                        <div class="flex items-center gap-2 mb-3">
                            <i class="fa-solid fa-chart-line text-purple"></i>
                            <span class="text-[11px] font-medium text-gray-600 uppercase tracking-wider">1 Mese</span>
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
                            <i class="fa-solid fa-chart-area text-purple"></i>
                            <span class="text-[11px] font-medium text-gray-600 uppercase tracking-wider">3 Mesi</span>
                        </div>
                        <div class="text-2xl font-bold text-positive mb-1"><?php echo $perf_3m[
                            "pct"
                        ]; ?></div>
                        <div class="text-[11px] text-gray-500">vs <?php echo $perf_3m[
                            "value"
                        ]; ?></div>
                    </div>

                    <div class="widget-card widget-purple p-6">
                        <div class="flex items-center gap-2 mb-3">
                            <i class="fa-solid fa-arrow-trend-up text-purple"></i>
                            <span class="text-[11px] font-medium text-gray-600 uppercase tracking-wider">YTD</span>
                        </div>
                        <div class="text-2xl font-bold text-positive mb-1"><?php echo $perf_ytd[
                            "pct"
                        ]; ?></div>
                        <div class="text-[11px] text-gray-500">vs <?php echo $perf_ytd[
                            "value"
                        ]; ?></div>
                    </div>

                    <div class="widget-card widget-purple p-6">
                        <div class="flex items-center gap-2 mb-3">
                            <i class="fa-solid fa-trophy text-purple"></i>
                            <span class="text-[11px] font-medium text-gray-600 uppercase tracking-wider">Best Performer</span>
                        </div>
                        <div class="text-xl font-bold text-primary mb-1"><?php echo $best_performer
                            ? htmlspecialchars($best_performer["ticker"])
                            : "-"; ?></div>
                        <div class="text-[11px] text-positive"><?php echo $best_performer
                            ? "+" .
                                number_format(
                                    $best_performer["pnl_percentage"],
                                    2,
                                    ",",
                                    "."
                                ) .
                                "%"
                            : "-"; ?></div>
                    </div>
                </div>

                <!-- Grafici Performance & Flussi -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
                    <div class="widget-card widget-purple p-6">
                        <div class="flex justify-between items-center mb-5 pb-4 border-b border-gray-200">
                            <div class="flex items-center gap-2">
                                <i class="fa-solid fa-chart-area text-purple text-sm"></i>
                                <span class="text-[11px] font-medium text-gray-600 uppercase tracking-wider">Andamento Annuale (2025)</span>
                            </div>
                        </div>
                        <div class="relative h-[250px]">
                            <canvas id="performanceDetailChart"></canvas>
                        </div>
                    </div>

                    <div class="widget-card widget-purple p-6">
                        <div class="flex justify-between items-center mb-5 pb-4 border-b border-gray-200">
                            <div class="flex items-center gap-2">
                                <i class="fa-solid fa-sack-dollar text-purple text-sm"></i>
                                <span class="text-[11px] font-medium text-gray-600 uppercase tracking-wider">Guadagno Cumulativo (YTD)</span>
                            </div>
                        </div>
                        <div class="relative h-[250px]">
                            <canvas id="cumulativeGainChart"></canvas>
                        </div>
                    </div>

                    <div class="widget-card widget-purple p-6">
                        <div class="flex justify-between items-center mb-5 pb-4 border-b border-gray-200">
                            <div class="flex items-center gap-2">
                                <i class="fa-solid fa-calendar-days text-purple text-sm"></i>
                                <span class="text-[11px] font-medium text-gray-600 uppercase tracking-wider">Ultimi 5 Giorni</span>
                            </div>
                        </div>
                        <div class="relative h-[250px]">
                            <canvas id="valueOverTimeChart"></canvas>
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
                                // Carica storico performance dagli snapshot DB (ultimi 30)
                                $history_data = [];
                                $snapshotsDb = $snapshots ?? [];

                                if (!empty($snapshotsDb)) {
                                    // Ordina per data e prendi ultimi 30
                                    usort($snapshotsDb, fn($a, $b) => strcmp($a["snapshot_date"], $b["snapshot_date"]));
                                    $snapshotsDb = array_slice($snapshotsDb, -30);

                                    $prevValue = null;
                                    $prevInvested = $metadata["total_invested"];

                                    foreach ($snapshotsDb as $snap) {
                                        $value = (float) $snap["total_market_value"];
                                        $invested = (float) ($snap["total_invested"] ?? $prevInvested);
                                        $cumul_gain = $value - $invested;
                                        $gain_pct = $invested > 0 ? ($cumul_gain / $invested) * 100 : 0;

                                        $day_change = $prevValue !== null ? $value - $prevValue : 0;
                                        $day_pct = ($prevValue !== null && $prevValue > 0)
                                            ? ($day_change / $prevValue) * 100
                                            : 0;

                                        $history_data[] = [
                                            "date" => date("d/m/Y", strtotime($snap["snapshot_date"])),
                                            "value" => $value,
                                            "cumul_gain" => $cumul_gain,
                                            "gain_pct" => $gain_pct,
                                            "open_pos" => $snap["total_holdings"] ?? $metadata["total_holdings"] ?? count($top_holdings),
                                            "day_change" => $day_change,
                                            "day_pct" => $day_pct,
                                        ];

                                        $prevValue = $value;
                                        $prevInvested = $invested;
                                    }
                                }

                                // Se nessun snapshot, mostra riga vuota
                                if (empty($history_data)) {
                                    echo '<tr><td colspan="7" class="px-4 py-8 text-center text-gray-500">Nessuno storico disponibile. Crea snapshot per visualizzare i dati.</td></tr>';
                                } else {
                                    foreach ($history_data as $row):
                                        $day_is_positive =
                                            $row["day_change"] >= 0; ?>
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
                                        echo $day_is_positive ? "+" : "";
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

                <!-- Timeline Transazioni -->
                <div class="mb-8 widget-card p-6">
                    <div class="flex justify-between items-center mb-5 pb-4 border-b border-gray-200">
                        <div class="flex items-center gap-2">
                            <i class="fa-solid fa-clock-rotate-left text-purple text-sm"></i>
                            <span class="text-[11px] font-medium text-gray-600 uppercase tracking-wider">Timeline Transazioni</span>
                        </div>
                        <span class="text-[11px] text-gray-500"><?php echo count(
                            $transactions
                        ); ?> eventi</span>
                    </div>
                    <?php if (empty($transactions)): ?>
                        <div class="p-6 text-center text-gray-500 italic">
                            Nessuna transazione registrata. Aggiungi/modifica una posizione o attendi payout dividendi.
                        </div>
                    <?php else: ?>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead class="bg-gray-50 border-b border-gray-200">
                                    <tr>
                                        <th class="px-4 py-3 text-left font-semibold text-gray-700 text-[11px] uppercase">Data</th>
                                        <th class="px-4 py-3 text-left font-semibold text-gray-700 text-[11px] uppercase">Tipo</th>
                                        <th class="px-4 py-3 text-left font-semibold text-gray-700 text-[11px] uppercase">Ticker</th>
                                        <th class="px-4 py-3 text-right font-semibold text-gray-700 text-[11px] uppercase">Quantità</th>
                                        <th class="px-4 py-3 text-right font-semibold text-gray-700 text-[11px] uppercase">Importo</th>
                                        <th class="px-4 py-3 text-left font-semibold text-gray-700 text-[11px] uppercase">Note</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($transactions as $tx):

                                        $ts = $tx["timestamp"] ?? "";
                                        $dateStr = $ts
                                            ? date("d/m/Y H:i", strtotime($ts))
                                            : "-";
                                        $amount = $tx["amount"] ?? 0;
                                        $qty = $tx["quantity_change"] ?? 0;
                                        $type = strtoupper($tx["type"] ?? "-");
                                        $isPositive = $amount >= 0;
                                        ?>
                                        <tr class="border-b border-gray-200 hover:bg-gray-50">
                                            <td class="px-4 py-3 text-sm text-gray-700"><?php echo htmlspecialchars(
                                                $dateStr
                                            ); ?></td>
                                            <td class="px-4 py-3 font-semibold text-primary"><?php echo htmlspecialchars(
                                                $type
                                            ); ?></td>
                                            <td class="px-4 py-3 font-medium text-gray-800"><?php echo htmlspecialchars(
                                                $tx["ticker"] ?? "-"
                                            ); ?></td>
                                            <td class="px-4 py-3 text-right text-gray-700"><?php echo number_format(
                                                $qty,
                                                2,
                                                ",",
                                                "."
                                            ); ?></td>
                                            <td class="px-4 py-3 text-right font-semibold <?php echo $isPositive
                                                ? "text-positive"
                                                : "text-negative"; ?>">
                                                <?php echo $isPositive
                                                    ? "+"
                                                    : ""; ?>€<?php echo number_format(
    $amount,
    2,
    ",",
    "."
); ?>
                                            </td>
                                            <td class="px-4 py-3 text-left text-[11px] text-gray-600"><?php echo htmlspecialchars(
                                                $tx["note"] ?? "-"
                                            ); ?></td>
                                        </tr>
                                    <?php
                                    endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Charts Scripts with Dynamic Data -->
                <script>
                <?php
                // Prepara dati per i grafici da snapshots
                $chart_labels = [];
                $chart_values = [];
                $chart_cumul_gain = [];
                $chart_gain_pct = [];
                $chart_monthly_labels = [];
                $chart_monthly_values = [];
                $chart_monthly_gain_pct = [];

                if (!empty($history_data)) {
                    $chart_labels = array_column($history_data, "date");
                    $chart_values = array_column($history_data, "value");
                    $chart_cumul_gain = array_column(
                        $history_data,
                        "cumul_gain"
                    );
                    $chart_gain_pct = array_column($history_data, "gain_pct");
                }

                // Dati mensili per performanceDetailChart da monthly_performance (DB)
                if (!empty($monthly_performance)) {
                    // Prendi ultimi 12 mesi ordinati
                    $mp = $monthly_performance;
                    // monthly_performance già formattato come ['month' => 'Nov', 'value' => ..., 'gain_pct' => ...]
                    $chart_monthly_labels = array_column($mp, "month");
                    $chart_monthly_values = array_column($mp, "value");
                    $chart_monthly_gain_pct = array_map(function($item) {
                        // Se gain_pct non esiste, calcola da value e metadata (fallback)
                        if (isset($item["gain_pct"])) {
                            return (float) $item["gain_pct"];
                        }
                        $invested = $item["invested"] ?? ($item["total_invested"] ?? 0);
                        $value = $item["value"] ?? 0;
                        return $invested > 0 ? (($value - $invested) / $invested) * 100 : 0;
                    }, $mp);
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

                // Performance Detail Chart (Andamento Annuale) - Usando ChartManager
                if (document.getElementById('performanceDetailChart')) {
                    try {
                        window.ChartManager.createPerformanceDetailChart(
                            'performanceDetailChart',
                            <?php echo json_encode($chart_monthly_labels); ?>,
                            <?php echo json_encode($chart_monthly_values); ?>,
                            <?php echo json_encode($chart_monthly_gain_pct); ?>
                        );
                        initializedCharts.add('performanceDetailChart');
                        console.log('✅ Performance Detail Chart created');
                    } catch (error) {
                        console.error('Errore inizializzazione Performance Detail Chart:', error);
                    }
                }

                // Cumulative Gain Chart - Usando ChartManager
                if (document.getElementById('cumulativeGainChart')) {
                    try {
                        window.ChartManager.createCumulativeGainChart(
                            'cumulativeGainChart',
                            <?php echo json_encode($chart_labels); ?>,
                            <?php echo json_encode($chart_cumul_gain); ?>,
                            <?php echo json_encode($chart_gain_pct); ?>
                        );
                        initializedCharts.add('cumulativeGainChart');
                        console.log('✅ Cumulative Gain Chart created');
                    } catch (error) {
                        console.error('Errore inizializzazione Cumulative Gain Chart:', error);
                    }
                }

                // Value Over Time Chart - Usando ChartManager
                if (document.getElementById('valueOverTimeChart')) {
                    try {
                        window.ChartManager.createValueOverTimeChart(
                            'valueOverTimeChart',
                            <?php echo json_encode($chart_labels); ?>,
                            <?php echo json_encode($chart_values); ?>,
                            <?php echo json_encode($chart_gain_pct); ?>
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
