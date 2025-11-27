# 📊 ETF Portfolio Manager - Stato Avanzamento Lavori

**Ultimo aggiornamento:** 26 Novembre 2025
**Versione:** 0.2.0-MVP (JSON Based + n8n Integration)
**Stato:** MVP Avanzato ✅ - Fase 1 (JSON Storage) completa, n8n integration attiva, Fase 2 (Database Migration) in roadmap

> 📋 **Documentazione:**
> - [README.md](README.md) - Panoramica generale e setup (aggiornato 26 Nov 2025)
> - [STYLE_GUIDE.md](STYLE_GUIDE.md) - Linee guida UI/UX (REGOLE FERREE)
> - Questo documento - Stato tecnico dettagliato e prossimi step operativi

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
- [x] **Grafici Performance** (Chart.js):
  - Grafico "Andamento Annuale (2025)" con dati mensili
  - Grafico "Guadagno Cumulativo (YTD)" con storico snapshot
  - Grafico "Ultimi 5 Giorni" con valori portafoglio
  - Widget performance: 1M, 3M, YTD con percentuali e valori
  - Fix lazy-loading: inizializzazione chart solo quando tab visibile (MutationObserver)
  - Configurazione punti evidenziati (radius: 8) per singoli data point
  - Pie chart allocazioni con labels corrette

### **2. Refactoring Architettura (100%)**
- [x] **Struttura modulare MVC-like:**
  ```
  index.php              → Controller principale (40 righe)
  data/portfolio_data.php → Loader dati
  views/layouts/         → Header, sidebar, footer
  views/tabs/            → Viste separate per sezione
  ```
- [x] **Backup** creato: `index.php.backup` (119KB originale)

### **3. Sistema Dati Dinamici (95%)**
- [x] **Storage JSON** (`data/portfolio.json`) con struttura completa:
  - `metadata` - Metriche aggregate portfolio
  - `holdings` - Posizioni correnti
  - `transactions` - Storico operazioni
  - `dividends` - Dividendi ricevuti
  - `monthly_performance` - Performance mensile (auto-aggiornato da snapshots)
  - `allocation_by_asset_class` - Allocazioni per classe (auto-calcolato)
  - `n8n_config` - Config integrazione n8n
- [x] **PortfolioManager class** (`lib/PortfolioManager.php`):
  - CRUD holdings completo
  - Import CSV Fineco (parser con separatore `;`)
  - Ricalcolo metriche automatico con `allocation_by_asset_class`
  - Metodo `setData()` per inizializzazione snapshot
  - Preparazione payload per n8n
  - Backup automatico prima del save
- [x] **API REST Endpoints** (`api/holdings.php`):
  - `GET /api/holdings.php` → Lista holdings
  - `POST /api/holdings.php` → Create/Update holding
  - `DELETE /api/holdings.php?isin=X` → Delete holding
  - `POST /api/holdings.php?action=import` → Import CSV
- [x] **Frontend CRUD Holdings:**
  - Form Add/Edit posizione (modale responsive)
  - Import CSV con validazione
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
   - [ ] Verificare ricalcolo metriche automatico

2. **Integrare API Quotazioni:**
   - [ ] Scegliere provider (Alpha Vantage gratuito 25 req/giorno, Yahoo Finance illimitato)
   - [ ] Creare endpoint `POST /api/update-prices.php`
   - [ ] Implementare fetch quotazioni per ISIN list
   - [ ] Aggiornare `current_price` in portfolio.json
   - [ ] Trigger ricalcolo metriche
   - [ ] Pulsante "Aggiorna Prezzi" nel frontend (Holdings header)

3. **Storico Performance:**
   - [ ] Creare `data/snapshots.json` per storico giornaliero
   - [ ] Cron job o pulsante manuale per creare snapshot
   - [ ] Usare snapshots per grafico "Andamento Portafoglio" dinamico

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
   - [ ] Testare chiamata da PHP `PortfolioManager->prepareN8nPayload()`
   - [ ] Ricevere risultati in `PortfolioManager->receiveN8nResults()`

3. **Workflow Analisi Tecnica:**
   - [ ] Implementare calcolo indicatori (EMA, MACD, RSI, Bollinger)
   - [ ] Logica generazione segnali BUY/SELL/HOLD/WATCH
   - [ ] Storage risultati in `data/technical_analysis.json`
   - [ ] Mostrare in vista "Analisi Tecnica"

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
│       └── flows.php           # ✅ Vista Flussi (12KB)
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
