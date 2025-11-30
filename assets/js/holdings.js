/**
 * Holdings Management - Frontend Logic
 * Gestisce CRUD holdings e import CSV
 */

// ============================================
// MODAL MANAGEMENT
// ============================================

function openHoldingModal(holding = null) {
    const modal = document.getElementById('holdingModal');
    const form = document.getElementById('holdingForm');
    const title = document.getElementById('modalTitle');
    const editMode = document.getElementById('editMode');

    // Reset form
    form.reset();

    if (holding) {
        // Edit mode
        title.textContent = 'Modifica Posizione';
        editMode.value = 'true';

        // Popola form
        document.getElementById('isin').value = holding.isin || holding.ticker || '';
        document.getElementById('ticker').value = holding.ticker;
        document.getElementById('name').value = holding.name;
        document.getElementById('quantity').value = holding.quantity;
        document.getElementById('avg_price').value = holding.avg_price;
        document.getElementById('target_allocation').value = holding.target_allocation || '';
        document.getElementById('asset_class').value = holding.asset_class || '';
        document.getElementById('notes').value = holding.notes || '';

        // Disabilita ISIN in edit mode (non modificabile)
        document.getElementById('isin').setAttribute('readonly', true);
        document.getElementById('isin').classList.add('bg-gray-100');
    } else {
        // Add mode
        title.textContent = 'Aggiungi Posizione';
        editMode.value = 'false';

        // Abilita ISIN
        document.getElementById('isin').removeAttribute('readonly');
        document.getElementById('isin').classList.remove('bg-gray-100');
    }

    modal.classList.remove('hidden');
}

function closeHoldingModal() {
    const modal = document.getElementById('holdingModal');
    modal.classList.add('hidden');
}

function openImportModal() {
    const modal = document.getElementById('importModal');
    const form = document.getElementById('importForm');

    form.reset();
    document.getElementById('importResult').classList.add('hidden');
    document.getElementById('importResult').innerHTML = '';

    modal.classList.remove('hidden');
}

function closeImportModal() {
    const modal = document.getElementById('importModal');
    modal.classList.add('hidden');
}

// ============================================
// CRUD OPERATIONS
// ============================================

// Inizializzazione Event Listeners quando DOM è pronto
document.addEventListener('DOMContentLoaded', function() {
    console.log('Holdings.js: DOM ready, inizializzazione event listeners...');

    // Chiudi modal con ESC
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeHoldingModal();
            closeImportModal();
        }
    });

    // Save Holding (Create/Update)
    const holdingForm = document.getElementById('holdingForm');
    if (holdingForm) {
        holdingForm.addEventListener('submit', async function(e) {
    e.preventDefault();

    const formData = {
        isin: document.getElementById('isin').value.trim().toUpperCase() || document.getElementById('ticker').value.trim().toUpperCase(),
        ticker: document.getElementById('ticker').value.trim().toUpperCase(),
        name: document.getElementById('name').value.trim(),
        quantity: parseFloat(document.getElementById('quantity').value.replace(',', '.')),
        avg_price: parseFloat(document.getElementById('avg_price').value.replace(',', '.')),
        target_allocation: parseFloat(document.getElementById('target_allocation').value) || 0,
        asset_class: document.getElementById('asset_class').value.trim() || 'Unknown',
        notes: document.getElementById('notes').value.trim(),
        is_update: document.getElementById('editMode').value === 'true'
    };

    try {
        const response = await fetch('/api/holdings.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(formData)
        });

        const result = await response.json();

        if (result.success) {
            showNotification('success', result.message);
            closeHoldingModal();

            // Salva vista corrente prima del reload
            localStorage.setItem('activeView', 'holdings');

            // Reload page per aggiornare dati
            setTimeout(() => {
                window.location.reload();
            }, 500);
        } else {
            showNotification('error', result.error || 'Errore salvataggio posizione');
        }
    } catch (error) {
        console.error('Error saving holding:', error);
        showNotification('error', 'Errore di connessione');
    }
    });
    } else {
        console.warn('holdingForm non trovato');
    }

    // Import CSV Form
    const importForm = document.getElementById('importForm');
    if (importForm) {
        importForm.addEventListener('submit', async function(e) {
            e.preventDefault();

            const overwriteConfirm = document.getElementById('overwriteConfirm');
            if (!overwriteConfirm.checked) {
                showNotification('warning', 'Devi confermare la sovrascrittura delle posizioni esistenti');
                return;
            }

            const fileInput = document.getElementById('csvFile');
            const file = fileInput.files[0];

            if (!file) {
                showNotification('error', 'Seleziona un file CSV');
                return;
            }

            const formData = new FormData();
            formData.append('csv_file', file);

            try {
                // Mostra loading
                const importResult = document.getElementById('importResult');
                importResult.classList.remove('hidden');
                importResult.innerHTML = `
                    <div class="p-4 bg-blue-50 border border-blue-200 text-sm text-blue-800">
                        <i class="fa-solid fa-spinner fa-spin mr-2"></i>
                        Importazione in corso...
                    </div>
                `;

                const response = await fetch('/api/holdings.php?action=import', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    importResult.innerHTML = `
                        <div class="p-4 bg-green-50 border border-green-200 text-sm text-green-800">
                            <i class="fa-solid fa-check-circle mr-2"></i>
                            <strong>Importazione completata!</strong>
                            <br>
                            ${result.imported} posizioni importate con successo.
                            ${result.errors && result.errors.length > 0 ? `<br><br><strong>Errori:</strong><ul class="list-disc ml-6 mt-2">${result.errors.map(err => '<li>' + err + '</li>').join('')}</ul>` : ''}
                        </div>
                    `;

                    // Salva vista corrente prima del reload
                    localStorage.setItem('activeView', 'holdings');

                    // Reload dopo 2 secondi
                    setTimeout(() => {
                        window.location.reload();
                    }, 2000);

                } else {
                    importResult.innerHTML = `
                        <div class="p-4 bg-red-50 border border-red-200 text-sm text-red-800">
                            <i class="fa-solid fa-exclamation-circle mr-2"></i>
                            <strong>Errore importazione:</strong> ${result.error || 'Errore sconosciuto'}
                        </div>
                    `;
                }
            } catch (error) {
                console.error('Error importing CSV:', error);
                const importResult = document.getElementById('importResult');
                importResult.innerHTML = `
                    <div class="p-4 bg-red-50 border border-red-200 text-sm text-red-800">
                        <i class="fa-solid fa-exclamation-circle mr-2"></i>
                        <strong>Errore di connessione</strong>
                    </div>
                `;
            }
        });
    } else {
        console.warn('importForm non trovato');
    }

    // Ripristina vista corrente dopo reload
    const activeView = localStorage.getItem('activeView');
    if (activeView && activeView !== 'dashboard') {
        console.log('Ripristino vista:', activeView);
        // Simula click sul link della sidebar
        const viewLink = document.querySelector(`[data-view="${activeView}"]`);
        if (viewLink) {
            viewLink.click();
            // Pulisci localStorage
            localStorage.removeItem('activeView');
        }
    }
});

// Edit Holding
function editHolding(holding) {
    openHoldingModal(holding);
}

// Delete Holding
async function deleteHolding(isin, ticker) {
    if (!confirm(`Sei sicuro di voler eliminare la posizione ${ticker} (${isin})?`)) {
        return;
    }

    // Se l'ISIN non è disponibile (es. dati DB senza colonna), usa il ticker per la delete
    const hasIsin = Boolean(isin);
    const identifier = hasIsin ? `isin=${encodeURIComponent(isin)}` : (ticker ? `ticker=${encodeURIComponent(ticker)}` : '');

    if (!identifier) {
        showNotification('error', 'Nessun identificatore valido per la posizione da eliminare');
        return;
    }

    try {
        const response = await fetch(`/api/holdings.php?${identifier}`, {
            method: 'DELETE'
        });

        const result = await response.json();

        if (result.success) {
            showNotification('success', result.message);

            // Rimuovi riga dalla tabella (animazione)
            const row = document.querySelector(`tr[data-isin="${isin}"]`);
            if (row) {
                row.style.opacity = '0';
                row.style.transition = 'opacity 0.3s';
                setTimeout(() => {
                    row.remove();

                    // Se non ci sono più righe, reload
                    const tbody = document.querySelector('#holdingsTable tbody');
                    if (tbody.children.length === 0) {
                        window.location.reload();
                    }
                }, 300);
            }
        } else {
            showNotification('error', result.error || 'Errore eliminazione posizione');
        }
    } catch (error) {
        console.error('Error deleting holding:', error);
        showNotification('error', 'Errore di connessione');
    }
}

// ============================================
// UTILITY
// ============================================

function showNotification(type, message) {
    // Crea notification toast
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 z-[60] px-6 py-4 rounded-lg shadow-lg text-white max-w-md transition-all duration-300 ${
        type === 'success' ? 'bg-green-600' :
        type === 'error' ? 'bg-red-600' :
        type === 'warning' ? 'bg-yellow-600' :
        'bg-blue-600'
    }`;

    const icon = type === 'success' ? 'fa-check-circle' :
                 type === 'error' ? 'fa-exclamation-circle' :
                 type === 'warning' ? 'fa-exclamation-triangle' :
                 'fa-info-circle';

    notification.innerHTML = `
        <div class="flex items-center gap-3">
            <i class="fa-solid ${icon} text-xl"></i>
            <div class="text-sm font-medium">${message}</div>
        </div>
    `;

    document.body.appendChild(notification);

    // Animazione entrata
    setTimeout(() => {
        notification.style.transform = 'translateY(0)';
        notification.style.opacity = '1';
    }, 10);

    // Rimuovi dopo 3 secondi
    setTimeout(() => {
        notification.style.opacity = '0';
        notification.style.transform = 'translateY(-20px)';
        setTimeout(() => {
            notification.remove();
        }, 300);
    }, 3000);
}

console.log('Holdings management loaded');
