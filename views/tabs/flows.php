            <div id="flows" class="view hidden">
                <div class="mb-6 sm:mb-10">
                    <h1 class="text-[18px] sm:text-[20px] font-medium text-primary tracking-tight">Flussi &amp; Allocazioni</h1>
                </div>

                <!-- Grafico Evoluzione Allocazione -->
                <div class="mb-8 widget-card p-6">
                    <div class="flex justify-between items-center mb-5 pb-4 border-b border-gray-200">
                        <div class="flex items-center gap-2">
                            <i class="fa-solid fa-chart-pie text-purple text-sm"></i>
                            <span class="text-[11px] font-medium text-gray-600 uppercase tracking-wider">Evoluzione Allocazione nel Tempo</span>
                        </div>
                    </div>
                    <div id="allocationHistoryLoading" class="text-center py-8">
                        <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-purple border-t-transparent"></div>
                        <p class="text-gray-600 mt-2 text-sm">Caricamento dati allocazione...</p>
                    </div>
                    <div id="allocationHistoryError" class="hidden text-center py-8">
                        <p class="text-red-600">Errore nel caricamento dati allocazione</p>
                    </div>
                    <div id="allocationHistoryEmpty" class="hidden text-center py-8">
                        <p class="text-gray-600">Nessuno storico allocazione disponibile</p>
                    </div>
                    <div id="allocationHistoryChart" class="hidden">
                        <div class="relative h-[350px]">
                            <canvas id="allocationEvolutionChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Timeline Transazioni -->
                <div class="mb-8 widget-card p-6">
                    <div class="flex justify-between items-center mb-5 pb-4 border-b border-gray-200">
                        <div class="flex items-center gap-2">
                            <i class="fa-solid fa-clock-rotate-left text-purple text-sm"></i>
                            <span class="text-[11px] font-medium text-gray-600 uppercase tracking-wider">Timeline Transazioni</span>
                        </div>
                        <span class="text-[11px] text-gray-500"><?php echo count(
                            $transactions
                        ); ?> eventi</span>
                    </div>
                    <?php if (empty($transactions)): ?>
                        <div class="p-6 text-center text-gray-500 italic">
                            Nessuna transazione registrata. Aggiungi/modifica una posizione o attendi payout dividendi.
                        </div>
                    <?php else: ?>
                        <div class="overflow-x-hidden">
                            <table class="w-full text-sm">
                                <thead class="bg-gray-50 border-b border-gray-200">
                                    <tr>
                                        <th class="px-4 py-3 text-left font-semibold text-gray-700 text-[11px] uppercase">Data</th>
                                        <th class="px-4 py-3 text-left font-semibold text-gray-700 text-[11px] uppercase">Tipo</th>
                                        <th class="px-4 py-3 text-left font-semibold text-gray-700 text-[11px] uppercase">Ticker</th>
                                        <th class="px-4 py-3 text-right font-semibold text-gray-700 text-[11px] uppercase">Quantità</th>
                                        <th class="px-4 py-3 text-right font-semibold text-gray-700 text-[11px] uppercase">Importo</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($transactions as $tx):

                                        $dateValue =
                                            $tx["date"] ??
                                            ($tx["transaction_date"] ??
                                                ($tx["timestamp"] ?? null));
                                        $dateStr = $dateValue
                                            ? date("d/m/Y", strtotime($dateValue))
                                            : "-";
                                        $amount = $tx["amount"] ?? 0;
                                        $qty = $tx["quantity"] ?? ($tx["quantity_change"] ?? 0);
                                        $type = strtoupper($tx["type"] ?? "-");
                                        $isOutflow = in_array($type, ["SELL", "WITHDRAWAL", "FEE"], true);
                                        $displayAmount = $isOutflow ? -abs($amount) : abs($amount);
                                        $isPositive = $displayAmount >= 0;
                                        ?>
                                        <tr class="border-b border-gray-200 hover:bg-gray-50">
                                            <td class="px-4 py-3 text-sm text-gray-700"><?php echo htmlspecialchars(
                                                $dateStr
                                            ); ?></td>
                                            <td class="px-4 py-3 font-semibold text-primary"><?php echo htmlspecialchars(
                                                $type
                                            ); ?></td>
                                            <td class="px-4 py-3 font-medium text-gray-800"><?php echo htmlspecialchars(
                                                $tx["ticker"] ?? "-"
                                            ); ?></td>
                                            <td class="px-4 py-3 text-right text-gray-700"><?php echo number_format(
                                                $qty,
                                                2,
                                                ",",
                                                "."
                                            ); ?></td>
                                            <td class="px-4 py-3 text-right font-semibold <?php echo $isPositive
                                                ? "text-positive"
                                                : "text-negative"; ?>">
                                                <?php echo $isPositive ? "+" : ""; ?>€<?php echo number_format(
    $displayAmount,
    2,
    ",",
    "."
); ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
