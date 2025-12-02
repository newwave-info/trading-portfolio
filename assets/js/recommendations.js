/**
 * Recommendations Module - Frontend Integration Fase 6
 * Gestione segnali di trading generati automaticamente dai workflow n8n
 */

console.log('recommendations.js loaded');

// State management
const RecommendationsState = {
    allSignals: [],
    filteredSignals: [],
    filters: {
        urgency: 'all',
        type: 'all',
        status: 'ACTIVE'
    },
    stats: {
        total: 0,
        active: 0,
        by_urgency: {},
        by_type: {}
    }
};

/**
 * Inizializza il modulo recommendations
 */
async function initRecommendations() {
    console.log('[Recommendations] Initializing...');

    // Carica segnali dal database
    await loadSignalsFromAPI();

    // Setup event listeners
    setupEventListeners();

    // Renderizza UI
    renderAllSignals();

    console.log('[Recommendations] Initialized successfully');
}

/**
 * Carica segnali dal database via API
 */
async function loadSignalsFromAPI() {
    try {
        showLoadingState();

        const response = await fetch('/api/recommendations.php?status=' + RecommendationsState.filters.status, {
            method: 'GET'
        });

        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }

        const data = await response.json();

        if (data.success) {
            RecommendationsState.allSignals = data.data || [];
            RecommendationsState.filteredSignals = [...RecommendationsState.allSignals];

            // Calcola statistiche
            calculateStatistics();

            console.log(`[Recommendations] Loaded ${RecommendationsState.allSignals.length} signals from API`);
        } else {
            throw new Error(data.error || 'Failed to load signals');
        }

    } catch (error) {
        console.error('[Recommendations] Error loading signals:', error);
        showErrorState(error.message);
    } finally {
        hideLoadingState();
    }
}

/**
 * Calcola statistiche sui segnali
 */
function calculateStatistics() {
    const signals = RecommendationsState.allSignals;

    RecommendationsState.stats = {
        total: signals.length,
        active: signals.filter(s => s.status === 'ACTIVE').length,
        by_urgency: {
            IMMEDIATO: signals.filter(s => s.urgency === 'IMMEDIATO').length,
            QUESTA_SETTIMANA: signals.filter(s => s.urgency === 'QUESTA_SETTIMANA').length,
            PROSSIME_2_SETTIMANE: signals.filter(s => s.urgency === 'PROSSIME_2_SETTIMANE').length,
            MONITORAGGIO: signals.filter(s => s.urgency === 'MONITORAGGIO').length
        },
        by_type: {
            BUY_LIMIT: signals.filter(s => s.type === 'BUY_LIMIT').length,
            SELL_PARTIAL: signals.filter(s => s.type === 'SELL_PARTIAL').length,
            SET_STOP_LOSS: signals.filter(s => s.type === 'SET_STOP_LOSS').length,
            SET_TAKE_PROFIT: signals.filter(s => s.type === 'SET_TAKE_PROFIT').length,
            REBALANCE: signals.filter(s => s.type === 'REBALANCE').length
        }
    };
}

/**
 * Applica filtri ai segnali
 */
function applyFilters() {
    let filtered = [...RecommendationsState.allSignals];

    // Filtro per urgenza
    if (RecommendationsState.filters.urgency !== 'all') {
        filtered = filtered.filter(s => s.urgency === RecommendationsState.filters.urgency);
    }

    // Filtro per tipo
    if (RecommendationsState.filters.type !== 'all') {
        filtered = filtered.filter(s => s.type === RecommendationsState.filters.type);
    }

    // Filtro per stato
    if (RecommendationsState.filters.status !== 'all') {
        filtered = filtered.filter(s => s.status === RecommendationsState.filters.status);
    }

    RecommendationsState.filteredSignals = filtered;

    console.log(`[Recommendations] Filters applied: ${filtered.length}/${RecommendationsState.allSignals.length} signals`);
}

/**
 * Renderizza tutti i segnali nell'UI
 */
function renderAllSignals() {
    applyFilters();

    // Renderizza statistiche overview
    renderStatisticsOverview();

    // Renderizza azioni immediate (IMMEDIATO + QUESTA_SETTIMANA)
    renderImmediateActions();

    // Renderizza piano operativo (PROSSIME_2_SETTIMANE + MONITORAGGIO)
    renderOperationalPlan();
}

/**
 * Renderizza statistiche overview
 */
function renderStatisticsOverview() {
    const stats = RecommendationsState.stats;
    const statsContainer = document.getElementById('signals-stats-overview');

    if (!statsContainer) return;

    statsContainer.innerHTML = `
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <div class="p-4 bg-white border border-gray-200 rounded">
                <div class="text-xs text-gray-500 uppercase mb-1">Totali</div>
                <div class="text-2xl font-bold text-primary">${stats.total}</div>
            </div>
            <div class="p-4 bg-green-50 border border-green-200 rounded">
                <div class="text-xs text-gray-500 uppercase mb-1">Attivi</div>
                <div class="text-2xl font-bold text-green-700">${stats.active}</div>
            </div>
            <div class="p-4 bg-red-50 border border-red-200 rounded">
                <div class="text-xs text-gray-500 uppercase mb-1">Immediati</div>
                <div class="text-2xl font-bold text-red-700">${stats.by_urgency.IMMEDIATO || 0}</div>
            </div>
            <div class="p-4 bg-yellow-50 border border-yellow-200 rounded">
                <div class="text-xs text-gray-500 uppercase mb-1">Questa Settimana</div>
                <div class="text-2xl font-bold text-yellow-700">${stats.by_urgency.QUESTA_SETTIMANA || 0}</div>
            </div>
        </div>
    `;
}

/**
 * Renderizza azioni immediate
 */
function renderImmediateActions() {
    const tbody = document.getElementById('immediate-actions-tbody');
    if (!tbody) return;

    const immediateSignals = RecommendationsState.filteredSignals.filter(s =>
        s.urgency === 'IMMEDIATO' || s.urgency === 'QUESTA_SETTIMANA'
    );

    if (immediateSignals.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="8" class="px-4 py-8 text-center text-gray-500 italic">
                    Nessuna azione immediata. I segnali vengono generati automaticamente dai workflow n8n.
                </td>
            </tr>
        `;
        return;
    }

    tbody.innerHTML = immediateSignals.map(signal => renderSignalRow(signal)).join('');
}

/**
 * Renderizza piano operativo
 */
function renderOperationalPlan() {
    const container = document.getElementById('operational-plan-container');
    if (!container) return;

    const planSignals = RecommendationsState.filteredSignals.filter(s =>
        s.urgency === 'PROSSIME_2_SETTIMANE' || s.urgency === 'MONITORAGGIO'
    );

    if (planSignals.length === 0) {
        container.innerHTML = `
            <div class="p-4 bg-gray-50 border border-gray-200 text-center text-gray-500 italic">
                Nessun piano operativo disponibile. I segnali vengono generati automaticamente.
            </div>
        `;
        return;
    }

    // Raggruppa per urgenza
    const byPeriod = {
        'PROSSIME_2_SETTIMANE': planSignals.filter(s => s.urgency === 'PROSSIME_2_SETTIMANE'),
        'MONITORAGGIO': planSignals.filter(s => s.urgency === 'MONITORAGGIO')
    };

    container.innerHTML = Object.entries(byPeriod)
        .filter(([_, signals]) => signals.length > 0)
        .map(([period, signals]) => `
            <div class="p-4 border-l-4 ${period === 'PROSSIME_2_SETTIMANE' ? 'border-purple bg-purple-50' : 'border-gray-400 bg-gray-50'} mb-4">
                <div class="font-semibold text-primary mb-2">${formatUrgencyLabel(period)}</div>
                <ul class="text-xs text-gray-700 space-y-2 ml-4">
                    ${signals.map(s => `<li>• <strong>${s.ticker}</strong>: ${s.type} - ${s.rationale_primary || 'Nessuna motivazione'}</li>`).join('')}
                </ul>
            </div>
        `).join('');
}

/**
 * Renderizza una riga di segnale
 */
function renderSignalRow(signal) {
    const urgencyClass = getUrgencyClass(signal.urgency);
    const typeClass = getTypeClass(signal.type);
    const confidenceColor = getConfidenceColor(signal.confidence_score);

    return `
        <tr class="border-b border-gray-200 hover:bg-gray-50" data-signal-id="${signal.id}">
            <td class="px-4 py-3">
                <span class="px-2 py-1 ${urgencyClass} text-xs font-bold">${formatUrgencyLabel(signal.urgency)}</span>
            </td>
            <td class="px-4 py-3 font-semibold text-purple">${signal.ticker || '-'}</td>
            <td class="px-4 py-3">
                <span class="px-2 py-1 ${typeClass} text-xs font-semibold">${formatTypeLabel(signal.type)}</span>
            </td>
            <td class="px-4 py-3 text-xs">${signal.rationale_primary || 'N/A'}</td>
            <td class="px-4 py-3 text-right">
                ${signal.trigger_price ? '€' + parseFloat(signal.trigger_price).toFixed(2) : '-'}
            </td>
            <td class="px-4 py-3 text-center">
                <span class="px-2 py-1 ${confidenceColor} text-xs font-semibold">${signal.confidence_score || 0}%</span>
            </td>
            <td class="px-4 py-3 text-xs text-gray-500">
                ${signal.expires_at ? new Date(signal.expires_at).toLocaleDateString('it-IT') : '-'}
            </td>
            <td class="px-4 py-3 text-center">
                <div class="flex gap-1 justify-center">
                    <button onclick="markSignalExecuted(${signal.id})" class="text-green-600 hover:text-green-700" title="Segna come eseguito">
                        <i class="fa-solid fa-check text-sm"></i>
                    </button>
                    <button onclick="ignoreSignal(${signal.id})" class="text-gray-600 hover:text-gray-700" title="Ignora">
                        <i class="fa-solid fa-ban text-sm"></i>
                    </button>
                    <button onclick="deleteSignal(${signal.id})" class="text-red-600 hover:text-red-700" title="Elimina">
                        <i class="fa-solid fa-trash text-sm"></i>
                    </button>
                </div>
            </td>
        </tr>
    `;
}

/**
 * Utility: Ottieni classe CSS per urgenza
 */
function getUrgencyClass(urgency) {
    const classes = {
        'IMMEDIATO': 'bg-red-100 text-red-700',
        'QUESTA_SETTIMANA': 'bg-orange-100 text-orange-700',
        'PROSSIME_2_SETTIMANE': 'bg-yellow-100 text-yellow-700',
        'MONITORAGGIO': 'bg-gray-100 text-gray-700'
    };
    return classes[urgency] || 'bg-gray-100 text-gray-700';
}

/**
 * Utility: Ottieni classe CSS per tipo segnale
 */
function getTypeClass(type) {
    const classes = {
        'BUY_LIMIT': 'bg-green-100 text-green-700',
        'SELL_PARTIAL': 'bg-red-100 text-red-700',
        'SET_STOP_LOSS': 'bg-orange-100 text-orange-700',
        'SET_TAKE_PROFIT': 'bg-blue-100 text-blue-700',
        'REBALANCE': 'bg-purple-100 text-purple-700'
    };
    return classes[type] || 'bg-gray-100 text-gray-700';
}

/**
 * Utility: Ottieni colore per confidence score
 */
function getConfidenceColor(score) {
    if (score >= 80) return 'bg-green-100 text-green-700';
    if (score >= 60) return 'bg-yellow-100 text-yellow-700';
    return 'bg-gray-100 text-gray-700';
}

/**
 * Utility: Formatta label urgenza
 */
function formatUrgencyLabel(urgency) {
    const labels = {
        'IMMEDIATO': 'Immediato',
        'QUESTA_SETTIMANA': 'Questa Settimana',
        'PROSSIME_2_SETTIMANE': 'Prossime 2 Settimane',
        'MONITORAGGIO': 'Monitoraggio'
    };
    return labels[urgency] || urgency;
}

/**
 * Utility: Formatta label tipo
 */
function formatTypeLabel(type) {
    const labels = {
        'BUY_LIMIT': 'Acquista',
        'SELL_PARTIAL': 'Vendi Parziale',
        'SET_STOP_LOSS': 'Stop Loss',
        'SET_TAKE_PROFIT': 'Take Profit',
        'REBALANCE': 'Ribilanciamento'
    };
    return labels[type] || type;
}

/**
 * Segna segnale come eseguito
 */
async function markSignalExecuted(signalId) {
    if (!confirm('Confermi di aver eseguito questa raccomandazione?')) return;

    try {
        const response = await fetch(`/api/recommendations.php?id=${signalId}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ status: 'EXECUTED' })
        });

        if (response.ok) {
            // Ricarica segnali
            await loadSignalsFromAPI();
            renderAllSignals();
            showSuccessMessage('Raccomandazione segnata come eseguita');
        } else {
            throw new Error('Errore nell\'aggiornamento');
        }
    } catch (error) {
        console.error('[Recommendations] Error marking signal as executed:', error);
        showErrorMessage('Errore nell\'aggiornamento del segnale');
    }
}

/**
 * Ignora segnale
 */
async function ignoreSignal(signalId) {
    if (!confirm('Vuoi ignorare questa raccomandazione?')) return;

    try {
        const response = await fetch(`/api/recommendations.php?id=${signalId}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ status: 'IGNORED' })
        });

        if (response.ok) {
            await loadSignalsFromAPI();
            renderAllSignals();
            showSuccessMessage('Raccomandazione ignorata');
        } else {
            throw new Error('Errore nell\'aggiornamento');
        }
    } catch (error) {
        console.error('[Recommendations] Error ignoring signal:', error);
        showErrorMessage('Errore nell\'aggiornamento del segnale');
    }
}

/**
 * Elimina segnale
 */
async function deleteSignal(signalId) {
    if (!confirm('Sei sicuro di voler eliminare questa raccomandazione?')) return;

    try {
        const response = await fetch(`/api/recommendations.php?id=${signalId}`, {
            method: 'DELETE'
        });

        if (response.ok) {
            await loadSignalsFromAPI();
            renderAllSignals();
            showSuccessMessage('Raccomandazione eliminata');
        } else {
            throw new Error('Errore nell\'eliminazione');
        }
    } catch (error) {
        console.error('[Recommendations] Error deleting signal:', error);
        showErrorMessage('Errore nell\'eliminazione del segnale');
    }
}

/**
 * Setup event listeners
 */
function setupEventListeners() {
    // Filtro urgenza
    const urgencyFilter = document.getElementById('filter-urgency');
    if (urgencyFilter) {
        urgencyFilter.addEventListener('change', (e) => {
            RecommendationsState.filters.urgency = e.target.value;
            renderAllSignals();
        });
    }

    // Filtro tipo
    const typeFilter = document.getElementById('filter-type');
    if (typeFilter) {
        typeFilter.addEventListener('change', (e) => {
            RecommendationsState.filters.type = e.target.value;
            renderAllSignals();
        });
    }

    // Filtro stato
    const statusFilter = document.getElementById('filter-status');
    if (statusFilter) {
        statusFilter.addEventListener('change', async (e) => {
            RecommendationsState.filters.status = e.target.value;
            await loadSignalsFromAPI();
            renderAllSignals();
        });
    }

    // Refresh button
    const refreshBtn = document.getElementById('refresh-signals-btn');
    if (refreshBtn) {
        refreshBtn.addEventListener('click', async () => {
            await loadSignalsFromAPI();
            renderAllSignals();
        });
    }
}

/**
 * Mostra stato loading
 */
function showLoadingState() {
    const container = document.getElementById('recommendations-loading');
    if (container) container.classList.remove('hidden');
}

/**
 * Nascondi stato loading
 */
function hideLoadingState() {
    const container = document.getElementById('recommendations-loading');
    if (container) container.classList.add('hidden');
}

/**
 * Mostra stato errore
 */
function showErrorState(message) {
    const container = document.getElementById('recommendations-error');
    if (container) {
        container.textContent = `Errore: ${message}`;
        container.classList.remove('hidden');
    }
}

/**
 * Mostra messaggio successo
 */
function showSuccessMessage(message) {
    // TODO: Implementare toast notification
    alert(message);
}

/**
 * Mostra messaggio errore
 */
function showErrorMessage(message) {
    // TODO: Implementare toast notification
    alert(message);
}

// Inizializza quando la pagina è caricata
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initRecommendations);
} else {
    initRecommendations();
}
