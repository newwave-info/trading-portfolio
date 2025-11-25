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
                    <div class="widget-card widget-purple p-6">
                        <div class="text-[11px] text-gray-500 mb-2 uppercase tracking-wider">Previsto 6 Mesi</div>
                        <div class="text-xl font-bold text-primary">€542.16</div>
                        <div class="text-[11px] text-gray-500 mt-1">Gen - Giu 2026</div>
                    </div>
                    <div class="widget-card widget-purple p-6">
                        <div class="text-[11px] text-gray-500 mb-2 uppercase tracking-wider">Yield Medio Portafoglio</div>
                        <div class="text-xl font-bold text-primary">2.8%</div>
                        <div class="text-[11px] text-gray-500 mt-1">Annualizzato</div>
                    </div>
                    <div class="widget-card widget-purple p-6">
                        <div class="text-[11px] text-gray-500 mb-2 uppercase tracking-wider">Prossimo Stacco</div>
                        <div class="text-xl font-bold text-primary">15 Dic</div>
                        <div class="text-[11px] text-gray-500 mt-1">VWCE €30.18</div>
                    </div>
                </div>

                <!-- AI Insights per Dividends -->
                <div class="mb-8">
                    <div class="widget-ai-insight px-6 py-4 transition-all duration-300">
                        <div class="flex items-center gap-2.5 mb-3 pb-2.5 border-b border-purple/20">
                            <span class="badge-ai bg-purple text-white text-[9px] font-bold px-1.5 py-0.5 uppercase tracking-wide">AI Insight</span>
                            <span class="text-[11px] font-medium text-purple uppercase tracking-wider">Analisi Dividendi</span>
                        </div>
                        <div class="text-[13px] leading-relaxed text-gray-700">
                            <div class="pl-4 relative py-2 border-b border-dashed border-gray-300">
                                <span class="absolute left-0 text-purple font-bold">→</span>
                                Portafoglio con yield medio 2.8%, in linea con ETF diversificati. Focus su crescita capitale piuttosto che rendita.
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
                    <div class="widget-card widget-purple p-6">
                        <div class="flex justify-between items-center mb-5 pb-4 border-b border-gray-200">
                            <div class="flex items-center gap-2">
                                <i class="fa-solid fa-calendar-days text-purple text-sm"></i>
                                <span class="text-[11px] font-medium text-gray-600 uppercase tracking-wider">Calendario Prossimi 6 Mesi</span>
                            </div>
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
                            <div class="p-4 bg-white border border-gray-200">
                                <div class="font-semibold text-primary text-sm"><?php echo $month['month']; ?></div>
                                <div class="text-[10px] text-gray-500 mb-2"><?php echo $month['year']; ?></div>
                                <?php if ($has_events): ?>
                                    <div class="text-xs text-gray-600 mb-1">
                                        <span class="px-2 py-0.5 bg-purple-100 text-purple-700 font-semibold"><?php echo $month['events']; ?> evento/i</span>
                                    </div>
                                    <div class="text-sm font-bold text-positive mt-2">€<?php echo number_format($month['amount'], 2, ',', '.'); ?></div>
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
                                <span class="text-[11px] font-medium text-gray-600 uppercase tracking-wider">Rendita Cumulativa</span>
                            </div>
                        </div>
                        <div class="relative h-[300px]">
                            <canvas id="dividendsCumulativeChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- View: Recommendations -->
