            <div id="dividends" class="view hidden">
                <div class="mb-6 sm:mb-10">
                    <h1 class="text-[18px] sm:text-[20px] font-medium text-primary tracking-tight">Calendario Dividendi</h1>
                </div>

                <!-- Summary Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 mb-8">
                    <?php
                    $startYear = date("Y") . "-01-01";
                    $dividendsYtd = array_filter($dividends, function ($d) use (
                        $startYear
                    ) {
                        if (($d["status"] ?? "") !== "RECEIVED") {
                            return false;
                        }
                        $ex = $d["ex_date"] ?? null;
                        if (!$ex) {
                            return false;
                        }
                        return $ex >= $startYear;
                    });
                    $totalYtd = array_sum(
                        array_column($dividendsYtd, "amount")
                    );
                    $receivedCount = count($dividendsYtd);
                    $countClass =
                        $receivedCount > 0 ? "text-positive" : "text-gray-500";
                    ?>
                    <div class="widget-card widget-purple p-6">
                        <div class="text-[11px] text-gray-500 mb-2 uppercase tracking-wider">Totale Dividendi (Ricevuti YTD)</div>
                        <div class="text-2xl font-bold text-primary">€<?php echo number_format(
                            $totalYtd,
                            2,
                            ",",
                            "."
                        ); ?></div>
                        <div class="text-[11px] <?php echo $countClass; ?> mt-1"><?php echo $receivedCount; ?> pagamenti</div>
                    </div>
                    <div class="widget-card widget-purple p-6">
                        <div class="text-[11px] text-gray-500 mb-2 uppercase tracking-wider">Previsto 12 Mesi</div>
                        <div class="text-xl font-bold text-primary">
                            <?php echo $dividends_calendar_data["forecast_6m"][
                                "total_amount"
                            ] !== null
                                ? "€" .
                                    number_format(
                                        $dividends_calendar_data["forecast_6m"][
                                            "total_amount"
                                        ],
                                        2,
                                        ",",
                                        "."
                                    )
                                : "-"; ?>
                        </div>
                        <div class="text-[11px] text-gray-500 mt-1"><?php echo htmlspecialchars(
                            $dividends_calendar_data["forecast_6m"]["period"]
                        ); ?></div>
                    </div>
                    <div class="widget-card widget-purple p-6">
                        <div class="text-[11px] text-gray-500 mb-2 uppercase tracking-wider">Yield Medio Portafoglio</div>
                        <div class="text-xl font-bold text-primary">
                            <?php echo $dividends_calendar_data[
                                "portfolio_yield"
                            ] !== null
                                ? number_format(
                                        $dividends_calendar_data[
                                            "portfolio_yield"
                                        ],
                                        1,
                                        ",",
                                        "."
                                    ) . "%"
                                : "-"; ?>
                        </div>
                        <div class="text-[11px] text-gray-500 mt-1">Annualizzato</div>
                    </div>
                    <div class="widget-card widget-purple p-6">
                        <div class="text-[11px] text-gray-500 mb-2 uppercase tracking-wider">Prossimo Stacco</div>
                        <div class="text-xl font-bold text-primary">
                            <?php
                            $nd = $dividends_calendar_data["next_dividend"];
                            echo $nd["date"] !== "-"
                                ? date("d/m/Y", strtotime($nd["date"]))
                                : "-";
                            ?>
                        </div>
                        <div class="text-[11px] text-gray-500 mt-1">
                            <?php
                            $nextDiv =
                                $dividends_calendar_data["next_dividend"];
                            echo htmlspecialchars($nextDiv["ticker"]) . " ";
                            echo $nextDiv["amount"] !== null
                                ? "€" .
                                    number_format(
                                        $nextDiv["amount"],
                                        2,
                                        ",",
                                        "."
                                    )
                                : "-";
                            ?>
                        </div>
                    </div>
                </div>

                <!-- AI Insights per Dividends -->
                <?php if ($dividends_calendar_data["ai_insight"] !== "-"): ?>
                <div class="mb-8">
                    <div class="widget-ai-insight px-6 py-4 transition-all duration-300">
                        <div class="flex items-center gap-2.5 mb-3 pb-2.5 border-b border-purple/20">
                            <span class="badge-ai bg-purple text-white text-[9px] font-bold px-1.5 py-0.5 uppercase tracking-wide">AI Insight</span>
                            <span class="text-[11px] font-medium text-purple uppercase tracking-wider">Analisi Dividendi</span>
                        </div>
                        <div class="text-[13px] leading-relaxed text-gray-700">
                            <div class="pl-4 relative py-2">
                                <span class="absolute left-0 text-purple font-bold">→</span>
                                <?php echo htmlspecialchars(
                                    $dividends_calendar_data["ai_insight"]
                                ); ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Calendario Mensile -->
                <div class="mb-8">
                    <div class="widget-card widget-purple p-6">
                        <div class="flex justify-between items-center mb-5 pb-4 border-b border-gray-200">
                            <div class="flex items-center gap-2">
                                <i class="fa-solid fa-calendar-days text-purple text-sm"></i>
                                <span class="text-[11px] font-medium text-gray-600 uppercase tracking-wider">Calendario Prossimi 12 Mesi</span>
                            </div>
                        </div>
                        <?php if (
                            empty($dividends_calendar_data["monthly_forecast"])
                        ): ?>
                            <div class="p-6 text-center text-gray-500 italic">
                                Nessun calendario disponibile. I dati verranno popolati dal workflow automatico.
                            </div>
                        <?php
                            // Calculate year based on month position in forecast
                            // Calculate year based on month position in forecast
                            // Calculate year based on month position in forecast
                            // Calculate year based on month position in forecast
                            // Calculate year based on month position in forecast
                            // Calculate year based on month position in forecast
                            // Calculate year based on month position in forecast
                            // Calculate year based on month position in forecast
                            else: ?>
                            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                                <?php foreach (
                                    $dividends_calendar_data["monthly_forecast"]
                                    as $month
                                ):
                                    $has_events =
                                        ($month["amount"] ?? 0) > 0; ?>
                                <div class="p-4 bg-white border border-gray-200">
                                    <div class="font-semibold text-primary text-sm"><?php echo htmlspecialchars(
                                        $month["month"]
                                    ); ?></div>
                                    <div class="text-[10px] text-gray-500 mb-2"><?php echo htmlspecialchars(
                                        $month["year"] ?? "-"
                                    ); ?></div>
                                    <?php if ($has_events): ?>
                                        <div class="text-sm font-bold text-positive mt-2">€<?php echo number_format(
                                            $month["amount"] ?? 0,
                                            2,
                                            ",",
                                            "."
                                        ); ?></div>
                                        <?php if (
                                            !empty($month["payment_dates"]) &&
                                            is_array($month["payment_dates"])
                                        ): ?>
                                            <div class="text-[10px] text-gray-500 mt-1 leading-4">
                                                Data pagamento: <?php echo htmlspecialchars(
                                                    implode(
                                                        ", ",
                                                        array_map(function (
                                                            $d
                                                        ) {
                                                            return date(
                                                                "d/m/Y",
                                                                strtotime($d)
                                                            );
                                                        }, $month["payment_dates"])
                                                    )
                                                ); ?>
                                            </div>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <div class="text-xs text-gray-400">Nessun evento</div>
                                    <?php endif; ?>
                                </div>
                                <?php
                                endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- DEBUG: Dividends Data -->
                <?php if (isset($_GET["debug"])): ?>
                <div class="mb-8 p-4 bg-gray-100 border border-gray-300 text-xs font-mono">
                    <strong>DEBUG INFO:</strong><br>
                    Dividends calendar loaded: <?php echo !empty(
                        $dividends_calendar_data
                    )
                        ? "YES"
                        : "NO"; ?><br>
                    Monthly forecast items: <?php echo count(
                        $dividends_calendar_data["monthly_forecast"] ?? []
                    ); ?><br>
                    Historical dividends: <?php echo count(
                        $dividends
                    ); ?><br><br>

                    <strong>Monthly Forecast from JSON:</strong><br>
                    <?php if (
                        !empty($dividends_calendar_data["monthly_forecast"])
                    ) {
                        foreach (
                            $dividends_calendar_data["monthly_forecast"]
                            as $m
                        ) {
                            echo "{$m["month"]}: €{$m["amount"]}<br>";
                        }
                    } else {
                        echo "EMPTY!<br>";
                    } ?>
                </div>
                <?php endif; ?>

                <!-- Grafici Dividendi -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                        <div class="widget-card widget-purple p-6">
                            <div class="flex justify-between items-center mb-5 pb-4 border-b border-gray-200">
                                <div class="flex items-center gap-2">
                                    <i class="fa-solid fa-calendar-check text-purple text-sm"></i>
                                    <span class="text-[11px] font-medium text-gray-600 uppercase tracking-wider">Dividendi Mensili (Ricevuti + Previsti)</span>
                                </div>
                                <div class="flex items-center gap-3 text-[10px]">
                                    <span class="flex items-center gap-1"><span class="w-3 h-3 bg-purple inline-block"></span> Ricevuti</span>
                                    <span class="flex items-center gap-1"><span class="w-3 h-3 bg-purple/40 inline-block"></span> Previsti</span>
                                </div>
                            </div>
                            <div class="relative h-[300px]">
                                <canvas id="dividendsMonthlyChart"></canvas>
                            </div>
                        </div>
                        <div class="widget-card widget-purple p-6">
                            <div class="flex justify-between items-center mb-5 pb-4 border-b border-gray-200">
                                <div class="flex items-center gap-2">
                                    <i class="fa-solid fa-money-bill-trend-up text-purple text-sm"></i>
                                    <span class="text-[11px] font-medium text-gray-600 uppercase tracking-wider">Rendita Cumulativa (Ricevuti + Previsti)</span>
                                </div>
                                <div class="flex items-center gap-3 text-[10px]">
                                    <span class="flex items-center gap-1"><span class="w-3 h-3 bg-purple inline-block"></span> Ricevuti</span>
                                    <span class="flex items-center gap-1"><span class="w-3 h-3 border-2 border-purple border-dashed inline-block"></span> Previsti</span>
                                </div>
                            </div>
                            <div class="relative h-[300px]">
                                <canvas id="dividendsCumulativeChart"></canvas>
                            </div>
                        </div>
                    </div>

                <!-- Asset Distributivi -->
                <div class="mb-8">
                    <div class="widget-card widget-purple p-6">
                        <div class="flex justify-between items-center mb-5 pb-4 border-b border-gray-200">
                            <div class="flex items-center gap-2">
                                <i class="fa-solid fa-piggy-bank text-purple text-sm"></i>
                                <span class="text-[11px] font-medium text-gray-600 uppercase tracking-wider">Asset Distributivi</span>
                            </div>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm sortable-table">
                                <thead class="bg-gray-50 border-b border-gray-200">
                                    <tr>
                                        <th class="px-4 py-3 text-left font-semibold text-gray-700 text-[11px] uppercase cursor-pointer" role="button">Ticker</th>
                                        <th class="px-4 py-3 text-left font-semibold text-gray-700 text-[11px] uppercase cursor-pointer" role="button">Nome</th>
                                        <th class="px-4 py-3 text-right font-semibold text-gray-700 text-[11px] uppercase cursor-pointer" role="button">Yield %</th>
                                        <th class="px-4 py-3 text-center font-semibold text-gray-700 text-[11px] uppercase cursor-pointer" role="button">Frequenza</th>
                                        <th class="px-4 py-3 text-right font-semibold text-gray-700 text-[11px] uppercase cursor-pointer" role="button">Ultimo Pay</th>
                                        <th class="px-4 py-3 text-right font-semibold text-gray-700 text-[11px] uppercase cursor-pointer" role="button">Prossimo Stacco</th>
                                        <th class="px-4 py-3 text-right font-semibold text-gray-700 text-[11px] uppercase cursor-pointer" role="button">Prossimo Pay</th>
                                        <th class="px-4 py-3 text-right font-semibold text-gray-700 text-[11px] uppercase cursor-pointer" role="button">Prossimo Importo</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (
                                        empty(
                                            $dividends_calendar_data[
                                                "distributing_assets"
                                            ]
                                        )
                                    ): ?>
                                        <tr>
                                            <td colspan="7" class="px-4 py-8 text-center text-gray-500 italic">
                                                Nessun asset distributivo. I dati verranno popolati dal workflow automatico.
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach (
                                            $dividends_calendar_data[
                                                "distributing_assets"
                                            ]
                                            as $asset
                                        ): ?>
                                        <tr class="border-b border-gray-200 hover:bg-gray-50">
                                            <td class="px-4 py-3 font-semibold text-purple"><?php echo htmlspecialchars(
                                                $asset["ticker"]
                                            ); ?></td>
                                            <td class="px-4 py-3"><?php echo htmlspecialchars(
                                                $asset["name"]
                                            ); ?></td>
                                            <td class="px-4 py-3 text-right font-semibold"><?php echo number_format(
                                                $asset["dividend_yield"] ?? 0,
                                                1,
                                                ",",
                                                "."
                                            ); ?>%</td>
                                            <td class="px-4 py-3 text-center text-xs"><?php echo htmlspecialchars(
                                                $asset["frequency"]
                                            ); ?></td>
                                            <td class="px-4 py-3 text-right">
                                                <?php echo !empty(
                                                    $asset["last_payment_date"]
                                                )
                                                    ? date(
                                                        "d/m/Y",
                                                        strtotime(
                                                            $asset[
                                                                "last_payment_date"
                                                            ]
                                                        )
                                                    )
                                                    : "-"; ?>
                                            </td>
                                            <td class="px-4 py-3 text-right font-medium">
                                                <?php echo !empty(
                                                    $asset["next_div_date"]
                                                )
                                                    ? date(
                                                        "d/m/Y",
                                                        strtotime(
                                                            $asset[
                                                                "next_div_date"
                                                            ]
                                                        )
                                                    )
                                                    : "-"; ?>
                                            </td>
                                            <td class="px-4 py-3 text-right">
                                                <?php echo !empty(
                                                    $asset["next_payment_date"]
                                                )
                                                    ? date(
                                                        "d/m/Y",
                                                        strtotime(
                                                            $asset[
                                                                "next_payment_date"
                                                            ]
                                                        )
                                                    )
                                                    : "-"; ?>
                                            </td>
                                            <td class="px-4 py-3 text-right font-semibold text-gray-800">
                                                €<?php echo number_format(
                                                    $asset["next_amount"] ?? 0,
                                                    2,
                                                    ",",
                                                    "."
                                                ); ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Dividends History (ultimi 12 mesi, solo RECEIVED) -->
                <div class="mb-8">
                    <div class="widget-card widget-purple p-6">
                        <div class="flex justify-between items-center mb-5 pb-4 border-b border-gray-200">
                            <div class="flex items-center gap-2">
                                <i class="fa-solid fa-clock-rotate-left text-purple text-sm"></i>
                                <span class="text-[11px] font-medium text-gray-600 uppercase tracking-wider">Dividendi Ricevuti (ultimi 12 mesi)</span>
                            </div>
                        </div>
                        <div class="space-y-3">
                            <?php
                            $today = date("Y-m-d");
                            $twelveMonthsAgo = date(
                                "Y-m-d",
                                strtotime("-12 months")
                            );
                            $dividendsReceived = array_filter(
                                $dividends,
                                function ($d) use ($twelveMonthsAgo, $today) {
                                    if (($d["status"] ?? "") !== "RECEIVED") {
                                        return false;
                                    }
                                    $ex = $d["ex_date"] ?? null;
                                    if (!$ex) {
                                        return false;
                                    }
                                    return $ex >= $twelveMonthsAgo &&
                                        $ex <= $today;
                                }
                            );
                            usort($dividendsReceived, function ($a, $b) {
                                $da = $a["ex_date"] ?? "";
                                $db = $b["ex_date"] ?? "";
                                return strcmp($db, $da);
                            });
                            $divCount = count($dividendsReceived);
                            foreach ($dividendsReceived as $div): ?>
                            <div class="flex justify-between items-center p-3 bg-gray-50 border border-gray-200">
                                <div>
                                    <div class="font-semibold text-primary text-sm"><?php echo htmlspecialchars(
                                        $div["ticker"]
                                    ); ?></div>
                                    <div class="text-[11px] text-gray-700">
                                        Ex-date: <?php echo $div["ex_date"]
                                            ? date(
                                                "d/m/Y",
                                                strtotime($div["ex_date"])
                                            )
                                            : "-"; ?>
                                        <span class="text-gray-500 text-[10px] ml-1">
                                            Pay-date: <?php echo !empty(
                                                $div["payment_date"]
                                            )
                                                ? date(
                                                    "d/m/Y",
                                                    strtotime(
                                                        $div["payment_date"]
                                                    )
                                                )
                                                : "-"; ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="font-bold text-positive">€<?php echo number_format(
                                        $div["amount"],
                                        2,
                                        ",",
                                        "."
                                    ); ?></div>
                                </div>
                            </div>
                            <?php endforeach;
                            ?>
                            <?php if ($divCount === 0): ?>
                                <div class="p-3 text-gray-500 text-sm italic bg-gray-50 border border-gray-200">
                                    Nessun dividendo ricevuto negli ultimi 12 mesi.
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Charts Scripts with Dynamic Data -->
                <script>
                // Prepare dividends data for charts
                <?php
                // Crea array mensili per i grafici (rolling 12 mesi con anno)
                $monthKeys = [];
                $months_labels = [];
                $monthly_received = [];
                $monthly_forecast = [];

                $start = new DateTime("first day of this month");
                for ($i = 0; $i < 12; $i++) {
                    $key = $start->format("Y-m");
                    $monthKeys[] = $key;
                    $months_labels[] = $start->format("m/Y");
                    $monthly_received[$key] = 0;
                    $monthly_forecast[$key] = 0;
                    $start->modify("+1 month");
                }

                $today = date("Y-m-d");

                // Popola array RICEVUTI con dati reali da $dividends (storico, status RECEIVED e ex_date <= oggi)
                foreach ($dividends as $div) {
                    if (empty($div["ex_date"])) {
                        continue;
                    }
                    $exDate = $div["ex_date"];
                    if ($div["status"] !== "RECEIVED" || $exDate > $today) {
                        continue;
                    }
                    $key = date("Y-m", strtotime($exDate));
                    if (isset($monthly_received[$key])) {
                        $monthly_received[$key] += $div["amount"];
                    }
                }

                // Popola array PREVISTI con dati dal calendario dividendi (12 mesi, solo status FORECAST o date future)
                if (!empty($dividends_calendar_data["monthly_forecast"])) {
                    foreach (
                        $dividends_calendar_data["monthly_forecast"]
                        as $forecast
                    ) {
                        $key = $forecast["month_year"] ?? null;
                        $amount = $forecast["amount"] ?? 0;
                        if (
                            $key &&
                            isset($monthly_forecast[$key]) &&
                            $amount > 0
                        ) {
                            $monthly_forecast[$key] += $amount;
                        }
                    }
                } else {
                    // Fallback: genera forecast base dai holdings arricchiti (annual_dividend + frequency)
                    $freqMap = [
                        "Monthly" => 12,
                        "Quarterly" => 4,
                        "Semi-Annual" => 2,
                        "Annual" => 1,
                    ];
                    $monthKeysCount = count($monthKeys);
                    foreach ($top_holdings as $h) {
                        if (
                            empty($h["has_dividends"]) ||
                            empty($h["annual_dividend"])
                        ) {
                            continue;
                        }
                        $paymentsPerYear =
                            $freqMap[$h["dividend_frequency"] ?? ""] ?? 0;
                        if ($paymentsPerYear === 0) {
                            continue;
                        }
                        $paymentAmount =
                            (($h["annual_dividend"] ?? 0) *
                                ($h["quantity"] ?? 0)) /
                            $paymentsPerYear;
                        $step = (int) floor(12 / $paymentsPerYear);
                        $startIdx = 0; // mese corrente (index 0)
                        for ($i = 0; $i < $paymentsPerYear; $i++) {
                            $idx = ($startIdx + $i * $step) % $monthKeysCount;
                            $key = $monthKeys[$idx];
                            $monthly_forecast[$key] += $paymentAmount;
                        }
                    }
                }

                // Converte in array ordinati secondo monthKeys
                $monthly_received_values = [];
                $monthly_forecast_values = [];
                foreach ($monthKeys as $key) {
                    $monthly_received_values[] = $monthly_received[$key];
                    $monthly_forecast_values[] = $monthly_forecast[$key];
                }

                // Calcola cumulativo RICEVUTI
                $cumulative_received = array_fill(0, 12, 0);
                $cumul = 0;
                for ($i = 0; $i < 12; $i++) {
                    $cumul += $monthly_received_values[$i];
                    $cumulative_received[$i] = $cumul;
                }

                // Calcola cumulativo TOTALE (ricevuti + previsti)
                $cumulative_total = array_fill(0, 12, 0);
                $cumul = 0;
                for ($i = 0; $i < 12; $i++) {
                    $cumul +=
                        $monthly_received_values[$i] +
                        $monthly_forecast_values[$i];
                    $cumulative_total[$i] = $cumul;
                }
                ?>

                // DEBUG: Log data to console
                console.log('=== DIVIDENDS DEBUG ===');
                console.log('Month labels:', <?php echo json_encode(
                    $months_labels
                ); ?>);
                console.log('Monthly Received:', <?php echo json_encode(
                    $monthly_received_values
                ); ?>);
                console.log('Monthly Forecast:', <?php echo json_encode(
                    $monthly_forecast_values
                ); ?>);
                console.log('Cumulative Received:', <?php echo json_encode(
                    $cumulative_received
                ); ?>);
                console.log('Cumulative Total:', <?php echo json_encode(
                    $cumulative_total
                ); ?>);

                // Dividends Monthly Chart - Usando ChartManager
                if (document.getElementById('dividendsMonthlyChart')) {
                    try {
                            window.ChartManager.createDividendsMonthlyChart(
                                'dividendsMonthlyChart',
                                <?php echo json_encode($months_labels); ?>,
                                <?php echo json_encode(
                                    $monthly_received_values
                                ); ?>,
                                <?php echo json_encode(
                                    $monthly_forecast_values
                                ); ?>
                        );
                        initializedCharts.add('dividendsMonthlyChart');
                        console.log('✅ Dividends Monthly Chart created');
                    } catch (error) {
                        console.error('Errore inizializzazione Dividends Monthly Chart:', error);
                    }
                }

                // Dividends Cumulative Chart - Usando ChartManager
                if (document.getElementById('dividendsCumulativeChart')) {
                    try {
                            window.ChartManager.createDividendsCumulativeChart(
                                'dividendsCumulativeChart',
                                <?php echo json_encode($months_labels); ?>,
                                <?php echo json_encode(
                                    $cumulative_received
                                ); ?>,
                                <?php echo json_encode($cumulative_total); ?>
                            );
                        initializedCharts.add('dividendsCumulativeChart');
                        console.log('✅ Dividends Cumulative Chart created');
                    } catch (error) {
                        console.error('Errore inizializzazione Dividends Cumulative Chart:', error);
                    }
                }
                </script>

            </div>

            <!-- View: Recommendations -->
