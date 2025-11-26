# n8n Step 1 – Portfolio Enrichment & Price Update

**Obiettivo**: Creare il primo workflow n8n che aggiorna prezzi e arricchisce metadata degli holdings, tenendo tutto compatto in un unico workflow estendibile per analisi future (LLM, news, technical).

**Data**: 26 Novembre 2025
**Versione**: 1.0 - MVP Price Update
**Status**: Da implementare

---

## Cosa fa questo workflow

1. **Legge il portafoglio** dall'app PHP tramite API dedicata
2. **Aggiorna i prezzi** per ogni holding usando Yahoo Finance (gratuito, nessun rate limit)
3. **Arricchisce metadati** (asset_class, sector) con classificazione base
4. **Scrive tutto indietro** all'app in un'unica chiamata
5. **Trigger ricalcolo** di metriche (P&L, allocations, drift)

---

## Architettura del workflow

```ascii
┌──────────────────┐
│ Schedule Trigger │ ← Ogni notte 22:00 CET
│ (Cron: 0 22 * * *)│
└────────┬─────────┘
│
▼
┌────────────────────────┐
│ HTTP GET /api/n8n/portfolio │ ← Legge holdings dall'app
└────────┬───────────────┘
│
▼
┌─────────────────────┐
│ Split In Batches │ ← 5 holdings per batch
│ (Rate limit safe) │
└────────┬────────────┘
│
▼
┌────────────────────────────┐
│ Yahoo Finance API │ ← Fetch prezzo per ticker
│ (yfinance unofficial API) │
└────────┬───────────────────┘
│
▼
┌─────────────────────────┐
│ Code Node: Classify │ ← Arricchimento metadata
│ asset_class & sector │
└────────┬────────────────┘
│
▼
┌─────────────────────┐
│ Wait 200ms │ ← Evita sovraccarico
└────────┬────────────┘
│
▼
┌─────────────────────────┐
│ Aggregate Results │ ← Costruisce array finale
└────────┬────────────────┘
│
▼

```

---

## Endpoint PHP necessari

### 1. `GET /api/n8n/portfolio.php`

**Scopo**: Fornisce a n8n la lista holdings da arricchire.

**Output** (esempio):
```json
{
"success": true,
"metadata": {
"portfolio_name": "Portafoglio ETF Personale",
"base_currency": "EUR",
"total_value": 17795.02,
"last_update": "2025-11-26T13:15:40Z"
},
"holdings": [
{
"isin": "IE00B579F325",
"ticker": "SGLD.MI",
"name": "Invesco Physical Gold ETC",
"quantity": 10,
"avg_price": 272.553,
"current_price": 272.553,
"asset_class": "Unknown",
"sector": "Unknown",
"instrument_type": "ETC",
"market": "AFF",
"currency": "EUR"
}
]
}
```

**Autenticazione**: HMAC-SHA256 (header `X-Webhook-Signature`)

---

### 2. `POST /api/n8n/enrich.php`

**Scopo**: Riceve holdings arricchite e aggiorna `portfolio.json`.

**Input** (esempio):
```json
{
"workflow_id": "portfolio_enrichment_v1",
"timestamp": "2025-11-26T22:05:00Z",
"holdings": [
{
"isin": "IE00B579F325",
"current_price": 275.10,
"asset_class": "Commodity",
"sector": "Gold",
"expense_ratio": 0.12
},
{
"isin": "IE00B8GKDB10",
"current_price": 68.50,
"asset_class": "Equity",
"sector": "Global",
"expense_ratio": 0.22
}
]
}
```

**Logica**:
1. Valida HMAC
2. Per ogni holding nel payload:
   - Trova per `isin` in `portfolio.json`
   - Aggiorna `current_price`, `asset_class`, `sector`, `expense_ratio`
3. Chiama `PortfolioManager->recalculateMetrics()`
4. Salva `portfolio.json`

**Output**:
```json
{
"success": true,
"message": "Portfolio enriched successfully",
"updated_holdings": 7,
"metadata": {
"total_value": 18234.56,
"unrealized_pnl": 439.54,
"holdings_count": 7
}
}
```

---

## Provider dati: Yahoo Finance (yfinance)

**API usata**: Unofficial Yahoo Finance API
**Endpoint**: `https://query1.finance.yahoo.com/v8/finance/chart/{TICKER}`
**Rate limit**: Nessuno (unofficial ma stabile)
**Costo**: Gratuito

**Esempio chiamata**:
```
GET https://query1.finance.yahoo.com/v8/finance/chart/SGLD.MI?range=1d&interval=1d
```

**Parsing risposta**:
```javascript
const data = $json.chart.result;
const currentPrice = data.meta.regularMarketPrice;
```

**Nota**: Per fondi UCITS non quotati (es. Pictet), Yahoo Finance potrebbe non avere dati. In questo caso, mantieni il prezzo precedente o implementa fallback (es. JustETF scraping).

---

## Classificazione asset_class & sector

**Logica base nel Code node**:

```javascript
function classifyHolding(holding) {
const name = holding.name.toLowerCase();
const isin = holding.isin;
const instrumentType = holding.instrument_type;

// Commodity
if (name.includes('gold') || name.includes('silver') ||
name.includes('physical') || instrumentType === 'ETC') {
return {
asset_class: 'Commodity',
sector: name.includes('gold') ? 'Gold' : 'Precious Metals'
};
}

// Equity ETF
if (instrumentType === 'ETF') {
if (name.includes('dividend') || name.includes('aristocrat')) {
return { asset_class: 'Equity', sector: 'Dividend' };
}
if (name.includes('world') || name.includes('global')) {
return { asset_class: 'Equity', sector: 'Global' };
}
if (name.includes('u.s.') || name.includes('s&p')) {
return { asset_class: 'Equity', sector: 'USA' };
}
return { asset_class: 'Equity', sector: 'Mixed' };
}

// Fondi attivi
if (instrumentType === 'Fondo') {
if (name.includes('biotech')) {
return { asset_class: 'Equity', sector: 'Healthcare' };
}
if (name.includes('robot') || name.includes('tech')) {
return { asset_class: 'Equity', sector: 'Technology' };
}
if (name.includes('dividend')) {
return { asset_class: 'Equity', sector: 'Dividend' };
}
return { asset_class: 'Equity', sector: 'Active Fund' };
}

// Default
return { asset_class: 'Unknown', sector: 'Unknown' };
}
```

**Estensione futura**: Sostituire con chiamata a LLM (OpenRouter GPT-4) per classificazione più accurata.

---

## Setup e configurazione

### Step 1: Implementare endpoint PHP

1. Crea `api/n8n/portfolio.php` (vedi scheletro PHP allegato)
2. Crea `api/n8n/enrich.php` (vedi scheletro PHP allegato)
3. Aggiungi validazione HMAC in `lib/HMACValidator.php`
4. Testa manualmente con `curl` (esempi sotto)

### Step 2: Importare workflow n8n

1. Accedi a n8n (`http://localhost:5678` o tuo dominio)
2. Menu → Workflows → Import from File
3. Carica `n8n-portfolio-enrichment-v1.json` (allegato)
4. Configura credenziali:
   - Crea credenziale "HMAC Secret" per header `X-Webhook-Signature`
   - Imposta `N8N_WEBHOOK_SECRET` in environment variables n8n
5. Attiva workflow

### Step 3: Test manuale

**Test endpoint GET**:
```bash
# Genera HMAC signature
SECRET="your_secret_here"
PAYLOAD='{"action":"get_portfolio"}'
SIGNATURE=$(echo -n "$PAYLOAD" | openssl dgst -sha256 -hmac "$SECRET" | awk '{print $2}')

# Chiamata
curl -X GET "http://your-app.test/api/n8n/portfolio.php"
-H "X-Webhook-Signature: sha256=$SIGNATURE"
-H "Content-Type: application/json"
```

**Test endpoint POST**:
```bash
SECRET="your_secret_here"
PAYLOAD='{"workflow_id":"test","timestamp":"2025-11-26T22:00:00Z","holdings":[{"isin":"IE00B579F325","current_price":275.10}]}'
SIGNATURE=$(echo -n "$PAYLOAD" | openssl dgst -sha256 -hmac "$SECRET" | awk '{print $2}')

curl -X POST "http://your-app.test/api/n8n/enrich.php"
-H "X-Webhook-Signature: sha256=$SIGNATURE"
-H "Content-Type: application/json"
-d "$PAYLOAD"
```

### Step 4: Esecuzione workflow

1. In n8n, apri workflow importato
2. Click "Execute Workflow" (test manuale)
3. Verifica log execution per errori
4. Controlla `portfolio.json` per verificare aggiornamenti
5. Ricarica dashboard per vedere prezzi aggiornati

---

## Estensioni future (già pronte nel workflow)

Questo workflow è progettato per essere esteso senza modificare la struttura base:

### Branch "Technical Analysis" (da aggiungere dopo punto 6)
- Calcola indicatori (EMA, MACD, RSI, Bollinger)
- Genera segnali BUY/SELL/HOLD
- Scrive in `technical_analysis.json`

### Branch "LLM Insights" (da aggiungere dopo aggregazione)
- Chiama OpenRouter/GPT-4 con context portfolio
- Genera raccomandazioni testuali
- Scrive in `dashboard_insights.json` e `recommendations.json`

### Branch "News Sentiment" (parallelo)
- Fetch news per settori holdings
- Sentiment analysis con LLM
- Correlazione news → holdings

Tutte queste branch si aggiungono **nello stesso workflow**, mantenendolo compatto.

---

## Troubleshooting

### Errore: "HMAC signature invalid"
- Verifica che `N8N_WEBHOOK_SECRET` in n8n corrisponda a quello in `.env` PHP
- Controlla che il body JSON sia identico a quello hashato
- Usa `error_log()` in PHP per debuggare signature ricevuta vs calcolata

### Errore: "Ticker not found in Yahoo Finance"
- Alcuni fondi non sono su Yahoo (es. Pictet)
- Implementa fallback: mantieni prezzo precedente e logga warning
- Alternativa: usa JustETF API (a pagamento) per dati completi

### Workflow n8n non parte
- Verifica che trigger Cron sia attivo (toggle ON)
- Controlla timezone n8n (deve essere Europe/Rome)
- Test manuale con "Execute Workflow"

### Holdings non aggiornati
- Verifica log PHP in `/var/log/apache2/error.log`
- Controlla permessi file `portfolio.json` (deve essere writable da www-data)
- Test con `chmod 664 data/portfolio.json`

---

## Monitoraggio

### Log n8n
- Execution History mostra tutti i run
- Ogni nodo ha output/error log visualizzabile
- Imposta notifiche email/Slack per fallimenti

### Log PHP
- Aggiungi logging in `enrich.php`:
```php
error_log("[n8n] Received enrichment for " . count($data['holdings']) . " holdings");
```

### Metriche
- Tempo esecuzione workflow (target: < 30 secondi per 10 holdings)
- Success rate (target: > 95%)
- Holdings aggiornati per run (target: 100%)

---

## Checklist implementazione

- [ ] Creare `api/n8n/portfolio.php`
- [ ] Creare `api/n8n/enrich.php`
- [ ] Implementare `lib/HMACValidator.php`
- [ ] Testare endpoint con curl
- [ ] Importare workflow n8n
- [ ] Configurare credenziali n8n
- [ ] Test esecuzione manuale workflow
- [ ] Attivare schedule trigger
- [ ] Verificare primo run automatico
- [ ] Controllare dashboard con prezzi aggiornati
- [ ] Documentare modifiche in PROJECT_STATUS.md

---

**Prossimi step**: Dopo completamento, estendere workflow con branch "Technical Analysis" o "LLM Insights".
