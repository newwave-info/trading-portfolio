            <div id="holdings" class="view hidden">
                <div class="mb-6 sm:mb-10">
                    <h1 class="text-[18px] sm:text-[20px] font-medium text-primary tracking-tight">Holdings Dettagliate</h1>
                </div>

                <!-- Holdings Table -->
                <div class="widget-card widget-purple p-6">
                    <div class="flex justify-between items-center mb-5 pb-4 border-b border-gray-200">
                        <div class="flex items-center gap-2">
                            <i class="fa-solid fa-list text-purple text-sm"></i>
                            <span class="text-[11px] font-medium text-gray-600 uppercase tracking-wider">Tutte le Posizioni</span>
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
                                    <th class="px-4 py-3 text-right font-semibold text-gray-700 text-[11px] uppercase cursor-pointer" role="button">P&L €</th>
                                    <th class="px-4 py-3 text-right font-semibold text-gray-700 text-[11px] uppercase cursor-pointer" role="button">P&L %</th>
                                    <th class="px-4 py-3 text-right font-semibold text-gray-700 text-[11px] uppercase cursor-pointer" role="button">Allocation</th>
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
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <div class="font-semibold text-positive">+<?php echo number_format($holding['pnl_percentage'], 2, ',', '.'); ?>%</div>
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <span class="px-2 py-1 text-[11px] <?php echo $holding['drift'] < 0 ? 'bg-yellow-100 text-yellow-700' : 'bg-green-100 text-green-700'; ?>">
                                            <?php echo number_format($holding['current_allocation'], 2, ',', '.'); ?>%
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- View: Performance & Flussi -->
