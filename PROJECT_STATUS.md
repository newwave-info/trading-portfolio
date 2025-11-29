# 📊 ETF Portfolio Manager - Stato Avanzamento Lavori

**Ultimo aggiornamento:** 29 Novembre 2025
**Versione:** 0.3.3-MySQL ✅
**Stato:** Produzione - Migrazione MySQL completata, Repository Pattern implementato, n8n integration attiva, viste Performance/Dividendi allineate a MySQL

**Aggiornamenti recenti**
- n8n DB-first: `/api/n8n/portfolio.php` e `/api/n8n/enrich.php` leggono/scrivono solo MySQL (niente JSON).
- Ricalcolo metriche/snapshot su DB via `PortfolioMetricsService` dopo ogni enrichment.
- Aggiunta `base_currency` su `portfolios` + vista `v_portfolio_metadata` aggiornata (migrazione `docs/migrations/2025_11_29_add_base_currency.sql`).
- Dashboard snellita: rimangono Salute Portafoglio, AI Insight, Andamento Mensile; i widget operativi (Top/Bottom/Allocazioni/Dividendi ricevuti/Opportunità) sono ora nella vista Holdings.
- Vista Dividendi allineata al DB: ex-date come metrica base, pay-date secondario, conteggi RECEIVED ultimi 12 mesi, calendario e forecast solo da MySQL.

> 📋 **Documentazione:**
> - [README.md](README.md) - Panoramica generale e setup (aggiornato 26 Nov 2025)
> - [STYLE_GUIDE.md](STYLE_GUIDE.md) - Linee guida UI/UX (REGOLE FERREE)
> - Questo documento - Stato tecnico dettagliato e prossimi step operativi

---

## 🚀 **MIGRAZIONE MYSQL (v0.3.1) - COMPLETATA 27/11/2025**

### **Architettura Database**
- **Storage:** Migrazione completa da JSON a MySQL
- **Database:** `trading_portfolio` (MySQL 8.2.29)
- **Schema:** 10 tabelle + 2 VIEWs per computed values real-time
- **Pattern:** Repository Pattern per separazione concerns
- **Performance:** Query indexed, connection pooling, transactions

### **Database Schema**

#### Tabelle (10)
1. **`portfolios`** - Metadata portfolio
2. **`holdings`** - Posizioni con prezzi correnti
3. **`transactions`** - Storico transazioni (BUY/SELL/DIVIDEND/FEE)
4. **`dividend_payments`** - Dividendi ricevuti e previsti
5. **`snapshots`** - Snapshot giornalieri portafoglio
6. **`snapshot_holdings`** - Holdings per ogni snapshot
7. **`allocation_by_asset_class`** - Allocazioni per classe asset
8. **`monthly_performance`** - Performance mensile aggregata
9. **`metadata_cache`** - Cache valori computati (opzionale)
10. **`cron_logs`** - Log esecuzioni cron jobs

#### VIEWs (2) - Computed Values Real-Time
1. **`v_holdings_enriched`** - Holdings con P&L calcolati in tempo reale
   - Calcola: `invested`, `market_value`, `pnl`, `pnl_pct`
   - Usata per: Dashboard, Holdings table, API
2. **`v_portfolio_metadata`** - Metadata portfolio aggregati
   - Calcola: totali invested/value/pnl, count holdings, dividends received
   - Usata per: Dashboard header, metriche

### **Repository Pattern**

#### Struttura
```
lib/Database/
├── DatabaseManager.php          (Singleton PDO wrapper)
├── config.php                   (Database configuration)
└── Repositories/
    ├── BaseRepository.php       (Abstract CRUD base)
    ├── PortfolioRepository.php  (Metadata, allocations, performance)
    ├── HoldingRepository.php    (Holdings + enriched VIEW)
    ├── TransactionRepository.php
    ├── DividendRepository.php
    └── SnapshotRepository.php
```

#### Features
- **DatabaseManager:** Singleton, connection pooling, transactions, auto-reconnect
- **BaseRepository:** CRUD base (find, findAll, create, update, delete)
- **Transactions:** BEGIN/COMMIT/ROLLBACK support per atomic operations
- **Security:** PDO prepared statements, no SQL injection

### **Migrazione Dati**

#### Script: `scripts/migrate-to-mysql.php`
- ✅ **Eseguita:** 27 Novembre 2025
- **Dati migrati:**
  - 1 Portfolio
  - 4 Holdings (SGLD.MI, VHYL.MI, TDIV.MI, SPYD.FRA)
  - 0 Transactions (nessuna transazione in JSON)
  - 0 Dividends (nessun dividendo in JSON)
  - 2 Allocations (Equity 67.33%, Commodity 32.67%)
  - 1 Snapshot
  - 1 Monthly Performance

#### Validazione Post-Migrazione
- ✅ Total invested: €9,733.27
- ✅ Total value: €10,575.85
- ✅ Holdings count: 4
- ✅ P&L calculations: Corretti
- ✅ VIEWs: Funzionanti
- ✅ Dashboard: Nessun errore

### **API Updates**

#### Nuovi Endpoint
1. **`POST /api/update.php`** - Webhook n8n per aggiornamento prezzi
   - Bulk update prices con transazioni atomiche
   - HMAC signature validation (opzionale)
   - Response JSON compatibile con n8n
   - Auto-update portfolio timestamp

#### Endpoint Aggiornati
2. **`GET /api/holdings.php`** - Lista holdings (usa MySQL VIEW)
3. **`POST /api/holdings.php`** - Create/Update holding (usa Repository)
4. **`DELETE /api/holdings.php?ticker=X`** - Soft delete holding
5. **POST/PUT/DELETE holdings** ora triggerano ricalcolo metriche (allocazioni, snapshot giornaliero, monthly_performance) tramite `PortfolioMetricsService`

### **Data Loader**

#### File: `data/portfolio_data.php`
- **Refactoring completo:** Da PortfolioManager (JSON) a Repositories (MySQL)
- **Mapping compatibilità:** Chiavi JSON → MySQL per retrocompatibilità view
- **Performance:** Query ottimizzate con VIEWs
- **Features:**
  - Metadata da `v_portfolio_metadata` VIEW
  - Holdings enriched da `v_holdings_enriched` VIEW
  - Allocazioni calcolate in real-time e ricaricate post-edit (via `PortfolioMetricsService`)
  - Monthly performance e storico performance letti da MySQL (`monthly_performance`, `snapshots`, `snapshot_holdings`) con fallback su metadati correnti
  - Fallback graceful in caso di errore DB

### **Vantaggi Migrazione**

✅ **Performance:** Query indexed più veloci di file I/O
✅ **Scalabilità:** Supporta milioni di record senza degrado
✅ **Integrità:** Foreign keys, constraints, transactions ACID
✅ **Real-time P&L:** Calcolato dalle VIEWs, sempre aggiornato
✅ **Concorrenza:** Gestione lock ottimistica per update simultanei
✅ **Backup:** Backup automatici MySQL invece di file JSON
✅ **Query complesse:** Aggregazioni, JOIN, analisi storiche

### **n8n Integration**

#### Webhook Configuration
- **URL:** `POST https://portfolio.newwave-media.it/api/update.php`
- **Payload:**
```json
{
  "holdings": [
    {"ticker": "SGLD.MI", "price": 345.50, "source": "YahooFinance_v8"},
    {"ticker": "VHYL.MI", "price": 68.85, "source": "YahooFinance_v8"}
  ]
}
```
- **Authentication:** HMAC SHA256 (opzionale, configurabile in `.env`)
- **Response:** `{"success": true, "updated": 4, "timestamp": "..."}`

#### Compatibilità
- ✅ Stesso payload format
- ✅ Stessa logica di update
- ✅ Response format invariato
- ✅ Nessuna modifica richiesta al workflow n8n

---

## ✅ **COMPLETATO**

### **1. Frontend UI/UX (100%)**
- [x] **Design System** implementato e documentato in `STYLE_GUIDE.md`
- [x] **Template statico** responsive con Tailwind CSS
- [x] **7 Viste principali** separate in file modulari:
  - Dashboard (con widget Salute Portafoglio, metriche, AI Insights)
  - Holdings (tabella posizioni con CRUD)
  - Performance & Flussi
  - Analisi Tecnica
  - Dividendi
  - Raccomandazioni (widget uniformati, no accordion)
  - Flussi
- [x] **Componenti UI standardizzati:**
  - Widget cards con header uniformi (icone + tooltip)
  - AI Insight widgets con styling viola dedicato
  - Tooltip system strutturale (da `_old/perspect`)
  - Grafici Chart.js con pattern Patternomaly
  - Tabelle sortable
  - Modali responsive
- [x] **Navigazione** sidebar con accordion
- [x] **Layout ottimizzato:**
  - Dashboard: Salute Portafoglio + 4 metriche (row 1), Riepilogo full-width (row 2)
  - Grafico donut spacing: 6
  - 32+ icone Font Awesome su titoli widget
  - Nomenclatura unificata: "Portafoglio" (non "portfolio")
- [x] **Sistema Grafici Centralizzato** (Chart.js + ChartManager):
  - **ChartManager** (`assets/js/charts.js`): Sistema centralizzato per tutti i grafici
    - Factory functions per 7 tipi di grafici (Performance, Dividendi, Dashboard)
    - Configurazioni globali unificate (colori, animazioni, stili punti)
    - Gestione pattern Patternomaly centralizzata
    - Utility functions per formattazione (euro, percentuali)
  - **Grafici Performance:**
    - Andamento Annuale (2025) con dati mensili + dual axis
    - Guadagno Cumulativo (YTD) con storico snapshot + dual axis
    - Ultimi 5 Giorni con valori portafoglio + dual axis
  - **Grafici Dividendi:**
    - Dividendi Mensili (bar chart stacked) con Ricevuti/Previsti
    - Dividendi Cumulativi (line chart) con pattern diagonali
  - **Grafici Dashboard:**
    - Performance Chart mensile con pattern diagonali
    - Allocation Chart (donut) per asset class
  - **Design System Unificato:**
    - Punti: quadrati (rect) 6px con bordo bianco 2px - uniforme su tutti i grafici
    - Dataset valore euro: linea viola + area con pattern a righe diagonali
    - Dataset percentuale: linea grigio scuro con z-index superiore
    - Doppio asse Y: euro (sx) e percentuale (dx) con tooltip personalizzati
    - Legenda bottom con dimensioni ottimizzate (12px boxWidth, 10px font)
  - **Ottimizzazioni:**
    - Lazy-loading con MutationObserver per tab nascoste
    - Registro grafici inizializzati per evitare duplicati
    - Codice ridotto del 35% (~450 righe risparmiate)

### **2. Refactoring Architettura (100%)**
- [x] **Struttura modulare MVC-like:**
  ```
  index.php              → Controller principale (40 righe)
  data/portfolio_data.php → Loader dati
  views/layouts/         → Header, sidebar, footer
  views/tabs/            → Viste separate per sezione
  ```
- [x] **Backup** creato: `index.php.backup` (119KB originale)

### **3. Sistema Dati (100%) - MySQL Database**
- [x] **Storage MySQL** (`trading_portfolio` database):
  - 10 tabelle relazionali con foreign keys
  - 2 VIEWs per computed values real-time
  - Indexes ottimizzati per query performance
  - Transactions ACID per data integrity
- [x] **Repository Pattern** (`lib/Database/Repositories/` + `Services/`):
  - 5 Repository classes (Portfolio, Holding, Transaction, Dividend, Snapshot)
  - BaseRepository con CRUD operations comuni
  - DatabaseManager singleton con connection pooling
  - Security: PDO prepared statements, no SQL injection
- [x] **PortfolioMetricsService** (`lib/Database/Services/PortfolioMetricsService.php`):
  - Ricalcolo derivati (allocazioni, snapshot giorno, monthly_performance) post-edit
  - Usato da `api/holdings.php` su create/update/delete
  - Script CLI: `scripts/recalculate-db-metrics.php` per sync manuale
- [x] **API REST Endpoints**:
  - `GET /api/holdings.php` → Lista holdings (da VIEW)
  - `POST /api/holdings.php` → Create/Update holding
  - `DELETE /api/holdings.php?ticker=X` → Soft delete holding
  - `POST /api/update.php` → Webhook n8n bulk price update
- [x] **Dividendi DB-first**:
  - Tabella `dividend_payments` popolata da n8n (`/api/dividends.php`) con forecast/received
  - Vista `v_dividends_enriched`: importi effettivi in base agli snapshot holdings alla ex_date
  - Repository `DividendEnrichedRepository` per lettura vista
- [x] **Data Loader** (`data/portfolio_data.php`):
  - Carica dati da MySQL usando Repositories
  - Mapping compatibilità chiavi per retrocompatibilità view
  - Performance/allocazioni ora da MySQL (`monthly_performance`, `snapshots`) con fallback metadati
  - Dividendi: storico da `dividend_payments`; forecast/calendar ora calcolati dal DB (FORECAST) con solo `ai_insight` in fallback JSON
  - Fallback graceful in caso di errore DB
- [x] **Migration Script** (`scripts/migrate-to-mysql.php`):
  - Migrazione completa JSON → MySQL
  - Validazione pre/post migrazione
  - Dry-run mode per testing
- [x] **Deprecato:** `lib/PortfolioManager.php` (sostituito da Repositories)
  - Pulsanti Edit/Delete per ogni riga
  - JavaScript AJAX con fetch API (`assets/js/holdings.js`)
  - Notification toast system
  - Animazioni fade-out su delete

### **4. Documentazione (90%)**
- [x] `README.md` completo con architettura, setup, API logic
- [x] `STYLE_GUIDE.md` v1.2 con 9 regole ferree + checklist
- [x] Struttura `docs/` con file tematici (da aggiornare post-refactoring)
- [x] Commenti inline nel codice PHP/JS

---

## 🚧 **IN CORSO / PARZIALE**

### **1. Gestione Dati (70%)**
- [x] Storage JSON funzionante
- [x] **Snapshot giornalieri** implementati con auto-creazione giornaliera
  - Sistema snapshot completo (`data/snapshots.json`)
  - Auto-creazione via n8n enrichment (daily @ 22:00)
  - Script manuali: `initialize-snapshots.php`, `add-snapshot.php`
  - Grafici performance popolati da snapshots
- [ ] **Migrazione a Database MariaDB** (schema definito, non implementato)
  - Schema `market_data` (etf_info, prices, fineco_commissions)
  - Schema `utente` (users, portfolios, holdings, transactions, dividends, snapshots)
  - Script SQL da creare in `db/init.sql`
- [ ] **Storico transazioni** (struttura presente, non usata nel frontend)

### **2. Integrazione Quotazioni (0%)**
- [ ] API esterna per prezzi (Alpha Vantage / Yahoo Finance)
- [ ] Aggiornamento automatico current_price negli holdings
- [ ] Gestione multi-valuta (EUR/USD/GBP) con tassi cambio

### **3. Autenticazione & Multi-utente (0%)**
- [ ] Sistema login/registrazione (Argon2id + pepper)
- [ ] Gestione sessioni sicure
- [ ] Isolamento dati per `user_id`
- [ ] Multi-portafoglio per utente

---

### **5. Integrazione n8n (100%)**
- [x] **Setup n8n** operativo (self-hosted)
- [x] **Webhook sicuro con HMAC-SHA256:**
  - Endpoint `POST /api/n8n/enrich.php` con autenticazione HMAC
  - Verifica signature per prevenire chiamate non autorizzate
  - Webhook URL e HMAC secret configurabili in `portfolio.json`
- [x] **Workflow Portfolio Enrichment v2.2** (giornaliero @ 22:00):
  - Fetch prezzi da 4 provider con fallback chain:
    1. TwelveData API (primario)
    2. Financial Modeling Prep (secondario)
    3. Yahoo Finance (terziario)
    4. JustETF web scraping (ultimo tentativo)
  - Classificazione automatica settore/asset_class da nome ETF
  - Aggiornamento holdings in `portfolio.json` con prezzi attuali
  - Creazione snapshot giornaliero in `data/snapshots.json`
  - Aggiornamento `monthly_performance` da snapshots
  - Gestione rate limits e batch processing (5 holdings/batch)
- [x] **Script di gestione:**
  - `initialize-snapshots.php` - Crea primo snapshot
  - `add-snapshot.php` - Aggiunge snapshot manualmente
  - `recalculate-metrics.php` - Ricalcola allocation_by_asset_class
  - `debug-charts.php` / `debug-charts-web.php` - Debug dati grafici
  - `debug-performance-widgets-web.php` - Debug widget performance

## ❌ **NON INIZIATO**

### **1. Workflow n8n Aggiuntivi**
- [ ] **Workflow B - Opportunità ETF** (settimanale):
  - Fetch ETF Fineco zero commissioni (scraping/API)
  - Filtra: non già in portafoglio
  - Analisi fondamentale (TER, AUM, Yield, YTD)
  - Storage: `data/opportunities.json`
- [ ] **Workflow C - Macro/News Sentiment** (giornaliero):
  - Fetch news finanziarie (newsdata.io API)
  - NLP sentiment analysis per settore
  - Storage: `data/macro_sentiment.json`
- [ ] **Workflow D - Piano Ribilanciamento** (mensile):
  - Calcolo drift vs target allocation
  - Generazione piano operativo con stima costi
  - Storage: `data/rebalancing_plan.json`

### **2. Features Frontend**
- [ ] **Dashboard:**
  - Grafici storici performance dinamici (da snapshots)
  - Aggiornamento real-time metriche senza reload
- [ ] **Holdings:**
  - Validazione ISIN lato client (regex)
  - Autocomplete ISIN da database ETF
  - Preview CSV prima import (mostra prime 5 righe)
  - Export CSV holdings correnti
  - Filtri/ricerca nella tabella
  - Ordinamento colonne cliccabile avanzato
- [ ] **Dividendi:**
  - Form registrazione dividendo manuale
  - Calendario prossimi stacchi dividendi
  - Calcolo yield annualizzato
- [ ] **Transazioni:**
  - Timeline transazioni filtrata (data, tipo, ISIN)
  - Grafici flussi in/out
  - Export CSV storico

### **3. Ottimizzazioni & Produzione**
- [ ] **Caching:**
  - Redis per query pesanti
  - Cache API esterne (rate limiting)
- [ ] **Security:**
  - Rate limiting endpoints API
  - CSRF token per form
  - Validazione server-side robusta
  - Sanitizzazione output HTML (XSS prevention)
  - SSL/TLS setup
  - WAF + fail2ban
- [ ] **Performance:**
  - Background jobs per operazioni lunghe (import, calcoli)
  - Lazy loading tabelle grandi
  - Ottimizzazione query DB (indici, prepared statements)
- [ ] **Deployment:**
  - Docker Compose production-ready
  - Nginx reverse proxy
  - Backup automatici DB/JSON
  - Monitoring (logs, metriche)
  - CI/CD pipeline (GitHub Actions)

### **4. Features Avanzate (Roadmap futura)**
- [ ] PWA (Progressive Web App) con service worker
- [ ] Notifiche push per alert critici (drift >5%, segnali)
- [ ] Analisi rischio avanzata (volatilità, Sharpe ratio, drawdown, correlazioni)
- [ ] Ottimizzazione fiscale italiana (mod. 730, PFU)
- [ ] RBAC - ruoli (viewer, editor, admin per portafoglio)
- [ ] OAuth Fineco (se API diventa pubblica)
- [ ] Mobile app (React Native / Flutter)

---

## 📋 **PROSSIMI STEP CONSIGLIATI**

> 💡 **Nota strategica:** Il progetto segue una roadmap a 4 fasi (vedi [README.md](README.md#roadmap)):
> - ✅ **Fase 1 (MVP JSON)** - Completata
> - 🔄 **Fase 2 (Database Migration)** - Prossima priorità
> - 🚀 **Fase 3 (Automation n8n)** - Dopo Fase 2
> - 💎 **Fase 4 (Advanced Features)** - Long-term
>
> Gli step qui sotto sono **operativi/tattici** per completare le funzionalità intermedie prima della Fase 2.

### **STEP 1: Completare Backend Dati (Priorità ALTA)**
**Obiettivo:** Rendere il sistema completamente funzionale con dati reali

1. **Testare CRUD Holdings:**
   - [ ] Aggiungere posizione manualmente via form
   - [ ] Modificare posizione esistente
   - [ ] Eliminare posizione
   - [ ] Importare CSV Fineco (`data/portafoglio-export.csv`)
   - [x] Verificare ricalcolo metriche automatico (allocazioni/snapshot/monthly_performance) post-edit

2. **Integrare API Quotazioni:**
   - [ ] Scegliere provider (Alpha Vantage gratuito 25 req/giorno, Yahoo Finance illimitato)
   - [ ] Creare endpoint `POST /api/update-prices.php`
   - [ ] Implementare fetch quotazioni per ISIN list
   - [ ] Aggiornare `current_price` in DB holdings
   - [ ] Trigger ricalcolo metriche
   - [ ] Pulsante "Aggiorna Prezzi" nel frontend (Holdings header)

3. **Storico Performance (DB-first):**
   - [x] Usare `snapshots`/`monthly_performance` da MySQL per grafici Performance
   - [ ] Cron job o pulsante manuale per creare snapshot (usa `PortfolioMetricsService`)
   - [ ] Popolare `snapshot_holdings` con stato corrente (service già pronto)

### **STEP 2: Setup Integrazione n8n (Priorità ALTA)**
**Obiettivo:** Automatizzare analisi e recupero dati

1. **Setup Container n8n:**
   - [ ] Aggiornare `docker-compose.yml` con service n8n
   - [ ] Configurare volume persistente
   - [ ] Avviare n8n su `localhost:5678`

2. **Workflow Quotazioni (il più semplice per iniziare):**
   - [ ] Creare workflow n8n che:
     - Riceve lista ISIN via webhook
     - Fetch quotazioni da Yahoo Finance/Alpha Vantage
     - Restituisce JSON `{ "IE00B3RBWM25": 89.45, ... }`
   - [ ] Ricevere risultati direttamente in DB (aggiorna holdings + ricalcolo metriche), deprecando `PortfolioManager`

3. **Workflow Analisi Tecnica:**
   - [ ] Implementare calcolo indicatori (EMA, MACD, RSI, Bollinger)
   - [ ] Logica generazione segnali BUY/SELL/HOLD/WATCH
   - [ ] Storage risultati in DB (tabella `technical_analysis`) invece di `data/technical_analysis.json`
   - [ ] Mostrare in vista "Analisi Tecnica"

4. **Workflow Dividendi (attivo):**
   - [x] n8n genera forecast (media ultimi 4, rolling 4 future), status FORECAST
   - [x] Quando ex_date arriva, status → RECEIVED; quantità correnti inviate (API calcola qty snapshot se esiste)
   - [x] Invio a `/api/dividends.php` (upsert ticker+ex_date+status), totale calcolato da API se manca
   - [ ] Aggiungere snapshot giornalieri per migliorare accuratezza quantità a ex_date

### **STEP 3: Miglioramenti UX (Priorità MEDIA)**
**Obiettivo:** Rendere il sistema più usabile quotidianamente

1. **Dashboard Real-time:**
   - [ ] AJAX refresh metriche ogni 30 secondi (opzionale)
   - [ ] Indicatore "Ultimo aggiornamento: X minuti fa"
   - [ ] Pulsante refresh manuale globale

2. **Holdings Enhancements:**
   - [ ] Validazione ISIN con regex (formato corretto)
   - [ ] Messaggio errore se ISIN già esistente
   - [ ] Conferma prima di eliminare (con recap valore posizione)
   - [ ] Undo dopo delete (toast con pulsante "Annulla")

3. **Dividendi**
   - [x] Integrazione n8n completata (yield, freq, annual_dividend, calendario forecast)
   - [x] Grafici e tab Dividendi collegati a dati reali (ricevuti) e previsti
   - [x] Payout automatico con `dividends-payout.php` (cron daily, niente input manuale)
   - [ ] Modale "Registra Dividendo" manuale (future)
   - [ ] Salvataggio nuove registrazioni in `portfolio.json → dividends[]` (future)

4. **Transazioni**
   - [x] Log automatico BUY/SELL/DIVIDEND in `portfolio.json -> transactions[]` (upsert holdings, payout dividendi)
   - [ ] Timeline/visualizzazione flussi da transactions (future)

### **STEP 4: Database Migration (Priorità BASSA)**
**Obiettivo:** Scalabilità e performance per produzione

1. **Script SQL:**
   - [ ] Creare `db/init.sql` con schema completo
   - [ ] Setup MariaDB container in Docker Compose
   - [ ] Import dati da JSON a DB

2. **Refactor Backend:**
   - [ ] Sostituire `PortfolioManager->load()` con query DB
   - [ ] Sostituire `PortfolioManager->save()` con INSERT/UPDATE
   - [ ] Mantenere retrocompatibilità con JSON (fallback)

3. **Migration Tool:**
   - [ ] Script PHP `migrate-json-to-db.php`
   - [ ] Conversione holdings, transactions, dividends
   - [ ] Validazione integrità dati

---

## 🗂️ **STRUTTURA FILE PROGETTO**

```
trading-portfolio/
├── index.php                     # Controller principale (40 righe)
├── index.php.backup             # Backup originale (119KB)
├── README.md                    # Documentazione progetto
├── STYLE_GUIDE.md              # Linee guida UI/UX v1.2
├── PROJECT_STATUS.md           # Questo file (stato lavori)
│
├── data/
│   ├── portfolio.json          # ✅ Storage principale dati
│   ├── portfolio.json.backup   # Backup automatico
│   ├── portfolio_data.php      # ✅ Loader dati (retrocompatibilità)
│   └── portafoglio-export.csv  # Esempio CSV Fineco
│
├── lib/
│   └── PortfolioManager.php    # ✅ Classe gestione portfolio
│
├── api/
│   └── holdings.php            # ✅ REST API CRUD holdings
│
├── views/
│   ├── layouts/
│   │   ├── header.php          # ✅ HTML head + top header
│   │   ├── sidebar.php         # ✅ Navigation sidebar
│   │   └── footer.php          # ✅ Scripts + chiusura HTML
│   └── tabs/
│       ├── dashboard.php       # ✅ Vista Dashboard (32KB)
│       ├── holdings.php        # ✅ Vista Holdings con CRUD (12KB)
│       ├── performance.php     # ✅ Vista Performance (10KB)
│       ├── technical.php       # ✅ Vista Analisi Tecnica (8KB)
│       ├── dividends.php       # ✅ Vista Dividendi (14KB)
│       ├── recommendations.php # ✅ Vista Raccomandazioni (15KB)
│
├── assets/
│   ├── css/
│   │   └── styles.css          # ✅ CSS custom + tooltip system
│   └── js/
│       ├── app.js              # ✅ Chart configs + utilities
│       └── holdings.js         # ✅ CRUD holdings frontend
│
├── docs/
│   ├── 01-ARCHITETTURA.md      # ⚠️  Da aggiornare post-refactoring
│   ├── 02-GESTIONE-UTENTI.md
│   ├── 03-DATABASE.md
│   ├── 04-API-REST.md
│   ├── 05-FRONTEND.md
│   └── 06-N8N-WORKFLOWS.md
│
└── _old/
    └── perspect/                # Reference implementation UI
        └── assets/css/styles.css
```

---

## 🐛 **PROBLEMI NOTI**

1. **Permessi file server:**
   - I file creati localmente potrebbero avere permessi 600
   - Sul server Plesk, Apache non può leggerli
   - **Fix:** `chmod 644` sui file, `chmod 755` sulle cartelle prima del deploy

2. **Chart.js doppia inizializzazione:**
   - Grafici `performanceChart` e `allocationChart` hanno script inline con PHP
   - Esclusi da IntersectionObserver in `app.js` per evitare errore "canvas already in use"

3. **Import CSV sovrascrive tutto:**
   - Attualmente l'import cancella tutti gli holdings esistenti
   - Per import incrementale, modificare `PortfolioManager->importFromCsv()` (rimuovere `$this->data['holdings'] = [];`)

4. **Dati mockati in `portfolio_data.php`:**
   - `technical_analysis` e `opportunities` ancora hardcoded
   - Saranno sostituiti da risultati n8n workflows

---

## 📞 **NOTE TECNICHE IMPORTANTI**

### **Decisioni Architetturali:**
1. **Storage JSON per MVP** → Scelta deliberata per prototipazione rapida e semplicità
   - ✅ Vantaggi: Setup immediato, portabilità, debugging facilitato, backup semplice
   - 🔄 Migrazione DB pianificata in Fase 2 (roadmap)
2. **Import CSV sovrascrittivo** → Una tantum, non ripetibile (per design)
3. **Quotazioni da n8n** (Fase 3) → Non API diretta in PHP (delegato a workflow)
4. **Calcoli lato server** → Tutti i P&L, allocation, drift calcolati in PHP
5. **AI solo per analisi** → Non per dati finanziari (quotazioni, dividendi)
6. **Nessuna autenticazione in MVP** → Uso locale/personale, multi-utente in Fase 2

### **Convenzioni Codice:**
- **PHP:** PSR-12, classi in `lib/`, snake_case per variabili
- **JavaScript:** ES6+, camelCase, async/await per fetch
- **CSS:** Tailwind utility-first, custom classes in `styles.css`
- **Naming:** "Portafoglio" (IT), non "Portfolio" (EN)

### **Sicurezza:**
- Password hashing: Argon2id + pepper (da implementare)
- Session: HttpOnly, Secure, SameSite=Strict
- API: Validazione input + sanitizzazione
- n8n webhook: HMAC-SHA256 authentication

---

## 🎯 **OBIETTIVO FINALE**

**Sistema self-hosted completo per gestione portafoglio ETF su Fineco con:**

### **Fase 1: MVP - JSON Based** ✅ COMPLETATO
- ✅ Frontend responsive professionale con design system
- ✅ CRUD Holdings completo con API REST
- ✅ Import CSV da Fineco
- ✅ Dashboard interattiva con metriche
- ✅ Storage JSON per dati portfolio
- ✅ Sistema modulare e manutenibile

### **Fase 2: Database Migration** 🔄 PROSSIMA (Stima: ~30-40 ore)
- [ ] Backend robusto con MariaDB/PostgreSQL
- [ ] Autenticazione multi-utente
- [ ] Multi-portafoglio per utente
- [ ] Storico transazioni completo
- [ ] Snapshots temporali performance
- [ ] Containerizzazione Docker

### **Fase 3: Automation & Intelligence** 🚀 FUTURA (Stima: ~40-60 ore)
- [ ] Integrazione n8n per workflow automation
- [ ] Aggiornamento prezzi automatico
- [ ] Analisi tecnica giornaliera
- [ ] Scouting opportunità ETF
- [ ] Alert intelligenti

### **Fase 4: Advanced Features** 💎 LONG-TERM
- [ ] Multi-valuta con conversioni FX
- [ ] Analisi rischio avanzata
- [ ] Ottimizzazione fiscale italiana
- [ ] PWA e notifiche push
- [ ] API pubblica per integrazioni

**Priorità attuale:** Consolidamento MVP e preparazione Fase 2 (Database Migration)

---

**Prossima sessione:** Iniziare con **STEP 1: Completare Backend Dati** (vedi sezione "PROSSIMI STEP CONSIGLIATI" in questo documento)

---

## 📅 **CHANGELOG**

### [0.2.0-MVP] - 27 Novembre 2025
**Centralizzazione e Unificazione Sistema Grafici:**
- ✅ **ChartManager** (`assets/js/charts.js`) - Sistema centralizzato per tutti i grafici
  - Factory functions per 7 tipi di grafici (Performance, Dividendi, Dashboard)
  - Configurazioni globali unificate (colori, animazioni, stili punti)
  - Gestione pattern Patternomaly centralizzata
  - Utility functions per formattazione consistente (euro, percentuali)
  - Riduzione codice del 35% (~450 righe eliminate)
- ✅ **Design System Visivo Unificato:**
  - Punti quadrati (rect) 6px con bordo bianco 2px - uniforme su tutti i grafici
  - Dataset valore euro: linea viola + area con pattern a righe diagonali
  - Dataset percentuale: linea grigio scuro con z-index superiore
  - Doppio asse Y (euro sx, percentuale dx) con tooltip personalizzati
  - Legenda bottom ottimizzata (12px boxWidth, 10px font)
- ✅ **Refactoring File:**
  - `views/tabs/performance.php`: ridotto da 722 a 473 righe (-34%)
  - `views/tabs/dividends.php`: ridotto da 476 a 377 righe (-21%)
  - `views/layouts/footer.php`: ridotto da 95 a 36 righe (-62%)
  - `views/layouts/header.php`: aggiunto caricamento charts.js
- ✅ **Grafici Aggiornati:**
  - Performance: Andamento Annuale, Guadagno Cumulativo YTD, Ultimi 5 Giorni
  - Dividendi: Mensili (bar stacked), Cumulativi (line pattern)
  - Dashboard: Performance mensile, Allocation (donut)

### [0.1.0-MVP] - 26 Novembre 2025
**Documentazione Completa:**
- ✅ **README.md aggiornato completamente** - riflette stato MVP con storage JSON
  - Architettura attuale vs futura
  - API REST implementate documentate
  - Setup semplificato senza Docker
  - Roadmap a 4 fasi dettagliata
  - Utilizzo quotidiano e best practices
- ✅ **PROJECT_STATUS.md aggiornato** - allineato con README e roadmap
  - Versioning uniformato (0.1.0-MVP)
  - Obiettivi suddivisi per fase
  - Decisioni architetturali documentate
- ✅ Rinominati widget con indicazioni temporali (YTD, 2025, Ultimi 5 Giorni)
- ✅ Fix larghezze colonne tabella Holdings (whitespace-nowrap)
- ✅ Aggiunta colonne Target % e Note nella tabella Holdings
- ✅ Sistema di reload che mantiene vista attiva (localStorage)

**In Roadmap:**
- 🔄 Fase 2: Database Migration (prossima priorità)
- 🚀 Fase 3: Automation & Intelligence (n8n workflows)
- 💎 Fase 4: Advanced Features

### [0.5-alpha] - 25 Novembre 2025
**Completato:**
- ✅ Refactoring completo architettura (modularizzazione viste)
- ✅ CRUD Holdings completo (API + frontend)
- ✅ Import CSV Fineco
- ✅ Sistema permissions fix
- ✅ PortfolioManager class con calcolo metriche

---
