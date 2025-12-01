            <!-- View: Grafici Tecnici -->
            <div id="charts" class="view hidden">
                <div class="mb-6 sm:mb-10">
                    <h1 class="text-[18px] sm:text-[20px] font-medium text-primary tracking-tight">Grafici Tecnici Storici</h1>
                    <p class="text-sm text-gray-600 mt-2">Analisi storica degli indicatori tecnici (ultimi 30-60 giorni)</p>
                </div>

                <!-- Selettore Strumento -->
                <div class="mb-6">
                    <div class="widget-card widget-purple p-4">
                        <label for="chartInstrumentSelect" class="block text-sm font-semibold text-gray-700 mb-2">
                            Seleziona Strumento:
                        </label>
                        <select
                            id="chartInstrumentSelect"
                            class="w-full md:w-1/2 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple focus:border-purple">
                            <option value="">-- Seleziona uno strumento --</option>
                            <?php foreach ($top_holdings as $holding): ?>
                                <option
                                    value="<?php echo htmlspecialchars($holding['isin']); ?>"
                                    data-ticker="<?php echo htmlspecialchars($holding['ticker']); ?>"
                                    data-name="<?php echo htmlspecialchars($holding['name']); ?>">
                                    <?php echo htmlspecialchars($holding['ticker']); ?> - <?php echo htmlspecialchars($holding['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <!-- Loading State -->
                <div id="chartsLoading" class="hidden text-center py-12">
                    <div class="inline-block animate-spin rounded-full h-12 w-12 border-4 border-purple border-t-transparent"></div>
                    <p class="text-gray-600 mt-4">Caricamento dati storici...</p>
                </div>

                <!-- Empty State -->
                <div id="chartsEmpty" class="text-center py-12">
                    <div class="text-gray-400 mb-4">
                        <i class="fa-solid fa-chart-line text-6xl"></i>
                    </div>
                    <p class="text-gray-600 text-lg">Seleziona uno strumento per visualizzare i grafici storici</p>
                </div>

                <!-- Error State -->
                <div id="chartsError" class="hidden">
                    <div class="bg-red-50 border border-red-300 p-4 text-center">
                        <div class="text-red-600 mb-3">
                            <i class="fa-solid fa-triangle-exclamation text-5xl"></i>
                        </div>
                        <p class="text-red-700 font-semibold mb-2">Errore nel caricamento dei dati</p>
                        <p class="text-red-600 text-sm" id="chartsErrorMessage"></p>
                    </div>
                </div>

                <!-- No Data State -->
                <div id="chartsNoData" class="hidden">
                    <div class="bg-yellow-50 border border-yellow-300 p-4 text-center">
                        <div class="text-yellow-600 mb-3">
                            <i class="fa-solid fa-inbox text-5xl"></i>
                        </div>
                        <p class="text-yellow-700 font-semibold mb-2">Nessun dato storico disponibile</p>
                        <p class="text-yellow-600 text-sm">
                            Non ci sono snapshot tecnici per questo strumento. Esegui il workflow n8n di enrichment per popolare i dati.
                        </p>
                    </div>
                </div>

                <!-- Charts Container -->
                <div id="chartsContainer" class="hidden space-y-6">
                    <!-- Header Info -->
                    <div class="widget-card widget-purple p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <h2 class="text-xl font-bold text-primary" id="chartInstrumentTitle">-</h2>
                                <p class="text-sm text-gray-600" id="chartInstrumentSubtitle">-</p>
                            </div>
                            <div class="text-right">
                                <div class="text-sm text-gray-600">Periodo</div>
                                <div class="text-lg font-semibold text-primary" id="chartDataPeriod">-</div>
                            </div>
                        </div>
                    </div>

                    <!-- RSI Chart -->
                    <div class="widget-card widget-purple p-6">
                        <div class="flex items-center gap-2 mb-4">
                            <i class="fa-solid fa-chart-line text-purple text-2xl"></i>
                            <h3 class="text-lg font-bold text-primary">RSI 14 - Relative Strength Index</h3>
                        </div>
                        <div class="bg-gray-50 p-3 mb-4 text-sm text-gray-700">
                            <strong>Interpretazione:</strong> RSI > 70 = Ipercomprato (possibile correzione), RSI < 30 = Ipervenduto (possibile rimbalzo), RSI 30-70 = Zona neutrale.
                        </div>
                        <div style="height: 300px;">
                            <canvas id="rsiChart"></canvas>
                        </div>
                    </div>

                    <!-- MACD Chart -->
                    <div class="widget-card widget-purple p-6">
                        <div class="flex items-center gap-2 mb-4">
                            <i class="fa-solid fa-chart-simple text-purple text-2xl"></i>
                            <h3 class="text-lg font-bold text-primary">MACD - Moving Average Convergence Divergence</h3>
                        </div>
                        <div class="bg-gray-50 p-3 mb-4 text-sm text-gray-700">
                            <strong>Interpretazione:</strong> MACD sopra segnale = Momentum rialzista, MACD sotto segnale = Momentum ribassista. Istogramma positivo/negativo indica forza del trend.
                        </div>
                        <div style="height: 300px;">
                            <canvas id="macdChart"></canvas>
                        </div>
                    </div>

                    <!-- Volatility Chart -->
                    <div class="widget-card widget-purple p-6">
                        <div class="flex items-center gap-2 mb-4">
                            <i class="fa-solid fa-chart-area text-purple text-2xl"></i>
                            <h3 class="text-lg font-bold text-primary">Volatilità Storica 30 Giorni</h3>
                        </div>
                        <div class="bg-gray-50 p-3 mb-4 text-sm text-gray-700">
                            <strong>Interpretazione:</strong> Volatilità alta = Movimenti ampi, rischio elevato. Volatilità bassa = Movimenti contenuti, fase di consolidamento.
                        </div>
                        <div style="height: 300px;">
                            <canvas id="volatilityChart"></canvas>
                        </div>
                    </div>

                    <!-- Bollinger %B Chart -->
                    <div class="widget-card widget-purple p-6">
                        <div class="flex items-center gap-2 mb-4">
                            <i class="fa-solid fa-wave-square text-purple text-2xl"></i>
                            <h3 class="text-lg font-bold text-primary">Bollinger %B - Posizione nelle Bande</h3>
                        </div>
                        <div class="bg-gray-50 p-3 mb-4 text-sm text-gray-700">
                            <strong>Interpretazione:</strong> %B > 1 = Sopra banda superiore (ipercomprato), %B < 0 = Sotto banda inferiore (ipervenduto), %B 0.5 = Centro banda (neutro).
                        </div>
                        <div style="height: 300px;">
                            <canvas id="bollingerChart"></canvas>
                        </div>
                    </div>

                    <!-- Range Percentile Chart -->
                    <div class="widget-card widget-purple p-6">
                        <div class="flex items-center gap-2 mb-4">
                            <i class="fa-solid fa-ruler text-purple text-2xl"></i>
                            <h3 class="text-lg font-bold text-primary">Range 1Y Percentile</h3>
                        </div>
                        <div class="bg-gray-50 p-3 mb-4 text-sm text-gray-700">
                            <strong>Interpretazione:</strong> Percentile > 80% = Prezzo vicino ai massimi annuali, Percentile < 20% = Prezzo vicino ai minimi annuali.
                        </div>
                        <div style="height: 300px;">
                            <canvas id="rangePercentileChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
