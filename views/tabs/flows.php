            <div id="flows" class="view hidden">
                <div class="mb-6 sm:mb-10">
                    <h1 class="text-[18px] sm:text-[20px] font-medium text-primary tracking-tight">Flussi & Guadagni Progressivi</h1>
                </div>

                <!-- Info Metrics -->
                <?php
                // Carica snapshots data per calcolare metriche
                $snapshotsPath = __DIR__ . '/../../data/snapshots.json';
                $snapshots = [];
                $flows_start_date = '-';
                $flows_days_tracked = 0;
                $flows_snapshot_count = 0;

                if (file_exists($snapshotsPath)) {
                    $snapshotsData = json_decode(file_get_contents($snapshotsPath), true);
                    $snapshots = $snapshotsData['snapshots'] ?? [];
                    $flows_snapshot_count = count($snapshots);
                    if (!empty($snapshots)) {
                        $first_snap = $snapshots[0];
                        $last_snap = end($snapshots);
                        $flows_start_date = date('d M Y', strtotime($first_snap['date']));
                        $date1 = new DateTime($first_snap['date']);
                        $date2 = new DateTime($last_snap['date']);
                        $flows_days_tracked = $date1->diff($date2)->days;
                    }
                }
                ?>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 mb-8">
                    <div class="widget-card widget-purple p-6">
                        <div class="text-[11px] text-gray-500 mb-2 uppercase tracking-wider">Timeline Start</div>
                        <div class="text-xl font-bold text-primary"><?php echo $flows_start_date; ?></div>
                        <div class="text-[11px] text-gray-500 mt-1">Primo snapshot</div>
                    </div>
                    <div class="widget-card widget-purple p-6">
                        <div class="text-[11px] text-gray-500 mb-2 uppercase tracking-wider">Days Tracked</div>
                        <div class="text-xl font-bold text-primary"><?php echo $flows_days_tracked > 0 ? $flows_days_tracked . ' giorni' : '-'; ?></div>
                        <div class="text-[11px] text-gray-500 mt-1"><?php echo $flows_snapshot_count; ?> snapshot</div>
                    </div>
                    <div class="widget-card widget-purple p-6">
                        <div class="text-[11px] text-gray-500 mb-2 uppercase tracking-wider">Last Update</div>
                        <div class="text-xl font-bold text-primary"><?php echo date('d M Y', strtotime($metadata['last_update'])); ?></div>
                        <div class="text-[11px] text-gray-500 mt-1"><?php echo date('H:i', strtotime($metadata['last_update'])); ?> CET</div>
                    </div>
                    <div class="widget-card widget-purple p-6">
                        <div class="text-[11px] text-gray-500 mb-2 uppercase tracking-wider">Update Frequency</div>
                        <div class="text-xl font-bold text-primary">Manuale</div>
                        <div class="text-[11px] text-gray-500 mt-1">Aggiorna quando serve</div>
                    </div>
                </div>

                <!-- AI Insights per Flows -->
                <?php if (!empty($snapshots)): ?>
                <div class="mb-8">
                    <div class="widget-ai-insight px-6 py-4 transition-all duration-300">
                        <div class="flex items-center gap-2.5 mb-3 pb-2.5 border-b border-purple/20">
                            <span class="badge-ai bg-purple text-white text-[9px] font-bold px-1.5 py-0.5 uppercase tracking-wide">AI Insight</span>
                            <span class="text-[11px] font-medium text-purple uppercase tracking-wider">Analisi Flussi</span>
                        </div>
                        <div class="text-[13px] leading-relaxed text-gray-700">
                            <div class="pl-4 relative py-2 border-b border-dashed border-gray-300">
                                <span class="absolute left-0 text-purple font-bold">→</span>
                                Portafoglio in crescita costante con +<?php echo number_format($metadata['unrealized_pnl_pct'], 2, ',', '.'); ?>% da inizio tracking.
                            </div>
                            <div class="pl-4 relative py-2">
                                <span class="absolute left-0 text-purple font-bold">→</span>
                                Gain open €<?php echo number_format($metadata['unrealized_pnl'], 2, ',', '.'); ?> su posizioni attive. Nessuna posizione chiusa registrata.
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Tabella Storica -->
                <div class="mb-8 widget-card p-6">
                    <div class="flex justify-between items-center mb-5 pb-4 border-b border-gray-200">
                        <div class="flex items-center gap-2">
                            <i class="fa-solid fa-table-list text-purple text-sm"></i>
                            <span class="text-[11px] font-medium text-gray-600 uppercase tracking-wider">Tabella Storica Progressiva</span>
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
                                // Genera history_data da snapshots
                                $history_data = [];
                                if (!empty($snapshots)) {
                                    $initial_invested = !empty($snapshots) ? $snapshots[0]['metadata']['total_invested'] : 0;
                                    $prevValue = null;

                                    foreach ($snapshots as $snap) {
                                        $value = $snap['metadata']['total_value'];
                                        $invested = $snap['metadata']['total_invested'];
                                        $cumul_gain = $value - $invested;
                                        $gain_pct = $invested > 0 ? ($cumul_gain / $invested) * 100 : 0;
                                        $day_change = $prevValue !== null ? $value - $prevValue : 0;
                                        $day_pct = $prevValue !== null && $prevValue > 0 ? ($day_change / $prevValue) * 100 : 0;

                                        $history_data[] = [
                                            'date' => date('d/m/Y', strtotime($snap['date'])),
                                            'value' => $value,
                                            'cumul_gain' => $cumul_gain,
                                            'gain_pct' => $gain_pct,
                                            'open_pos' => $snap['metadata']['holdings_count'],
                                            'day_change' => $day_change,
                                            'day_pct' => $day_pct
                                        ];
                                        $prevValue = $value;
                                    }
                                }

                                if (empty($history_data)):
                                ?>
                                    <tr>
                                        <td colspan="7" class="px-4 py-8 text-center text-gray-500 italic">
                                            Nessuno storico disponibile. Crea snapshot per visualizzare i flussi progressivi.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($history_data as $row):
                                        $day_is_positive = $row['day_change'] >= 0;
                                        $gain_is_positive = $row['cumul_gain'] >= 0;
                                    ?>
                                    <tr class="border-b border-gray-200 hover:bg-gray-50">
                                        <td class="px-4 py-3 font-medium"><?php echo $row['date']; ?></td>
                                        <td class="px-4 py-3 text-right font-semibold">€<?php echo number_format($row['value'], 2, ',', '.'); ?></td>
                                        <td class="px-4 py-3 text-right <?php echo $gain_is_positive ? 'text-positive' : 'text-negative'; ?> font-semibold">
                                            <?php echo $gain_is_positive ? '+' : ''; ?>€<?php echo number_format($row['cumul_gain'], 2, ',', '.'); ?>
                                        </td>
                                        <td class="px-4 py-3 text-right <?php echo $gain_is_positive ? 'text-positive' : 'text-negative'; ?> font-semibold">
                                            <?php echo $gain_is_positive ? '+' : ''; ?><?php echo number_format($row['gain_pct'], 2, ',', '.'); ?>%
                                        </td>
                                        <td class="px-4 py-3 text-right"><?php echo $row['open_pos']; ?></td>
                                        <td class="px-4 py-3 text-right <?php echo $day_is_positive ? 'text-positive' : 'text-negative'; ?> font-semibold">
                                            <?php echo $day_is_positive ? '+' : ''; ?>€<?php echo number_format($row['day_change'], 2, ',', '.'); ?>
                                        </td>
                                        <td class="px-4 py-3 text-right <?php echo $day_is_positive ? 'text-positive' : 'text-negative'; ?> font-semibold">
                                            <?php echo $day_is_positive ? '+' : ''; ?><?php echo number_format($row['day_pct'], 2, ',', '.'); ?>%
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Grafici -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                    <div class="widget-card widget-purple p-6">
                        <div class="flex justify-between items-center mb-5 pb-4 border-b border-gray-200">
                            <div class="flex items-center gap-2">
                                <i class="fa-solid fa-chart-column text-purple text-sm"></i>
                                <span class="text-[11px] font-medium text-gray-600 uppercase tracking-wider">Guadagno Cumulativo nel Tempo</span>
                            </div>
                        </div>
                        <div class="relative h-[300px]">
                            <canvas id="cumulativeGainChart"></canvas>
                        </div>
                    </div>
                    <div class="widget-card widget-purple p-6">
                        <div class="flex justify-between items-center mb-5 pb-4 border-b border-gray-200">
                            <div class="flex items-center gap-2">
                                <i class="fa-solid fa-chart-gantt text-purple text-sm"></i>
                                <span class="text-[11px] font-medium text-gray-600 uppercase tracking-wider">Valore Portafoglio nel Tempo</span>
                            </div>
                        </div>
                        <div class="relative h-[300px]">
                            <canvas id="valueOverTimeChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Statistiche Performance -->
                <div class="mb-8">
                    <div class="flex items-center gap-2 mb-4">
                        <i class="fa-solid fa-chart-simple text-purple text-sm"></i>
                        <div class="text-[11px] font-medium text-gray-600 uppercase tracking-wider">Statistiche Performance</div>
                    </div>
                    <?php
                    // Calcola statistiche da history_data
                    $perf_since_start = '-';
                    $perf_since_start_pct = '-';
                    $best_week_amount = '-';
                    $best_week_date = '-';
                    $worst_week_amount = '-';
                    $worst_week_date = '-';
                    $avg_weekly_change = '-';

                    if (!empty($history_data)) {
                        $last_row = end($history_data);
                        $perf_since_start = '€' . number_format($last_row['cumul_gain'], 2, ',', '.');
                        $perf_since_start_pct = number_format($last_row['gain_pct'], 2, ',', '.') . '%';

                        // Trova best e worst week
                        $max_change = null;
                        $min_change = null;
                        $total_change = 0;
                        $count_changes = 0;

                        foreach ($history_data as $row) {
                            if ($row['day_change'] != 0) { // Ignora il primo giorno con 0
                                if ($max_change === null || $row['day_change'] > $max_change['amount']) {
                                    $max_change = ['amount' => $row['day_change'], 'date' => $row['date']];
                                }
                                if ($min_change === null || $row['day_change'] < $min_change['amount']) {
                                    $min_change = ['amount' => $row['day_change'], 'date' => $row['date']];
                                }
                                $total_change += abs($row['day_change']);
                                $count_changes++;
                            }
                        }

                        if ($max_change) {
                            $best_week_amount = '€' . number_format($max_change['amount'], 2, ',', '.');
                            $best_week_date = $max_change['date'];
                        }
                        if ($min_change) {
                            $worst_week_amount = '€' . number_format($min_change['amount'], 2, ',', '.');
                            $worst_week_date = $min_change['date'];
                        }
                        if ($count_changes > 0) {
                            $avg_weekly_change = '€' . number_format($total_change / $count_changes, 2, ',', '.');
                        }
                    }
                    ?>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
                        <div class="widget-card widget-purple p-6">
                            <div class="text-[11px] text-gray-500 mb-2 uppercase tracking-wider">Performance Since Start</div>
                            <div class="text-2xl font-bold text-positive"><?php echo $perf_since_start; ?></div>
                            <div class="text-[11px] text-positive mt-1"><?php echo $perf_since_start_pct; ?></div>
                        </div>
                        <div class="widget-card widget-purple p-6">
                            <div class="text-[11px] text-gray-500 mb-2 uppercase tracking-wider">Best Day</div>
                            <div class="text-xl font-bold text-primary"><?php echo $best_week_amount; ?></div>
                            <div class="text-[11px] text-gray-500 mt-1"><?php echo $best_week_date; ?></div>
                        </div>
                        <div class="widget-card widget-purple p-6">
                            <div class="text-[11px] text-gray-500 mb-2 uppercase tracking-wider">Worst Day</div>
                            <div class="text-xl font-bold text-primary"><?php echo $worst_week_amount; ?></div>
                            <div class="text-[11px] text-gray-500 mt-1"><?php echo $worst_week_date; ?></div>
                        </div>
                        <div class="widget-card widget-purple p-6">
                            <div class="text-[11px] text-gray-500 mb-2 uppercase tracking-wider">Average Change</div>
                            <div class="text-xl font-bold text-primary"><?php echo $avg_weekly_change; ?></div>
                            <div class="text-[11px] text-gray-500 mt-1">Per snapshot</div>
                        </div>
                    </div>
                </div>

                <!-- Charts Scripts with Dynamic Data -->
                <script>
                // Cumulative Gain Chart - Dynamic from snapshots
                const cumulativeGainCtxEl = document.getElementById('cumulativeGainChart');
                if (cumulativeGainCtxEl && !initializedCharts.has('cumulativeGainChart')) {
                    try {
                        <?php
                        $chart_labels = array_column($history_data, 'date');
                        $chart_cumul_gain = array_column($history_data, 'cumul_gain');
                        ?>
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
                        <?php
                        $chart_values = array_column($history_data, 'value');
                        ?>
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
