/**
 * Technical Analysis Modal - Gestione modale dettagli tecnici
 *
 * Questo modulo gestisce la visualizzazione dettagliata dell'analisi tecnica
 * per ogni strumento, includendo:
 * - Fibonacci levels con distanze percentuali
 * - Bollinger Bands dettagli (upper, middle, lower, width)
 * - Badge 52W High/Low
 * - Day Range e Gap %
 * - Tutti gli indicatori tecnici disponibili
 */

/**
 * Apre la modale con i dettagli tecnici dello strumento
 * @param {string} ticker - Ticker dello strumento
 */
function openTechnicalModal(ticker) {
    const holding = technicalData.find(h => h.ticker === ticker);

    if (!holding) {
        console.error('Holding non trovato:', ticker);
        return;
    }

    // Aggiorna titolo modale
    document.getElementById('modalTitle').textContent = `${holding.ticker} - ${holding.name}`;

    // Genera contenuto
    const content = generateModalContent(holding);
    document.getElementById('modalContent').innerHTML = content;

    // Mostra modale
    document.getElementById('technicalModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden'; // Previene scroll della pagina sottostante
}

/**
 * Chiude la modale
 */
function closeTechnicalModal() {
    document.getElementById('technicalModal').classList.add('hidden');
    document.body.style.overflow = 'auto';
}

/**
 * Chiude modale al click fuori dal contenuto
 */
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('technicalModal');
    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                closeTechnicalModal();
            }
        });
    }

    // ESC key per chiudere
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
            closeTechnicalModal();
        }
    });
});

/**
 * Genera il contenuto HTML della modale
 * @param {object} holding - Oggetto holding con tutti i dati
 * @returns {string} HTML content
 */
function generateModalContent(holding) {
    let html = '<div class="space-y-6">';

    // ===== SEZIONE 1: Overview e Badge =====
    html += generateOverviewSection(holding);

    // ===== SEZIONE 2: Fibonacci Levels =====
    html += generateFibonacciSection(holding);

    // ===== SEZIONE 3: Bollinger Bands Dettagli =====
    html += generateBollingerSection(holding);

    // ===== SEZIONE 4: Range e Percentili =====
    html += generateRangeSection(holding);

    // ===== SEZIONE 5: Volume e Liquidità =====
    html += generateVolumeSection(holding);

    // ===== SEZIONE 6: Moving Averages =====
    html += generateMovingAveragesSection(holding);

    // ===== SEZIONE 7: AI Insights (se disponibili) =====
    if (holding.insight) {
        html += generateAIInsightsSection(holding);
    }

    html += '</div>';
    return html;
}

/**
 * SEZIONE 1: Overview e Badge
 */
function generateOverviewSection(holding) {
    const price = holding.current_price || 0;
    const prevClose = holding.previous_close || price;
    const dayHigh = holding.day_high || 0;
    const dayLow = holding.day_low || 0;
    const fiftyTwoWeekHigh = holding.fifty_two_week_high || 0;
    const fiftyTwoWeekLow = holding.fifty_two_week_low || 0;

    // Calcolo gap %
    const gap = prevClose > 0 ? ((price - prevClose) / prevClose) * 100 : 0;
    const gapClass = gap >= 0 ? 'text-positive' : 'text-negative';
    const gapIcon = gap >= 0 ? '▲' : '▼';

    // Badge 52W
    const near52wHigh = fiftyTwoWeekHigh > 0 && (price / fiftyTwoWeekHigh) > 0.95;
    const near52wLow = fiftyTwoWeekLow > 0 && (price / fiftyTwoWeekLow) < 1.05;

    let html = `
        <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Prezzo Corrente -->
                <div class="text-center md:text-left">
                    <div class="text-[11px] text-gray-500 uppercase tracking-wider mb-1">Prezzo Corrente</div>
                    <div class="text-3xl font-bold text-primary">€${formatNumber(price, 2)}</div>
                    ${prevClose > 0 ? `
                        <div class="text-sm ${gapClass} font-semibold mt-1">
                            ${gap >= 0 ? '+' : ''}${formatNumber(gap, 2)}% ${gapIcon}
                        </div>
                    ` : ''}
                </div>

                <!-- Day Range -->
                <div class="text-center">
                    <div class="text-[11px] text-gray-500 uppercase tracking-wider mb-1">Range Giornata</div>
                    ${dayLow > 0 && dayHigh > 0 ? `
                        <div class="text-lg font-semibold text-gray-800">
                            €${formatNumber(dayLow, 2)} - €${formatNumber(dayHigh, 2)}
                        </div>
                        <div class="text-[11px] text-gray-600 mt-1">
                            Range: ${formatNumber(((dayHigh - dayLow) / dayLow) * 100, 2)}%
                        </div>
                    ` : '<div class="text-sm text-gray-500">-</div>'}
                    ${prevClose > 0 ? `
                        <div class="text-[11px] text-gray-500 mt-2">
                            Prev Close: €${formatNumber(prevClose, 2)}
                        </div>
                    ` : ''}
                </div>

                <!-- 52 Week Range e Badge -->
                <div class="text-center md:text-right">
                    <div class="text-[11px] text-gray-500 uppercase tracking-wider mb-1">52 Week Range</div>
                    ${fiftyTwoWeekLow > 0 && fiftyTwoWeekHigh > 0 ? `
                        <div class="text-sm font-semibold text-gray-800">
                            €${formatNumber(fiftyTwoWeekLow, 2)} - €${formatNumber(fiftyTwoWeekHigh, 2)}
                        </div>
                        <div class="flex gap-2 justify-center md:justify-end mt-2">
                            ${near52wHigh ? `
                                <span class="px-3 py-1 text-[10px] font-bold bg-green-100 text-green-700 border border-green-300">
                                    Near 52W High
                                </span>
                            ` : ''}
                            ${near52wLow ? `
                                <span class="px-3 py-1 text-[10px] font-bold bg-red-100 text-red-700 border border-red-300">
                                    Near 52W Low
                                </span>
                            ` : ''}
                        </div>
                        <div class="text-[11px] text-gray-500 mt-1">
                            Dal min: ${formatNumber(((price - fiftyTwoWeekLow) / fiftyTwoWeekLow) * 100, 1)}% |
                            Dal max: ${formatNumber(((price - fiftyTwoWeekHigh) / fiftyTwoWeekHigh) * 100, 1)}%
                        </div>
                    ` : '<div class="text-sm text-gray-500">-</div>'}
                </div>
            </div>
        </div>
    `;

    return html;
}

/**
 * SEZIONE 2: Fibonacci Levels
 */
function generateFibonacciSection(holding) {
    const hasFib = holding.fib_50_0 !== null && holding.fib_50_0 > 0;

    if (!hasFib) {
        return ''; // Non mostrare sezione se non ci sono dati Fibonacci
    }

    const price = holding.current_price || 0;
    const fibLevels = [
        { name: '23.6%', value: holding.fib_23_6, dist: holding.fib_23_6_dist_pct },
        { name: '38.2%', value: holding.fib_38_2, dist: holding.fib_38_2_dist_pct },
        { name: '50.0%', value: holding.fib_50_0, dist: holding.fib_50_0_dist_pct },
        { name: '61.8%', value: holding.fib_61_8, dist: holding.fib_61_8_dist_pct },
        { name: '78.6%', value: holding.fib_78_6, dist: holding.fib_78_6_dist_pct }
    ];

    // Trova il livello più vicino
    let closestLevel = null;
    let minDist = Infinity;
    fibLevels.forEach(level => {
        if (level.dist !== null && Math.abs(level.dist) < minDist) {
            minDist = Math.abs(level.dist);
            closestLevel = level;
        }
    });

    let html = `
        <div class="border border-purple/30 rounded-lg p-4 bg-purple/5">
            <div class="flex items-center gap-2 mb-4">
                <i class="fa-solid fa-chart-line text-purple text-xl"></i>
                <h3 class="text-lg font-bold text-primary">Livelli di Fibonacci</h3>
                <span class="text-[11px] text-gray-500 ml-auto">Range: €${formatNumber(holding.fib_low, 2)} - €${formatNumber(holding.fib_high, 2)}</span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-5 gap-3">
    `;

    fibLevels.forEach(level => {
        const isClosest = closestLevel && level.name === closestLevel.name;
        const isAbove = level.value > price;
        const typeLabel = isAbove ? 'Resistenza' : 'Supporto';
        const typeColor = isAbove ? 'text-negative' : 'text-positive';

        html += `
            <div class="p-3 rounded-lg border-2 ${isClosest ? 'border-purple bg-purple/10' : 'border-gray-200 bg-white'}">
                <div class="text-[11px] text-gray-600 uppercase tracking-wide">${level.name}</div>
                <div class="text-xl font-bold text-primary mt-1">€${formatNumber(level.value, 2)}</div>
                <div class="text-[11px] ${typeColor} font-semibold mt-1">${typeLabel}</div>
                ${level.dist !== null ? `
                    <div class="text-[11px] text-gray-600 mt-1">
                        Distanza: ${level.dist >= 0 ? '+' : ''}${formatNumber(level.dist, 1)}%
                    </div>
                ` : ''}
                ${isClosest ? `
                    <div class="text-[10px] font-bold text-purple mt-2 uppercase">
                        ⭐ Più Vicino
                    </div>
                ` : ''}
            </div>
        `;
    });

    html += `
            </div>
            <div class="mt-3 p-2 bg-blue-50 border border-blue-200 text-[11px] text-gray-700">
                <i class="fa-solid fa-lightbulb text-blue-600 mr-1"></i> <strong>Come usare:</strong> I livelli Fibonacci sono supporti/resistenze automatici.
                ${closestLevel ? `Il livello <strong>${closestLevel.name}</strong> è il più vicino (${Math.abs(closestLevel.dist).toFixed(1)}%) e potrebbe agire da ${closestLevel.value > price ? 'resistenza' : 'supporto'}.` : ''}
            </div>
        </div>
    `;

    return html;
}

/**
 * SEZIONE 3: Bollinger Bands Dettagli
 */
function generateBollingerSection(holding) {
    const hasBB = holding.bb_middle !== null;

    if (!hasBB) {
        return '';
    }

    const price = holding.current_price || 0;
    const bbUpper = holding.bb_upper || 0;
    const bbMiddle = holding.bb_middle || 0;
    const bbLower = holding.bb_lower || 0;
    const bbWidth = holding.bb_width_pct || 0;
    const bbPercentB = holding.bb_percent_b || 0;

    // Interpretazione bb_width_pct
    let widthLabel = '';
    let widthColor = '';
    if (bbWidth < 5) {
        widthLabel = 'Volatilità Compressa';
        widthColor = 'text-positive';
    } else if (bbWidth > 10) {
        widthLabel = 'Volatilità Alta';
        widthColor = 'text-negative';
    } else {
        widthLabel = 'Volatilità Normale';
        widthColor = 'text-gray-700';
    }

    // Posizione prezzo
    let positionLabel = '';
    if (bbPercentB < 0.2) {
        positionLabel = 'Vicino Banda Inferiore (possibile rimbalzo)';
    } else if (bbPercentB <= 0.8) {
        positionLabel = 'Centro Banda (neutro)';
    } else {
        positionLabel = 'Vicino Banda Superiore (possibile pullback)';
    }

    let html = `
        <div class="border border-blue-300 rounded-lg p-4 bg-blue-50">
            <div class="flex items-center gap-2 mb-4">
                <i class="fa-solid fa-chart-simple text-purple text-xl"></i>
                <h3 class="text-lg font-bold text-primary">Bollinger Bands</h3>
                <span class="ml-auto px-3 py-1 text-[11px] font-bold ${widthColor} bg-white border border-gray-300">
                    ${widthLabel}
                </span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="bg-white p-3 rounded-lg border border-gray-200">
                    <div class="text-[11px] text-gray-500 uppercase">Banda Superiore</div>
                    <div class="text-xl font-bold text-negative">€${formatNumber(bbUpper, 2)}</div>
                    <div class="text-[11px] text-gray-600 mt-1">
                        +${formatNumber(((bbUpper - price) / price) * 100, 1)}% dal prezzo
                    </div>
                </div>

                <div class="bg-white p-3 rounded-lg border border-gray-200">
                    <div class="text-[11px] text-gray-500 uppercase">Banda Media (SMA 20)</div>
                    <div class="text-xl font-bold text-gray-700">€${formatNumber(bbMiddle, 2)}</div>
                    <div class="text-[11px] text-gray-600 mt-1">
                        ${formatNumber(((bbMiddle - price) / price) * 100, 1)}% dal prezzo
                    </div>
                </div>

                <div class="bg-white p-3 rounded-lg border border-gray-200">
                    <div class="text-[11px] text-gray-500 uppercase">Banda Inferiore</div>
                    <div class="text-xl font-bold text-positive">€${formatNumber(bbLower, 2)}</div>
                    <div class="text-[11px] text-gray-600 mt-1">
                        ${formatNumber(((bbLower - price) / price) * 100, 1)}% dal prezzo
                    </div>
                </div>

                <div class="bg-white p-3 rounded-lg border border-gray-200">
                    <div class="text-[11px] text-gray-500 uppercase">BB Width</div>
                    <div class="text-xl font-bold ${widthColor}">${formatNumber(bbWidth, 2)}%</div>
                    <div class="text-[11px] text-gray-600 mt-1">
                        %B: ${formatNumber(bbPercentB * 100, 1)}%
                    </div>
                </div>
            </div>

            <div class="mt-4 p-3 bg-white rounded border border-gray-300">
                <div class="text-sm font-semibold text-gray-700 mb-2">Posizione Prezzo:</div>
                <div class="text-sm text-gray-600">${positionLabel}</div>
                ${bbWidth < 5 ? `
                    <div class="mt-2 p-2 bg-yellow-50 border border-yellow-300 text-[11px] text-gray-700">
                        <i class="fa-solid fa-triangle-exclamation text-yellow-600 mr-1"></i> <strong>Squeeze Alert:</strong> Volatilità compressa (<5%). Possibile breakout imminente - monitora il volume.
                    </div>
                ` : ''}
            </div>
        </div>
    `;

    return html;
}

/**
 * SEZIONE 4: Range e Percentili
 */
function generateRangeSection(holding) {
    const hasRange = holding.range_1y_percentile !== null;

    if (!hasRange) {
        return '';
    }

    const ranges = [
        {
            period: '1 Mese',
            min: holding.range_1m_min,
            max: holding.range_1m_max,
            percentile: holding.range_1m_percentile
        },
        {
            period: '3 Mesi',
            min: holding.range_3m_min,
            max: holding.range_3m_max,
            percentile: holding.range_3m_percentile
        },
        {
            period: '6 Mesi',
            min: holding.range_6m_min,
            max: holding.range_6m_max,
            percentile: holding.range_6m_percentile
        },
        {
            period: '1 Anno',
            min: holding.range_1y_min,
            max: holding.range_1y_max,
            percentile: holding.range_1y_percentile
        }
    ];

    let html = `
        <div class="border border-gray-300 rounded-lg p-4 bg-gray-50">
            <div class="flex items-center gap-2 mb-4">
                <i class="fa-solid fa-ruler text-purple text-xl"></i>
                <h3 class="text-lg font-bold text-primary">Range Multi-Timeframe</h3>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    `;

    ranges.forEach(range => {
        if (range.min !== null && range.max !== null) {
            const price = holding.current_price || 0;
            const rangeSpread = ((range.max - range.min) / range.min) * 100;
            const percentile = range.percentile || 0;

            // Color-coding percentile
            let percentileColor = 'text-gray-700';
            let percentileLabel = 'Neutro';
            if (percentile > 80) {
                percentileColor = 'text-negative';
                percentileLabel = 'Vicino Massimi';
            } else if (percentile < 20) {
                percentileColor = 'text-positive';
                percentileLabel = 'Vicino Minimi';
            }

            html += `
                <div class="bg-white p-3 rounded-lg border border-gray-200">
                    <div class="text-[11px] text-gray-500 uppercase font-semibold mb-2">${range.period}</div>
                    <div class="text-sm text-gray-700">
                        Min: €${formatNumber(range.min, 2)} | Max: €${formatNumber(range.max, 2)}
                    </div>
                    <div class="text-[11px] text-gray-600 mt-1">
                        Range: ${formatNumber(rangeSpread, 1)}%
                    </div>
                    <div class="mt-2 flex items-center justify-between">
                        <span class="text-[11px] text-gray-600">Percentile:</span>
                        <span class="px-2 py-1 rounded text-[11px] font-bold ${percentileColor} bg-gray-100">
                            ${formatNumber(percentile, 0)}% - ${percentileLabel}
                        </span>
                    </div>
                </div>
            `;
        }
    });

    html += `
            </div>
            <div class="mt-3 p-2 bg-blue-50 border border-blue-200 text-[11px] text-gray-700">
                <i class="fa-solid fa-lightbulb text-blue-600 mr-1"></i> <strong>Interpretazione:</strong> Percentile >80% = prezzo vicino ai massimi del periodo (possibile resistenza).
                Percentile <20% = prezzo vicino ai minimi (possibile supporto).
            </div>
        </div>
    `;

    return html;
}

/**
 * SEZIONE 5: Volume e Liquidità
 */
function generateVolumeSection(holding) {
    const hasVolume = holding.volume !== null;

    if (!hasVolume) {
        return '';
    }

    const volume = holding.volume || 0;
    const volAvg20d = holding.vol_avg_20d || 0;
    const volRatio = holding.vol_ratio_current_20d || 0;

    // Alert volume anomalo
    const volAnomalous = volRatio > 2 || volRatio < 0.5;
    let volLabel = 'Volume Normale';
    let volColor = 'text-gray-700';

    if (volRatio > 2) {
        volLabel = 'Volume Molto Alto';
        volColor = 'text-negative';
    } else if (volRatio < 0.5) {
        volLabel = 'Volume Molto Basso';
        volColor = 'text-blue-600';
    }

    let html = `
        <div class="border border-gray-300 rounded-lg p-4 bg-gray-50">
            <div class="flex items-center gap-2 mb-4">
                <i class="fa-solid fa-chart-column text-purple text-xl"></i>
                <h3 class="text-lg font-bold text-primary">Volume e Liquidità</h3>
                ${volAnomalous ? `
                    <span class="ml-auto px-3 py-1 text-[11px] font-bold ${volColor} bg-white border border-gray-300">
                        <i class="fa-solid fa-triangle-exclamation mr-1"></i> Volume Anomalo
                    </span>
                ` : ''}
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-white p-3 rounded-lg border border-gray-200">
                    <div class="text-[11px] text-gray-500 uppercase">Volume Corrente</div>
                    <div class="text-xl font-bold text-primary">${formatNumberCompact(volume)}</div>
                </div>

                <div class="bg-white p-3 rounded-lg border border-gray-200">
                    <div class="text-[11px] text-gray-500 uppercase">Media 20 Giorni</div>
                    <div class="text-xl font-bold text-gray-700">${formatNumberCompact(volAvg20d)}</div>
                </div>

                <div class="bg-white p-3 rounded-lg border border-gray-200">
                    <div class="text-[11px] text-gray-500 uppercase">Ratio vs Media</div>
                    <div class="text-xl font-bold ${volColor}">${formatNumber(volRatio, 2)}x</div>
                    <div class="text-[11px] ${volColor} font-semibold mt-1">${volLabel}</div>
                </div>
            </div>

            ${volAnomalous ? `
                <div class="mt-3 p-3 bg-yellow-50 border border-yellow-300">
                    <div class="text-sm font-semibold text-gray-700 mb-1"><i class="fa-solid fa-triangle-exclamation text-yellow-600 mr-1"></i> Alert Volume Anomalo</div>
                    <div class="text-[11px] text-gray-700">
                        ${volRatio > 2
                            ? `Volume ${volRatio.toFixed(1)}x superiore alla media. Possibile notizia importante o breakout imminente - monitora i prezzi.`
                            : `Volume ${volRatio.toFixed(1)}x inferiore alla media. Bassa liquidità - possibile scarso interesse o fase di consolidamento.`
                        }
                    </div>
                </div>
            ` : ''}
        </div>
    `;

    return html;
}

/**
 * SEZIONE 6: Moving Averages
 */
function generateMovingAveragesSection(holding) {
    const hasEMA = holding.ema50 !== null;

    if (!hasEMA) {
        return '';
    }

    const price = holding.current_price || 0;
    const emas = [
        { name: 'EMA 9', value: holding.ema9, period: 'Breve termine' },
        { name: 'EMA 21', value: holding.ema21, period: 'Medio termine' },
        { name: 'EMA 50', value: holding.ema50, period: 'Trend intermedio' },
        { name: 'EMA 200', value: holding.ema200, period: 'Trend lungo' }
    ];

    // Trend e Momentum
    const trend = holding.ema50 > holding.ema200 ? 'Rialzista' : 'Ribassista';
    const trendColor = trend === 'Rialzista' ? 'text-positive' : 'text-negative';
    const momentum = holding.ema9 > holding.ema21 ? 'Positivo' : 'Negativo';
    const momentumColor = momentum === 'Positivo' ? 'text-positive' : 'text-negative';

    let html = `
        <div class="border border-gray-300 rounded-lg p-4 bg-gray-50">
            <div class="flex items-center gap-2 mb-4">
                <i class="fa-solid fa-wave-square text-purple text-xl"></i>
                <h3 class="text-lg font-bold text-primary">Moving Averages (EMA)</h3>
                <span class="ml-auto flex gap-2">
                    <span class="px-2 py-1 rounded text-[11px] font-bold ${trendColor} bg-white border border-gray-300">
                        Trend: ${trend}
                    </span>
                    <span class="px-2 py-1 rounded text-[11px] font-bold ${momentumColor} bg-white border border-gray-300">
                        Momentum: ${momentum}
                    </span>
                </span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
    `;

    emas.forEach(ema => {
        if (ema.value !== null) {
            const distPct = ((price - ema.value) / ema.value) * 100;
            const isAbove = price > ema.value;
            const positionLabel = isAbove ? 'Sopra' : 'Sotto';
            const positionColor = isAbove ? 'text-positive' : 'text-negative';

            html += `
                <div class="bg-white p-3 rounded-lg border border-gray-200">
                    <div class="text-[11px] text-gray-500 uppercase">${ema.name}</div>
                    <div class="text-xl font-bold text-primary">€${formatNumber(ema.value, 2)}</div>
                    <div class="text-[11px] text-gray-500 mt-1">${ema.period}</div>
                    <div class="text-[11px] ${positionColor} font-semibold mt-2">
                        ${positionLabel}: ${Math.abs(distPct).toFixed(1)}%
                    </div>
                </div>
            `;
        }
    });

    html += `
            </div>
        </div>
    `;

    return html;
}

/**
 * SEZIONE 7: AI Insights
 */
function generateAIInsightsSection(holding) {
    const insightText = holding.insight || '';
    const insightFlags = holding.insight_flags || [];
    const insightLevels = holding.insight_levels || {};
    const insightScores = holding.insight_scores || {};
    const insightSignals = holding.insight_signals || {};

    let html = `
        <div class="border border-purple/30 rounded-lg p-4 bg-purple/5">
            <div class="flex items-center gap-2 mb-4">
                <i class="fa-solid fa-robot text-purple text-xl"></i>
                <h3 class="text-lg font-bold text-primary">AI Technical Insights</h3>
            </div>

            <div class="bg-white p-4 rounded-lg border border-gray-200 mb-4">
                <p class="text-sm text-gray-700 leading-relaxed">${insightText}</p>
            </div>

            ${Object.keys(insightScores).length > 0 ? `
                <div class="bg-white p-3 rounded-lg border border-gray-200 mb-4">
                    <div class="text-[11px] font-semibold text-gray-600 uppercase mb-2">Score AI</div>
                    <div class="flex flex-wrap gap-2">
                        ${insightScores.trend_strength ? `
                            <span class="px-3 py-1 rounded-full bg-purple/10 text-primary border border-purple/20 text-[11px] font-semibold">
                                Trend: ${insightScores.trend_strength}/100
                            </span>
                        ` : ''}
                        ${insightScores.momentum_strength ? `
                            <span class="px-3 py-1 rounded-full bg-purple/10 text-primary border border-purple/20 text-[11px] font-semibold">
                                Momentum: ${insightScores.momentum_strength}/100
                            </span>
                        ` : ''}
                        ${insightScores.volatility_score ? `
                            <span class="px-3 py-1 rounded-full bg-blue-50 text-blue-700 border border-blue-200 text-[11px] font-semibold">
                                Volatilità: ${insightScores.volatility_score}/100
                            </span>
                        ` : ''}
                        ${insightScores.overextension_score ? `
                            <span class="px-3 py-1 rounded-full bg-yellow-50 text-yellow-700 border border-yellow-200 text-[11px] font-semibold">
                                Estensione: ${insightScores.overextension_score}/100
                            </span>
                        ` : ''}
                    </div>
                </div>
            ` : ''}

            ${insightFlags.length > 0 ? `
                <div class="bg-white p-3 rounded-lg border border-gray-200 mb-4">
                    <div class="text-[11px] font-semibold text-gray-600 uppercase mb-2">Flag</div>
                    <div class="flex flex-wrap gap-2">
                        ${insightFlags.map(flag => `
                            <span class="px-3 py-1 rounded-full bg-purple/10 text-primary border border-purple/20 text-[11px]">
                                ${flag}
                            </span>
                        `).join('')}
                    </div>
                </div>
            ` : ''}

            ${(insightLevels.potential_support_levels?.length > 0 || insightLevels.potential_resistance_levels?.length > 0) ? `
                <div class="bg-white p-3 rounded-lg border border-gray-200">
                    <div class="text-[11px] font-semibold text-gray-600 uppercase mb-2">Livelli Chiave AI</div>
                    ${insightLevels.potential_support_levels?.length > 0 ? `
                        <div class="mb-2 p-2 rounded bg-green-50 border border-green-200">
                            <div class="text-[11px] font-semibold text-positive">Supporti:</div>
                            <div class="text-sm text-gray-700">${insightLevels.potential_support_levels.join(', ')}</div>
                        </div>
                    ` : ''}
                    ${insightLevels.potential_resistance_levels?.length > 0 ? `
                        <div class="p-2 rounded bg-red-50 border border-red-200">
                            <div class="text-[11px] font-semibold text-negative">Resistenze:</div>
                            <div class="text-sm text-gray-700">${insightLevels.potential_resistance_levels.join(', ')}</div>
                        </div>
                    ` : ''}
                </div>
            ` : ''}
        </div>
    `;

    return html;
}

/**
 * Utility: Formatta numero con separatori
 */
function formatNumber(num, decimals = 2) {
    if (num === null || num === undefined) return '-';
    return Number(num).toLocaleString('it-IT', {
        minimumFractionDigits: decimals,
        maximumFractionDigits: decimals
    });
}

/**
 * Utility: Formatta numero in formato compatto (K, M)
 */
function formatNumberCompact(num) {
    if (num === null || num === undefined) return '-';
    if (num >= 1000000) {
        return (num / 1000000).toFixed(1) + 'M';
    } else if (num >= 1000) {
        return (num / 1000).toFixed(1) + 'K';
    }
    return num.toLocaleString('it-IT');
}
