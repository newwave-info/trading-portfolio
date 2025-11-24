# 06 – Workflow n8n

Questo documento descrive i quattro workflow principali di n8n Community Edition che automatizzano l'analisi, lo scouting e i suggerimenti per **ETF Portfolio Manager**.

**Prerequisiti**:
- n8n CE self‑hosted (Docker container `n8n`)
- Segreto HMAC condiviso con il backend PHP (configurato in `.env`)
- API key per provider dati (Alpha Vantage, Yahoo Finance, o equivalenti)
- Accesso a feed RSS/news finanziarie

---

## 1. Workflow A – Analisi Tecnica Giornaliera

**Obiettivo**: Per ogni ISIN presente nei portafogli attivi, recuperare quotazioni e calcolare indicatori tecnici (EMA, MACD, RSI, Bollinger Bands). Generare segnali sintetici e inviarli al backend.

**Trigger**: Schedulato ogni giorno alle 20:00 CET (dopo chiusura mercati USA).

### 1.1 Struttura workflow

graph TD
A[Cron Trigger
20:00 CET] --> B[HTTP Request
GET /api/holdings/active]
B --> C[IF Node
Check holdings count > 0]
C -->|true| D[Split In Batches
Batch size: 5]
D --> E[HTTP Request
Alpha Vantage API]
E --> F[Code Node
Calculate indicators]
F --> G[Set Node
Format output]
G --> H[HTTP Request
POST /api/analysis/results]
H --> I[Wait Node
1 sec delay]
I --> D
C -->|false| J[NoOp Node
Log no holdings]


### 1.2 Dettaglio nodi

#### Nodo 1: Cron Trigger
- **Type**: Schedule Trigger
- **Settings**:
  - Mode: Every Day
  - Time: 20:00
  - Timezone: Europe/Rome

#### Nodo 2: HTTP Request (Recupera holdings attivi)
- **Method**: GET
- **URL**: `http://app:80/api/holdings/active`
- **Authentication**: None (container‑to‑container)
- **Headers**:
Content-Type: application/json

- **Query Parameters**:
is_active=1


**Codice JavaScript per preparare request**:
// In nodo Code prima di HTTP Request
const webhookSecret = process.env.N8N_WEBHOOK_SECRET;

// Costruisci HMAC per autenticazione backend
const payload = JSON.stringify({ request_type: 'active_holdings' });
const signature = require('crypto')
.createHmac('sha256', webhookSecret)
.update(payload)
.digest('hex');

return [{
json: {
headers: {
'X-Webhook-Signature': 'sha256=' + signature,
'Content-Type': 'application/json'
},
body: payload
}
}];


#### Nodo 3: IF (Check holdings count)
- **Condition**: `{{ $json.data.length }}` > 0
- **True**: procedi con batch
- **False**: log e termina

#### Nodo 4: Split In Batches
- **Batch Size**: 5 (per rispettare rate limit API)
- **Options**: Wait between batches = 1 second

#### Nodo 5: HTTP Request (Alpha Vantage)
- **Method**: GET
- **URL**: `https://www.alphavantage.co/query`
- **Query Parameters**:
function=TIME_SERIES_DAILY
symbol={{ $json.isin }}
apikey={{ $env.ALPHA_VANTAGE_API_KEY }}


**Nota**: Alpha Vantage supporta ISIN per alcuni ETF. Se non disponibile, usare ticker.

#### Nodo 6: Code (Calculate Technical Indicators)
// Input: $json (dati Alpha Vantage)
// Output: $json con indicatori calcolati

const data = $json['Time Series (Daily)'];
const closes = Object.keys(data)
.sort()
.slice(-50) // Ultimi 50 giorni
.map(date => parseFloat(data[date]['4. close']));

// EMA
function calculateEMA(period, prices) {
const multiplier = 2 / (period + 1);
let ema = prices;
for (let i = 1; i < prices.length; i++) {
ema = (prices[i] - ema) * multiplier + ema;
}
return ema;
}

const ema9 = calculateEMA(9, closes);
const ema21 = calculateEMA(21, closes);
const ema50 = calculateEMA(50, closes);

// RSI
function calculateRSI(period, prices) {
let gains = 0, losses = 0;
for (let i = prices.length - period; i < prices.length - 1; i++) {
const change = prices[i + 1] - prices[i];
if (change > 0) gains += change;
else losses -= change;
}
const avgGain = gains / period;
const avgLoss = losses / period;
const rs = avgGain / avgLoss;
return 100 - (100 / (1 + rs));
}

const rsi = calculateRSI(14, closes);

// Bollinger Bands
const sma20 = closes.slice(-20).reduce((a, b) => a + b, 0) / 20;
const variance = closes.slice(-20).reduce((sum, price) => sum + Math.pow(price - sma20, 2), 0) / 20;
const stdDev = Math.sqrt(variance);
const bbUpper = sma20 + (2 * stdDev);
const bbLower = sma20 - (2 * stdDev);

// Segnale sintetico
let signal = 'HOLD';
if (ema9 > ema21 && ema21 > ema50 && rsi < 70) signal = 'BUY';
if (ema9 < ema21 && ema21 < ema50 && rsi > 30) signal = 'SELL';
if (rsi > 80) signal = 'WATCH'; // Ipercomprato

return [{
json: {
isin: $json.isin,
analysis_type: 'TECHNICAL',
signal: signal,
confidence_score: 0.85, // Semplificato, potrebbe essere più complesso
data: {
ema_9: ema9,
ema_21: ema21,
ema_50: ema50,
rsi: rsi,
bb_upper: bbUpper,
bb_lower: bbLower,
current_price: closes[closes.length - 1]
}
}
}];


#### Nodo 7: Set (Format output)
return [{
json: {
workflow_id: 'technical_analysis_daily',
timestamp: new Date().toISOString(),
results: $json
}
}];


#### Nodo 8: HTTP Request (Invia al backend)
- **Method**: POST
- **URL**: `http://app:80/api/analysis/results`
- **Authentication**: HMAC (vedi sezione 1.2)
- **Body**: `{{ JSON.stringify($json) }}`

#### Nodo 9: Wait
- **Wait Time**: 1 second (per rispettare rate limit)

---

## 2. Workflow B – Scouting Opportunità ETF

**Obiettivo**: Identificare ETF acquistabili su Fineco (focus su zero commissioni) non presenti nei portafogli, calcolare indicatori base e inviare lista di opportunità al backend.

**Trigger**: Schedulato ogni settimana (es. domenica sera).

### 2.1 Struttura workflow

graph TD
A[Cron Trigger
Sunday 21:00] --> B[HTTP Request
GET /api/etf/universe]
B --> C[HTTP Request
Scrape Fineco ETF list]
C --> D[Code Node
Filter new ETFs]
D --> E[Split In Batches
Batch size: 10]
E --> F[HTTP Request
Alpha Vantage API]
F --> G[Code Node
Calculate basic signals]
G --> H[Set Node
Format opportunities]
H --> I[HTTP Request
POST /api/analysis/results]
I --> J[Wait Node
1 sec]
J --> E


### 2.2 Dettaglio nodi

#### Nodo 1: Cron Trigger
- **Schedule**: Every Week, Sunday, 21:00 CET

#### Nodo 2: HTTP Request (Recupera ETF già in portafogli)
- **URL**: `http://app:80/api/etf/in-portfolio`
- **HMAC**: Come workflow A

#### Nodo 3: HTTP Request (Scrape Fineco)
- **URL**: `https://www.finecobank.com/it/mercati-e-trading/etf/etf-a-zero-commissioni`
- **Method**: GET
- **Extract**: HTML Extract node per estrarre ISIN, nome, ticker

#### Nodo 4: Code (Filtra nuovi ETF)
const existingISINs = new Set($json.existing.map(e => e.isin));
const finecoETFs = $json.scraped;

const newETFs = finecoETFs.filter(etf => !existingISINs.has(etf.isin));

return newETFs.map(etf => ({ json: etf }));


#### Nodo 5: Split In Batches
- **Batch Size**: 10

#### Nodo 6: HTTP Request (Alpha Vantage)
- Come workflow A, ma per ogni nuovo ETF

#### Nodo 7: Code (Calcola segnale base)
const price = $json.current_price;
const sma20 = $json.sma_20;
const rsi = $json.rsi;

let signal = 'NEUTRAL';
if (price > sma20 && rsi < 70) signal = 'BUY';
if (price < sma20 && rsi > 30) signal = 'SELL';

return [{
json: {
isin: $json.isin,
analysis_type: 'OPPORTUNITY',
signal: signal,
confidence_score: 0.75,
data: {
name: $json.name,
ticker: $json.ticker,
current_price: price,
sma_20: sma20,
rsi: rsi
}
}
}];


#### Nodo 8: Set (Format)
return [{
json: {
workflow_id: 'opportunity_scouting_weekly',
timestamp: new Date().toISOString(),
results: $json
}
}];


#### Nodo 9: HTTP Request (Invia al backend)
- **URL**: `http://app:80/api/analysis/results`
- **HMAC**: Come sopra

---

## 3. Workflow C – Macro/News Sentiment

**Obiettivo**: Leggere feed RSS di news finanziarie, applicare sentiment analysis (via API esterna o LLM), correlare a settori e holdings, inviare aggregati al backend.

**Trigger**: Ogni 12 ore (es. 08:00 e 20:00).

### 3.1 Struttura workflow

graph TD
A[Cron Trigger
Every 12h] --> B[RSS Feed Read Node
Fetch financial news]
B --> C[Split In Batches
Per article]
C --> D[HTTP Request
Sentiment API]
D --> E[Code Node
Map to sectors]
E --> F[Aggregate Node
Group by sector]
F --> G[Set Node
Format sentiment]
G --> H[HTTP Request
POST /api/analysis/results]


### 3.2 Dettaglio nodi

#### Nodo 1: Cron Trigger
- **Schedule**: Every 12 Hours

#### Nodo 2: RSS Feed Read
- **URL**: `https://feeds.reuters.com/reuters/businessNews`
- **Options**: Limit 50 articoli

#### Nodo 3: Split In Batches
- **Batch Size**: 1

#### Nodo 4: HTTP Request (Sentiment API)
- **URL**: `https://api.openai.com/v1/chat/completions`
- **Method**: POST
- **Headers**:
Authorization: Bearer {{ $env.OPENAI_API_KEY }}
Content-Type: application/json

- **Body**:
{
"model": "gpt-4o-mini",
"messages": [
{
"role": "user",
"content": "Analyze sentiment of this financial news title: "{{ $json.title }}". Respond with ONLY: POSITIVE, NEUTRAL, or NEGATIVE."
}
]
}


#### Nodo 5: Code (Mappa a settori)
const title = $json.title.toLowerCase();
const sectors = [];

if (title.includes('tech') || title.includes('technology')) sectors.push('Technology');
if (title.includes('health') || title.includes('pharma')) sectors.push('Healthcare');
if (title.includes('energy') || title.includes('oil')) sectors.push('Energy');
// ... altri settori

return sectors.map(sector => ({
json: {
sector: sector,
sentiment: $json.sentiment,
article_title: $json.title,
published_at: $json.pubDate
}
}));


#### Nodo 6: Aggregate (Group by sector)
- **Operation**: Aggregate
- **Group By**: `sector`
- **Aggregations**:
  - `sentiment`: count
  - `sentiment_POSITIVE`: count where sentiment = POSITIVE
  - `sentiment_NEGATIVE`: count where sentiment = NEGATIVE

#### Nodo 7: Set (Format)
return [{
json: {
workflow_id: 'macro_news_sentiment',
timestamp: new Date().toISOString(),
results: $json.map(row => ({
sector: row.sector,
sentiment_score: (row.sentiment_POSITIVE - row.sentiment_NEGATIVE) / row.sentiment_count,
articles_analyzed: row.sentiment_count
}))
}
}];


#### Nodo 8: HTTP Request (Invia al backend)
- **URL**: `http://app:80/api/analysis/results`
- **HMAC**: Come sopra

---

## 4. Workflow D – Advisor di Ribilanciamento

**Obiettivo**: Mensilmente, calcolare drift per ciascun portafoglio, generare piano di trade per riportare allocazioni verso target, stimare costi commissionali Fineco.

**Trigger**: Primo giorno del mese, 09:00 CET.

### 4.1 Struttura workflow

graph TD
A[Cron Trigger
1st day of month 09:00] --> B[HTTP Request
GET /api/portfolios/all]
B --> C[Split In Batches
Per portfolio]
C --> D[HTTP Request
GET /api/portfolio/snapshot]
D --> E[Code Node
Calculate drift]
E --> F[IF Node
drift > 5%]
F -->|true| G[Code Node
Generate rebalance plan]
G --> H[HTTP Request
POST /api/analysis/results]
F -->|false| I[NoOp Node
Log no rebalance needed]


### 4.2 Dettaglio nodi

#### Nodo 1: Cron Trigger
- **Schedule**: Custom Cron
- **Expression**: `0 9 1 * *` (09:00, day 1, every month)

#### Nodo 2: HTTP Request (Recupera tutti i portafogli)
- **URL**: `http://app:80/api/portfolios/all`
- **HMAC**: Come sopra

#### Nodo 3: Split In Batches
- **Batch Size**: 1

#### Nodo 4: HTTP Request (Snapshot portafoglio)
- **URL**: `http://app:80/api/portfolio/snapshot?portfolio_id={{ $json.portfolio_id }}`

#### Nodo 5: Code (Calcola drift)
const holdings = $json.holdings;
const plan = [];

holdings.forEach(h => {
const drift = h.current_allocation - h.target_allocation;
if (Math.abs(drift) > 5) { // Soglia configurabile
const targetValue = ($json.total_value * h.target_allocation) / 100;
const currentValue = h.market_value;
const diffValue = targetValue - currentValue;

    let action = 'SELL';
    let quantity = Math.abs(diffValue / h.current_price);
    
    if (diffValue > 0) {
        action = 'BUY';
    }
    
    // Stima commissione
    const commission = h.commission_profile === 'ZERO' ? 0 : quantity * h.current_price * 0.0019; // 0.19% standard
    
    plan.push({
        isin: h.isin,
        ticker: h.ticker,
        action: action,
        quantity: Math.round(quantity * 1000) / 1000,
        estimated_cost: commission,
        reason: `Drift: ${drift.toFixed(2)}%`
    });
}
});

return [{
json: {
portfolio_id: $json.portfolio_id,
plan: plan,
total_estimated_cost: plan.reduce((sum, p) => sum + p.estimated_cost, 0)
}
}];


#### Nodo 6: IF (Drift > 5%)
- **Condition**: `{{ $json.plan.length }}` > 0

#### Nodo 7: Code (Format rebalance plan)
return [{
json: {
workflow_id: 'rebalancing_advisor_monthly',
timestamp: new Date().toISOString(),
results: {
portfolio_id: $json.portfolio_id,
analysis_type: 'REBALANCING',
signal: 'REBALANCE',
confidence_score: 0.90,
data: {
plan: $json.plan,
total_estimated_cost: $json.total_estimated_cost,
rationale: 'Drift superiore a soglia 5%'
}
}
}
}];


#### Nodo 8: HTTP Request (Invia al backend)
- **URL**: `http://app:80/api/analysis/results`
- **HMAC**: Come sopra

---

## 5. Configurazione comune

### 5.1 Variabili di ambiente n8n

Nel container n8n, configurare in **Settings > Variables**:

ALPHA_VANTAGE_API_KEY=your_key
OPENAI_API_KEY=your_key
N8N_WEBHOOK_SECRET=your_32_char_secret


### 5.2 Credenziali n8n

Creare credenziali di tipo **HTTP Header** per HMAC:

- **Name**: `HMAC Backend Auth`
- **Headers**:
X-Webhook-Signature: {{ $json.signature }}


### 5.3 Error handling

In ogni workflow, aggiungere **Error Workflow** separato per loggare fallimenti:

// Error Workflow: log to file or send email
const error = $json.error;
console.error('Workflow error:', error);
// Oppure: invia email di alert


---

## 6. Testing e debug

### 6.1 Test manuale

1. **Esegui workflow manualmente** da n8n UI (click "Execute Workflow").
2. **Verifica log** in n8n (view execution).
3. **Controlla backend**: query su `analysis_results` per vedere dati inseriti.

### 6.2 Mock data

Per test senza chiamare API esterne, usare **IF Node** con condizione `$env.NODE_ENV === 'test'` e **Set Node** con dati mock.

---

## 7. Monitoraggio

- **n8n**: Dashboard mostra esecuzioni, successi, errori.
- **Backend**: Logga ricezione webhook in `error_log` o tabella dedicata.
- **Alert**: Configurare n8n per inviare email se workflow fallisce > 3 volte consecutive.

---

## 8. Manutenzione

- **Aggiornamento API key**: Rotazione ogni 6 mesi, aggiornare in n8n credentials.
- **Rate limit**: Monitorare errori 429; se necessario, aumentare wait time o ridurre batch size.
- **Schema API**: Se backend cambia endpoint, aggiornare URL in tutti i workflow.

---

## 9. Riferimenti

- [n8n Documentation](https://docs.n8n.io)
- [Alpha Vantage API Docs](https://www.alphavantage.co/documentation/)
- [OpenAI API Docs](https://platform.openai.com/docs)
- [HMAC Authentication Guide](https://cheatsheetseries.owasp.org/cheatsheets/REST_Security_Cheat_Sheet.html)

---
