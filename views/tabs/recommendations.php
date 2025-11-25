            <div id="recommendations" class="view hidden">
                <div class="mb-6 sm:mb-10">
                    <h1 class="text-[18px] sm:text-[20px] font-medium text-primary tracking-tight">Raccomandazioni & Piano Operativo</h1>
                </div>

                <!-- AI Insights per Recommendations -->
                <div class="mb-8">
                    <div class="widget-ai-insight px-6 py-4 transition-all duration-300">
                        <div class="flex items-center gap-2.5 mb-3 pb-2.5 border-b border-purple/20">
                            <span class="badge-ai bg-purple text-white text-[9px] font-bold px-1.5 py-0.5 uppercase tracking-wide">AI Insight</span>
                            <span class="text-[11px] font-medium text-purple uppercase tracking-wider">Strategia Consigliata</span>
                            <div class="tooltip-container ml-1">
                                <i class="fa-solid fa-circle-info text-purple/60 text-[9px] cursor-help"></i>
                                <div class="tooltip-content">Analisi automatica delle opportunità operative basata su segnali tecnici e allocazione target</div>
                            </div>
                        </div>
                        <div class="text-[13px] leading-relaxed text-gray-700">
                            <div class="pl-4 relative py-2 border-b border-dashed border-gray-300">
                                <span class="absolute left-0 text-purple font-bold">→</span>
                                Focus su accumulo ETF core (VWCE, VUSA) approfittando dei ribassi. Diversificare su emerging con EIMI.
                            </div>
                            <div class="pl-4 relative py-2">
                                <span class="absolute left-0 text-danger font-bold">→</span>
                                Evitare movimenti impulsivi. Mantenere DCA settimanale e attendere conferme tecniche per incrementi straordinari.
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Widget: Azioni Immediate -->
                <div class="mb-8">
                    <div class="widget-card widget-purple p-6">
                        <div class="flex justify-between items-center mb-5 pb-4 border-b border-gray-200">
                            <div class="flex items-center gap-2">
                                <i class="fa-solid fa-bolt text-purple text-sm"></i>
                                <span class="text-[11px] font-medium text-gray-600 uppercase tracking-wider">Azioni Immediate</span>
                                <div class="tooltip-container">
                                    <i class="fa-solid fa-circle-info text-gray-400 text-[9px] cursor-help"></i>
                                    <div class="tooltip-content">Operazioni prioritarie suggerite in base all'analisi tecnica e alla strategia di portafoglio</div>
                                </div>
                            </div>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm sortable-table">
                                <thead class="bg-gray-50 border-b border-gray-200">
                                    <tr>
                                        <th class="px-4 py-3 text-left font-semibold text-gray-700 text-[11px] uppercase cursor-pointer" role="button">Priorità</th>
                                        <th class="px-4 py-3 text-left font-semibold text-gray-700 text-[11px] uppercase cursor-pointer" role="button">Ticker</th>
                                        <th class="px-4 py-3 text-left font-semibold text-gray-700 text-[11px] uppercase cursor-pointer" role="button">Azione</th>
                                        <th class="px-4 py-3 text-left font-semibold text-gray-700 text-[11px] uppercase cursor-pointer" role="button">Motivazione</th>
                                        <th class="px-4 py-3 text-right font-semibold text-gray-700 text-[11px] uppercase cursor-pointer" role="button">Target</th>
                                        <th class="px-4 py-3 text-right font-semibold text-gray-700 text-[11px] uppercase cursor-pointer" role="button">Qty</th>
                                        <th class="px-4 py-3 text-right font-semibold text-gray-700 text-[11px] uppercase cursor-pointer" role="button">Importo</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="border-b border-gray-200 hover:bg-gray-50">
                                        <td class="px-4 py-3"><span class="px-2 py-1 bg-red-100 text-red-700 text-xs font-bold">ALTA</span></td>
                                        <td class="px-4 py-3 font-semibold text-purple">VWCE</td>
                                        <td class="px-4 py-3"><span class="px-2 py-1 bg-green-100 text-green-700 text-xs font-semibold">ACCUMULA</span></td>
                                        <td class="px-4 py-3 text-xs">Momentum positivo, RSI 68, EMA rialzista</td>
                                        <td class="px-4 py-3 text-right">€115.80</td>
                                        <td class="px-4 py-3 text-right">2</td>
                                        <td class="px-4 py-3 text-right font-semibold">€231.60</td>
                                    </tr>
                                    <tr class="border-b border-gray-200 hover:bg-gray-50">
                                        <td class="px-4 py-3"><span class="px-2 py-1 bg-yellow-100 text-yellow-700 text-xs font-bold">MEDIA</span></td>
                                        <td class="px-4 py-3 font-semibold text-purple">EIMI</td>
                                        <td class="px-4 py-3"><span class="px-2 py-1 bg-gray-100 text-gray-700 text-xs font-semibold">HOLD</span></td>
                                        <td class="px-4 py-3 text-xs">Consolidamento, attendere breakout €30</td>
                                        <td class="px-4 py-3 text-right">€30.00</td>
                                        <td class="px-4 py-3 text-right">-</td>
                                        <td class="px-4 py-3 text-right font-semibold">-</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Widget: Nuovi ETF Raccomandati -->
                <div class="mb-8">
                    <div class="widget-card widget-purple p-6">
                        <div class="flex justify-between items-center mb-5 pb-4 border-b border-gray-200">
                            <div class="flex items-center gap-2">
                                <i class="fa-solid fa-star text-purple text-sm"></i>
                                <span class="text-[11px] font-medium text-gray-600 uppercase tracking-wider">Nuovi ETF Raccomandati</span>
                                <span class="px-2 py-0.5 bg-green-100 text-green-700 text-[9px] font-semibold ml-2">Zero Commissioni Fineco</span>
                                <div class="tooltip-container">
                                    <i class="fa-solid fa-circle-info text-gray-400 text-[9px] cursor-help"></i>
                                    <div class="tooltip-content">ETF suggeriti dal workflow n8n con profilo zero commissioni su Fineco e buone prospettive</div>
                                </div>
                            </div>
                        </div>
                        <div class="space-y-4">
                            <?php foreach ($opportunities as $opp): ?>
                            <div class="p-4 border border-gray-200 bg-gray-50">
                                <div class="flex justify-between items-start mb-3">
                                    <div>
                                        <div class="font-bold text-primary text-base"><?php echo htmlspecialchars($opp['ticker']); ?></div>
                                        <div class="text-sm text-gray-600 mt-1"><?php echo htmlspecialchars($opp['name']); ?></div>
                                    </div>
                                    <span class="px-2 py-1 text-[11px] font-semibold <?php echo $opp['signal'] === 'BUY' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700'; ?>">
                                        <?php echo $opp['signal']; ?>
                                    </span>
                                </div>
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-3 text-xs">
                                    <div><span class="text-gray-500">TER:</span> <strong><?php echo number_format($opp['expense_ratio'], 2, ',', '.'); ?>%</strong></div>
                                    <div><span class="text-gray-500">AUM:</span> <strong>€2.5B</strong></div>
                                    <div><span class="text-gray-500">Yield:</span> <strong><?php echo number_format($opp['yield'], 2, ',', '.'); ?>%</strong></div>
                                    <div><span class="text-gray-500">YTD:</span> <strong class="text-positive">+18.5%</strong></div>
                                </div>
                                <div class="text-xs text-gray-600 mb-3 p-3 bg-white border-l-2 border-purple">
                                    <i class="fa-solid fa-info-circle text-purple mr-1"></i>
                                    <?php echo htmlspecialchars($opp['reason']); ?>
                                </div>
                                <div class="flex gap-4 text-xs">
                                    <div><span class="text-gray-500">Entry Price:</span> <strong>€<?php echo number_format($opp['entry_price'], 2, ',', '.'); ?></strong></div>
                                    <div><span class="text-gray-500">Target:</span> <strong>€<?php echo number_format($opp['target_price'], 2, ',', '.'); ?></strong></div>
                                    <div><span class="text-gray-500">Upside:</span> <strong class="text-positive">+<?php echo number_format((($opp['target_price'] - $opp['entry_price']) / $opp['entry_price']) * 100, 1, ',', '.'); ?>%</strong></div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- Widget: Piano Operativo Temporale -->
                <div class="mb-8">
                    <div class="widget-card widget-purple p-6">
                        <div class="flex justify-between items-center mb-5 pb-4 border-b border-gray-200">
                            <div class="flex items-center gap-2">
                                <i class="fa-solid fa-calendar-days text-purple text-sm"></i>
                                <span class="text-[11px] font-medium text-gray-600 uppercase tracking-wider">Piano Operativo Temporale</span>
                                <div class="tooltip-container">
                                    <i class="fa-solid fa-circle-info text-gray-400 text-[9px] cursor-help"></i>
                                    <div class="tooltip-content">Roadmap operativa con timeline settimanale per l'esecuzione graduale della strategia</div>
                                </div>
                            </div>
                        </div>
                        <div class="space-y-4">
                            <div class="p-4 border-l-4 border-purple bg-purple-50">
                                <div class="font-semibold text-primary mb-2">Settimana 1-2: Fase Accumulo</div>
                                <ul class="text-xs text-gray-700 space-y-1 ml-4">
                                    <li>• VWCE: incremento 2 quote se price ≤ €115.80</li>
                                    <li>• VUSA: monitorare per entry point sotto €110</li>
                                    <li>• Mantenere liquidità 15% per opportunità</li>
                                </ul>
                            </div>
                            <div class="p-4 border-l-4 border-gray-400 bg-gray-50">
                                <div class="font-semibold text-primary mb-2">Settimana 3-4: Fase Consolidamento</div>
                                <ul class="text-xs text-gray-700 space-y-1 ml-4">
                                    <li>• EIMI: attendere breakout €30 per accumulo</li>
                                    <li>• Verificare stacco dividendi VWCE (15 Dic)</li>
                                    <li>• Valutare ribilanciamento se drift >5%</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Widget: Avvertenze e Rischi -->
                <div class="mb-8">
                    <div class="widget-card widget-negative p-6">
                        <div class="flex justify-between items-center mb-5 pb-4 border-b border-gray-200">
                            <div class="flex items-center gap-2">
                                <i class="fa-solid fa-triangle-exclamation text-purple text-sm"></i>
                                <span class="text-[11px] font-medium text-gray-600 uppercase tracking-wider">Avvertenze e Rischi</span>
                                <div class="tooltip-container">
                                    <i class="fa-solid fa-circle-info text-gray-400 text-[9px] cursor-help"></i>
                                    <div class="tooltip-content">Fattori di rischio macro e di portafoglio da monitorare attentamente prima di operare</div>
                                </div>
                            </div>
                        </div>
                        <div class="space-y-4">
                            <div class="p-4 bg-red-50 border border-red-200">
                                <div class="font-semibold text-danger mb-2">Rischi Macro</div>
                                <p class="text-xs text-gray-700">Volatilità attesa per dicembre legata a decisioni Fed e BCE. Possibili correzioni 3-5% su equity globale.</p>
                            </div>
                            <div class="p-4 bg-yellow-50 border border-yellow-200">
                                <div class="font-semibold text-warning mb-2">Rischi Portafoglio</div>
                                <p class="text-xs text-gray-700">Concentrazione su equity globale (87%). Considerare diversificazione su bond o commodities se risk appetite diminuisce.</p>
                            </div>
                            <div class="p-4 bg-gray-50 border border-gray-200">
                                <div class="font-semibold text-primary mb-2">Note Operative</div>
                                <p class="text-xs text-gray-700">DCA settimanale consigliato. Evitare lump sum oltre €500. Mantenere sempre 10-15% liquidità per opportunità.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- View: Flussi & Guadagni -->
