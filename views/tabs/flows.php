            <div id="flows" class="view hidden">
                <div class="mb-6 sm:mb-10">
                    <h1 class="text-[18px] sm:text-[20px] font-medium text-primary tracking-tight">Flussi & Guadagni Progressivi</h1>
                </div>

                <!-- Info Metrics -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 mb-8">
                    <div class="widget-card widget-purple p-6">
                        <div class="text-[11px] text-gray-500 mb-2 uppercase tracking-wider">Timeline Start</div>
                        <div class="text-xl font-bold text-primary">14 Nov 2025</div>
                        <div class="text-[11px] text-gray-500 mt-1">Primo utilizzo</div>
                    </div>
                    <div class="widget-card widget-purple p-6">
                        <div class="text-[11px] text-gray-500 mb-2 uppercase tracking-wider">Days Tracked</div>
                        <div class="text-xl font-bold text-primary">5 giorni</div>
                        <div class="text-[11px] text-gray-500 mt-1">5 snapshot</div>
                    </div>
                    <div class="widget-card widget-purple p-6">
                        <div class="text-[11px] text-gray-500 mb-2 uppercase tracking-wider">Last Update</div>
                        <div class="text-xl font-bold text-primary"><?php echo date('d M Y', strtotime($metadata['last_update'])); ?></div>
                        <div class="text-[11px] text-gray-500 mt-1"><?php echo date('H:i', strtotime($metadata['last_update'])); ?> CET</div>
                    </div>
                    <div class="widget-card widget-purple p-6">
                        <div class="text-[11px] text-gray-500 mb-2 uppercase tracking-wider">Update Frequency</div>
                        <div class="text-xl font-bold text-primary">Settimanale</div>
                        <div class="text-[11px] text-gray-500 mt-1">Giovedi o manuale</div>
                    </div>
                </div>

                <!-- AI Insights per Flows -->
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
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
                        <div class="widget-card widget-purple p-6">
                            <div class="text-[11px] text-gray-500 mb-2 uppercase tracking-wider">Performance Since Start</div>
                            <div class="text-2xl font-bold text-positive">€25,750.50</div>
                            <div class="text-[11px] text-positive mt-1">+25.75%</div>
                        </div>
                        <div class="widget-card widget-purple p-6">
                            <div class="text-[11px] text-gray-500 mb-2 uppercase tracking-wider">Best Week</div>
                            <div class="text-xl font-bold text-primary">€8,500.00</div>
                            <div class="text-[11px] text-gray-500 mt-1">23 Nov 2025</div>
                        </div>
                        <div class="widget-card widget-purple p-6">
                            <div class="text-[11px] text-gray-500 mb-2 uppercase tracking-wider">Worst Week</div>
                            <div class="text-xl font-bold text-primary">€2,500.00</div>
                            <div class="text-[11px] text-gray-500 mt-1">17 Nov 2025</div>
                        </div>
                        <div class="widget-card widget-purple p-6">
                            <div class="text-[11px] text-gray-500 mb-2 uppercase tracking-wider">Average Weekly Change</div>
                            <div class="text-xl font-bold text-primary">€6,437.63</div>
                            <div class="text-[11px] text-gray-500 mt-1">Volatility: 3.12%</div>
                        </div>
                    </div>
                </div>
            </div>
