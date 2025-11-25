            <div id="technical" class="view hidden">
                <div class="mb-6 sm:mb-10">
                    <h1 class="text-[18px] sm:text-[20px] font-medium text-primary tracking-tight">Analisi Tecnica</h1>
                </div>

                <!-- AI Insights per Technical -->
                <div class="mb-8">
                    <div class="widget-ai-insight px-6 py-4 transition-all duration-300">
                        <div class="flex items-center gap-2.5 mb-3 pb-2.5 border-b border-purple/20">
                            <span class="badge-ai bg-purple text-white text-[9px] font-bold px-1.5 py-0.5 uppercase tracking-wide">AI Insight</span>
                            <span class="text-[11px] font-medium text-purple uppercase tracking-wider">Analisi Tecnica</span>
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
                        <button class="px-3 py-1 text-xs font-semibold bg-purple text-white hover:bg-purple-dark transition-colors" data-filter="all">Tutti</button>
                        <button class="px-3 py-1 text-xs font-semibold bg-gray-200 text-gray-700 hover:bg-gray-300 transition-colors" data-filter="BUY">BUY</button>
                        <button class="px-3 py-1 text-xs font-semibold bg-gray-200 text-gray-700 hover:bg-gray-300 transition-colors" data-filter="HOLD">HOLD</button>
                        <button class="px-3 py-1 text-xs font-semibold bg-gray-200 text-gray-700 hover:bg-gray-300 transition-colors" data-filter="WATCH">WATCH</button>
                    </div>
                    <button class="px-3 py-1 bg-purple text-white text-[11px] font-semibold hover:bg-purple-dark transition-colors">
                        <i class="fa-solid fa-download mr-1"></i> Export CSV
                    </button>
                </div>

                <!-- Technical Table -->
                <div class="widget-card widget-purple p-6">
                    <div class="overflow-x-auto">
                        <table id="technicalTable" class="w-full text-sm sortable-table">
                            <thead class="bg-gray-50 border-b border-gray-200">
                                <tr>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-700 text-[11px] uppercase" role="button">Ticker</th>
                                    <th class="px-4 py-3 text-right font-semibold text-gray-700 text-[11px] uppercase" role="button">Prezzo</th>
                                    <th class="px-4 py-3 text-right font-semibold text-gray-700 text-[11px] uppercase" role="button">EMA9</th>
                                    <th class="px-4 py-3 text-right font-semibold text-gray-700 text-[11px] uppercase" role="button">EMA21</th>
                                    <th class="px-4 py-3 text-right font-semibold text-gray-700 text-[11px] uppercase" role="button">EMA50</th>
                                    <th class="px-4 py-3 text-right font-semibold text-gray-700 text-[11px] uppercase" role="button">EMA200</th>
                                    <th class="px-4 py-3 text-right font-semibold text-gray-700 text-[11px] uppercase" role="button">RSI</th>
                                    <th class="px-4 py-3 text-center font-semibold text-gray-700 text-[11px] uppercase" role="button">MACD</th>
                                    <th class="px-4 py-3 text-right font-semibold text-gray-700 text-[11px] uppercase" role="button">Score</th>
                                    <th class="px-4 py-3 text-center font-semibold text-gray-700 text-[11px] uppercase" role="button">Decisione</th>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-700 text-[11px] uppercase" role="button">Azione</th>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-700 text-[11px] uppercase" role="button">Motivazione</th>
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
                                        <span class="px-2 py-1 text-[11px] font-semibold <?php echo $analysis['signal'] === 'BUY' ? 'bg-green-100 text-green-700' : ($analysis['signal'] === 'WATCH' ? 'bg-yellow-100 text-yellow-700' : 'bg-gray-100 text-gray-700'); ?>">
                                            <?php echo $analysis['signal']; ?>
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-xs text-gray-600"><?php echo $action; ?></td>
                                    <td class="px-4 py-3 text-xs text-gray-600 max-w-md">
                                        <span class="italic"><?php echo htmlspecialchars($analysis['reasoning']); ?></span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- View: Dividends -->
