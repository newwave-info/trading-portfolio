/**
 * Technical Charts - Grafici storici indicatori tecnici
 *
 * Gestisce il caricamento e la visualizzazione dei grafici storici
 * degli indicatori tecnici da technical_snapshots.
 */

// Stato globale
let currentChartInstances = {};

/**
 * Inizializzazione
 */
document.addEventListener('DOMContentLoaded', function() {
    const selectElement = document.getElementById('chartInstrumentSelect');
    if (selectElement) {
        selectElement.addEventListener('change', handleInstrumentChange);
    }
});

/**
 * Handler cambio strumento
 */
async function handleInstrumentChange(event) {
    const isin = event.target.value;
    const option = event.target.selectedOptions[0];

    if (!isin) {
        showEmpty();
        return;
    }

    const ticker = option.dataset.ticker;
    const name = option.dataset.name;

    await loadTechnicalHistory(isin, ticker, name);
}

/**
 * Carica i dati storici dall'API
 */
async function loadTechnicalHistory(isin, ticker, name, days = 30) {
    try {
        showLoading();

        const response = await fetch(`/api/technical-history.php?isin=${encodeURIComponent(isin)}&days=${days}`);
        const json = await response.json();

        if (!json.success) {
            showError(json.error || 'Errore nel caricamento dei dati');
            return;
        }

        if (!json.data || json.data.length === 0) {
            showNoData();
            return;
        }

        // Aggiorna UI e crea grafici
        updateChartsHeader(ticker, name, json.data);
        createAllCharts(json.data);
        showCharts();

    } catch (error) {
        console.error('Error loading technical history:', error);
        showError('Errore di connessione al server');
    }
}

/**
 * Aggiorna header informazioni
 */
function updateChartsHeader(ticker, name, data) {
    document.getElementById('chartInstrumentTitle').textContent = ticker;
    document.getElementById('chartInstrumentSubtitle').textContent = name;

    if (data.length > 0) {
        const firstDate = data[0].snapshot_date;
        const lastDate = data[data.length - 1].snapshot_date;
        document.getElementById('chartDataPeriod').textContent =
            `${formatDate(firstDate)} - ${formatDate(lastDate)} (${data.length} giorni)`;
    }
}

/**
 * Crea tutti i grafici
 */
function createAllCharts(data) {
    // Distruggi grafici esistenti
    destroyAllCharts();

    // Estrai labels (date)
    const labels = data.map(d => formatDate(d.snapshot_date));

    // Crea ogni grafico
    createRSIChart(labels, data);
    createMACDChart(labels, data);
    createVolatilityChart(labels, data);
    createBollingerChart(labels, data);
    createRangePercentileChart(labels, data);
}

/**
 * Grafico RSI con bande 30/70
 */
function createRSIChart(labels, data) {
    const ctx = document.getElementById('rsiChart');
    if (!ctx) return;

    const rsiValues = data.map(d => d.rsi14);

    currentChartInstances.rsi = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'RSI 14',
                    data: rsiValues,
                    borderColor: '#7c3aed',
                    backgroundColor: 'rgba(124, 58, 237, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0,
                    pointStyle: 'rect',
                    pointRadius: 4,
                    pointHoverRadius: 6
                },
                {
                    label: 'Ipercomprato (70)',
                    data: Array(labels.length).fill(70),
                    borderColor: '#dc2626',
                    borderDash: [5, 5],
                    borderWidth: 1,
                    fill: false,
                    pointRadius: 0
                },
                {
                    label: 'Ipervenduto (30)',
                    data: Array(labels.length).fill(30),
                    borderColor: '#16a34a',
                    borderDash: [5, 5],
                    borderWidth: 1,
                    fill: false,
                    pointRadius: 0
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'bottom'
                },
                tooltip: {
                    mode: 'index',
                    intersect: false
                }
            },
            scales: {
                y: {
                    min: 0,
                    max: 100,
                    title: {
                        display: true,
                        text: 'RSI'
                    }
                },
                x: {
                    ticks: {
                        maxRotation: 45,
                        minRotation: 45
                    }
                }
            }
        }
    });
}

/**
 * Grafico MACD
 */
function createMACDChart(labels, data) {
    const ctx = document.getElementById('macdChart');
    if (!ctx) return;

    const macdValues = data.map(d => d.macd_value);
    const macdSignal = data.map(d => d.macd_signal);
    const macdHist = data.map((d, i) => {
        if (d.macd_value !== null && d.macd_signal !== null) {
            return d.macd_value - d.macd_signal;
        }
        return 0;
    });

    currentChartInstances.macd = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [
                {
                    type: 'line',
                    label: 'MACD',
                    data: macdValues,
                    borderColor: '#7c3aed',
                    borderWidth: 3,
                    fill: false,
                    tension: 0,
                    pointStyle: 'rect',
                    pointRadius: 4,
                    order: 1
                },
                {
                    type: 'line',
                    label: 'Signal',
                    data: macdSignal,
                    borderColor: '#52525b',
                    borderWidth: 3,
                    fill: false,
                    tension: 0,
                    pointStyle: 'rect',
                    pointRadius: 4,
                    order: 2
                },
                {
                    type: 'bar',
                    label: 'Histogram',
                    data: macdHist,
                    backgroundColor: macdHist.map(v => v >= 0 ? 'rgba(34, 197, 94, 0.5)' : 'rgba(239, 68, 68, 0.5)'),
                    borderWidth: 0,
                    order: 3
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'bottom'
                },
                tooltip: {
                    mode: 'index',
                    intersect: false
                }
            },
            scales: {
                y: {
                    title: {
                        display: true,
                        text: 'MACD Value'
                    }
                },
                x: {
                    ticks: {
                        maxRotation: 45,
                        minRotation: 45
                    }
                }
            }
        }
    });
}

/**
 * Grafico Volatilità
 */
function createVolatilityChart(labels, data) {
    const ctx = document.getElementById('volatilityChart');
    if (!ctx) return;

    const volValues = data.map(d => d.hist_vol_30d);

    currentChartInstances.volatility = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Volatilità 30d (%)',
                    data: volValues,
                    borderColor: '#52525b',
                    backgroundColor: 'rgba(82, 82, 91, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0,
                    pointStyle: 'rect',
                    pointRadius: 4,
                    pointHoverRadius: 6
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'bottom'
                },
                tooltip: {
                    mode: 'index',
                    intersect: false,
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ': ' + context.parsed.y.toFixed(2) + '%';
                        }
                    }
                }
            },
            scales: {
                y: {
                    min: 0,
                    title: {
                        display: true,
                        text: 'Volatilità (%)'
                    },
                    ticks: {
                        callback: value => value + '%'
                    }
                },
                x: {
                    ticks: {
                        maxRotation: 45,
                        minRotation: 45
                    }
                }
            }
        }
    });
}

/**
 * Grafico Bollinger %B
 */
function createBollingerChart(labels, data) {
    const ctx = document.getElementById('bollingerChart');
    if (!ctx) return;

    const bbValues = data.map(d => d.bb_percent_b !== null ? d.bb_percent_b * 100 : null);

    currentChartInstances.bollinger = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Bollinger %B',
                    data: bbValues,
                    borderColor: '#7c3aed',
                    backgroundColor: 'rgba(124, 58, 237, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0,
                    pointStyle: 'rect',
                    pointRadius: 4,
                    pointHoverRadius: 6
                },
                {
                    label: 'Banda Superiore (100%)',
                    data: Array(labels.length).fill(100),
                    borderColor: '#dc2626',
                    borderDash: [5, 5],
                    borderWidth: 1,
                    fill: false,
                    pointRadius: 0
                },
                {
                    label: 'Centro (50%)',
                    data: Array(labels.length).fill(50),
                    borderColor: '#6b7280',
                    borderDash: [3, 3],
                    borderWidth: 1,
                    fill: false,
                    pointRadius: 0
                },
                {
                    label: 'Banda Inferiore (0%)',
                    data: Array(labels.length).fill(0),
                    borderColor: '#16a34a',
                    borderDash: [5, 5],
                    borderWidth: 1,
                    fill: false,
                    pointRadius: 0
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'bottom'
                },
                tooltip: {
                    mode: 'index',
                    intersect: false,
                    callbacks: {
                        label: function(context) {
                            if (context.datasetIndex === 0) {
                                return context.dataset.label + ': ' + context.parsed.y.toFixed(1) + '%';
                            }
                            return context.dataset.label;
                        }
                    }
                }
            },
            scales: {
                y: {
                    min: -20,
                    max: 120,
                    title: {
                        display: true,
                        text: 'Posizione (%)'
                    },
                    ticks: {
                        callback: value => value + '%'
                    }
                },
                x: {
                    ticks: {
                        maxRotation: 45,
                        minRotation: 45
                    }
                }
            }
        }
    });
}

/**
 * Grafico Range Percentile
 */
function createRangePercentileChart(labels, data) {
    const ctx = document.getElementById('rangePercentileChart');
    if (!ctx) return;

    const percentileValues = data.map(d => d.range_1y_percentile);

    currentChartInstances.rangePercentile = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Range 1Y Percentile',
                    data: percentileValues,
                    borderColor: '#8b5cf6',
                    backgroundColor: 'rgba(139, 92, 246, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0,
                    pointStyle: 'rect',
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    segment: {
                        backgroundColor: ctx => {
                            const value = ctx.p1.parsed.y;
                            if (value > 80) return 'rgba(239, 68, 68, 0.2)';
                            if (value < 20) return 'rgba(34, 197, 94, 0.2)';
                            return 'rgba(139, 92, 246, 0.1)';
                        }
                    }
                },
                {
                    label: 'Vicino Massimi (80%)',
                    data: Array(labels.length).fill(80),
                    borderColor: '#dc2626',
                    borderDash: [5, 5],
                    borderWidth: 1,
                    fill: false,
                    pointRadius: 0
                },
                {
                    label: 'Vicino Minimi (20%)',
                    data: Array(labels.length).fill(20),
                    borderColor: '#16a34a',
                    borderDash: [5, 5],
                    borderWidth: 1,
                    fill: false,
                    pointRadius: 0
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'bottom'
                },
                tooltip: {
                    mode: 'index',
                    intersect: false,
                    callbacks: {
                        label: function(context) {
                            if (context.datasetIndex === 0) {
                                return context.dataset.label + ': ' + context.parsed.y.toFixed(1) + '%';
                            }
                            return context.dataset.label;
                        }
                    }
                }
            },
            scales: {
                y: {
                    min: 0,
                    max: 100,
                    title: {
                        display: true,
                        text: 'Percentile (%)'
                    },
                    ticks: {
                        callback: value => value + '%'
                    }
                },
                x: {
                    ticks: {
                        maxRotation: 45,
                        minRotation: 45
                    }
                }
            }
        }
    });
}

/**
 * Distruggi tutti i grafici esistenti
 */
function destroyAllCharts() {
    Object.values(currentChartInstances).forEach(chart => {
        if (chart) chart.destroy();
    });
    currentChartInstances = {};
}

/**
 * UI States
 */
function showLoading() {
    document.getElementById('chartsEmpty').classList.add('hidden');
    document.getElementById('chartsError').classList.add('hidden');
    document.getElementById('chartsNoData').classList.add('hidden');
    document.getElementById('chartsContainer').classList.add('hidden');
    document.getElementById('chartsLoading').classList.remove('hidden');
}

function showEmpty() {
    document.getElementById('chartsLoading').classList.add('hidden');
    document.getElementById('chartsError').classList.add('hidden');
    document.getElementById('chartsNoData').classList.add('hidden');
    document.getElementById('chartsContainer').classList.add('hidden');
    document.getElementById('chartsEmpty').classList.remove('hidden');
    destroyAllCharts();
}

function showError(message) {
    document.getElementById('chartsLoading').classList.add('hidden');
    document.getElementById('chartsEmpty').classList.add('hidden');
    document.getElementById('chartsNoData').classList.add('hidden');
    document.getElementById('chartsContainer').classList.add('hidden');
    document.getElementById('chartsError').classList.remove('hidden');
    document.getElementById('chartsErrorMessage').textContent = message;
}

function showNoData() {
    document.getElementById('chartsLoading').classList.add('hidden');
    document.getElementById('chartsEmpty').classList.add('hidden');
    document.getElementById('chartsError').classList.add('hidden');
    document.getElementById('chartsContainer').classList.add('hidden');
    document.getElementById('chartsNoData').classList.remove('hidden');
}

function showCharts() {
    document.getElementById('chartsLoading').classList.add('hidden');
    document.getElementById('chartsEmpty').classList.add('hidden');
    document.getElementById('chartsError').classList.add('hidden');
    document.getElementById('chartsNoData').classList.add('hidden');
    document.getElementById('chartsContainer').classList.remove('hidden');
}

/**
 * Utility: Formatta data
 */
function formatDate(dateString) {
    const date = new Date(dateString);
    const day = String(date.getDate()).padStart(2, '0');
    const month = String(date.getMonth() + 1).padStart(2, '0');
    return `${day}/${month}`;
}
