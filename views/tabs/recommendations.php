            <div id="recommendations" class="view hidden">
                <div class="mb-6 sm:mb-10">
                    <h1 class="text-[18px] sm:text-[20px] font-medium text-primary tracking-tight">Raccomandazioni & Piano Operativo</h1>
                </div>

                <!-- AI Insights per Recommendations -->
                <?php if (!empty($recommendations['immediate_actions']) || !empty($recommendations['operational_plan']) || !empty($recommendations['warnings_risks'])): ?>
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
                        <div class="text-[13px] leading-relaxed text-gray-700 text-gray-500 italic">
                            <div class="pl-4 relative py-2">
                                <span class="absolute left-0 text-gray-400 font-bold">→</span>
                                L'analisi strategica verrà generata automaticamente dal workflow AI una volta disponibili i dati.
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

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
                                    <?php if (empty($recommendations['immediate_actions'])): ?>
                                        <tr>
                                            <td colspan="7" class="px-4 py-8 text-center text-gray-500 italic">
                                                Nessuna azione immediata suggerita. Le raccomandazioni verranno generate dal workflow AI.
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($recommendations['immediate_actions'] as $action): ?>
                                            <?php
                                            $priorityClass = $action['priority'] === 'ALTA' ? 'bg-red-100 text-red-700' : ($action['priority'] === 'MEDIA' ? 'bg-yellow-100 text-yellow-700' : 'bg-gray-100 text-gray-700');
                                            $actionClass = $action['action'] === 'ACCUMULA' || $action['action'] === 'BUY' ? 'bg-green-100 text-green-700' : ($action['action'] === 'SELL' ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-700');
                                            ?>
                                            <tr class="border-b border-gray-200 hover:bg-gray-50">
                                                <td class="px-4 py-3"><span class="px-2 py-1 <?php echo $priorityClass; ?> text-xs font-bold"><?php echo htmlspecialchars($action['priority']); ?></span></td>
                                                <td class="px-4 py-3 font-semibold text-purple"><?php echo htmlspecialchars($action['ticker']); ?></td>
                                                <td class="px-4 py-3"><span class="px-2 py-1 <?php echo $actionClass; ?> text-xs font-semibold"><?php echo htmlspecialchars($action['action']); ?></span></td>
                                                <td class="px-4 py-3 text-xs"><?php echo htmlspecialchars($action['reason']); ?></td>
                                                <td class="px-4 py-3 text-right"><?php echo isset($action['target_price']) ? '€' . number_format($action['target_price'], 2, ',', '.') : '-'; ?></td>
                                                <td class="px-4 py-3 text-right"><?php echo isset($action['quantity']) ? $action['quantity'] : '-'; ?></td>
                                                <td class="px-4 py-3 text-right font-semibold"><?php echo isset($action['amount']) ? '€' . number_format($action['amount'], 2, ',', '.') : '-'; ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
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
                            <?php if (empty($recommendations['operational_plan'])): ?>
                                <div class="p-4 bg-gray-50 border border-gray-200 text-center text-gray-500 italic">
                                    Nessun piano operativo disponibile. La roadmap verrà generata dal workflow AI.
                                </div>
                            <?php else: ?>
                                <?php foreach ($recommendations['operational_plan'] as $plan): ?>
                                    <?php
                                    $borderColor = $plan['priority'] === 'high' ? 'border-purple' : 'border-gray-400';
                                    $bgColor = $plan['priority'] === 'high' ? 'bg-purple-50' : 'bg-gray-50';
                                    ?>
                                    <div class="p-4 border-l-4 <?php echo $borderColor . ' ' . $bgColor; ?>">
                                        <div class="font-semibold text-primary mb-2"><?php echo htmlspecialchars($plan['period']); ?></div>
                                        <ul class="text-xs text-gray-700 space-y-1 ml-4">
                                            <?php foreach ($plan['tasks'] as $task): ?>
                                                <li>• <?php echo htmlspecialchars($task); ?></li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
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
                            <?php if (empty($recommendations['warnings_risks'])): ?>
                                <div class="p-4 bg-gray-50 border border-gray-200 text-center text-gray-500 italic">
                                    Nessun avviso di rischio disponibile. L'analisi dei rischi verrà generata dal workflow AI.
                                </div>
                            <?php else: ?>
                                <?php foreach ($recommendations['warnings_risks'] as $risk): ?>
                                    <?php
                                    $bgColor = 'bg-gray-50';
                                    $borderColor = 'border-gray-200';
                                    $titleColor = 'text-primary';

                                    if ($risk['severity'] === 'high') {
                                        $bgColor = 'bg-red-50';
                                        $borderColor = 'border-red-200';
                                        $titleColor = 'text-danger';
                                    } elseif ($risk['severity'] === 'medium') {
                                        $bgColor = 'bg-yellow-50';
                                        $borderColor = 'border-yellow-200';
                                        $titleColor = 'text-warning';
                                    }
                                    ?>
                                    <div class="p-4 <?php echo $bgColor . ' border ' . $borderColor; ?>">
                                        <div class="font-semibold <?php echo $titleColor; ?> mb-2"><?php echo htmlspecialchars($risk['title']); ?></div>
                                        <p class="text-xs text-gray-700"><?php echo htmlspecialchars($risk['description']); ?></p>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- View: Flussi & Guadagni -->
