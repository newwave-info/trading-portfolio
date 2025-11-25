            <div id="dashboard" class="view">
                <div class="mb-6 sm:mb-10">
                    <h1 class="text-[18px] sm:text-[20px] font-medium text-primary tracking-tight">Dashboard Overview</h1>
                </div>

                <!-- Dashboard Overview - 2 Columns Layout -->
        <?php
        $portfolio_score = 74; // Calcolo dinamico in seguito
        $score_label = $portfolio_score >= 75 ? 'Ottimo' : ($portfolio_score >= 60 ? 'Buono' : 'Migliorabile');
        ?>

        <!-- First Row: Salute Portafoglio + 4 Metrics -->
        <div class="grid grid-cols-1 lg:grid-cols-[1fr_auto] gap-6 mb-6">
            <!-- Salute Portafoglio -->
            <div class="widget-card widget-purple p-6">
                <div class="flex justify-between items-center mb-5 pb-4 border-b border-gray-200">
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-heart-pulse text-purple text-sm"></i>
                        <span class="text-[11px] font-medium text-gray-600 uppercase tracking-wider">Salute Portafoglio</span>
                        <div class="tooltip-container">
                            <i class="fa-solid fa-circle-info text-gray-400 text-[9px] cursor-help"></i>
                            <div class="tooltip-content">Indicatore sintetico della salute del portafoglio basato su diversificazione, performance e profilo di rischio</div>
                        </div>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <div class="flex flex-col items-center justify-center">
                        <div class="text-5xl font-bold text-purple"><?php echo $portfolio_score; ?></div>
                        <div class="text-2xl font-semibold text-gray-600 mt-1">/100</div>
                        <div class="text-sm text-gray-500 mt-2 px-3 py-1 bg-purple-100 ">
                            <?php echo $score_label; ?>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <div>
                            <div class="text-xs text-gray-500 mb-1">Diversificazione:</div>
                            <span class="px-3 py-1 bg-success-light text-success-dark text-xs font-semibold">Buona</span>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <div>
                            <div class="text-xs text-gray-500 mb-1">Performance:</div>
                            <span class="px-3 py-1 bg-success-light text-success-dark text-xs font-semibold">Positiva</span>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <div>
                            <div class="text-xs text-gray-500 mb-1">Rischio:</div>
                            <span class="px-3 py-1 bg-warning-light text-warning text-xs font-semibold">Moderato</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 4 Metrics Grid -->
            <div class="grid grid-cols-2 gap-4 lg:w-[500px]">
                <!-- Valore Totale -->
                <div class="widget-card widget-purple p-6 flex flex-col">
                    <div class="flex items-center gap-2 mb-3">
                        <i class="fa-solid fa-wallet text-purple"></i>
                        <span class="text-[11px] font-medium text-gray-600 uppercase tracking-wider">Valore Totale</span>
                        <div class="tooltip-container">
                            <i class="fa-solid fa-circle-info text-gray-400 text-[9px] cursor-help"></i>
                            <div class="tooltip-content">Valore totale corrente del portafoglio calcolato sui prezzi di mercato più recenti</div>
                        </div>
                    </div>
                    <div class="text-2xl font-bold text-primary mb-1">€<?php echo number_format($metadata['total_value'], 2, ',', '.'); ?></div>
                    <div class="text-[11px] text-gray-500">Investito: €<?php echo number_format($metadata['total_invested'], 2, ',', '.'); ?></div>
                </div>

                <!-- P&L Non Realizzato -->
                <div class="widget-card widget-purple p-6 flex flex-col">
                    <div class="flex items-center gap-2 mb-3">
                        <i class="fa-solid fa-chart-line text-purple"></i>
                        <span class="text-[11px] font-medium text-gray-600 uppercase tracking-wider">P&L Non Realizzato</span>
                        <div class="tooltip-container">
                            <i class="fa-solid fa-circle-info text-gray-400 text-[9px] cursor-help"></i>
                            <div class="tooltip-content">Profitto/perdita non realizzato sulle posizioni aperte rispetto al prezzo di carico</div>
                        </div>
                    </div>
                    <div class="text-2xl font-bold text-positive mb-1">€<?php echo number_format($metadata['unrealized_pnl'], 2, ',', '.'); ?></div>
                    <div class="text-[11px] text-gray-500">+<?php echo number_format($metadata['unrealized_pnl_pct'], 2, ',', '.'); ?>%</div>
                </div>

                <!-- Dividendi Totali -->
                <div class="widget-card widget-purple p-6 flex flex-col">
                    <div class="flex items-center gap-2 mb-3">
                        <i class="fa-solid fa-coins text-purple"></i>
                        <span class="text-[11px] font-medium text-gray-600 uppercase tracking-wider">Dividendi Totali</span>
                        <div class="tooltip-container">
                            <i class="fa-solid fa-circle-info text-gray-400 text-[9px] cursor-help"></i>
                            <div class="tooltip-content">Totale dividendi lordi ricevuti da tutte le posizioni nel periodo</div>
                        </div>
                    </div>
                    <div class="text-2xl font-bold text-primary mb-1">€<?php echo number_format($metadata['total_dividends'], 2, ',', '.'); ?></div>
                    <div class="text-[11px] text-gray-500"><?php echo count($dividends); ?> pagamenti</div>
                </div>

                <!-- Posizioni -->
                <div class="widget-card widget-purple p-6 flex flex-col">
                    <div class="flex items-center gap-2 mb-3">
                        <i class="fa-solid fa-boxes-stacked text-purple"></i>
                        <span class="text-[11px] font-medium text-gray-600 uppercase tracking-wider">Posizioni</span>
                        <div class="tooltip-container">
                            <i class="fa-solid fa-circle-info text-gray-400 text-[9px] cursor-help"></i>
                            <div class="tooltip-content">Numero totale di asset diversi attualmente presenti nel portafoglio</div>
                        </div>
                    </div>
                    <div class="text-2xl font-bold text-primary mb-1"><?php echo $holdings_count; ?></div>
                    <div class="text-[11px] text-gray-500">ETF attivi</div>
                </div>
            </div>
        </div>

        <!-- Second Row: AI Insights Riepilogo Portafoglio (Full Width) -->
        <div class="mb-8">
            <div class="widget-ai-insight px-6 py-4 transition-all duration-300">
                <div class="flex items-center gap-2.5 mb-3 pb-2.5 border-b border-purple/20">
                    <span class="badge-ai bg-purple text-white text-[9px] font-bold px-1.5 py-0.5 uppercase tracking-wide">AI Insight</span>
                    <span class="text-[11px] font-medium text-purple uppercase tracking-wider">Riepilogo Portafoglio <?php echo date('Y', strtotime($metadata['last_update'])); ?></span>
                    <div class="tooltip-container ml-1">
                        <i class="fa-solid fa-circle-info text-purple/60 text-[9px] cursor-help"></i>
                        <div class="tooltip-content">Analisi automatica del portafoglio con evidenza dei punti di attenzione e delle opportunità</div>
                    </div>
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
                        Portafoglio bilanciato con <?php echo $holdings_count; ?> posizioni. Allocazione concentrata su global equity (<?php echo number_format($allocation_by_asset_class[0]['percentage'], 1, ',', '.'); ?>%).
                    </div>
                </div>
            </div>
        </div>
        <!-- End Dashboard Overview -->

        <!-- Performance Chart -->
        <div class="mb-8">
            <div class="widget-card widget-purple p-6">
                <div class="flex justify-between items-center mb-5 pb-4 border-b border-gray-200">
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-chart-area text-purple text-sm"></i>
                        <span class="text-[11px] font-medium text-gray-600 uppercase tracking-wider">Andamento Portafoglio</span>
                        <div class="tooltip-container">
                            <i class="fa-solid fa-circle-info text-gray-400 text-[9px] cursor-help"></i>
                            <div class="tooltip-content">Evoluzione temporale del valore complessivo del portafoglio</div>
                        </div>
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
                <div class="widget-card widget-purple p-6">
                    <div class="flex justify-between items-center mb-5 pb-4 border-b border-gray-200">
                        <div class="flex items-center gap-2">
                            <i class="fa-solid fa-arrow-trend-up text-purple text-sm"></i>
                            <span class="text-[11px] font-medium text-gray-600 uppercase tracking-wider">Top 5 Performer</span>
                            <div class="tooltip-container">
                                <i class="fa-solid fa-circle-info text-gray-400 text-[9px] cursor-help"></i>
                                <div class="tooltip-content">Migliori 5 asset per performance percentuale nel periodo</div>
                            </div>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm sortable-table">
                            <thead class="bg-gray-50 border-b border-gray-200">
                                <tr>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-700 text-[11px] uppercase cursor-pointer" role="button">Ticker</th>
                                    <th class="px-4 py-3 text-right font-semibold text-gray-700 text-[11px] uppercase cursor-pointer" role="button">Gain %</th>
                                    <th class="px-4 py-3 text-right font-semibold text-gray-700 text-[11px] uppercase cursor-pointer" role="button">Gain €</th>
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
                <div class="widget-card widget-negative p-6">
                    <div class="flex justify-between items-center mb-5 pb-4 border-b border-gray-200">
                        <div class="flex items-center gap-2">
                            <i class="fa-solid fa-arrow-trend-down text-purple text-sm"></i>
                            <span class="text-[11px] font-medium text-gray-600 uppercase tracking-wider">Bottom 5 Performer</span>
                            <div class="tooltip-container">
                                <i class="fa-solid fa-circle-info text-gray-400 text-[9px] cursor-help"></i>
                                <div class="tooltip-content">Peggiori 5 asset per performance, richiedono monitoraggio attivo</div>
                            </div>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm sortable-table">
                            <thead class="bg-gray-50 border-b border-gray-200">
                                <tr>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-700 text-[11px] uppercase cursor-pointer" role="button">Ticker</th>
                                    <th class="px-4 py-3 text-right font-semibold text-gray-700 text-[11px] uppercase cursor-pointer" role="button">Gain %</th>
                                    <th class="px-4 py-3 text-right font-semibold text-gray-700 text-[11px] uppercase cursor-pointer" role="button">Gain €</th>
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
            <div class="widget-card widget-purple p-6">
                <div class="flex justify-between items-center mb-5 pb-4 border-b border-gray-200">
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-chart-pie text-purple text-sm"></i>
                        <span class="text-[11px] font-medium text-gray-600 uppercase tracking-wider">Breakdown per Tipo</span>
                        <div class="tooltip-container">
                            <i class="fa-solid fa-circle-info text-gray-400 text-[9px] cursor-help"></i>
                            <div class="tooltip-content">Distribuzione del portafoglio per tipologia di strumento finanziario</div>
                        </div>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm sortable-table">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700 text-[11px] uppercase cursor-pointer" role="button">Tipo</th>
                                <th class="px-4 py-3 text-right font-semibold text-gray-700 text-[11px] uppercase cursor-pointer" role="button">Valore €</th>
                                <th class="px-4 py-3 text-right font-semibold text-gray-700 text-[11px] uppercase cursor-pointer" role="button">Percentuale</th>
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
                                    <span class="px-2 py-1 text-[11px] bg-purple-100 text-purple-700 font-semibold">
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
            <div class="widget-card widget-purple p-6">
                <div class="flex justify-between items-center mb-5 pb-4 border-b border-gray-200">
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-ranking-star text-purple text-sm"></i>
                        <span class="text-[11px] font-medium text-gray-600 uppercase tracking-wider">Top Holdings</span>
                        <div class="tooltip-container">
                            <i class="fa-solid fa-circle-info text-gray-400 text-[9px] cursor-help"></i>
                            <div class="tooltip-content">Principali posizioni ordinate per valore assoluto detenuto</div>
                        </div>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm sortable-table">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700 text-[11px] uppercase cursor-pointer" role="button">Ticker</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700 text-[11px] uppercase cursor-pointer" role="button">Nome</th>
                                <th class="px-4 py-3 text-right font-semibold text-gray-700 text-[11px] uppercase cursor-pointer" role="button">Quantità</th>
                                <th class="px-4 py-3 text-right font-semibold text-gray-700 text-[11px] uppercase cursor-pointer" role="button">Prezzo Medio</th>
                                <th class="px-4 py-3 text-right font-semibold text-gray-700 text-[11px] uppercase cursor-pointer" role="button">Prezzo Attuale</th>
                                <th class="px-4 py-3 text-right font-semibold text-gray-700 text-[11px] uppercase cursor-pointer" role="button">Valore</th>
                                <th class="px-4 py-3 text-right font-semibold text-gray-700 text-[11px] uppercase cursor-pointer" role="button">P&L</th>
                                <th class="px-4 py-3 text-right font-semibold text-gray-700 text-[11px] uppercase cursor-pointer" role="button">Drift</th>
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
                                    <span class="px-2 py-1 text-[11px] <?php echo $holding['drift'] < 0 ? 'bg-yellow-100 text-yellow-700' : 'bg-green-100 text-green-700'; ?>">
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
                        <i class="fa-solid fa-circle-half-stroke text-purple text-sm"></i>
                        <span class="text-[11px] font-medium text-gray-600 uppercase tracking-wider">Allocazione per Asset Class</span>
                        <div class="tooltip-container">
                            <i class="fa-solid fa-circle-info text-gray-400 text-[9px] cursor-help"></i>
                            <div class="tooltip-content">Distribuzione percentuale del capitale per classe di asset (equity, bond, cash)</div>
                        </div>
                    </div>
                </div>
                <div class="relative h-[250px]">
                    <canvas id="allocationChart"></canvas>
                </div>
            </div>

            <div class="widget-card widget-purple p-6">
                <div class="flex justify-between items-center mb-5 pb-4 border-b border-gray-200">
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-chart-line text-purple text-sm"></i>
                        <span class="text-[11px] font-medium text-gray-600 uppercase tracking-wider">Analisi Tecnica</span>
                        <div class="tooltip-container">
                            <i class="fa-solid fa-circle-info text-gray-400 text-[9px] cursor-help"></i>
                            <div class="tooltip-content">Snapshot degli indicatori tecnici principali per valutare trend e timing di ingresso</div>
                        </div>
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
                            <span class="px-2 py-1 text-[11px] font-semibold <?php echo $analysis['signal'] === 'BUY' ? 'bg-green-100 text-green-700' : ($analysis['signal'] === 'SELL' ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-700'); ?>">
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
            <div class="widget-card widget-purple p-6">
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

            <div class="widget-card widget-purple p-6">
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
                            <span class="px-2 py-1 text-[11px] font-semibold <?php echo $opp['signal'] === 'BUY' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700'; ?>">
                                <?php echo $opp['signal']; ?>
                            </span>
                        </div>
                        <div class="text-[11px] text-gray-600 mt-2">
                            <i class="fa-solid fa-lightbulb text-yellow-500"></i> <?php echo htmlspecialchars($opp['reason']); ?>
                        </div>
                        <div class="flex gap-3 mt-2 text-[11px]">
                            <span class="text-gray-500">Yield: <strong><?php echo number_format($opp['yield'], 2, ',', '.'); ?>%</strong></span>
                            <span class="text-gray-500">TER: <strong><?php echo number_format($opp['expense_ratio'], 2, ',', '.'); ?>%</strong></span>
                            <span class="px-1 py-0.5 bg-green-100 text-green-700 text-[10px]"><?php echo $opp['commission_profile']; ?></span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
            </div>
            <!-- End Dashboard View -->

            <!-- View: Holdings -->
