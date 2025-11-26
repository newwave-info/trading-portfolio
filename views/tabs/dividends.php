            <div id="dividends" class="view hidden">
                <div class="mb-6 sm:mb-10">
                    <h1 class="text-[18px] sm:text-[20px] font-medium text-primary tracking-tight">Calendario Dividendi</h1>
                </div>

                <!-- Summary Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 mb-8">
                    <div class="widget-card widget-purple p-6">
                        <div class="text-[11px] text-gray-500 mb-2 uppercase tracking-wider">Totale 2025</div>
                        <div class="text-2xl font-bold text-primary">€<?php echo number_format(
                            $metadata["total_dividends"],
                            2,
                            ",",
                            "."
                        ); ?></div>
                        <div class="text-[11px] text-positive mt-1"><?php echo count(
                            $dividends
                        ); ?> pagamenti</div>
                    </div>
                    <div class="widget-card widget-purple p-6">
                        <div class="text-[11px] text-gray-500 mb-2 uppercase tracking-wider">Previsto 6 Mesi</div>
                        <div class="text-xl font-bold text-primary">
                            <?php echo $dividends_calendar_data['forecast_6m']['total_amount'] !== null ? '€' . number_format($dividends_calendar_data['forecast_6m']['total_amount'], 2, ',', '.') : '-'; ?>
                        </div>
                        <div class="text-[11px] text-gray-500 mt-1"><?php echo htmlspecialchars($dividends_calendar_data['forecast_6m']['period']); ?></div>
                    </div>
                    <div class="widget-card widget-purple p-6">
                        <div class="text-[11px] text-gray-500 mb-2 uppercase tracking-wider">Yield Medio Portafoglio</div>
                        <div class="text-xl font-bold text-primary">
                            <?php echo $dividends_calendar_data['portfolio_yield'] !== null ? number_format($dividends_calendar_data['portfolio_yield'], 1, ',', '.') . '%' : '-'; ?>
                        </div>
                        <div class="text-[11px] text-gray-500 mt-1">Annualizzato</div>
                    </div>
                    <div class="widget-card widget-purple p-6">
                        <div class="text-[11px] text-gray-500 mb-2 uppercase tracking-wider">Prossimo Stacco</div>
                        <div class="text-xl font-bold text-primary"><?php echo htmlspecialchars($dividends_calendar_data['next_dividend']['date']); ?></div>
                        <div class="text-[11px] text-gray-500 mt-1">
                            <?php
                            $nextDiv = $dividends_calendar_data['next_dividend'];
                            echo htmlspecialchars($nextDiv['ticker']) . ' ';
                            echo $nextDiv['amount'] !== null ? '€' . number_format($nextDiv['amount'], 2, ',', '.') : '-';
                            ?>
                        </div>
                    </div>
                </div>

                <!-- AI Insights per Dividends -->
                <?php if ($dividends_calendar_data['ai_insight'] !== '-'): ?>
                <div class="mb-8">
                    <div class="widget-ai-insight px-6 py-4 transition-all duration-300">
                        <div class="flex items-center gap-2.5 mb-3 pb-2.5 border-b border-purple/20">
                            <span class="badge-ai bg-purple text-white text-[9px] font-bold px-1.5 py-0.5 uppercase tracking-wide">AI Insight</span>
                            <span class="text-[11px] font-medium text-purple uppercase tracking-wider">Analisi Dividendi</span>
                        </div>
                        <div class="text-[13px] leading-relaxed text-gray-700">
                            <div class="pl-4 relative py-2">
                                <span class="absolute left-0 text-purple font-bold">→</span>
                                <?php echo htmlspecialchars($dividends_calendar_data['ai_insight']); ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Calendario Mensile -->
                <div class="mb-8">
                    <div class="widget-card widget-purple p-6">
                        <div class="flex justify-between items-center mb-5 pb-4 border-b border-gray-200">
                            <div class="flex items-center gap-2">
                                <i class="fa-solid fa-calendar-days text-purple text-sm"></i>
                                <span class="text-[11px] font-medium text-gray-600 uppercase tracking-wider">Calendario Prossimi 6 Mesi</span>
                            </div>
                        </div>
                        <?php if (empty($dividends_calendar_data['monthly_forecast'])): ?>
                            <div class="p-6 text-center text-gray-500 italic">
                                Nessun calendario disponibile. I dati verranno popolati dal workflow automatico.
                            </div>
                        <?php else: ?>
                            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                                <?php foreach ($dividends_calendar_data['monthly_forecast'] as $month):
                                    $has_events = $month["events"] > 0; ?>
                                <div class="p-4 bg-white border border-gray-200">
                                    <div class="font-semibold text-primary text-sm"><?php echo htmlspecialchars($month["month"]); ?></div>
                                    <div class="text-[10px] text-gray-500 mb-2"><?php echo htmlspecialchars($month["year"]); ?></div>
                                    <?php if ($has_events): ?>
                                        <div class="text-xs text-gray-600 mb-1">
                                            <span class="px-2 py-0.5 bg-purple-100 text-purple-700 font-semibold"><?php echo $month["events"]; ?> evento/i</span>
                                        </div>
                                        <div class="text-sm font-bold text-positive mt-2">€<?php echo number_format(
                                            $month["amount"],
                                            2,
                                            ",",
                                            "."
                                        ); ?></div>
                                    <?php else: ?>
                                        <div class="text-xs text-gray-400">Nessun evento</div>
                                    <?php endif; ?>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Grafici Dividendi -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                        <div class="widget-card widget-purple p-6">
                            <div class="flex justify-between items-center mb-5 pb-4 border-b border-gray-200">
                                <div class="flex items-center gap-2">
                                    <i class="fa-solid fa-calendar-check text-purple text-sm"></i>
                                    <span class="text-[11px] font-medium text-gray-600 uppercase tracking-wider">Dividendi Mensili 2025</span>
                                </div>
                            </div>
                            <div class="relative h-[300px]">
                                <canvas id="dividendsMonthlyChart"></canvas>
                            </div>
                        </div>
                        <div class="widget-card widget-purple p-6">
                            <div class="flex justify-between items-center mb-5 pb-4 border-b border-gray-200">
                                <div class="flex items-center gap-2">
                                    <i class="fa-solid fa-money-bill-trend-up text-purple text-sm"></i>
                                    <span class="text-[11px] font-medium text-gray-600 uppercase tracking-wider">Rendita Cumulativa (2025)</span>
                                </div>
                            </div>
                            <div class="relative h-[300px]">
                                <canvas id="dividendsCumulativeChart"></canvas>
                            </div>
                        </div>
                    </div>

                <!-- Asset Distributivi -->
                <div class="mb-8">
                    <div class="widget-card widget-purple p-6">
                        <div class="flex justify-between items-center mb-5 pb-4 border-b border-gray-200">
                            <div class="flex items-center gap-2">
                                <i class="fa-solid fa-piggy-bank text-purple text-sm"></i>
                                <span class="text-[11px] font-medium text-gray-600 uppercase tracking-wider">Asset Distributivi</span>
                            </div>
                            <button class="px-3 py-1 bg-purple text-white text-[11px] font-semibold hover:bg-purple-dark transition-colors">
                                <i class="fa-solid fa-download mr-1"></i> Export CSV
                            </button>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm sortable-table">
                                <thead class="bg-gray-50 border-b border-gray-200">
                                    <tr>
                                        <th class="px-4 py-3 text-left font-semibold text-gray-700 text-[11px] uppercase cursor-pointer" role="button">Ticker</th>
                                        <th class="px-4 py-3 text-left font-semibold text-gray-700 text-[11px] uppercase cursor-pointer" role="button">Nome</th>
                                        <th class="px-4 py-3 text-right font-semibold text-gray-700 text-[11px] uppercase cursor-pointer" role="button">Yield %</th>
                                        <th class="px-4 py-3 text-center font-semibold text-gray-700 text-[11px] uppercase cursor-pointer" role="button">Frequenza</th>
                                        <th class="px-4 py-3 text-right font-semibold text-gray-700 text-[11px] uppercase cursor-pointer" role="button">Ultimo Pag.</th>
                                        <th class="px-4 py-3 text-right font-semibold text-gray-700 text-[11px] uppercase cursor-pointer" role="button">Prossimo Stacco</th>
                                        <th class="px-4 py-3 text-right font-semibold text-gray-700 text-[11px] uppercase cursor-pointer" role="button">Importo Atteso</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($dividends_calendar_data['distributing_assets'])): ?>
                                        <tr>
                                            <td colspan="7" class="px-4 py-8 text-center text-gray-500 italic">
                                                Nessun asset distributivo. I dati verranno popolati dal workflow automatico.
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($dividends_calendar_data['distributing_assets'] as $asset): ?>
                                        <tr class="border-b border-gray-200 hover:bg-gray-50">
                                            <td class="px-4 py-3 font-semibold text-purple"><?php echo htmlspecialchars($asset['ticker']); ?></td>
                                            <td class="px-4 py-3"><?php echo htmlspecialchars($asset['name']); ?></td>
                                            <td class="px-4 py-3 text-right font-semibold"><?php echo number_format($asset['yield'], 1, ',', '.'); ?>%</td>
                                            <td class="px-4 py-3 text-center text-xs"><?php echo htmlspecialchars($asset['frequency']); ?></td>
                                            <td class="px-4 py-3 text-right"><?php echo htmlspecialchars($asset['last_payment']); ?></td>
                                            <td class="px-4 py-3 text-right font-medium"><?php echo htmlspecialchars($asset['next_ex_date']); ?></td>
                                            <td class="px-4 py-3 text-right text-positive font-semibold">€<?php echo number_format($asset['expected_amount'], 2, ',', '.'); ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Dividends History -->
                <div class="mb-8">
                    <div class="widget-card widget-purple p-6">
                        <div class="flex justify-between items-center mb-5 pb-4 border-b border-gray-200">
                            <div class="flex items-center gap-2">
                                <i class="fa-solid fa-clock-rotate-left text-purple text-sm"></i>
                                <span class="text-[11px] font-medium text-gray-600 uppercase tracking-wider">Storico Dividendi Ricevuti 2025</span>
                            </div>
                        </div>
                        <div class="space-y-3">
                            <?php foreach ($dividends as $div): ?>
                            <div class="flex justify-between items-center p-3 bg-gray-50 border border-gray-200">
                                <div>
                                    <div class="font-semibold text-primary text-sm"><?php echo htmlspecialchars(
                                        $div["ticker"]
                                    ); ?></div>
                                    <div class="text-[11px] text-gray-500"><?php echo date(
                                        "d/m/Y",
                                        strtotime($div["pay_date"])
                                    ); ?></div>
                                </div>
                                <div class="text-right">
                                    <div class="font-bold text-positive">€<?php echo number_format(
                                        $div["amount"],
                                        2,
                                        ",",
                                        "."
                                    ); ?></div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- Charts Scripts with Dynamic Data -->
                <script>
                // Prepare dividends data for charts
                <?php
                // Crea array mensili per i grafici
                $months_labels = ['Gen', 'Feb', 'Mar', 'Apr', 'Mag', 'Giu', 'Lug', 'Ago', 'Set', 'Ott', 'Nov', 'Dic'];
                $monthly_dividends = array_fill(0, 12, 0);
                $cumulative_dividends = array_fill(0, 12, 0);

                // Popola array con dati reali da $dividends
                foreach ($dividends as $div) {
                    $month_num = (int)date('n', strtotime($div['pay_date'])) - 1; // 0-11
                    $monthly_dividends[$month_num] += $div['amount'];
                }

                // Calcola cumulativo
                $cumul = 0;
                for ($i = 0; $i < 12; $i++) {
                    $cumul += $monthly_dividends[$i];
                    $cumulative_dividends[$i] = $cumul;
                }
                ?>

                // Dividends Monthly Chart - Dynamic from dividends data
                const dividendsMonthlyCtxEl = document.getElementById('dividendsMonthlyChart');
                if (dividendsMonthlyCtxEl && !initializedCharts.has('dividendsMonthlyChart')) {
                    try {
                        const dividendsMonthlyCtx = dividendsMonthlyCtxEl.getContext('2d');
                        new Chart(dividendsMonthlyCtx, {
                            type: 'bar',
                            data: {
                                labels: <?php echo json_encode($months_labels); ?>,
                                datasets: [{
                                    label: 'Dividendi Mensili',
                                    data: <?php echo json_encode($monthly_dividends); ?>,
                                    backgroundColor: typeof pattern !== 'undefined' ? pattern.draw('diagonal', '#8b5cf6') : '#8b5cf6',
                                    borderColor: '#8b5cf6',
                                    borderRadius: 0
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
                                        ticks: { callback: v => '€' + v.toFixed(2) }
                                    }
                                }
                            }
                        });
                        initializedCharts.add('dividendsMonthlyChart');
                    } catch (error) {
                        console.error('Errore inizializzazione Dividends Monthly Chart:', error);
                    }
                }

                // Dividends Cumulative Chart - Dynamic from dividends data
                const dividendsCumulativeCtxEl = document.getElementById('dividendsCumulativeChart');
                if (dividendsCumulativeCtxEl && !initializedCharts.has('dividendsCumulativeChart')) {
                    try {
                        const dividendsCumulativeCtx = dividendsCumulativeCtxEl.getContext('2d');
                        new Chart(dividendsCumulativeCtx, {
                            type: 'line',
                            data: {
                                labels: <?php echo json_encode($months_labels); ?>,
                                datasets: [{
                                    label: 'Rendita Cumulativa',
                                    data: <?php echo json_encode($cumulative_dividends); ?>,
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
                                        ticks: { callback: v => '€' + v.toFixed(2) }
                                    }
                                }
                            }
                        });
                        initializedCharts.add('dividendsCumulativeChart');
                    } catch (error) {
                        console.error('Errore inizializzazione Dividends Cumulative Chart:', error);
                    }
                }
                </script>

            </div>

            <!-- View: Recommendations -->
