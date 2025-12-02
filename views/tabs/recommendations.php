            <div id="recommendations" class="view hidden">
                <div class="mb-6 sm:mb-10 flex justify-between items-center">
                    <h1 class="text-[18px] sm:text-[20px] font-medium text-primary tracking-tight">Raccomandazioni & Piano Operativo</h1>
                    <button id="refresh-signals-btn" class="px-4 py-2 bg-purple text-white text-xs font-semibold rounded hover:bg-purple-700 transition-colors">
                        <i class="fa-solid fa-refresh mr-1"></i>
                        Aggiorna
                    </button>
                </div>

                <!-- Loading State -->
                <div id="recommendations-loading" class="hidden mb-8 p-6 bg-blue-50 border border-blue-200 text-center">
                    <i class="fa-solid fa-spinner fa-spin text-blue-600 text-2xl mb-2"></i>
                    <p class="text-sm text-blue-700">Caricamento segnali in corso...</p>
                </div>

                <!-- Error State -->
                <div id="recommendations-error" class="hidden mb-8 p-6 bg-red-50 border border-red-200 text-center text-red-700"></div>

                <!-- Statistiche Overview -->
                <div id="signals-stats-overview" class="mb-8">
                    <!-- Popolato via JavaScript -->
                </div>

                <!-- Filtri -->
                <div class="mb-8">
                    <div class="widget-card p-4 bg-gray-50">
                        <div class="flex flex-wrap gap-4 items-center">
                            <div class="flex items-center gap-2">
                                <i class="fa-solid fa-filter text-gray-600 text-sm"></i>
                                <span class="text-xs font-medium text-gray-600 uppercase">Filtri:</span>
                            </div>
                            <div class="flex gap-2">
                                <select id="filter-urgency" class="px-3 py-1.5 text-xs border border-gray-300 rounded">
                                    <option value="all">Tutte le urgenze</option>
                                    <option value="IMMEDIATO">Immediato</option>
                                    <option value="QUESTA_SETTIMANA">Questa Settimana</option>
                                    <option value="PROSSIME_2_SETTIMANE">Prossime 2 Settimane</option>
                                    <option value="MONITORAGGIO">Monitoraggio</option>
                                </select>
                                <select id="filter-type" class="px-3 py-1.5 text-xs border border-gray-300 rounded">
                                    <option value="all">Tutti i tipi</option>
                                    <option value="BUY_LIMIT">Acquista</option>
                                    <option value="SELL_PARTIAL">Vendi Parziale</option>
                                    <option value="SET_STOP_LOSS">Stop Loss</option>
                                    <option value="SET_TAKE_PROFIT">Take Profit</option>
                                    <option value="REBALANCE">Ribilanciamento</option>
                                </select>
                                <select id="filter-status" class="px-3 py-1.5 text-xs border border-gray-300 rounded">
                                    <option value="ACTIVE">Attivi</option>
                                    <option value="EXECUTED">Eseguiti</option>
                                    <option value="EXPIRED">Scaduti</option>
                                    <option value="IGNORED">Ignorati</option>
                                    <option value="all">Tutti</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- AI Insights -->
                <div class="mb-8">
                    <div class="widget-ai-insight px-6 py-4 transition-all duration-300">
                        <div class="flex items-center gap-2.5 mb-3 pb-2.5 border-b border-purple/20">
                            <span class="badge-ai bg-purple text-white text-[9px] font-bold px-1.5 py-0.5 uppercase tracking-wide">Automazione Attiva</span>
                            <span class="text-[11px] font-medium text-purple uppercase tracking-wider">Segnali Generati da Workflow n8n</span>
                            <div class="tooltip-container ml-1">
                                <i class="fa-solid fa-circle-info text-purple/60 text-[9px] cursor-help"></i>
                                <div class="tooltip-content">Segnali generati automaticamente dai workflow di analisi tecnica e strategia Core-Satellite Risk Parity</div>
                            </div>
                        </div>
                        <div class="text-[13px] leading-relaxed text-gray-700 text-gray-500 italic">
                            <div class="pl-4 relative py-2">
                                <span class="absolute left-0 text-gray-400 font-bold">→</span>
                                I segnali vengono generati automaticamente ogni giorno alle 19:30 CET e in sessioni intraday (08:00, 13:00, 18:00 CET).
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
                                        <th class="px-4 py-3 text-left font-semibold text-gray-700 text-[11px] uppercase cursor-pointer" role="button">Urgenza</th>
                                        <th class="px-4 py-3 text-left font-semibold text-gray-700 text-[11px] uppercase cursor-pointer" role="button">Ticker</th>
                                        <th class="px-4 py-3 text-left font-semibold text-gray-700 text-[11px] uppercase cursor-pointer" role="button">Tipo</th>
                                        <th class="px-4 py-3 text-left font-semibold text-gray-700 text-[11px] uppercase cursor-pointer" role="button">Motivazione</th>
                                        <th class="px-4 py-3 text-right font-semibold text-gray-700 text-[11px] uppercase cursor-pointer" role="button">Prezzo Trigger</th>
                                        <th class="px-4 py-3 text-center font-semibold text-gray-700 text-[11px] uppercase cursor-pointer" role="button">Confidence</th>
                                        <th class="px-4 py-3 text-center font-semibold text-gray-700 text-[11px] uppercase cursor-pointer" role="button">Scadenza</th>
                                        <th class="px-4 py-3 text-center font-semibold text-gray-700 text-[11px] uppercase">Azioni</th>
                                    </tr>
                                </thead>
                                <tbody id="immediate-actions-tbody">
                                    <!-- Popolato via JavaScript -->
                                    <tr>
                                        <td colspan="8" class="px-4 py-8 text-center text-gray-500 italic">
                                            Caricamento segnali in corso...
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
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
                                    <div class="tooltip-content">Segnali con urgenza nelle prossime 2 settimane o da monitorare</div>
                                </div>
                            </div>
                        </div>
                        <div id="operational-plan-container" class="space-y-4">
                            <!-- Popolato via JavaScript -->
                            <div class="p-4 bg-gray-50 border border-gray-200 text-center text-gray-500 italic">
                                Caricamento piano operativo...
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- View: Flussi & Guadagni -->
