# 05 – Frontend (PHP + JS)

Questo documento descrive la struttura del frontend di **ETF Portfolio Manager – Fineco + n8n**, composto da template PHP, CSS e JavaScript per le viste principali.

---

## 1. Architettura frontend

Il frontend è **server‑side rendered** con PHP 8.2:
- Nessun framework JS pesante (React/Vue) per mantenere la stack semplice.
- Template PHP con logica di presentazione minimale.
- CSS custom responsive (mobile‑first).
- JavaScript vanilla per interazioni dinamiche (AJAX, validazioni, charts).

**Struttura cartelle**:
php-app/
├── public/
│ ├── index.php # Entry point dashboard
│ ├── login.php # Pagina login
│ ├── register.php # Pagina registrazione
│ ├── holdings.php # Lista holdings
│ ├── transactions.php # Timeline transazioni
│ ├── analysis.php # Analisi tecniche
│ ├── opportunities.php # Opportunità ETF
│ ├── css/
│ │ ├── style.css # Stili principali
│ │ └── dashboard.css # Stili specifici dashboard
│ └── js/
│ ├── main.js # Logica comune
│ ├── dashboard.js # Grafici dashboard
│ └── holdings.js # CRUD holdings
└── src/
└── services/
└── ChartRenderer.php # Helper per grafici


---

## 2. Template base

### 2.1 Layout comune (`layout.php`)

Ogni pagina include un template base che gestisce:
- Header con menu navigazione
- Footer
- Inclusione CSS/JS
- Gestione messaggi flash (success/error)

<?php // File: src/templates/layout.php function renderLayout($title, $content) { $user = $_SESSION['user'] ?? null; $csrfToken = $_SESSION['csrf_token'] ?? ''; ?> <!DOCTYPE html> <html lang="it"> <head> <meta charset="UTF-8"> <meta name="viewport" content="width=device-width, initial-scale=1.0"> <title><?= htmlspecialchars($title) ?> - ETF Portfolio Manager</title> <link rel="stylesheet" href="/css/style.css"> <link rel="stylesheet" href="/css/dashboard.css"> </head> <body> <nav class="navbar"> <div class="container"> <a href="/" class="logo">ETF Portfolio Manager</a> <?php if ($user): ?> <ul class="nav-menu"> <li><a href="/dashboard.php">Dashboard</a></li> <li><a href="/holdings.php">Posizioni</a></li> <li><a href="/transactions.php">Transazioni</a></li> <li><a href="/analysis.php">Analisi</a></li> <li><a href="/opportunities.php">Opportunità</a></li> <li><a href="/logout.php">Logout (<?= htmlspecialchars($user['email']) ?>)</a></li> </ul> <?php endif; ?> </div> </nav>
<main class="container">
    <?php if (isset($_SESSION['flash_success'])): ?>
        <div class="alert alert-success"><?= htmlspecialchars($_SESSION['flash_success']) ?></div>
        <?php unset($_SESSION['flash_success']); ?>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['flash_error'])): ?>
        <div class="alert alert-error"><?= htmlspecialchars($_SESSION['flash_error']) ?></div>
        <?php unset($_SESSION['flash_error']); ?>
    <?php endif; ?>
    
    <?= $content ?>
</main>

<footer class="footer">
    <p>&copy; 2025 ETF Portfolio Manager – Non è consulenza finanziaria</p>
</footer>

<script src="/js/main.js"></script>
</body> </html> <?php } ?> ```
3. Dashboard (index.php)
3.1 Struttura pagina
La dashboard mostra:

Card riepilogo portafoglio (valore, P&L, dividendi)

Grafico allocazione (donut chart)

Tabella top holdings

Feed alert da n8n

<?php
// File: public/index.php
require_once '../src/lib/SessionManager.php';
require_once '../src/lib/PortfolioService.php';

SessionManager::requireLogin();
$userId = $_SESSION['user_id'];

// Seleziona portafoglio (default: primo attivo)
$portfolioId = $_GET['portfolio_id'] ?? PortfolioService::getDefaultPortfolioId($userId);

// Carica snapshot
$snapshot = PortfolioService::getSnapshot($portfolioId);

// Carica top holdings
$topHoldings = PortfolioService::getTopHoldings($portfolioId, 5);

// Carica ultimi alert
$alerts = AnalysisService::getLatestAlerts($portfolioId, 10);

// Renderizza
$content = renderDashboard($snapshot, $topHoldings, $alerts);
renderLayout('Dashboard', $content);
?>
3.2 Componenti dashboard
Card riepilogo:

<div class="dashboard-cards">
    <div class="card">
        <h3>Valore Totale</h3>
        <p class="value"><?= number_format($snapshot['total_value'], 2, ',', '.') ?> €</p>
    </div>
    <div class="card">
        <h3>P&L Non Realizzato</h3>
        <p class="value <?= $snapshot['unrealized_pnl'] >= 0 ? 'positive' : 'negative' ?>">
            <?= number_format($snapshot['unrealized_pnl'], 2, ',', '.') ?> €
        </p>
    </div>
    <div class="card">
        <h3>Dividendi Totali</h3>
        <p class="value"><?= number_format($snapshot['total_dividends'], 2, ',', '.') ?> €</p>
    </div>
</div>
Grafico allocazione (usando Chart.js):

<canvas id="allocationChart" width="400" height="400"></canvas>
<script>
const ctx = document.getElementById('allocationChart').getContext('2d');
const allocationData = <?= json_encode($allocationData) ?>;
new Chart(ctx, {
    type: 'doughnut',
    data: {
        labels: allocationData.map(d => d.ticker),
        datasets: [{
            data: allocationData.map(d => d.percentage),
            backgroundColor: ['#4CAF50', '#2196F3', '#FF9800', '#9C27B0']
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { position: 'right' }
        }
    }
});
</script>
4. Holdings (holdings.php)
4.1 Lista holdings
Tabella sortable con colonne:

ISIN / Ticker / Nome

Quantità

Prezzo medio / Prezzo corrente

Valore di mercato

P&L (€ e %)

Drift vs target allocation

Azioni (edit, delete)

// File: public/holdings.php
$holdings = HoldingService::getHoldings($portfolioId);
$content = renderHoldingsTable($holdings);
renderLayout('Posizioni', $content);
Tabella HTML:

<table class="data-table" id="holdingsTable">
    <thead>
        <tr>
            <th data-sort="isin">ISIN</th>
            <th data-sort="ticker">Ticker</th>
            <th data-sort="name">Nome</th>
            <th data-sort="quantity">Quantità</th>
            <th data-sort="avg_price">Prezzo Medio</th>
            <th data-sort="current_price">Prezzo Attuale</th>
            <th data-sort="market_value">Valore</th>
            <th data-sort="pnl">P&L</th>
            <th data-sort="drift">Drift</th>
            <th>Azioni</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($holdings as $h): ?>
        <tr>
            <td><?= htmlspecialchars($h['isin']) ?></td>
            <td><?= htmlspecialchars($h['ticker']) ?></td>
            <td><?= htmlspecialchars($h['instrument_name']) ?></td>
            <td><?= number_format($h['quantity'], 2, ',', '.') ?></td>
            <td><?= number_format($h['avg_price'], 2, ',', '.') ?> €</td>
            <td><?= number_format($h['current_price'], 2, ',', '.') ?> €</td>
            <td><?= number_format($h['market_value'], 2, ',', '.') ?> €</td>
            <td class="<?= $h['unrealized_pnl'] >= 0 ? 'positive' : 'negative' ?>">
                <?= number_format($h['unrealized_pnl'], 2, ',', '.') ?> €
                (<?= number_format($h['pnl_percentage'], 2, ',', '.') ?>%)
            </td>
            <td class="<?= abs($h['drift']) > 5 ? 'drift-high' : (abs($h['drift']) > 2 ? 'drift-medium' : 'drift-low') ?>">
                <?= number_format($h['drift'], 2, ',', '.') ?>%
            </td>
            <td>
                <a href="/holdings/edit.php?id=<?= $h['holding_id'] ?>" class="btn btn-sm">Modifica</a>
                <button class="btn btn-sm btn-danger" onclick="deleteHolding(<?= $h['holding_id'] ?>)">Elimina</button>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
4.2 Modale creazione holding
Form in overlay per aggiungere nuova posizione:

<button id="addHoldingBtn" class="btn btn-primary">+ Nuova Posizione</button>

<div id="addHoldingModal" class="modal">
    <div class="modal-content">
        <h3>Aggiungi Posizione</h3>
        <form id="addHoldingForm">
            <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
            <input type="hidden" name="portfolio_id" value="<?= $portfolioId ?>">
            
            <label>ISIN *</label>
            <input type="text" name="isin" required pattern="[A-Z0-9]{12}" maxlength="12">
            
            <label>Quantità *</label>
            <input type="number" name="quantity" step="0.001" required>
            
            <label>Prezzo Medio *</label>
            <input type="number" name="avg_price" step="0.01" required>
            
            <label>Data Acquisto *</label>
            <input type="date" name="purchase_date" required>
            
            <label>Target Allocation (%)</label>
            <input type="number" name="target_allocation" step="0.01" min="0" max="100">
            
            <button type="submit" class="btn btn-primary">Salva</button>
            <button type="button" class="btn btn-secondary" onclick="closeModal()">Annulla</button>
        </form>
    </div>
</div>

<script>
document.getElementById('addHoldingForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const formData = new FormData(e.target);
    const response = await fetch('/api/holdings', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(Object.fromEntries(formData))
    });
    const result = await response.json();
    if (result.success) {
        location.reload();
    } else {
        alert('Errore: ' + result.error);
    }
});
</script>
5. Transazioni (transactions.php)
5.1 Timeline transazioni
Tabella cronologica con filtri per tipo, data, ISIN.

$transactions = TransactionService::getTransactions($portfolioId, $filters);
$content = renderTransactionsTable($transactions);
renderLayout('Transazioni', $content);
Filtri UI:

<div class="filters">
    <select id="filterType">
        <option value="">Tutti i tipi</option>
        <option value="BUY">Acquisti</option>
        <option value="SELL">Vendite</option>
        <option value="DIVIDEND">Dividendi</option>
    </select>
    <input type="date" id="filterFromDate">
    <input type="date" id="filterToDate">
    <button onclick="applyFilters()">Applica</button>
</div>
6. Analisi (analysis.php)
6.1 Segnali tecnici per titolo
Tabella con ultimi segnali da workflow n8n.

$analysis = AnalysisService::getLatestByPortfolio($portfolioId);
$content = renderAnalysisTable($analysis);
renderLayout('Analisi', $content);
Visualizzazione segnali:

<table class="data-table">
    <thead>
        <tr>
            <th>ISIN</th>
            <th>Ticker</th>
            <th>Segnale</th>
            <th>Confidenza</th>
            <th>Data Analisi</th>
            <th>Dettagli</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($analysis as $a): ?>
        <tr>
            <td><?= $a['isin'] ?></td>
            <td><?= $a['ticker'] ?></td>
            <td>
                <span class="signal-badge signal-<?= strtolower($a['signal']) ?>">
                    <?= $a['signal'] ?>
                </span>
            </td>
            <td><?= number_format($a['confidence_score'] * 100, 0) ?>%</td>
            <td><?= date('d/m/Y H:i', strtotime($a['analyzed_at'])) ?></td>
            <td>
                <button class="btn btn-sm" onclick="showAnalysisDetails(<?= $a['analysis_id'] ?>)">
                    Dettagli
                </button>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
7. Opportunità (opportunities.php)
7.1 Lista opportunità ETF
Tabella di ETF suggeriti da n8n, ordinabili per confidence.

$opportunities = OpportunityService::getScoutedETFs($userId);
$content = renderOpportunitiesTable($opportunities);
renderLayout('Opportunità', $content);
8. Componenti JavaScript
8.1 main.js – Funzioni comuni
// CSRF token per AJAX
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

// Fetch wrapper con gestione errori
async function apiRequest(url, options = {}) {
    const headers = {
        'Content-Type': 'application/json',
        ...options.headers
    };
    
    if (csrfToken) {
        headers['X-CSRF-Token'] = csrfToken;
    }
    
    const response = await fetch(url, { ...options, headers });
    const data = await response.json();
    
    if (!response.ok) {
        throw new Error(data.error || 'Request failed');
    }
    
    return data;
}

// Gestione messaggi flash
function showFlash(message, type = 'success') {
    const container = document.querySelector('.flash-messages');
    const alert = document.createElement('div');
    alert.className = `alert alert-${type}`;
    alert.textContent = message;
    container.appendChild(alert);
    setTimeout(() => alert.remove(), 5000);
}
8.2 dashboard.js – Grafici
// Inizializza grafico allocazione
function initAllocationChart(data) {
    const ctx = document.getElementById('allocationChart');
    if (!ctx) return;
    
    new Chart(ctx.getContext('2d'), {
        type: 'doughnut',
        data: {
            labels: data.map(d => d.ticker),
            datasets: [{
                data: data.map(d => d.percentage),
                backgroundColor: ['#4CAF50', '#2196F3', '#FF9800', '#9C27B0', '#F44336']
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'right' },
                tooltip: {
                    callbacks: {
                        label: (context) => {
                            const item = data[context.dataIndex];
                            return `${item.ticker}: ${item.percentage.toFixed(2)}% (€${item.market_value.toLocaleString()})`;
                        }
                    }
                }
            }
        }
    });
}
9. Responsive design
9.1 Breakpoints
/* style.css */
:root {
    --breakpoint-mobile: 768px;
    --breakpoint-tablet: 1024px;
}

/* Mobile first */
.container {
    width: 100%;
    padding: 0 1rem;
}

@media (min-width: 768px) {
    .container {
        max-width: 750px;
        margin: 0 auto;
    }
}

@media (min-width: 1024px) {
    .container {
        max-width: 980px;
    }
}
9.2 Menu mobile (hamburger)
// Toggle menu mobile
document.querySelector('.menu-toggle').addEventListener('click', () => {
    document.querySelector('.nav-menu').classList.toggle('active');
});
10. Performance ottimizzazioni
Minificazione: In produzione, minificare CSS/JS (tool come gulp o webpack opzionale).

Caching: Impostare header cache per asset statici (Apache .htaccess).

Lazy loading: Immagini e grafici caricati solo quando visibili (Intersection Observer API).

Debouncing: Input di ricerca/filtro con debounce 300ms per ridurre chiamate API.

11. Accessibilità (WCAG 2.1 AA)
Colori: Contrasto minimo 4.5:1 (testo su sfondo).

Focus: Stili visibili per elementi focusabili.

ARIA: Label per form e tabelle.

Alt text: Immagini decorative con alt vuoto.

12. TODO frontend
 PWA (Progressive Web App) con service worker per offline.

 Notifiche push per alert critici (richiede HTTPS).

 Tema scuro (dark mode).

 Drag & drop per riordinare holdings (opzionale).

 Export PDF report mensile.
