            <div id="performance" class="view hidden">
                <div class="mb-6 sm:mb-10">
                    <h1 class="text-[18px] sm:text-[20px] font-medium text-primary tracking-tight">Performance & Flussi Progressivi</h1>
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

                <!-- Grafici Performance & Flussi -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
                    <div class="widget-card widget-purple p-6">
                        <div class="flex justify-between items-center mb-5 pb-4 border-b border-gray-200">
                            <div class="flex items-center gap-2">
                                <i class="fa-solid fa-chart-area text-purple text-sm"></i>
                                <span class="text-[11px] font-medium text-gray-600 uppercase tracking-wider">Andamento Portafoglio</span>
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
                                <span class="text-[11px] font-medium text-gray-600 uppercase tracking-wider">Guadagno Cumulativo</span>
                            </div>
                        </div>
                        <div class="relative h-[250px]">
                            <canvas id="cumulativeGainChart"></canvas>
                        </div>
                    </div>

                    <div class="widget-card widget-purple p-6">
                        <div class="flex justify-between items-center mb-5 pb-4 border-b border-gray-200">
                            <div class="flex items-center gap-2">
                                <i class="fa-solid fa-briefcase text-purple text-sm"></i>
                                <span class="text-[11px] font-medium text-gray-600 uppercase tracking-wider">Valore Portafoglio</span>
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
            </div>

            <!-- View: Technical Analysis -->
