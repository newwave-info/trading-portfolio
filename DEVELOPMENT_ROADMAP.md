# 🗺️ ETF Portfolio Manager - Development Roadmap

**Ultimo aggiornamento:** 26 Novembre 2025
**Versione:** 0.6-alpha
**Stato:** Frontend completato ✅ | Backend parziale ⚠️ | Integrazione dati in corso 🚧

---

## 📊 Situazione Corrente

### ✅ Completato
- **Frontend UI/UX completo** (7 viste, 32+ widget, tooltips, grafici)
- **CRUD Holdings** (form, API REST, import CSV Fineco)
- **PortfolioManager** (calcolo metriche automatiche)
- **Storage JSON** (portfolio.json funzionante)

### 🚧 In Corso
- **Gestione Dati Dinamici** (quotazioni, analisi tecnica, opportunità)
- **Integrazione n8n** (setup e workflows)

### ❌ Non Iniziato
- **Quotazioni Real-time** da API esterne
- **Sistema Snapshots** per performance storica
- **Workflow n8n** (4 workflow principali)
- **Database MariaDB** (migrazione da JSON)
- **Autenticazione** multi-utente

---

## 🎯 STEP 1: Sistema Quotazioni Real-time

**Priorità:** ⚡ MASSIMA
**Tempo stimato:** 4-6 ore
**Dipendenze:** Nessuna
**Stato:** ❌ Non iniziato

### Obiettivo
Sostituire i prezzi mock con quotazioni reali aggiornate automaticamente per calcolare metriche corrette del portafoglio.

### Task Dettagliati

#### 1.1 Creare Endpoint PHP Ricezione Prezzi
**File:** `api/update-prices.php`

**Checklist:**
- [ ] Creare file `api/update-prices.php`
- [ ] Implementare validazione input JSON
- [ ] Chiamare `PortfolioManager->updatePrices($prices)`
- [ ] Gestione errori e logging
- [ ] Autenticazione HMAC-SHA256 (opzionale fase 1)

**Codice Skeleton:**
```php
<?php
/**
 * API Endpoint - Update Prices
 * POST /api/update-prices.php
 * Body: { "prices": { "ISIN": price, ... }, "timestamp": "..." }
 */

header('Content-Type: application/json');
require_once __DIR__ . '/../lib/PortfolioManager.php';

try {
    // Validate request
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Method not allowed');
    }

    $input = json_decode(file_get_contents('php://input'), true);
    if (!isset($input['prices']) || !is_array($input['prices'])) {
        throw new Exception('Invalid input: prices array required');
    }

    // Update prices
    $pm = new PortfolioManager();
    $success = $pm->updatePrices($input['prices']);

    if ($success) {
        echo json_encode([
            'success' => true,
            'message' => 'Prices updated successfully',
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    } else {
        throw new Exception('Failed to update prices');
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
```

#### 1.2 Setup n8n Container
**Comando Docker Compose:**

**Checklist:**
- [ ] Aggiungere service n8n in `docker-compose.yml`
- [ ] Configurare volume persistente per workflows
- [ ] Esporre porta 5678
- [ ] Avviare container: `docker-compose up -d n8n`
- [ ] Accedere a http://localhost:5678

**docker-compose.yml:**
```yaml
services:
  n8n:
    image: n8nio/n8n:latest
    container_name: trading-portfolio-n8n
    ports:
      - "5678:5678"
    environment:
      - N8N_BASIC_AUTH_ACTIVE=true
      - N8N_BASIC_AUTH_USER=admin
      - N8N_BASIC_AUTH_PASSWORD=changeme
      - GENERIC_TIMEZONE=Europe/Rome
      - TZ=Europe/Rome
    volumes:
      - ./n8n_data:/home/node/.n8n
    restart: unless-stopped
```

#### 1.3 Creare Workflow n8n "Quotazioni"
**Nome Workflow:** `Portfolio - Update Prices`

**Checklist:**
- [ ] Creare nuovo workflow in n8n
- [ ] Node 1: Trigger Schedule (ogni 1 ora) o Webhook
- [ ] Node 2: HTTP Request GET portfolio ISINs
- [ ] Node 3: Loop sui singoli ISIN
- [ ] Node 4: HTTP Request Yahoo Finance API
- [ ] Node 5: Formatta output JSON
- [ ] Node 6: HTTP Request POST a `/api/update-prices.php`
- [ ] Testare workflow manualmente
- [ ] Attivare schedulazione

**Yahoo Finance API Endpoint:**
```
GET https://query1.finance.yahoo.com/v8/finance/chart/{TICKER}
Params: interval=1d, range=1d
```

**Mapping ISIN → Ticker:**
- Potrebbe servire una lookup table o API per convertire ISIN in ticker Yahoo
- Alternativa: salvare `yahoo_ticker` in portfolio.json

#### 1.4 Pulsante Frontend "Aggiorna Prezzi"
**File:** `views/tabs/holdings.php`

**Checklist:**
- [ ] Aggiungere pulsante nell'header Holdings
- [ ] Funzione JavaScript `triggerPriceUpdate()`
- [ ] Chiamata fetch a endpoint n8n webhook (trigger manuale)
- [ ] Loading state durante aggiornamento
- [ ] Notification success/error
- [ ] Reload automatico dopo aggiornamento

**Codice Skeleton:**
```javascript
async function triggerPriceUpdate() {
    const btn = document.getElementById('updatePricesBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Aggiornamento...';

    try {
        // Trigger n8n webhook
        const response = await fetch('http://localhost:5678/webhook/update-prices', {
            method: 'POST'
        });

        if (response.ok) {
            showNotification('success', 'Prezzi aggiornati con successo!');
            setTimeout(() => window.location.reload(), 1000);
        } else {
            throw new Error('Failed to update prices');
        }
    } catch (error) {
        showNotification('error', 'Errore aggiornamento prezzi');
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="fa-solid fa-sync"></i> Aggiorna Prezzi';
    }
}
```

### Deliverable STEP 1
- ✅ Endpoint `/api/update-prices.php` funzionante
- ✅ n8n container attivo
- ✅ Workflow "Quotazioni" schedulato ogni ora
- ✅ Pulsante frontend trigger manuale
- ✅ Metriche portfolio calcolate su prezzi reali

### Test di Accettazione
1. Avviare workflow n8n manualmente
2. Verificare che `portfolio.json` → `current_price` sia aggiornato
3. Verificare che dashboard mostri valori corretti
4. Premere pulsante "Aggiorna Prezzi" nel frontend
5. Verificare notifica success e reload

---

## 🎯 STEP 2: Sistema Snapshots Performance Storica

**Priorità:** ⚡ ALTA
**Tempo stimato:** 3-4 ore
**Dipendenze:** STEP 1 (preferibile ma non bloccante)
**Stato:** ❌ Non iniziato

### Obiettivo
Storicizzare lo stato del portafoglio giornalmente per generare grafici performance reali invece di dati mock.

### Task Dettagliati

#### 2.1 Creare Struttura Snapshots JSON
**File:** `data/snapshots.json`

**Checklist:**
- [ ] Creare file `data/snapshots.json` con struttura iniziale
- [ ] Definire schema snapshot
- [ ] Aggiungere metodo `createSnapshot()` in PortfolioManager
- [ ] Aggiungere metodo `getSnapshots($limit, $offset)` in PortfolioManager
- [ ] Test creazione snapshot manuale

**Struttura snapshots.json:**
```json
{
  "snapshots": [
    {
      "id": "snapshot_20251126",
      "date": "2025-11-26",
      "timestamp": "2025-11-26T23:59:59Z",
      "metadata": {
        "total_value": 125750.50,
        "total_invested": 100000.00,
        "unrealized_pnl": 25750.50,
        "unrealized_pnl_pct": 25.75,
        "total_dividends": 850.00,
        "holdings_count": 5
      },
      "holdings": [
        {
          "isin": "IE00B3RBWM25",
          "ticker": "IWDA",
          "quantity": 250.50,
          "avg_price": 75.20,
          "current_price": 89.45,
          "market_value": 22407.23,
          "unrealized_pnl": 3570.73,
          "current_allocation": 17.82
        }
      ]
    }
  ]
}
```

#### 2.2 Aggiungere Metodi in PortfolioManager
**File:** `lib/PortfolioManager.php`

**Checklist:**
- [ ] Metodo `createSnapshot()`: salva stato corrente
- [ ] Metodo `getSnapshots($days = 30)`: legge ultimi N giorni
- [ ] Metodo `getSnapshotsByDateRange($start, $end)`
- [ ] Metodo `getMonthlySnapshots()`: aggrega per mese
- [ ] Gestione file size (limita a 365 giorni, poi archivio)

**Codice Skeleton:**
```php
public function createSnapshot(): bool {
    $snapshotsPath = __DIR__ . '/../data/snapshots.json';

    // Load existing snapshots
    if (file_exists($snapshotsPath)) {
        $snapshots = json_decode(file_get_contents($snapshotsPath), true);
    } else {
        $snapshots = ['snapshots' => []];
    }

    // Create new snapshot
    $snapshot = [
        'id' => 'snapshot_' . date('Ymd'),
        'date' => date('Y-m-d'),
        'timestamp' => date('c'),
        'metadata' => [
            'total_value' => $this->data['metadata']['total_value'],
            'total_invested' => $this->data['metadata']['total_invested'],
            'unrealized_pnl' => $this->data['metadata']['unrealized_pnl'],
            'unrealized_pnl_pct' => $this->data['metadata']['unrealized_pnl_pct'],
            'total_dividends' => $this->data['metadata']['total_dividends'],
            'holdings_count' => count($this->data['holdings'])
        ],
        'holdings' => $this->data['holdings']
    ];

    // Check if snapshot for today already exists
    $today = date('Y-m-d');
    $existing = array_filter($snapshots['snapshots'], fn($s) => $s['date'] === $today);

    if (!empty($existing)) {
        // Update existing snapshot
        foreach ($snapshots['snapshots'] as &$s) {
            if ($s['date'] === $today) {
                $s = $snapshot;
                break;
            }
        }
    } else {
        // Add new snapshot
        $snapshots['snapshots'][] = $snapshot;
    }

    // Keep only last 365 days
    usort($snapshots['snapshots'], fn($a, $b) => $b['date'] <=> $a['date']);
    $snapshots['snapshots'] = array_slice($snapshots['snapshots'], 0, 365);

    // Save
    return file_put_contents($snapshotsPath, json_encode($snapshots, JSON_PRETTY_PRINT)) !== false;
}

public function getSnapshots(int $days = 30): array {
    $snapshotsPath = __DIR__ . '/../data/snapshots.json';

    if (!file_exists($snapshotsPath)) {
        return [];
    }

    $data = json_decode(file_get_contents($snapshotsPath), true);
    $snapshots = $data['snapshots'] ?? [];

    // Get last N days
    $cutoffDate = date('Y-m-d', strtotime("-{$days} days"));
    return array_filter($snapshots, fn($s) => $s['date'] >= $cutoffDate);
}
```

#### 2.3 Creare Endpoint API Create Snapshot
**File:** `api/create-snapshot.php`

**Checklist:**
- [ ] Creare endpoint POST `/api/create-snapshot.php`
- [ ] Validazione richiesta
- [ ] Chiamata `PortfolioManager->createSnapshot()`
- [ ] Response JSON con conferma

**Codice:**
```php
<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../lib/PortfolioManager.php';

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Method not allowed');
    }

    $pm = new PortfolioManager();
    $success = $pm->createSnapshot();

    if ($success) {
        echo json_encode([
            'success' => true,
            'message' => 'Snapshot created successfully',
            'date' => date('Y-m-d'),
            'timestamp' => date('c')
        ]);
    } else {
        throw new Exception('Failed to create snapshot');
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
```

#### 2.4 Aggiornare portfolio_data.php per Usare Snapshots
**File:** `data/portfolio_data.php`

**Checklist:**
- [ ] Sostituire `monthly_performance` mock con dati da snapshots
- [ ] Calcolare dati ultimi 5 giorni per grafico "Ultimi 5 Giorni"
- [ ] Calcolare dati mensili per grafico "Andamento Annuale (2025)"
- [ ] Mantenere retrocompatibilità se snapshots.json non esiste

**Codice:**
```php
// Load snapshots for charts
$snapshots = $portfolioManager->getSnapshots(365); // Ultimi 365 giorni

// Monthly Performance (per grafico "Andamento Annuale")
$monthly_performance = [];
if (!empty($snapshots)) {
    // Group by month
    $byMonth = [];
    foreach ($snapshots as $snap) {
        $month = date('M', strtotime($snap['date']));
        // Take last snapshot of each month
        if (!isset($byMonth[$month])) {
            $byMonth[$month] = [
                'month' => $month,
                'value' => $snap['metadata']['total_value']
            ];
        }
    }
    $monthly_performance = array_values($byMonth);
} else {
    // Fallback to mock data if no snapshots
    $monthly_performance = [
        ['month' => 'Gen', 'value' => 100000],
        // ... rest of mock data
    ];
}

// Last 5 Days (per grafico "Ultimi 5 Giorni")
$last_5_days = array_slice($snapshots, 0, 5);
$last_5_days = array_reverse($last_5_days); // Oldest first
```

#### 2.5 Pulsante Frontend "Crea Snapshot"
**File:** `views/tabs/holdings.php` o dashboard

**Checklist:**
- [ ] Aggiungere pulsante "Crea Snapshot"
- [ ] Funzione JavaScript `createSnapshot()`
- [ ] Chiamata POST a `/api/create-snapshot.php`
- [ ] Notification conferma

#### 2.6 Cron Job Automatico (Opzionale)
**Setup:**

**Checklist:**
- [ ] Creare script bash `scripts/daily-snapshot.sh`
- [ ] Aggiungere cron job: `0 23 * * * /path/to/scripts/daily-snapshot.sh`
- [ ] Log output in `logs/snapshots.log`

**Script:**
```bash
#!/bin/bash
# daily-snapshot.sh

curl -X POST http://localhost/api/create-snapshot.php \
  -H "Content-Type: application/json" \
  >> /path/to/logs/snapshots.log 2>&1

echo "Snapshot created at $(date)" >> /path/to/logs/snapshots.log
```

### Deliverable STEP 2
- ✅ File `data/snapshots.json` con storico giornaliero
- ✅ Grafici dashboard con dati reali storici
- ✅ Pulsante "Crea Snapshot" funzionante
- ✅ (Opzionale) Cron job automatico

### Test di Accettazione
1. Premere pulsante "Crea Snapshot"
2. Verificare creazione snapshot in `snapshots.json`
3. Ricaricare dashboard
4. Verificare grafico "Andamento Mensile (2025)" mostra dati reali
5. Verificare grafico "Ultimi 5 Giorni" mostra dati reali

---

## 🎯 STEP 3: Workflow n8n Analisi Tecnica

**Priorità:** ⚡ ALTA
**Tempo stimato:** 6-8 ore
**Dipendenze:** STEP 1 (quotazioni)
**Stato:** ❌ Non iniziato

### Obiettivo
Generare segnali operativi reali (BUY/SELL/HOLD/WATCH) basati su indicatori tecnici calcolati e analisi AI.

### Task Dettagliati

#### 3.1 Creare Struttura Output JSON
**File:** `data/technical_analysis.json`

**Checklist:**
- [ ] Definire schema output
- [ ] Creare file con struttura vuota

**Struttura:**
```json
{
  "last_update": "2025-11-26T10:00:00Z",
  "analysis": [
    {
      "isin": "IE00B3RBWM25",
      "ticker": "IWDA",
      "current_price": 89.45,
      "signal": "BUY",
      "signal_strength": 8,
      "indicators": {
        "ema9": 88.20,
        "ema21": 86.50,
        "ema50": 84.30,
        "ema200": 78.90,
        "rsi": 45.3,
        "macd": {
          "value": 1.25,
          "signal": 0.95,
          "histogram": 0.30,
          "trend": "positive"
        },
        "bollinger": {
          "upper": 92.50,
          "middle": 88.00,
          "lower": 83.50,
          "position": "middle"
        }
      },
      "trend": "bullish",
      "support": 85.20,
      "resistance": 91.50,
      "notes": "Trend rialzista confermato da tutte le EMA. RSI neutrale permette ulteriore upside. MACD positivo.",
      "ai_summary": "Outlook positivo. Il titolo sta consolidando sopra le medie mobili principali con momentum favorevole."
    }
  ]
}
```

#### 3.2 Creare Workflow n8n "Analisi Tecnica"
**Nome Workflow:** `Portfolio - Technical Analysis`

**Checklist:**
- [ ] Node 1: Schedule Trigger (giornaliero ore 18:00)
- [ ] Node 2: HTTP Request GET holdings ISINs
- [ ] Node 3: Loop per ogni ISIN
- [ ] Node 4: HTTP Request fetch prezzi storici (60 giorni)
- [ ] Node 5: Code Node calcolo indicatori tecnici
- [ ] Node 6: AI Node (Claude/OpenAI) genera segnali
- [ ] Node 7: Merge risultati
- [ ] Node 8: Write File `technical_analysis.json`

**Node 5 - Code per Calcolo Indicatori:**
```javascript
// Node.js code in n8n
const prices = $input.item.json.prices; // Array of historical prices

// Calculate EMA
function ema(prices, period) {
  const k = 2 / (period + 1);
  let emaValue = prices[0];

  for (let i = 1; i < prices.length; i++) {
    emaValue = prices[i] * k + emaValue * (1 - k);
  }

  return emaValue;
}

// Calculate RSI
function rsi(prices, period = 14) {
  let gains = 0;
  let losses = 0;

  for (let i = 1; i <= period; i++) {
    const change = prices[i] - prices[i - 1];
    if (change > 0) gains += change;
    else losses += Math.abs(change);
  }

  const avgGain = gains / period;
  const avgLoss = losses / period;
  const rs = avgGain / avgLoss;

  return 100 - (100 / (1 + rs));
}

// Calculate MACD
function macd(prices) {
  const ema12 = ema(prices, 12);
  const ema26 = ema(prices, 26);
  const macdLine = ema12 - ema26;

  // Signal line (EMA 9 of MACD)
  // Simplified - in production calculate EMA of MACD values
  const signalLine = macdLine * 0.9;

  return {
    value: macdLine,
    signal: signalLine,
    histogram: macdLine - signalLine,
    trend: macdLine > signalLine ? 'positive' : 'negative'
  };
}

// Calculate indicators
const indicators = {
  ema9: ema(prices, 9),
  ema21: ema(prices, 21),
  ema50: ema(prices, 50),
  ema200: ema(prices, 200),
  rsi: rsi(prices),
  macd: macd(prices)
};

return {
  isin: $input.item.json.isin,
  ticker: $input.item.json.ticker,
  current_price: prices[prices.length - 1],
  indicators
};
```

**Node 6 - AI Prompt Template:**
```
Sei un analista finanziario esperto in analisi tecnica. Analizza i seguenti indicatori per l'ETF {{$json.ticker}} ({{$json.isin}}):

Prezzo corrente: €{{$json.current_price}}

Indicatori tecnici:
- EMA 9: €{{$json.indicators.ema9}}
- EMA 21: €{{$json.indicators.ema21}}
- EMA 50: €{{$json.indicators.ema50}}
- EMA 200: €{{$json.indicators.ema200}}
- RSI: {{$json.indicators.rsi}}
- MACD: {{$json.indicators.macd.trend}} (valore: {{$json.indicators.macd.value}})

Fornisci:
1. Segnale operativo: BUY, SELL, HOLD o WATCH
2. Forza segnale (1-10)
3. Trend: bullish, bearish o neutral
4. Livelli support e resistance stimati
5. Note tecniche (max 200 caratteri)
6. Summary AI (max 150 caratteri)

Rispondi in formato JSON:
{
  "signal": "BUY|SELL|HOLD|WATCH",
  "signal_strength": 1-10,
  "trend": "bullish|bearish|neutral",
  "support": number,
  "resistance": number,
  "notes": "string",
  "ai_summary": "string"
}
```

#### 3.3 Aggiornare portfolio_data.php
**File:** `data/portfolio_data.php`

**Checklist:**
- [ ] Rimuovere array `$technical_analysis` hardcoded
- [ ] Caricare dati da `technical_analysis.json`
- [ ] Gestire fallback se file non esiste

**Codice:**
```php
// Load technical analysis
$technical_analysis = [];
$technicalPath = __DIR__ . '/technical_analysis.json';

if (file_exists($technicalPath)) {
    $technicalData = json_decode(file_get_contents($technicalPath), true);
    $technical_analysis = $technicalData['analysis'] ?? [];
} else {
    // Fallback: empty or mock data
    $technical_analysis = [];
}
```

#### 3.4 Badge "Aggiornato X ore fa"
**File:** `views/tabs/technical.php`

**Checklist:**
- [ ] Leggere `last_update` da technical_analysis.json
- [ ] Calcolare tempo trascorso
- [ ] Mostrare badge nell'header widget

**Codice:**
```php
<?php
$lastUpdate = $technicalData['last_update'] ?? null;
$hoursAgo = $lastUpdate ? round((time() - strtotime($lastUpdate)) / 3600) : null;
?>

<div class="flex items-center gap-2">
    <span>Analisi Tecnica</span>
    <?php if ($hoursAgo !== null): ?>
    <span class="text-[9px] px-2 py-0.5 bg-gray-200 text-gray-600 rounded">
        Aggiornato <?php echo $hoursAgo; ?>h fa
    </span>
    <?php endif; ?>
</div>
```

### Deliverable STEP 3
- ✅ Workflow n8n "Analisi Tecnica" funzionante
- ✅ File `technical_analysis.json` aggiornato giornalmente
- ✅ Vista "Analisi Tecnica" con segnali reali
- ✅ Badge timestamp aggiornamento

### Test di Accettazione
1. Avviare workflow manualmente
2. Verificare creazione `technical_analysis.json`
3. Ricaricare vista "Analisi Tecnica"
4. Verificare segnali reali per ogni holding
5. Verificare badge "Aggiornato X ore fa"

---

## 🎯 STEP 4: Workflow n8n Opportunità ETF

**Priorità:** 🔸 MEDIA
**Tempo stimato:** 8-10 ore
**Dipendenze:** STEP 1
**Stato:** ❌ Non iniziato

### Obiettivo
Suggerire nuovi ETF zero commissioni Fineco non presenti nel portafoglio, con analisi fondamentale e scoring.

### Task Dettagliati

#### 4.1 Scraping Lista ETF Fineco Zero Commissioni
**Tool:** Puppeteer/Playwright in n8n

**Checklist:**
- [ ] Identificare URL lista ETF Fineco
- [ ] Node scraping con Puppeteer
- [ ] Estrarre: ISIN, Ticker, Nome, TER
- [ ] Filtrare: solo ETF non già in portafoglio

**URL Target:**
```
https://finecobank.com/it/mercati/etf/
(verificare URL esatto e selettori DOM)
```

#### 4.2 Arricchimento Dati Fondamentali
**API:** JustETF, Morningstar, Yahoo Finance

**Checklist:**
- [ ] Fetch dati per ogni ETF:
  - AUM (Assets Under Management)
  - TER (Total Expense Ratio)
  - Dividend Yield
  - YTD Performance
  - Domicilio, valuta, tipo replica
- [ ] Merge con dati scraping

#### 4.3 Scoring e Ranking AI
**AI Node:** Claude/GPT per analisi qualitativa

**Checklist:**
- [ ] Definire criteri scoring (TER basso, AUM alto, YTD positivo, diversificazione)
- [ ] Prompt AI per analisi fondamentale
- [ ] Calcolare score complessivo (0-100)
- [ ] Ordinare per score

**AI Prompt:**
```
Analizza questo ETF per un portafoglio diversificato:

Nome: {{$json.name}}
ISIN: {{$json.isin}}
TER: {{$json.ter}}%
AUM: €{{$json.aum}} milioni
Yield: {{$json.yield}}%
YTD: {{$json.ytd}}%
Settore/Area: {{$json.category}}

Il portafoglio attuale è concentrato su:
- Equity Global (45%)
- Equity Europe (25%)
- Bond Government (20%)
- Gold (10%)

Fornisci:
1. Score opportunità (0-100)
2. Pro e contro
3. Fit con portafoglio attuale
4. Raccomandazione finale

JSON format:
{
  "score": 0-100,
  "pros": ["...", "..."],
  "cons": ["...", "..."],
  "portfolio_fit": "string",
  "recommendation": "string"
}
```

#### 4.4 Salvare Output JSON
**File:** `data/opportunities.json`

**Struttura:**
```json
{
  "last_update": "2025-11-26T10:00:00Z",
  "opportunities": [
    {
      "isin": "IE00B4L5Y983",
      "ticker": "IWDA",
      "name": "iShares Core MSCI World UCITS ETF",
      "ter": 0.20,
      "aum": 50000,
      "yield": 1.85,
      "ytd": 18.5,
      "category": "Equity Global",
      "currency": "USD",
      "domicile": "Ireland",
      "score": 85,
      "ai_analysis": {
        "pros": ["TER competitivo", "AUM elevato", "Diversificazione globale"],
        "cons": ["Overlap con IWDA già in portafoglio"],
        "portfolio_fit": "Buon fit per aumentare esposizione equity global",
        "recommendation": "Considerare se si vuole aumentare allocation equity"
      }
    }
  ]
}
```

#### 4.5 Aggiornare portfolio_data.php
**File:** `data/portfolio_data.php`

**Checklist:**
- [ ] Rimuovere `$opportunities` mock
- [ ] Caricare da `opportunities.json`

### Deliverable STEP 4
- ✅ Workflow n8n "Opportunità" settimanale
- ✅ File `opportunities.json` con top 10 ETF raccomandati
- ✅ Vista "Raccomandazioni" con dati reali

---

## 🎯 STEP 5: Workflow n8n Macro Sentiment

**Priorità:** 🔹 BASSA
**Tempo stimato:** 6-8 ore
**Dipendenze:** Nessuna
**Stato:** ❌ Non iniziato

### Obiettivo
Fornire context macro su mercati e sentiment per prendere decisioni informate.

### Task Dettagliati

#### 5.1 Fetch News Finanziarie
**API:** NewsData.io, Google News API, Finnhub

**Checklist:**
- [ ] Setup API key
- [ ] Fetch news per categorie: stocks, bonds, commodities
- [ ] Filtrare per lingua italiana e rilevanza

#### 5.2 Sentiment Analysis NLP
**Tool:** Claude AI, OpenAI, o libreria NLP (VADER, TextBlob)

**Checklist:**
- [ ] Analizzare sentiment per ogni news (positive/negative/neutral)
- [ ] Aggregare sentiment per settore
- [ ] Calcolare score medio

#### 5.3 Salvare Output JSON
**File:** `data/macro_sentiment.json`

**Struttura:**
```json
{
  "last_update": "2025-11-26T10:00:00Z",
  "sentiment": {
    "equity": {
      "score": 65,
      "trend": "positive",
      "summary": "Mercati azionari in rialzo grazie a dati macro positivi"
    },
    "bonds": {
      "score": 45,
      "trend": "neutral",
      "summary": "Obbligazioni stabili in attesa decisioni BCE"
    },
    "commodities": {
      "score": 70,
      "trend": "positive",
      "summary": "Oro in forte rialzo per incertezza geopolitica"
    }
  },
  "recent_news": [
    {
      "title": "BCE mantiene tassi invariati",
      "source": "Il Sole 24 Ore",
      "sentiment": "neutral",
      "url": "..."
    }
  ]
}
```

### Deliverable STEP 5
- ✅ Workflow n8n "Macro Sentiment" giornaliero
- ✅ File `macro_sentiment.json`
- ✅ Widget dashboard con sentiment overview

---

## 🎯 STEP 6: Migrazione Database MariaDB

**Priorità:** 🔹 BASSA
**Tempo stimato:** 12-16 ore
**Dipendenze:** STEP 1, 2, 3 completati
**Stato:** ❌ Non iniziato

### Obiettivo
Migrare da JSON a database relazionale per scalabilità, performance e integrità dati.

### Task Dettagliati

#### 6.1 Setup Container MariaDB
**docker-compose.yml:**

```yaml
services:
  mariadb:
    image: mariadb:10.5
    container_name: trading-portfolio-db
    environment:
      MYSQL_ROOT_PASSWORD: rootpass
      MYSQL_DATABASE: portfolio
      MYSQL_USER: portfolio_user
      MYSQL_PASSWORD: changeme
    ports:
      - "3306:3306"
    volumes:
      - ./db/data:/var/lib/mysql
      - ./db/init.sql:/docker-entrypoint-initdb.d/init.sql
    restart: unless-stopped
```

#### 6.2 Creare Schema SQL
**File:** `db/init.sql`

**Checklist:**
- [ ] Tabelle: users, portfolios, holdings, transactions, dividends, snapshots
- [ ] Foreign keys e constraints
- [ ] Indici per performance

**Schema:** (vedere `docs/03-DATABASE.md` per schema completo)

#### 6.3 Script Migrazione JSON → DB
**File:** `scripts/migrate-json-to-db.php`

**Checklist:**
- [ ] Leggere portfolio.json
- [ ] Inserire holdings in tabella `holdings`
- [ ] Inserire snapshots in tabella `snapshots`
- [ ] Inserire dividends in tabella `dividends`
- [ ] Validazione integrità dati

#### 6.4 Refactor PortfolioManager
**File:** `lib/PortfolioManager.php`

**Checklist:**
- [ ] Sostituire `load()` / `save()` con query PDO
- [ ] Metodi CRUD usano prepared statements
- [ ] Mantenere retrocompatibilità con JSON (fallback)

### Deliverable STEP 6
- ✅ Database MariaDB funzionante
- ✅ Dati migrati da JSON
- ✅ PortfolioManager usa DB
- ✅ Performance migliorate

---

## 📊 Tabella Riepilogativa Step

| Step | Nome | Priorità | Tempo | Dipendenze | Stato |
|------|------|----------|-------|------------|-------|
| 1 | Sistema Quotazioni | ⚡ MASSIMA | 4-6h | - | ❌ |
| 2 | Sistema Snapshots | ⚡ ALTA | 3-4h | STEP 1 (opt) | ❌ |
| 3 | Analisi Tecnica | ⚡ ALTA | 6-8h | STEP 1 | ❌ |
| 4 | Opportunità ETF | 🔸 MEDIA | 8-10h | STEP 1 | ❌ |
| 5 | Macro Sentiment | 🔹 BASSA | 6-8h | - | ❌ |
| 6 | Migrazione DB | 🔹 BASSA | 12-16h | STEP 1-3 | ❌ |

**Totale stimato:** 39-52 ore sviluppo

---

## 🚀 Percorso Consigliato

### Fase 1: Dati Essenziali (Settimana 1)
1. **STEP 1** → Sistema Quotazioni (4-6h)
2. **STEP 2** → Sistema Snapshots (3-4h)
3. Testing e bugfix

**Risultato:** Portfolio con dati reali e grafici storici funzionanti

---

### Fase 2: Intelligence Operativa (Settimana 2)
1. **STEP 3** → Analisi Tecnica (6-8h)
2. Testing segnali operativi
3. Fine-tuning prompt AI

**Risultato:** Segnali BUY/SELL/HOLD reali per ogni holding

---

### Fase 3: Espansione Features (Settimana 3-4)
1. **STEP 4** → Opportunità ETF (8-10h)
2. **STEP 5** → Macro Sentiment (6-8h)
3. UI/UX improvements

**Risultato:** Sistema completo di analisi e raccomandazioni

---

### Fase 4: Produzione (Settimana 5-6)
1. **STEP 6** → Migrazione Database (12-16h)
2. Autenticazione multi-utente
3. Deployment produzione
4. Monitoring e logging

**Risultato:** Sistema production-ready

---

## 📝 Note Finali

### Risorse Necessarie
- **API Keys:** Yahoo Finance, NewsData.io (opzionale)
- **Docker:** Per n8n e MariaDB
- **AI API:** Claude/OpenAI (per analisi qualitativa)

### Considerazioni
- Gli STEP 1-2 sono **bloccanti** per avere un sistema veramente utile
- STEP 3 aggiunge valore operativo significativo
- STEP 4-5 sono "nice to have" ma non essenziali
- STEP 6 è necessario solo per produzione multi-utente

### Prossima Sessione
**Si consiglia di iniziare con STEP 1** per avere subito feedback con dati reali.

---

**Documento aggiornato il:** 26 Novembre 2025
**Prossimo aggiornamento previsto:** Dopo completamento STEP 1
