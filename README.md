# ETF Portfolio Manager

Strumento web per la gestione e analisi di un portafoglio ETF su Fineco Bank, con visualizzazione performance, analisi tecnica, tracciamento dividendi e raccomandazioni operative.

---

## Stato del progetto

Progetto in **produzione attiva** (v0.3.0-MySQL). Migrazione da storage JSON a **MySQL** completata il 27/11/2025. Architettura robusta con Repository Pattern, VIEWs per computed values real-time, e integrazione n8n per automazioni.

### Versione attuale (v0.3.0 - MySQL Production)

| Componente | Versione | Ruolo |
|-----------|----------|-------|
| PHP | 8.2+ | Backend API + Frontend rendering |
| MySQL | 8.2.29 | Storage relazionale (10 tabelle + 2 VIEWs) |
| Repository Pattern | - | Data layer con Repositories + DatabaseManager |
| JavaScript | Vanilla + Chart.js | Interazioni client-side e grafici |
| Apache/Nginx | - | Web server |

### Architettura attuale (MySQL Production)

```
┌─────────────────┐
│  Web Browser    │
└────────┬────────┘
         │ HTTP
         ▼
┌──────────────────────────────────────┐
│   PHP 8.2+ (portfolio-app)           │
│   - Dashboard con widgets            │
│   - Gestione Holdings (API REST)     │
│   - Visualizzazioni performance      │
│   - Repository Pattern (Data Layer)  │
│   - API n8n webhook (/api/update)    │
└─────────┬────────────────────────────┘
          │
          │ PDO + Repositories
          ▼
┌─────────────────────────────────────┐
│   MySQL 8.2.29 Database             │
│   ┌───────────────────────────┐     │
│   │ Tables (10):              │     │
│   │ - portfolios              │     │
│   │ - holdings                │     │
│   │ - transactions            │     │
│   │ - dividend_payments       │     │
│   │ - snapshots               │     │
│   │ - snapshot_holdings       │     │
│   │ - allocation_by_asset_class│    │
│   │ - monthly_performance     │     │
│   │ - metadata_cache          │     │
│   │ - cron_logs               │     │
│   └───────────────────────────┘     │
│   ┌───────────────────────────┐     │
│   │ VIEWs (2):                │     │
│   │ - v_holdings_enriched     │     │
│   │ - v_portfolio_metadata    │     │
│   └───────────────────────────┘     │
└──────────┬──────────────────────────┘
           │
           │ HMAC Webhook (POST /api/update.php)
           ▼
┌─────────────────────────────────────┐
│   n8n Workflow Automation           │
│   - Portfolio Enrichment (daily)    │
│   - Price fetching (YahooFinance)   │
│   - Bulk price update via webhook   │
│   - Auto-snapshot creation          │
└─────────┬───────────────────────────┘
          │
          │ REST API
          ▼
┌─────────────────────────────────────┐
│   External Services                 │
│   - Yahoo Finance API (v8)          │
│   - (Future: TwelveData, FMP)       │
└─────────────────────────────────────┘
```

**Vantaggi architettura MySQL:**

- **Performance**: Query indexed, connection pooling, cache results
- **Scalabilità**: Supporta milioni di record senza degrado
- **Integrità**: Foreign keys, constraints, transactions ACID
- **Real-time P&L**: Calcolato dalle VIEWs, sempre aggiornato
- **Concorrenza**: Gestione lock ottimistica per update simultanei
- **Backup**: Backup automatici MySQL schedulati
- **Query complesse**: Aggregazioni, JOIN, analisi storiche

---

## Che cosa è

ETF Portfolio Manager è una **web application PHP** che funziona come centro di controllo per investitori retail italiani che gestiscono portafogli ETF su Fineco Bank.

**Caratteristiche implementate (MVP):**

- ✅ Dashboard interattiva con visualizzazione metriche principali
- ✅ Gestione holdings con API REST (CRUD completo)
- ✅ Visualizzazione performance e allocazioni
- ✅ Analisi tecnica con indicatori e segnali
- ✅ Calendario dividendi e tracking distribuzioni
- ✅ Raccomandazioni operative e opportunità
- ✅ Import CSV da Fineco per inizializzazione rapida
- ✅ Interfaccia responsive e moderna
- ✅ Sistema di tabs per navigazione tra sezioni

**Recentemente completato:**

- ✅ Integrazione n8n per enrichment automatico (v2.2 attiva)
- ✅ Storico snapshots temporali per performance tracking
- ✅ Aggiornamento prezzi automatico giornaliero (4 provider API)
- ✅ Grafici performance dinamici da snapshot DB (Performance tab ora legge da MySQL, non da JSON)
- ✅ Sistema classificazione automatica ETF (settore/asset_class)
- ✅ Dividendi: sincronizzazione da n8n (yield, frequenza, annual_dividend), calendario forecast 6 mesi, grafici ricevuti/previsti, tab dedicata
- ✅ Payout automatico dividendi via script (`dividends-payout.php`) schedulabile in cron (nessun inserimento manuale)
- ✅ Log transazioni base (BUY/SELL/DIVIDEND) in `portfolio.json` per timeline e report futuri

**In roadmap (prossime iterazioni):**

- 🔄 Autenticazione multi-utente con sessioni sicure
- 🔄 Database relazionale (MariaDB/PostgreSQL)
- 🔄 Workflow n8n addizionali (analisi tecnica, opportunità, sentiment)
- 🔄 Storico transazioni completo
- 🔄 Multi-portafoglio per utente
- 🔄 Tracciamento commissioni Fineco

### Ricalcolo metriche & snapshot (MySQL)

- Service: `lib/Database/Services/PortfolioMetricsService.php`
  - Ricalcola allocazioni (`allocation_by_asset_class`), snapshot del giorno (`snapshots` + `snapshot_holdings`) e `monthly_performance` a partire dagli holdings correnti (VIEW `v_holdings_enriched`).
  - Invocato automaticamente da `api/holdings.php` dopo create/update/delete.
- Script manuale: `php scripts/recalculate-db-metrics.php` per forzare il sync (utile dopo deploy o migrazioni).
- Cron consigliato: job giornaliero che richiama lo script per avere uno snapshot per giorno e tenere aggiornato `monthly_performance`.

**Cosa NON è:**

- ❌ Non esegue ordini automatici (tutti manualmente su Fineco)
- ❌ Non è un robo-advisor o piattaforma di trading
- ❌ Non fornisce consulenza finanziaria (è uno strumento di analisi personale)

---

## Funzionalità principali

### 📊 Dashboard Portafoglio

La dashboard fornisce una visione completa e immediata dello stato del portafoglio:

- **Metriche principali**: Valore totale, capitale investito, P&L (realizzato e non realizzato)
- **Score portfolio**: Valutazione sintetica della qualità del portafoglio
- **Allocazioni**: Visualizzazione grafica della distribuzione per asset class e settori
- **Drift indicator**: Scostamento dall'allocazione target per facilitare il ribilanciamento
- **Widget interattivi**: Holdings principali, performance recenti, alert e notifiche

### 💼 Gestione Holdings

Sistema completo per la gestione delle posizioni in portafoglio:

- **API REST**: Endpoint per operazioni CRUD (Create, Read, Update, Delete)
- **Visualizzazione tabellare**: Lista completa holdings con dettagli (ISIN, ticker, quantità, prezzi)
- **Calcoli automatici**: Market value, P&L per posizione, allocazioni percentuali
- **Import CSV Fineco**: Caricamento rapido del portafoglio esportando da Fineco
- **Gestione metadati**: Asset class, settore, target allocation personalizzabili

**Formato dati JSON:**
```json
{
  "isin": "IE00B3RBWM25",
  "ticker": "VWCE",
  "name": "Vanguard FTSE All-World UCITS ETF",
  "quantity": 100,
  "avg_price": 85.50,
  "current_price": 89.45,
  "market_value": 8945.00,
  "unrealized_pnl": 395.00,
  "asset_class": "Equity",
  "sector": "Global"
}
```

### 📈 Analisi Performance

Visualizzazione dell'andamento storico del portafoglio con dati dinamici da snapshots:

- **Grafici temporali**: Evoluzione del valore nel tempo (ultimi 30 giorni)
- **Performance widgets**: Rendimenti a 1 mese, 3 mesi, YTD
- **Grafici dinamici** con design system unificato:
  - Andamento annuale (performance mensile)
  - Guadagno cumulativo YTD
  - Ultimi 5 giorni (valori giornalieri)
  - **Design**: Linea viola + area pattern diagonale (valori €) + linea grigio scuro (%)
  - **Dual axis**: Euro (sinistra) e percentuale (destra)
  - **Tooltip personalizzati**: Formattazione separata per € e %
- **Snapshot giornalieri**: Storico automatico creato daily via n8n (22:00)
- **Tabella storica**: Performance progressiva giornaliera con day change

### 📊 ChartManager - Sistema Grafici Centralizzato

Sistema unificato per la gestione di tutti i grafici Chart.js dell'applicazione (`assets/js/charts.js`):

**Factory Functions disponibili:**
- `createPerformanceDetailChart()` - Andamento Annuale mensile con dual axis
- `createCumulativeGainChart()` - Guadagno Cumulativo YTD con dual axis
- `createValueOverTimeChart()` - Ultimi 5 Giorni con dual axis
- `createDividendsMonthlyChart()` - Dividendi Mensili (bar chart stacked)
- `createDividendsCumulativeChart()` - Dividendi Cumulativi (line chart)
- `createPerformanceChart()` - Dashboard Performance mensile
- `createAllocationChart()` - Dashboard Allocation (donut chart)

**Configurazioni globali:**
```javascript
ChartManager.colors        // Palette colori unificata (purple, gray, ecc.)
ChartManager.animation     // Animazioni uniformi (800ms, easeOutQuart)
ChartManager.pointStyle    // Stile punti: quadrati 6px con bordo bianco 2px
```

**Design System unificato:**
- **Punti**: Quadrati (rect) 6px con bordo bianco 2px su tutti i grafici
- **Pattern**: Linee diagonali (Patternomaly) per aree riempite
- **Dual Axis**: Euro (sinistra) e Percentuale (destra) con tooltip formattati
- **Legenda**: Bottom con dimensioni ottimizzate (12px boxWidth, 10px font)
- **Lazy Loading**: MutationObserver per inizializzazione solo quando visibile

**Utility Functions:**
- `formatEuro(value, decimals)` - Formattazione consistente valori in euro
- `formatPercent(value, decimals)` - Formattazione consistente percentuali
- `getPattern(color)` - Generazione pattern diagonale Patternomaly

**Benefici:**
- Codice ridotto del 35% (~450 righe eliminate)
- Manutenzione centralizzata in un unico file
- Coerenza visiva garantita su tutti i grafici
- Facile estensione per nuovi tipi di grafici

### 🤖 Automazione n8n

Sistema di enrichment automatico del portafoglio con workflow n8n:

**Workflow "Portfolio Enrichment v2.2"** (daily @ 22:00):
- **Aggiornamento prezzi** da 4 provider API con fallback chain:
  1. TwelveData (primario)
  2. Financial Modeling Prep (secondario)
  3. Yahoo Finance (terziario)
  4. JustETF web scraping (ultimo tentativo)
- **Classificazione automatica** settore e asset_class da nome ETF
- **Creazione snapshot giornaliero** per tracking performance storica
- **Aggiornamento monthly_performance** da snapshots aggregati
- **Autenticazione HMAC-SHA256** per webhook sicuro
- **Gestione rate limits** con batch processing (5 holdings/batch, 10s delay)

**Configurazione** in `portfolio.json`:
```json
{
  "n8n_config": {
    "webhook_url": "https://n8n.domain.com/webhook/...",
    "hmac_secret": "your-secret-key",
    "sync_frequency": "daily"
  }
}
```

**Script di gestione**:
- `initialize-snapshots.php` - Inizializza primo snapshot
- `add-snapshot.php` - Crea snapshot manuale
- `recalculate-metrics.php` - Ricalcola allocation_by_asset_class

### 🔍 Analisi Tecnica

Indicatori tecnici e segnali operativi per ogni holding:

- **Indicatori disponibili**: EMA, MACD, RSI, Bollinger Bands
- **Segnali sintetici**: BUY, SELL, HOLD, WATCH con confidence score
- **Visualizzazione grafica**: Chart con indicatori sovrapposti
- **Alert automatici**: Notifiche su condizioni tecniche rilevanti

*Dati da `technical_analysis.json`*

### 💰 Calendario Dividendi

Tracciamento completo delle distribuzioni:

- **Calendario annuale**: Vista dividendi attesi e percepiti
- **Metriche per holding**: Dividend yield, frequenza distribuzione
- **Storico pagamenti**: Registro completo delle distribuzioni ricevute
- **Proiezioni**: Stima dividendi futuri basata su storico
- **Dati n8n**: yield/frequenza/annual_dividend aggiornati dal workflow; calendario generato automaticamente

*Dati da `dividends_calendar.json`*

### 💡 Raccomandazioni Operative

Sistema di suggerimenti per ottimizzare il portafoglio:

- **Opportunità**: Segnalazione ETF interessanti non presenti in portafoglio
- **Ribilanciamento**: Suggerimenti operativi per riallineare alle allocazioni target
- **Alert di mercato**: Notifiche su eventi rilevanti per le holdings
- **Confidence score**: Valutazione della qualità di ogni raccomandazione

*Dati da `recommendations.json` e `opportunities.json`*

### 📥 Import/Export Dati

Funzionalità per gestire i dati del portafoglio:

- **Import CSV Fineco**: Parser automatico del formato export Fineco
- **Backup JSON**: Copia semplice della cartella `/data` per backup completo
- **Export reports**: Possibilità di esportare viste e analisi
- **Portabilità**: Dati in formato leggibile e trasferibile

---

## API REST (implementate)

L'applicazione espone API REST per l'interazione con i dati del portafoglio:

**Base URL:**
- Sviluppo: `http://localhost:8000`
- Produzione: `https://portfolio.newwave-media.it`

### Holdings API

**Endpoint:** `/api/holdings.php`

| Metodo | Azione | Descrizione |
|--------|--------|-------------|
| `GET` | Lista holdings | Ritorna tutte le posizioni del portafoglio con calcoli aggiornati |
| `POST` | Crea/Aggiorna holding | Inserisce una nuova posizione o aggiorna esistente (via ISIN) |
| `DELETE` | Elimina holding | Rimuove una posizione dal portafoglio (parametro `isin` richiesto) |

**Esempio chiamata GET:**
```javascript
fetch('/api/holdings.php')
  .then(res => res.json())
  .then(data => {
    console.log(data.holdings);  // Array di holdings
    console.log(data.metadata);  // Metriche aggregate
  });
```

**Esempio chiamata POST (crea/aggiorna):**
```javascript
fetch('/api/holdings.php', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({
    isin: 'IE00B3RBWM25',
    ticker: 'VWCE',
    name: 'Vanguard FTSE All-World',
    quantity: 100,
    avg_price: 85.50,
    current_price: 89.45
  })
});
```

### Import CSV

**Classe:** `PortfolioManager::importFromCsv()`

- Parsing automatico del formato CSV export Fineco
- Mapping colonne: Titolo, ISIN, Simbolo, Mercato, Strumento, Valuta, Quantità, Prezzo medio
- Skip delle prime 3 righe (header Fineco)
- Validazione dati e report errori
- Backup automatico prima dell'import

### File JSON di output

| File | Contenuto | Aggiornamento |
|------|-----------|---------------|
| `portfolio.json` | Holdings, metadata, transazioni, n8n_config | Automatico (CRUD + n8n enrichment) |
| `snapshots.json` | Storico giornaliero performance | Automatico (n8n daily @ 22:00) |
| `technical_analysis.json` | Indicatori tecnici, segnali | Pianificato (workflow futuro) |
| `opportunities.json` | ETF opportunità, nuove idee | Pianificato (workflow futuro) |
| `recommendations.json` | Suggerimenti operativi | Manuale/Schedulato |
| `dividends_calendar.json` | Calendario dividendi attesi/percepiti | Manuale |
| `dashboard_insights.json` | Metriche dashboard | Automatico |

---

## Configurazione e setup

### Prerequisiti

**Per l'MVP attuale:**
- PHP 8.0+ con estensioni: `json`, `fileinfo`
- Web server (Apache, Nginx, o PHP built-in server)
- Permessi di scrittura sulla cartella `/data`
- Account Fineco (per operatività e export CSV)

**Non serve:**
- Database server (usa JSON)
- Docker (deployment semplificato)
- Configurazioni complesse

### Installazione rapida

**1. Clone del repository**
```bash
git clone https://github.com/your-username/trading-portfolio.git
cd trading-portfolio
```

**2. Verifica permessi cartella dati**
```bash
chmod 755 data/
chmod 644 data/*.json
```

**3. Avvia server di sviluppo**

**Opzione A - PHP Built-in Server (più semplice):**
```bash
php -S localhost:8000
```

**Opzione B - Apache/Nginx:**
- Punta document root a `/path/to/trading-portfolio`
- Assicurati che `.htaccess` sia processato (Apache) o configura rewrite rules (Nginx)

**4. Accedi all'applicazione**

**Sviluppo locale:**
```
http://localhost:8000
```

**Produzione:**
```
https://portfolio.newwave-media.it/
```

### Inizializzazione dati

**Prima volta:**

1. L'applicazione parte con portfolio vuoto (`portfolio.json` inizializzato)
2. Puoi:
   - **Import CSV da Fineco**: Esporta il tuo portafoglio da Fineco → Import nella sezione Holdings
   - **Insert manuale**: Aggiungi holdings singolarmente via UI o API

**Struttura `data/portfolio.json` iniziale:**
```json
{
  "metadata": {
    "portfolio_name": "Portafoglio ETF Personale",
    "owner": "User",
    "base_currency": "EUR",
    "total_value": 0.00,
    "holdings_count": 0
  },
  "holdings": [],
  "transactions": [],
  "dividends": []
}
```

### Popolamento dati di esempio (opzionale)

I file JSON nella cartella `/data` possono essere popolati con dati di esempio:

- `technical_analysis.json` - Segnali tecnici per holdings
- `opportunities.json` - ETF opportunità
- `recommendations.json` - Suggerimenti operativi
- `dividends_calendar.json` - Calendario dividendi

**Formato esempio per `technical_analysis.json`:**
```json
{
  "last_update": "2025-11-26T10:00:00Z",
  "signals": [
    {
      "isin": "IE00B3RBWM25",
      "ticker": "VWCE",
      "signal": "HOLD",
      "confidence": 75,
      "indicators": {
        "rsi": 58,
        "macd": "positive",
        "ema_trend": "up"
      }
    }
  ]
}
```

---

## Struttura progetto

```
trading-portfolio/
├── README.md                       # Questo file
├── STYLE_GUIDE.md                  # Linee guida design (REGOLE FERREE)
│
├── index.php                       # Entry point principale
│
├── lib/
│   └── PortfolioManager.php        # Classe gestione portfolio.json
│
├── api/
│   ├── holdings.php                # API REST per holdings
│   └── n8n/
│       └── enrich.php              # Webhook n8n per enrichment (prezzi/dividendi/classificazione)
│
├── views/
│   ├── layouts/
│   │   ├── header.php              # HTML head e top navigation
│   │   ├── sidebar.php             # Sidebar con menu
│   │   └── footer.php              # Chiusura HTML e scripts
│   │
│   └── tabs/
│       ├── dashboard.php           # Dashboard principale
│       ├── holdings.php            # Gestione posizioni
│       ├── performance.php         # Analisi performance
│       ├── technical.php           # Analisi tecnica
│       ├── dividends.php           # Calendario dividendi
│       ├── recommendations.php     # Raccomandazioni operative
│
├── assets/
│   ├── css/
│   │   └── styles.css              # Stili globali applicazione
│   │
│   └── js/
│       ├── charts.js               # ChartManager: sistema centralizzato grafici (7 factory functions)
│       ├── app.js                  # JavaScript principale (tabs, utilities, Chart.js defaults)
│       ├── holdings.js             # Gestione holdings interattiva (CRUD, import CSV)
│       └── holdings-debug.js       # Debug utilities
│
├── data/                           # Storage JSON (gitignored per dati reali)
│   ├── portfolio.json              # Holdings, metadata, transazioni
│   ├── technical_analysis.json     # Indicatori tecnici e segnali
│   ├── opportunities.json          # ETF opportunità
│   ├── recommendations.json        # Suggerimenti operativi
│   ├── dividends_calendar.json     # Calendario dividendi
│   ├── snapshots.json              # Storico performance
│   ├── dashboard_insights.json     # Metriche dashboard
│   └── portfolio_data.php          # Dati statici per demo (deprecato)
│
└── docs/                           # Documentazione tecnica (roadmap)
    ├── 01-ARCHITETTURA.md
    ├── 02-GESTIONE-UTENTI.md
    ├── 03-DATABASE.md
    ├── 04-API-REST.md
    ├── 05-FRONTEND.md
    └── 06-N8N-WORKFLOWS.md
```

**Note sulla struttura:**

- **Semplicità prima di tutto**: Nessuna struttura MVC complessa, file organizzati per funzionalità
- **Modularità**: Viste separate per ogni sezione (tabs), facile manutenzione
- **Storage JSON**: Cartella `/data` contiene tutti i file dati, facile backup
- **Documentazione**: `STYLE_GUIDE.md` per linee guida UI/UX, `/docs` per architettura futura

---

## Documentazione

### Documentazione disponibile

| File | Contenuto | Stato |
|------|-----------|-------|
| `README.md` | Questo file - Panoramica generale progetto | ✅ Aggiornato |
| [`STYLE_GUIDE.md`](STYLE_GUIDE.md) | **REGOLE FERREE** di design: widget, colori, tipografia, grafici | ✅ Attivo |
| `docs/01-ARCHITETTURA.md` | Architettura futura (DB, Docker, n8n) | 🔄 Roadmap |
| `docs/02-GESTIONE-UTENTI.md` | Autenticazione multi-utente futura | 🔄 Roadmap |
| `docs/03-DATABASE.md` | Schema MariaDB/PostgreSQL futuro | 🔄 Roadmap |
| `docs/04-API-REST.md` | API REST estese future | 🔄 Roadmap |
| `docs/05-FRONTEND.md` | Dettagli implementazione frontend | 🔄 Roadmap |
| `docs/06-N8N-WORKFLOWS.md` | Workflow automation n8n | 🔄 Roadmap |

### Linee guida per sviluppo

**Per modifiche UI/UX:**
- **Consulta sempre** [`STYLE_GUIDE.md`](STYLE_GUIDE.md) prima di modificare stili, colori, tipografia o layout
- Mantieni coerenza con il design system esistente

**Per modifiche al codice:**
- Testa sempre localmente prima di committare
- Backup della cartella `/data` prima di modifiche che impattano i JSON
- Valida il JSON dopo modifiche manuali ai file dati

**Per aggiungere nuove funzionalità:**
- Inizia aggiornando questo README con la nuova funzionalità
- Documenta nuovi endpoint API nella sezione "API REST"
- Aggiungi esempi di utilizzo quando possibile

---

## Utilizzo quotidiano

### Workflow tipico

**1. Apertura applicazione**
```
http://localhost:8000
```
L'applicazione si apre direttamente sulla Dashboard (nessun login richiesto nella versione MVP)

**2. Visualizzazione stato portafoglio**
- **Dashboard** mostra:
  - Valore totale e P&L complessivo
  - Score portfolio
  - Allocazioni per asset class/settore
  - Drift da target allocation
  - Holdings principali

**3. Gestione operazioni**

**Dopo un'operazione su Fineco:**
- Vai alla sezione **Holdings**
- Aggiorna la holding interessata (quantità, prezzo medio)
- Oppure aggiungi una nuova holding se è un primo acquisto
- Il sistema ricalcola automaticamente tutte le metriche

**Import massivo:**
- Esporta portfolio da Fineco in CSV
- Usa funzione "Import CSV" nella sezione Holdings
- Verifica i dati importati

**4. Analisi e monitoraggio**

**Tabs disponibili:**
- **Performance**: Andamento storico e metriche di performance
- **Analisi Tecnica**: Segnali operativi basati su indicatori tecnici
- **Dividendi**: Calendario dividendi attesi e percepiti
- **Raccomandazioni**: Suggerimenti per ottimizzare il portafoglio
- **Flussi**: Analisi flussi di capitale in entrata/uscita

**5. Manutenzione dati**

**Backup periodico:**
```bash
# Copia l'intera cartella data
cp -r data/ backup/data_$(date +%Y%m%d)/
```

**Aggiornamento prezzi:**
- Manualmente: Modifica `current_price` nelle holdings via API o UI
- Automatico (futuro): Integrazione con data provider

### Frequenza attività suggerite

| Attività | Frequenza | Sezione |
|----------|-----------|---------|
| Aggiorna holdings dopo trade | Ogni operazione | Holdings |
| Verifica dashboard | Giornaliera | Dashboard |
| Analisi tecnica | Settimanale | Technical |
| Review raccomandazioni | Settimanale | Recommendations |
| Ribilanciamento | Mensile | Dashboard (drift) |
| Backup dati | Settimanale | Cartella `/data` |

---

## Roadmap

### Fase 1: MVP - JSON Based ✅ (Attuale)

**Obiettivo:** Prototipo funzionante per validare UX e logiche di business

- ✅ Dashboard interattiva con metriche principali
- ✅ Gestione holdings con API REST (CRUD)
- ✅ Import CSV da Fineco
- ✅ Visualizzazioni performance, analisi tecnica, dividendi
- ✅ Sistema tabs modulare
- ✅ Storage JSON per dati portfolio

### Fase 2: Database Migration 🔄 (Prossima)

**Obiettivo:** Evoluzione verso architettura scalabile e robusta

- [ ] **Database relazionale**: Migrazione da JSON a MariaDB/PostgreSQL
  - Schema `portfolio`: users, portfolios, holdings, transactions
  - Schema `market_data`: quotazioni, ETF metadata, commissioni
- [ ] **Autenticazione multi-utente**: Login/registrazione con sessioni sicure
- [ ] **Multi-portfolio**: Supporto multipli portafogli per utente
- [ ] **Storico transazioni**: Timeline completa operazioni (BUY/SELL/DIVIDEND)
- [ ] **Snapshots temporali**: Salvataggio giornaliero stato portafoglio
- [ ] **Containerizzazione**: Docker Compose per deployment semplificato

### Fase 3: Automation & Intelligence 🚀 (Futura)

**Obiettivo:** Analisi avanzate e automazioni operative

- [ ] **Integrazione n8n**: Workflow automation per analisi
  - Workflow A: Analisi tecnica giornaliera (EMA, MACD, RSI, Bollinger)
  - Workflow B: Scouting opportunità ETF
  - Workflow C: Sentiment analysis macro/news
  - Workflow D: Piano ribilanciamento automatico
- [ ] **Aggiornamento prezzi automatico**: Integrazione API quotazioni real-time
- [ ] **Alert intelligenti**: Notifiche su condizioni di mercato rilevanti
- [ ] **Backtesting**: Simulazione strategie su dati storici

### Fase 4: Advanced Features 💎 (Long-term)

**Obiettivo:** Funzionalità avanzate per utenti esperti

- [ ] **Multi-valuta**: Supporto USD, GBP, CHF con conversioni FX automatiche
- [ ] **Analisi di rischio**: Volatilità, Sharpe ratio, Max Drawdown, correlazioni
- [ ] **Ottimizzazione fiscale**: Tracking plusvalenze, PFU, mod. 730
- [ ] **PWA**: App installabile con notifiche push
- [ ] **API pubblica**: Endpoint per integrazioni esterne
- [ ] **RBAC**: Ruoli e permessi per condivisione portfolio
- [ ] **OAuth Fineco**: Integrazione diretta (se API diventa pubblica)

---

## Troubleshooting

### Grafici Performance non si popolano

**Sintomo**: I grafici nella sezione "Performance & Flussi" non mostrano dati.

**Cause possibili**:
1. **Snapshots mancanti**: `data/snapshots.json` vuoto o non esistente
2. **Charts non inizializzati**: Script JavaScript non eseguito su tab nascosto

**Soluzioni**:

**1. Crea primo snapshot**:
```bash
php initialize-snapshots.php
```

**2. Verifica dati con debug**:
Apri nel browser: `debug-charts-web.php` o `debug-performance-widgets-web.php`

**3. Verifica inizializzazione charts**:
- Apri console browser (F12)
- Vai su tab "Performance & Flussi"
- Dovresti vedere: `🎯 initializePerformanceCharts() called`
- Se non vedi il messaggio: hard refresh (Ctrl+Shift+R / Cmd+Shift+R)

**Nota tecnica**: I grafici usano MutationObserver per inizializzarsi solo quando il tab diventa visibile, evitando problemi di rendering con Chart.js in container nascosti.

### Widget performance mostrano "-"

**Sintomo**: I widget 1M, 3M, YTD mostrano "-" invece delle percentuali.

**Causa**: Con un solo snapshot, non ci sono dati storici per calcolare variazioni.

**Soluzione**:
- Con 1 snapshot: i widget mostreranno `+0,00%` (nessuna variazione)
- Attendi il prossimo enrichment n8n (daily @ 22:00) per creare nuovi snapshot
- Oppure crea snapshot manuale: `php add-snapshot.php`

### Import CSV Fineco fallisce

**Sintomo**: Errore durante import CSV esportato da Fineco.

**Verifiche**:
1. **Formato file**: CSV con separatore `;` (punto e virgola)
2. **Encoding**: UTF-8 con BOM
3. **Colonne richieste**: Titolo, ISIN, Simbolo, Quantità, Prezzo medio
4. **Righe header**: Le prime 3 righe vengono automaticamente saltate

### n8n enrichment non aggiorna prezzi

**Sintomo**: Workflow n8n eseguito ma prezzi non aggiornati.

**Verifiche**:
1. **HMAC signature**: Controlla che `hmac_secret` in `portfolio.json` corrisponda a quello configurato in n8n
2. **Webhook URL**: Verifica URL corretto in `n8n_config.webhook_url`
3. **Logs n8n**: Controlla esecuzione workflow in n8n UI
4. **API rate limits**: TwelveData ha limiti giornalieri (8 calls/min, 800/day su free tier)

**Debug manuale**:
```bash
# Verifica ultimo aggiornamento
cat data/portfolio.json | grep last_update

# Controlla ultimo snapshot
cat data/snapshots.json | tail -n 50
```

---

## Sicurezza

### Stato attuale (MVP)

**⚠️ IMPORTANTE:** La versione MVP è pensata per uso locale/personale e **NON** include autenticazione.

**Misure implementate:**
- ✅ Storage locale JSON (nessun dato in cloud)
- ✅ Validazione input lato server nelle API
- ✅ Backup automatico prima di operazioni critiche (import CSV, update massive)

**Non implementate (roadmap Fase 2+):**
- ❌ Autenticazione/autorizzazione utenti
- ❌ Crittografia dati sensibili
- ❌ Rate limiting API
- ❌ Audit log operazioni
- ❌ SSL/TLS (da configurare su web server)

### Raccomandazioni per uso sicuro

**Uso locale/personale:**
1. **NON esporre** il server su internet pubblico
2. Esegui solo su `localhost` o rete privata
3. Backup regolari della cartella `/data`
4. Non committare dati reali su repository pubblici (gitignore `/data`)

**Per production (Fase 2+):**
- [ ] Implementare autenticazione robusta (Argon2id, pepper)
- [ ] HTTPS obbligatorio (Let's Encrypt, certificato valido)
- [ ] Rate limiting su API
- [ ] WAF (Web Application Firewall)
- [ ] Monitoring e alerting
- [ ] Backup automatizzati e encrypted

### Best practices gestione dati

```bash
# Gitignore per dati sensibili
echo "data/*.json" >> .gitignore
echo "data/*.backup" >> .gitignore

# Backup periodico
#!/bin/bash
BACKUP_DIR="backups/$(date +%Y%m%d_%H%M%S)"
mkdir -p $BACKUP_DIR
cp -r data/ $BACKUP_DIR/
echo "Backup salvato in: $BACKUP_DIR"
```

---

## Disclaimer

**⚠️ AVVERTENZE IMPORTANTI:**

1. **Non è consulenza finanziaria**: Questo software è uno strumento di analisi personale. Non costituisce consulenza finanziaria, raccomandazione d'investimento o sollecitazione all'investimento.

2. **Responsabilità dell'utente**: L'utente è pienamente e unicamente responsabile delle proprie decisioni di investimento. L'utilizzo di questo software avviene a proprio rischio.

3. **Accuratezza dati**: I dati, analisi e segnali forniti potrebbero essere incompleti, errati o non aggiornati. Verifica sempre le informazioni da fonti ufficiali.

4. **Rischi di mercato**: Gli investimenti finanziari comportano rischi, inclusa la possibile perdita del capitale investito.

5. **Nessuna garanzia**: Il software è fornito "così com'è" senza garanzie di alcun tipo, esplicite o implicite.

6. **Marchi**: Fineco Bank è marchio registrato di FinecoBank S.p.A. Nessuna affiliazione, sponsorizzazione o endorsement.

7. **Licenza**: Consultare il file LICENSE per i termini d'uso del software.

---

## Contatti e contributi

**Repository:** [GitHub - trading-portfolio](https://github.com/your-username/trading-portfolio)

**Contributi:**
- Pull requests benvenute per bugfix e miglioramenti
- Apri issue per segnalare bug o proporre funzionalità
- Consulta CONTRIBUTING.md per linee guida (se disponibile)

**Per domande:**
- Apri una discussione su GitHub Discussions
- Consulta la documentazione in `/docs`

---

**Ultimo aggiornamento README**: 27 Novembre 2025 (ChartManager System)
**Versione progetto**: 0.2.0-MVP (JSON Based + n8n Integration + Centralized Charts)
