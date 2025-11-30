            <div id="technical" class="view hidden">
                <div class="mb-6 sm:mb-10">
                    <h1 class="text-[18px] sm:text-[20px] font-medium text-primary tracking-tight">Analisi Tecnica</h1>
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
                                        <td class="px-4 py-3 font-semibold text-purple whitespace-nowrap"><?php echo htmlspecialchars(
                                            $row["ticker"]
                                        ); ?></td>
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
                                        <td class="px-4 py-3 text-left text-gray-700 text-sm"><?php echo htmlspecialchars(
                                            $row["insight"] ?? ""
                                        ); ?></td>
                                    </tr>
                                    <?php
                                    endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- View: Dividends -->
