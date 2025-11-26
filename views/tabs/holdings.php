            <div id="holdings" class="view hidden">
                <div class="mb-6 sm:mb-10 flex justify-between items-center">
                    <h1 class="text-[18px] sm:text-[20px] font-medium text-primary tracking-tight">Holdings Dettagliate</h1>
                    <div class="flex gap-3">
                        <button onclick="openImportModal()" class="px-4 py-2 bg-gray-600 text-white text-[11px] font-semibold hover:bg-gray-700 transition-colors flex items-center gap-2">
                            <i class="fa-solid fa-file-import"></i>
                            Importa CSV
                        </button>
                        <button onclick="openHoldingModal()" class="px-4 py-2 bg-purple text-white text-[11px] font-semibold hover:bg-purple-dark transition-colors flex items-center gap-2">
                            <i class="fa-solid fa-plus"></i>
                            Aggiungi Posizione
                        </button>
                    </div>
                </div>

                <!-- Holdings Table -->
                <div class="widget-card widget-purple p-6">
                    <div class="flex justify-between items-center mb-5 pb-4 border-b border-gray-200">
                        <div class="flex items-center gap-2">
                            <i class="fa-solid fa-list text-purple text-sm"></i>
                            <span class="text-[11px] font-medium text-gray-600 uppercase tracking-wider">Tutte le Posizioni</span>
                            <div class="tooltip-container">
                                <i class="fa-solid fa-circle-info text-gray-400 text-[9px] cursor-help"></i>
                                <div class="tooltip-content">Lista completa degli asset in portafoglio con metriche in tempo reale</div>
                            </div>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table id="holdingsTable" class="w-full text-sm sortable-table">
                            <thead class="bg-gray-50 border-b border-gray-200">
                                <tr>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-700 text-[11px] uppercase cursor-pointer whitespace-nowrap" role="button">Ticker</th>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-700 text-[11px] uppercase cursor-pointer min-w-[200px]" role="button">Nome</th>
                                    <th class="px-4 py-3 text-right font-semibold text-gray-700 text-[11px] uppercase cursor-pointer whitespace-nowrap" role="button">Quantità</th>
                                    <th class="px-4 py-3 text-right font-semibold text-gray-700 text-[11px] uppercase cursor-pointer whitespace-nowrap" role="button">Prezzo Medio</th>
                                    <th class="px-4 py-3 text-right font-semibold text-gray-700 text-[11px] uppercase cursor-pointer whitespace-nowrap" role="button">Prezzo Attuale</th>
                                    <th class="px-4 py-3 text-right font-semibold text-gray-700 text-[11px] uppercase cursor-pointer whitespace-nowrap" role="button">Valore</th>
                                    <th class="px-4 py-3 text-right font-semibold text-gray-700 text-[11px] uppercase cursor-pointer whitespace-nowrap" role="button">P&L €</th>
                                    <th class="px-4 py-3 text-right font-semibold text-gray-700 text-[11px] uppercase cursor-pointer whitespace-nowrap" role="button">P&L %</th>
                                    <th class="px-4 py-3 text-right font-semibold text-gray-700 text-[11px] uppercase cursor-pointer whitespace-nowrap" role="button">Allocation</th>
                                    <th class="px-4 py-3 text-right font-semibold text-gray-700 text-[11px] uppercase cursor-pointer whitespace-nowrap" role="button">Target %</th>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-700 text-[11px] uppercase min-w-[150px]">Note</th>
                                    <th class="px-4 py-3 text-center font-semibold text-gray-700 text-[11px] uppercase whitespace-nowrap">Azioni</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($top_holdings as $holding): ?>
                                <tr class="border-b border-gray-200 hover:bg-gray-50" data-isin="<?php echo htmlspecialchars($holding['isin']); ?>">
                                    <td class="px-4 py-3 font-semibold text-purple whitespace-nowrap"><?php echo htmlspecialchars($holding['ticker']); ?></td>
                                    <td class="px-4 py-3">
                                        <div class="font-medium text-gray-800"><?php echo htmlspecialchars($holding['name']); ?></div>
                                        <div class="text-[11px] text-gray-500 whitespace-nowrap"><?php echo htmlspecialchars($holding['asset_class']); ?></div>
                                    </td>
                                    <td class="px-4 py-3 text-right whitespace-nowrap"><?php echo number_format($holding['quantity'], 2, ',', '.'); ?></td>
                                    <td class="px-4 py-3 text-right whitespace-nowrap">€<?php echo number_format($holding['avg_price'], 2, ',', '.'); ?></td>
                                    <td class="px-4 py-3 text-right whitespace-nowrap">€<?php echo number_format($holding['current_price'], 2, ',', '.'); ?></td>
                                    <td class="px-4 py-3 text-right font-semibold whitespace-nowrap">€<?php echo number_format($holding['market_value'], 2, ',', '.'); ?></td>
                                    <td class="px-4 py-3 text-right whitespace-nowrap">
                                        <div class="font-semibold <?php echo $holding['unrealized_pnl'] >= 0 ? 'text-positive' : 'text-negative'; ?>">
                                            <?php echo $holding['unrealized_pnl'] >= 0 ? '+' : ''; ?>€<?php echo number_format($holding['unrealized_pnl'], 2, ',', '.'); ?>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-right whitespace-nowrap">
                                        <div class="font-semibold <?php echo $holding['pnl_percentage'] >= 0 ? 'text-positive' : 'text-negative'; ?>">
                                            <?php echo $holding['pnl_percentage'] >= 0 ? '+' : ''; ?><?php echo number_format($holding['pnl_percentage'], 2, ',', '.'); ?>%
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-right whitespace-nowrap">
                                        <span class="px-2 py-1 text-[11px] <?php echo $holding['drift'] < 0 ? 'bg-yellow-100 text-yellow-700' : 'bg-green-100 text-green-700'; ?>">
                                            <?php echo number_format($holding['current_allocation'], 2, ',', '.'); ?>%
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-right text-gray-600 whitespace-nowrap">
                                        <?php echo isset($holding['target_allocation']) && $holding['target_allocation'] > 0 ? number_format($holding['target_allocation'], 2, ',', '.') . '%' : '-'; ?>
                                    </td>
                                    <td class="px-4 py-3 text-left">
                                        <div class="text-[11px] text-gray-600 max-w-xs truncate" title="<?php echo htmlspecialchars($holding['notes'] ?? ''); ?>">
                                            <?php echo !empty($holding['notes']) ? htmlspecialchars($holding['notes']) : '-'; ?>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-center whitespace-nowrap">
                                        <div class="flex gap-2 justify-center">
                                            <button onclick='editHolding(<?php echo json_encode($holding); ?>)' class="text-purple hover:text-purple-dark text-sm" title="Modifica">
                                                <i class="fa-solid fa-pen-to-square"></i>
                                            </button>
                                            <button onclick="deleteHolding('<?php echo htmlspecialchars($holding['isin']); ?>', '<?php echo htmlspecialchars($holding['ticker']); ?>')" class="text-red-600 hover:text-red-800 text-sm" title="Elimina">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Modal: Add/Edit Holding -->
            <div id="holdingModal" class="fixed inset-0 bg-black/50 z-50 hidden flex items-center justify-center p-4">
                <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
                    <div class="p-6 border-b border-gray-200">
                        <div class="flex justify-between items-center">
                            <h2 class="text-lg font-semibold text-primary" id="modalTitle">Aggiungi Posizione</h2>
                            <button onclick="closeHoldingModal()" class="text-gray-400 hover:text-gray-600">
                                <i class="fa-solid fa-xmark text-xl"></i>
                            </button>
                        </div>
                    </div>
                    <form id="holdingForm" class="p-6">
                        <input type="hidden" id="editMode" value="false">

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- ISIN -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">ISIN *</label>
                                <input type="text" id="isin" name="isin" required class="w-full px-3 py-2 border border-gray-300 focus:ring-purple focus:border-purple text-sm" placeholder="IE00B3RBWM25">
                            </div>

                            <!-- Ticker -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Ticker *</label>
                                <input type="text" id="ticker" name="ticker" required class="w-full px-3 py-2 border border-gray-300 focus:ring-purple focus:border-purple text-sm" placeholder="IWDA">
                            </div>

                            <!-- Nome -->
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Nome *</label>
                                <input type="text" id="name" name="name" required class="w-full px-3 py-2 border border-gray-300 focus:ring-purple focus:border-purple text-sm" placeholder="iShares Core MSCI World UCITS ETF">
                            </div>

                            <!-- Quantità -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Quantità *</label>
                                <input type="number" id="quantity" name="quantity" step="0.01" required class="w-full px-3 py-2 border border-gray-300 focus:ring-purple focus:border-purple text-sm" placeholder="100.00">
                            </div>

                            <!-- Prezzo Medio -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Prezzo Medio Carico € *</label>
                                <input type="number" id="avg_price" name="avg_price" step="0.01" required class="w-full px-3 py-2 border border-gray-300 focus:ring-purple focus:border-purple text-sm" placeholder="75.20">
                            </div>

                            <!-- Target Allocation -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Target Allocation %</label>
                                <input type="number" id="target_allocation" name="target_allocation" step="0.01" class="w-full px-3 py-2 border border-gray-300 focus:ring-purple focus:border-purple text-sm" placeholder="25.00">
                            </div>

                            <!-- Asset Class -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Asset Class</label>
                                <input type="text" id="asset_class" name="asset_class" class="w-full px-3 py-2 border border-gray-300 focus:ring-purple focus:border-purple text-sm" placeholder="Equity Global">
                            </div>

                            <!-- Note -->
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Note</label>
                                <textarea id="notes" name="notes" rows="3" class="w-full px-3 py-2 border border-gray-300 focus:ring-purple focus:border-purple text-sm" placeholder="Note opzionali..."></textarea>
                            </div>
                        </div>

                        <div class="mt-6 flex justify-end gap-3">
                            <button type="button" onclick="closeHoldingModal()" class="px-4 py-2 text-gray-600 hover:text-gray-800 text-sm font-medium">
                                Annulla
                            </button>
                            <button type="submit" class="px-4 py-2 bg-purple text-white text-sm font-semibold hover:bg-purple-dark transition-colors">
                                <i class="fa-solid fa-save mr-1"></i> Salva
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Modal: Import CSV -->
            <div id="importModal" class="fixed inset-0 bg-black/50 z-50 hidden flex items-center justify-center p-4">
                <div class="bg-white rounded-lg shadow-xl max-w-3xl w-full">
                    <div class="p-6 border-b border-gray-200">
                        <div class="flex justify-between items-center">
                            <h2 class="text-lg font-semibold text-primary">Importa CSV Fineco</h2>
                            <button onclick="closeImportModal()" class="text-gray-400 hover:text-gray-600">
                                <i class="fa-solid fa-xmark text-xl"></i>
                            </button>
                        </div>
                    </div>
                    <div class="p-6">
                        <div class="mb-4 p-4 bg-blue-50 border border-blue-200 text-sm text-blue-800">
                            <i class="fa-solid fa-info-circle mr-2"></i>
                            <strong>Formato richiesto:</strong> CSV esportato da Fineco con separatore punto e virgola (;)
                        </div>

                        <form id="importForm">
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Seleziona file CSV</label>
                                <input type="file" id="csvFile" name="csv_file" accept=".csv" required class="w-full px-3 py-2 border border-gray-300 focus:ring-purple focus:border-purple text-sm">
                            </div>

                            <div class="mb-4">
                                <label class="flex items-center">
                                    <input type="checkbox" id="overwriteConfirm" class="mr-2">
                                    <span class="text-sm text-gray-700">Confermo di voler <strong class="text-red-600">sovrascrivere</strong> tutte le posizioni esistenti</span>
                                </label>
                            </div>

                            <div class="flex justify-end gap-3">
                                <button type="button" onclick="closeImportModal()" class="px-4 py-2 text-gray-600 hover:text-gray-800 text-sm font-medium">
                                    Annulla
                                </button>
                                <button type="submit" class="px-4 py-2 bg-purple text-white text-sm font-semibold hover:bg-purple-dark transition-colors">
                                    <i class="fa-solid fa-upload mr-1"></i> Importa
                                </button>
                            </div>
                        </form>

                        <div id="importResult" class="mt-4 hidden"></div>
                    </div>
                </div>
            </div>

            <!-- View: Performance & Flussi -->
