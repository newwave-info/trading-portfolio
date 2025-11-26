            <div id="performance" class="view hidden">
                <div class="mb-6 sm:mb-10">
                    <h1 class="text-[18px] sm:text-[20px] font-medium text-primary tracking-tight">Performance & Flussi Progressivi</h1>
                </div>

                <!-- Performance Metrics -->
                <?php
                // Calcola metriche da snapshots
                $perf_1m = ['pct' => '-', 'value' => '-'];
                $perf_3m = ['pct' => '-', 'value' => '-'];
                $perf_ytd = ['pct' => '-', 'value' => '-'];

                $snapshotsPath = __DIR__ . '/../../data/snapshots.json';
                if (file_exists($snapshotsPath)) {
                    $snapshotsData = json_decode(file_get_contents($snapshotsPath), true);
                    $snapshots = $snapshotsData['snapshots'] ?? [];

                    if (!empty($snapshots)) {
                        $current = end($snapshots);
                        $currentValue = $current['metadata']['total_value'];

                        // 1 Mese (30 giorni fa)
                        $oneMonthAgo = date('Y-m-d', strtotime('-30 days'));
                        $snap1m = null;
                        foreach ($snapshots as $s) {
                            if ($s['date'] >= $oneMonthAgo) {
                                $snap1m = $s;
                                break;
                            }
                        }
                        if ($snap1m) {
                            $value1m = $snap1m['metadata']['total_value'];
                            $change1m = $currentValue - $value1m;
                            $pct1m = $value1m > 0 ? ($change1m / $value1m) * 100 : 0;
                            $perf_1m = [
                                'pct' => ($pct1m >= 0 ? '+' : '') . number_format($pct1m, 2, ',', '.') . '%',
                                'value' => '€' . number_format($value1m / 1000, 1, ',', '.') . 'k'
                            ];
                        }

                        // 3 Mesi (90 giorni fa)
                        $threeMonthsAgo = date('Y-m-d', strtotime('-90 days'));
                        $snap3m = null;
                        foreach ($snapshots as $s) {
                            if ($s['date'] >= $threeMonthsAgo) {
                                $snap3m = $s;
                                break;
                            }
                        }
                        if ($snap3m) {
                            $value3m = $snap3m['metadata']['total_value'];
                            $change3m = $currentValue - $value3m;
                            $pct3m = $value3m > 0 ? ($change3m / $value3m) * 100 : 0;
                            $perf_3m = [
                                'pct' => ($pct3m >= 0 ? '+' : '') . number_format($pct3m, 2, ',', '.') . '%',
                                'value' => '€' . number_format($value3m / 1000, 1, ',', '.') . 'k'
                            ];
                        }

                        // YTD (inizio anno)
                        $ytdStart = date('Y') . '-01-01';
                        $snapYtd = null;
                        foreach ($snapshots as $s) {
                            if ($s['date'] >= $ytdStart) {
                                $snapYtd = $s;
                                break;
                            }
                        }
                        if ($snapYtd) {
                            $valueYtd = $snapYtd['metadata']['total_value'];
                            $changeYtd = $currentValue - $valueYtd;
                            $pctYtd = $valueYtd > 0 ? ($changeYtd / $valueYtd) * 100 : 0;
                            $perf_ytd = [
                                'pct' => ($pctYtd >= 0 ? '+' : '') . number_format($pctYtd, 2, ',', '.') . '%',
                                'value' => '€' . number_format($valueYtd / 1000, 1, ',', '.') . 'k'
                            ];
                        }
                    }
                }
                ?>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 mb-8">
                    <div class="widget-card widget-purple p-6">
                        <div class="flex items-center gap-2 mb-3">
                            <i class="fa-solid fa-chart-line text-purple"></i>
                            <span class="text-[11px] font-medium text-gray-600 uppercase tracking-wider">1 Mese</span>
                        </div>
                        <div class="text-2xl font-bold text-positive mb-1"><?php echo $perf_1m['pct']; ?></div>
                        <div class="text-[11px] text-gray-500">vs <?php echo $perf_1m['value']; ?></div>
                    </div>

                    <div class="widget-card widget-purple p-6">
                        <div class="flex items-center gap-2 mb-3">
                            <i class="fa-solid fa-chart-area text-purple"></i>
                            <span class="text-[11px] font-medium text-gray-600 uppercase tracking-wider">3 Mesi</span>
                        </div>
                        <div class="text-2xl font-bold text-positive mb-1"><?php echo $perf_3m['pct']; ?></div>
                        <div class="text-[11px] text-gray-500">vs <?php echo $perf_3m['value']; ?></div>
                    </div>

                    <div class="widget-card widget-purple p-6">
                        <div class="flex items-center gap-2 mb-3">
                            <i class="fa-solid fa-arrow-trend-up text-purple"></i>
                            <span class="text-[11px] font-medium text-gray-600 uppercase tracking-wider">YTD</span>
                        </div>
                        <div class="text-2xl font-bold text-positive mb-1"><?php echo $perf_ytd['pct']; ?></div>
                        <div class="text-[11px] text-gray-500">vs <?php echo $perf_ytd['value']; ?></div>
                    </div>

                    <div class="widget-card widget-purple p-6">
                        <div class="flex items-center gap-2 mb-3">
                            <i class="fa-solid fa-trophy text-purple"></i>
                            <span class="text-[11px] font-medium text-gray-600 uppercase tracking-wider">Best Performer</span>
                        </div>
                        <div class="text-xl font-bold text-primary mb-1"><?php echo $best_performer ? htmlspecialchars($best_performer['ticker']) : '-'; ?></div>
                        <div class="text-[11px] text-positive"><?php echo $best_performer ? '+' . number_format($best_performer['pnl_percentage'], 2, ',', '.') . '%' : '-'; ?></div>
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
                        <button class="px-3 py-1 bg-purple text-white text-[11px] font-semibold hover:bg-purple-dark transition-colors">
                            <i class="fa-solid fa-download mr-1"></i> Export CSV
                        </button>
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
                                // Carica storico performance da snapshots
                                $history_data = [];
                                $snapshotsPath = __DIR__ . '/../../data/snapshots.json';

                                if (file_exists($snapshotsPath)) {
                                    $snapshotsData = json_decode(file_get_contents($snapshotsPath), true);
                                    $snapshots = $snapshotsData['snapshots'] ?? [];

                                    // Prendi ultimi 30 giorni
                                    $snapshots = array_slice($snapshots, -30);

                                    // Calcola day change per ogni giorno
                                    $prevValue = null;
                                    $prevInvested = $metadata['total_invested'];

                                    foreach ($snapshots as $snap) {
                                        $value = $snap['metadata']['total_value'];
                                        $invested = $snap['metadata']['total_invested'] ?? $prevInvested;
                                        $cumul_gain = $value - $invested;
                                        $gain_pct = $invested > 0 ? ($cumul_gain / $invested) * 100 : 0;

                                        $day_change = $prevValue !== null ? $value - $prevValue : 0;
                                        $day_pct = $prevValue !== null && $prevValue > 0 ? ($day_change / $prevValue) * 100 : 0;

                                        $history_data[] = [
                                            'date' => date('d/m/Y', strtotime($snap['date'])),
                                            'value' => $value,
                                            'cumul_gain' => $cumul_gain,
                                            'gain_pct' => $gain_pct,
                                            'open_pos' => $snap['metadata']['holdings_count'] ?? count($top_holdings),
                                            'day_change' => $day_change,
                                            'day_pct' => $day_pct
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
                                <?php
                                    endforeach;
                                } // end if empty
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Charts Scripts with Dynamic Data -->
                <script>
                <?php
                // Prepara dati per i grafici da snapshots
                $chart_labels = [];
                $chart_values = [];
                $chart_cumul_gain = [];
                $chart_monthly_labels = [];
                $chart_monthly_values = [];

                if (!empty($history_data)) {
                    $chart_labels = array_column($history_data, 'date');
                    $chart_values = array_column($history_data, 'value');
                    $chart_cumul_gain = array_column($history_data, 'cumul_gain');
                }

                // Dati mensili per performanceDetailChart da snapshots
                if (file_exists($snapshotsPath)) {
                    $snapshotsData = json_decode(file_get_contents($snapshotsPath), true);
                    $snapshots = $snapshotsData['snapshots'] ?? [];

                    if (!empty($snapshots)) {
                        // Raggruppa per mese
                        $byMonth = [];
                        foreach ($snapshots as $snap) {
                            $monthKey = date('Y-m', strtotime($snap['date']));
                            $month = date('M', strtotime($snap['date']));

                            // Prendi l'ultimo snapshot di ogni mese
                            if (!isset($byMonth[$monthKey]) || $snap['date'] > $byMonth[$monthKey]['date']) {
                                $byMonth[$monthKey] = [
                                    'month' => $month,
                                    'value' => $snap['metadata']['total_value'],
                                    'date' => $snap['date']
                                ];
                            }
                        }

                        // Ordina e prendi ultimi 12 mesi
                        usort($byMonth, fn($a, $b) => $a['date'] <=> $b['date']);
                        $last12 = array_slice($byMonth, -12);
                        $chart_monthly_labels = array_column($last12, 'month');
                        $chart_monthly_values = array_column($last12, 'value');
                    }
                }
                ?>

                // Performance Detail Chart (Andamento Annuale) - Dynamic from snapshots
                const performanceDetailCtxEl = document.getElementById('performanceDetailChart');
                if (performanceDetailCtxEl && !initializedCharts.has('performanceDetailChart')) {
                    try {
                        const performanceDetailCtx = performanceDetailCtxEl.getContext('2d');
                        new Chart(performanceDetailCtx, {
                            type: 'line',
                            data: {
                                labels: <?php echo json_encode($chart_monthly_labels); ?>,
                                datasets: [{
                                    label: 'Valore Portafoglio',
                                    data: <?php echo json_encode($chart_monthly_values); ?>,
                                    borderColor: '#8b5cf6',
                                    backgroundColor: typeof pattern !== 'undefined' ? pattern.draw('diagonal', 'rgba(139, 92, 246, 0.05)') : 'rgba(139, 92, 246, 0.05)',
                                    borderWidth: 3,
                                    fill: true,
                                    tension: 0,
                                    pointStyle: 'rect',
                                    pointRadius: 4,
                                    pointHoverRadius: 6
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                animation: { duration: 800, easing: 'easeOutQuart' },
                                plugins: { legend: { display: false } },
                                scales: {
                                    y: {
                                        beginAtZero: false,
                                        ticks: { callback: v => '€' + v.toLocaleString('it-IT') }
                                    }
                                }
                            }
                        });
                        initializedCharts.add('performanceDetailChart');
                    } catch (error) {
                        console.error('Errore inizializzazione Performance Detail Chart:', error);
                    }
                }

                // Cumulative Gain Chart - Dynamic from snapshots
                const cumulativeGainCtxEl = document.getElementById('cumulativeGainChart');
                if (cumulativeGainCtxEl && !initializedCharts.has('cumulativeGainChart')) {
                    try {
                        const cumulativeGainCtx = cumulativeGainCtxEl.getContext('2d');
                        new Chart(cumulativeGainCtx, {
                            type: 'line',
                            data: {
                                labels: <?php echo json_encode($chart_labels); ?>,
                                datasets: [{
                                    label: 'Guadagno Cumulativo',
                                    data: <?php echo json_encode($chart_cumul_gain); ?>,
                                    borderColor: '#8b5cf6',
                                    backgroundColor: typeof pattern !== 'undefined' ? pattern.draw('diagonal', 'rgba(139, 92, 246, 0.05)') : 'rgba(139, 92, 246, 0.05)',
                                    borderWidth: 3,
                                    fill: true,
                                    tension: 0,
                                    pointRadius: 5
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                animation: { duration: 800, easing: 'easeOutQuart' },
                                plugins: { legend: { display: false } },
                                scales: {
                                    y: {
                                        beginAtZero: true,
                                        ticks: { callback: v => '€' + v.toLocaleString('it-IT') }
                                    }
                                }
                            }
                        });
                        initializedCharts.add('cumulativeGainChart');
                    } catch (error) {
                        console.error('Errore inizializzazione Cumulative Gain Chart:', error);
                    }
                }

                // Value Over Time Chart - Dynamic from snapshots
                const valueOverTimeCtxEl = document.getElementById('valueOverTimeChart');
                if (valueOverTimeCtxEl && !initializedCharts.has('valueOverTimeChart')) {
                    try {
                        const valueOverTimeCtx = valueOverTimeCtxEl.getContext('2d');
                        new Chart(valueOverTimeCtx, {
                            type: 'line',
                            data: {
                                labels: <?php echo json_encode($chart_labels); ?>,
                                datasets: [{
                                    label: 'Valore Portafoglio',
                                    data: <?php echo json_encode($chart_values); ?>,
                                    borderColor: '#8b5cf6',
                                    backgroundColor: 'rgba(139, 92, 246, 0.05)',
                                    borderWidth: 3,
                                    fill: true,
                                    tension: 0,
                                    pointRadius: 5
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                animation: { duration: 800, easing: 'easeOutQuart' },
                                plugins: { legend: { display: false } },
                                scales: {
                                    y: {
                                        beginAtZero: false,
                                        ticks: { callback: v => '€' + v.toLocaleString('it-IT') }
                                    }
                                }
                            }
                        });
                        initializedCharts.add('valueOverTimeChart');
                    } catch (error) {
                        console.error('Errore inizializzazione Value Over Time Chart:', error);
                    }
                }
                </script>
            </div>

            <!-- View: Technical Analysis -->
