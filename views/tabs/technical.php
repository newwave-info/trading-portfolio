            <div id="technical" class="view hidden">
                <div class="mb-6 sm:mb-10">
                    <h1 class="text-[18px] sm:text-[20px] font-medium text-primary tracking-tight">Analisi Tecnica</h1>
                </div>

                <!-- AI Technical Insights -->
                <div class="widget-card widget-purple p-5 mb-6">
                    <div class="flex items-center gap-2 mb-4 pb-2 border-b border-gray-200">
                        <span class="text-[11px] font-semibold text-purple uppercase tracking-wider">Insight AI Portafoglio</span>
                        <?php if (!empty($ai_portfolio_insight['generated_at'])): ?>
                            <span class="text-[11px] text-gray-500 ml-auto">Aggiornato: <?php echo date('d/m/Y H:i', strtotime($ai_portfolio_insight['generated_at'])); ?></span>
                        <?php endif; ?>
                    </div>
                    <?php if (!empty($ai_portfolio_insight['insight_text'])): ?>
                        <?php $scores = $ai_portfolio_insight['insight_json']['scores'] ?? []; ?>
                        <?php if (!empty($scores) && is_array($scores)): ?>
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-2 mb-4">
                                <?php foreach (['health_score'=>'Salute','risk_score'=>'Rischio','diversification_score'=>'Diversificazione','momentum_score'=>'Momentum','volatility_score'=>'Volatilità','extension_score'=>'Estensione'] as $k => $label): ?>
                                    <?php if (isset($scores[$k])): ?>
                                        <?php
                                            $val = (int)$scores[$k];
                                            // Colori: >70 positivo, 40-70 neutro, <40 negativo. Per Rischio invertiamo la lettura.
                                            $isRisk = $k === 'risk_score';
                                            if ($isRisk) {
                                                $css = $val < 40 ? 'text-positive' : ($val > 70 ? 'text-negative' : 'text-gray-700');
                                            } else {
                                                $css = $val > 70 ? 'text-positive' : ($val < 40 ? 'text-negative' : 'text-gray-700');
                                            }
                                        ?>
                                        <div class="bg-white rounded border border-purple/20 px-3 py-2 flex flex-col">
                                            <span class="text-[11px] text-gray-600"><?php echo $label; ?></span>
                                            <span class="text-lg font-bold <?php echo $css; ?>"><?php echo $val; ?><span class="text-[11px] text-gray-500 font-normal"> / 100</span></span>
                                        </div>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                        <?php
                            $trendText = $ai_portfolio_insight['insight_json']['trend'] ?? '-';
                            $riskText = $ai_portfolio_insight['insight_json']['risk'] ?? '-';
                            $volText = $ai_portfolio_insight['insight_json']['volatility_comment'] ?? '-';
                            $divText = $ai_portfolio_insight['insight_json']['diversification_comment'] ?? '-';
                        ?>
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2 mb-4">
                            <div class="bg-white rounded border border-gray-200 px-3 py-2">
                                <div class="text-[11px] text-gray-600 uppercase">Trend</div>
                                <div class="text-sm font-semibold text-primary"><?php echo htmlspecialchars($trendText); ?></div>
                            </div>
                            <div class="bg-white rounded border border-gray-200 px-3 py-2">
                                <div class="text-[11px] text-gray-600 uppercase">Rischio</div>
                                <div class="text-sm font-semibold text-gray-800"><?php echo htmlspecialchars($riskText); ?></div>
                            </div>
                            <div class="bg-white rounded border border-gray-200 px-3 py-2">
                                <div class="text-[11px] text-gray-600 uppercase">Volatilità</div>
                                <div class="text-sm text-gray-800"><?php echo htmlspecialchars($volText); ?></div>
                            </div>
                            <div class="bg-white rounded border border-gray-200 px-3 py-2">
                                <div class="text-[11px] text-gray-600 uppercase">Diversificazione</div>
                                <div class="text-sm text-gray-800"><?php echo htmlspecialchars($divText); ?></div>
                            </div>
                        </div>
                        <div class="text-sm text-gray-800 leading-relaxed mb-3">
                            <?php echo htmlspecialchars($ai_portfolio_insight['insight_text']); ?>
                        </div>
                        <?php if (!empty($ai_portfolio_insight['insight_json']) && is_array($ai_portfolio_insight['insight_json'])): ?>
                            <ul class="list-disc pl-5 text-xs text-gray-700 space-y-1">
                                <?php foreach (['trend','risk','volatility_comment','diversification_comment','notes'] as $k):
                                    if (!empty($ai_portfolio_insight['insight_json'][$k])): ?>
                                        <li><span class="font-semibold capitalize"><?php echo str_replace('_',' ', $k); ?>:</span> <?php echo htmlspecialchars($ai_portfolio_insight['insight_json'][$k]); ?></li>
                                    <?php endif;
                                endforeach; ?>
                            </ul>
                        <?php endif; ?>
                        <div class="text-[11px] text-gray-500 mt-3">Modello: <?php echo htmlspecialchars($ai_portfolio_insight['model'] ?? '-'); ?></div>
                    <?php else: ?>
                        <div class="text-sm text-gray-500 italic">Nessun insight AI disponibile. Esegui il workflow AI Technical Insights.</div>
                    <?php endif; ?>
                </div>

                <!-- Tabella Analisi Tecnica (DB-first) -->
                <div class="widget-card widget-purple p-6">
                    <div class="overflow-x-auto">
                        <table id="technicalTable" class="w-full text-sm sortable-table">
                            <thead class="bg-gray-50 border-b border-gray-200">
                                <tr>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-700 text-[11px] uppercase" role="button">Ticker</th>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-700 text-[11px] uppercase" role="button">Nome</th>
                                    <th class="px-4 py-3 text-right font-semibold text-gray-700 text-[11px] uppercase" role="button">Prezzo</th>
                                    <th class="px-4 py-3 text-center font-semibold text-gray-700 text-[11px] uppercase" role="button">Trend</th>
                                    <th class="px-4 py-3 text-center font-semibold text-gray-700 text-[11px] uppercase" role="button">Momentum</th>
                                    <th class="px-4 py-3 text-right font-semibold text-gray-700 text-[11px] uppercase" role="button">RSI 14</th>
                                    <th class="px-4 py-3 text-right font-semibold text-gray-700 text-[11px] uppercase" role="button">Vol. 30d</th>
                                    <th class="px-4 py-3 text-right font-semibold text-gray-700 text-[11px] uppercase" role="button">ATR %</th>
                                    <th class="px-4 py-3 text-right font-semibold text-gray-700 text-[11px] uppercase" role="button">Range 1Y %</th>
                                    <th class="px-4 py-3 text-center font-semibold text-gray-700 text-[11px] uppercase" role="button">Bollinger</th>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-700 text-[11px] uppercase">Insight tecnico</th>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-700 text-[11px] uppercase">Flag / Livelli</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($technical_analysis)): ?>
                                    <tr>
                                        <td colspan="11" class="px-4 py-8 text-center text-gray-500 italic">
                                            Nessun dato tecnico disponibile. Esegui il workflow n8n di enrichment per popolare gli indicatori.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                <?php foreach ($technical_analysis as $row):

                                        $rsi = $row["rsi14"] ?? null;
                                        $rsiClass = "text-gray-700";
                                        if ($rsi !== null) {
                                            if ($rsi < 30) {
                                                $rsiClass = "text-positive";
                                            } elseif ($rsi > 70) {
                                                $rsiClass = "text-negative";
                                            }
                                        }

                                        $bollPos = $row["bb_percent_b"] ?? null;
                                        $bollLabel = "-";
                                        if ($bollPos !== null) {
                                            if ($bollPos < 0.2) {
                                                $bollLabel =
                                                    "Vicino banda inferiore";
                                            } elseif ($bollPos <= 0.8) {
                                                $bollLabel = "Centro banda";
                                            } else {
                                                $bollLabel =
                                                    "Vicino banda superiore";
                                            }
                                        }
                                        ?>
                                    <tr class="border-b border-gray-200 hover:bg-gray-50">
                                        <td class="px-4 py-3 font-semibold whitespace-nowrap">
                                            <button
                                                onclick="openTechnicalModal('<?php echo htmlspecialchars($row["ticker"]); ?>')"
                                                class="text-purple hover:text-purple-dark underline cursor-pointer font-semibold transition-colors"
                                                title="Click per dettagli tecnici completi">
                                                <?php echo htmlspecialchars($row["ticker"]); ?>
                                            </button>
                                        </td>
                                        <td class="px-4 py-3 text-gray-800"><?php echo htmlspecialchars(
                                            $row["name"]
                                        ); ?></td>
                                        <td class="px-4 py-3 text-right font-medium">€<?php echo number_format(
                                            $row["price"],
                                            2,
                                            ",",
                                            "."
                                        ); ?></td>
                                        <td class="px-4 py-3 text-center">
                                            <span class="px-2 py-1 text-[11px] font-semibold rounded <?php echo $row[
                                                "trend"
                                            ] === "Rialzista"
                                                ? "bg-green-100 text-green-700"
                                                : ($row["trend"] ===
                                                "Ribassista"
                                                    ? "bg-red-100 text-red-700"
                                                    : "bg-gray-100 text-gray-700"); ?>">
                                                <?php echo $row["trend"]; ?>
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <span class="px-2 py-1 text-[11px] font-semibold rounded <?php echo $row[
                                                "momentum"
                                            ] === "Positivo"
                                                ? "bg-green-100 text-green-700"
                                                : ($row["momentum"] ===
                                                "Negativo"
                                                    ? "bg-red-100 text-red-700"
                                                    : "bg-gray-100 text-gray-700"); ?>">
                                                <?php echo $row["momentum"]; ?>
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-right font-semibold <?php echo $rsiClass; ?>"><?php echo $rsi !==
null
    ? number_format($rsi, 1, ",", ".")
    : "-"; ?></td>
                                        <td class="px-4 py-3 text-right text-gray-700"><?php echo $row[
                                            "hist_vol_30d"
                                        ] !== null
                                            ? number_format(
                                                    $row["hist_vol_30d"],
                                                    2,
                                                    ",",
                                                    "."
                                                ) . "%"
                                            : "-"; ?></td>
                                        <td class="px-4 py-3 text-right text-gray-700"><?php echo $row[
                                            "atr14_pct"
                                        ] !== null
                                            ? number_format(
                                                    $row["atr14_pct"],
                                                    2,
                                                    ",",
                                                    "."
                                                ) . "%"
                                            : "-"; ?></td>
                                        <td class="px-4 py-3 text-right text-gray-700"><?php echo $row[
                                            "range_1y_percentile"
                                        ] !== null
                                            ? number_format(
                                                    $row[
                                                        "range_1y_percentile"
                                                    ] * 100,
                                                    1,
                                                    ",",
                                                    "."
                                                ) . "%"
                                            : "-"; ?></td>
                                        <td class="px-4 py-3 text-center text-gray-700"><?php echo $bollPos !==
                                        null
                                            ? $bollLabel
                                            : "-"; ?></td>
                                    <?php
                                        $insightText = $row['insight'] ?? '';
                                        $insightFlags = $row['insight_flags'] ?? [];
                                        $insightLevels = $row['insight_levels'] ?? [];
                                        $insightScores = $row['insight_scores'] ?? [];
                                        $insightSignals = $row['insight_signals'] ?? [];
                                    ?>
                                        <td class="px-4 py-3 text-left text-gray-700 text-sm">
                                            <?php echo htmlspecialchars($insightText); ?>
                                            <?php if (!empty($insightScores) || !empty($insightSignals)): ?>
                                                <div class="mt-1 flex flex-wrap gap-1 text-[11px]">
                                                    <?php if (!empty($insightScores['trend_strength'])): ?>
                                                        <span class="px-2 py-0.5 rounded bg-purple/10 text-primary border border-purple/20">Trend <?php echo (int)$insightScores['trend_strength']; ?>/100</span>
                                                    <?php endif; ?>
                                                    <?php if (!empty($insightScores['momentum_strength'])): ?>
                                                        <span class="px-2 py-0.5 rounded bg-purple/10 text-primary border border-purple/20">Momentum <?php echo (int)$insightScores['momentum_strength']; ?>/100</span>
                                                    <?php endif; ?>
                                                    <?php if (!empty($insightScores['overextension_score'])): ?>
                                                        <span class="px-2 py-0.5 rounded bg-yellow-50 text-yellow-700 border border-yellow-200">Overext <?php echo (int)$insightScores['overextension_score']; ?>/100</span>
                                                    <?php endif; ?>
                                                    <?php if (!empty($insightScores['volatility_score'])): ?>
                                                        <span class="px-2 py-0.5 rounded bg-blue-50 text-blue-700 border border-blue-200">Vol <?php echo (int)$insightScores['volatility_score']; ?>/100</span>
                                                    <?php endif; ?>
                                                    <?php if (!empty($insightSignals)): ?>
                                                        <span class="px-2 py-0.5 rounded bg-gray-50 text-gray-700 border border-gray-200" title="Trend: <?php echo htmlspecialchars($insightSignals['trend_signal'] ?? '-'); ?> | Momentum: <?php echo htmlspecialchars($insightSignals['momentum_signal'] ?? '-'); ?> | RSI: <?php echo htmlspecialchars($insightSignals['rsi_condition'] ?? '-'); ?> | Bollinger: <?php echo htmlspecialchars($insightSignals['bollinger_position'] ?? '-'); ?> | Range: <?php echo htmlspecialchars($insightSignals['range_position'] ?? '-'); ?> | Vol: <?php echo htmlspecialchars($insightSignals['volatility_flag'] ?? '-'); ?>">Segnali</span>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                    <td class="px-4 py-3 text-left text-gray-700 text-sm">
                                        <?php if (!empty($insightFlags)): ?>
                                            <div class="flex flex-wrap gap-1">
                                                <?php foreach ($insightFlags as $flag): ?>
                                                    <span class="px-2 py-0.5 text-[11px] bg-purple/10 text-primary border border-purple/20 rounded-full"><?php echo htmlspecialchars($flag); ?></span>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>
                                        <?php if (!empty($insightLevels) && ( !empty($insightLevels['potential_support_levels']) || !empty($insightLevels['potential_resistance_levels']) )): ?>
                                            <div class="mt-2 text-[11px] text-gray-600 flex flex-col gap-1">
                                                <?php if (!empty($insightLevels['potential_support_levels'])): ?>
                                                    <div class="px-2 py-1 rounded bg-green-50 border border-green-200 text-positive">
                                                        <span class="font-semibold">Supporti:</span> <?php echo htmlspecialchars(implode(', ', $insightLevels['potential_support_levels'])); ?>
                                                    </div>
                                                <?php endif; ?>
                                                <?php if (!empty($insightLevels['potential_resistance_levels'])): ?>
                                                    <div class="px-2 py-1 rounded bg-red-50 border border-red-200 text-negative">
                                                        <span class="font-semibold">Resistenze:</span> <?php echo htmlspecialchars(implode(', ', $insightLevels['potential_resistance_levels'])); ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                    <?php
                                    endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Modal Analisi Tecnica Dettagliata -->
                <div id="technicalModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 overflow-y-auto">
                    <div class="flex items-start justify-center min-h-screen pt-10 pb-20 px-4">
                        <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full">
                            <!-- Header -->
                            <div class="bg-purple text-white px-6 py-4 rounded-t-lg flex justify-between items-center">
                                <h2 id="modalTitle" class="text-lg font-bold">Analisi Tecnica Dettagliata</h2>
                                <button onclick="closeTechnicalModal()" class="text-white hover:text-gray-200 text-2xl font-bold">&times;</button>
                            </div>

                            <!-- Content -->
                            <div id="modalContent" class="p-6">
                                <!-- Contenuto dinamico caricato via JavaScript -->
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Dati holdings per JavaScript -->
                <script>
                const technicalData = <?php echo json_encode($top_holdings, JSON_UNESCAPED_UNICODE); ?>;
                </script>
            </div>

            <!-- View: Dividends -->
