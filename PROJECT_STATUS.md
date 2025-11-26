# 📊 ETF Portfolio Manager - Stato Avanzamento Lavori

**Ultimo aggiornamento:** 26 Novembre 2025
**Versione:** 0.6-alpha
**Stato:** In sviluppo attivo - Frontend completato ✅, Backend parziale ⚠️, Gestione dati in corso 🚧

> 📋 **Per dettagli sviluppo futuro:** Vedi [DEVELOPMENT_ROADMAP.md](DEVELOPMENT_ROADMAP.md)

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

### **2. Refactoring Architettura (100%)**
- [x] **Struttura modulare MVC-like:**
  ```
  index.php              → Controller principale (40 righe)
  data/portfolio_data.php → Loader dati
  views/layouts/         → Header, sidebar, footer
  views/tabs/            → Viste separate per sezione
  ```
- [x] **Backup** creato: `index.php.backup` (119KB originale)

### **3. Sistema Dati Dinamici (80%)**
- [x] **Storage JSON** (`data/portfolio.json`) con struttura completa:
  - `metadata` - Metriche aggregate portfolio
  - `holdings` - Posizioni correnti (5 ETF mockati)
  - `transactions` - Storico operazioni
  - `dividends` - Dividendi ricevuti
  - `monthly_performance` - Performance mensile
  - `allocation_by_asset_class` - Allocazioni per classe
  - `n8n_config` - Config integrazione n8n
- [x] **PortfolioManager class** (`lib/PortfolioManager.php`):
  - CRUD holdings completo
  - Import CSV Fineco (parser con separatore `;`)
  - Ricalcolo metriche automatico
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

### **1. Gestione Dati (40%)**
- [x] Storage JSON funzionante
- [ ] **Migrazione a Database MariaDB** (schema definito, non implementato)
  - Schema `market_data` (etf_info, prices, fineco_commissions)
  - Schema `utente` (users, portfolios, holdings, transactions, dividends, snapshots)
  - Script SQL da creare in `db/init.sql`
- [ ] **Storico transazioni** (struttura presente, non usata nel frontend)
- [ ] **Snapshot giornalieri** per grafici storici performance

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

## ❌ **NON INIZIATO**

### **1. Integrazione n8n (Priorità ALTA)**
- [ ] **Setup n8n container** (Docker Compose)
- [ ] **Webhook bidirezionale:**
  - `POST /api/n8n/send-portfolio` → Invia portfolio.json a n8n
  - `POST /api/n8n/receive-results` → Riceve analisi/quotazioni da n8n
  - HMAC-SHA256 authentication
- [ ] **Workflow A - Analisi Tecnica** (giornaliero):
  - Input: ISIN list da holdings
  - Output: Segnali BUY/SELL/HOLD/WATCH con indicatori (EMA, MACD, RSI, Bollinger)
  - Storage: `data/technical_analysis.json` o tabella DB
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

3. **Dividendi Form:**
   - [ ] Modale "Registra Dividendo" in vista Dividendi
   - [ ] Campi: ISIN, data stacco, data pagamento, importo, ritenute
   - [ ] Salvataggio in `portfolio.json → dividends[]`
   - [ ] Aggiornamento tabella dividendi

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
1. **Storage JSON temporaneo** → Migrazione DB pianificata ma non prioritaria
2. **Import CSV sovrascrittivo** → Una tantum, non ripetibile (per design)
3. **Quotazioni da n8n** → Non API diretta in PHP (delegato a workflow)
4. **Calcoli lato server** → Tutti i P&L, allocation, drift calcolati in PHP
5. **AI solo per analisi** → Non per dati finanziari (quotazioni, dividendi)

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
- ✅ Frontend responsive professionale
- ⏳ Backend robusto con DB/JSON
- ⏳ Automazione n8n per analisi e quotazioni
- ⏳ Dashboard real-time con metriche accurate
- ⏳ Storico operazioni e performance
- ⏳ Consigli operativi AI-driven
- ⏳ Multi-utente e multi-portafoglio

**Stima completamento MVP:** ~40 ore sviluppo rimanenti
**Priorità attuale:** Integrazione n8n + Quotazioni API

---

**Prossima sessione:** Iniziare con **STEP 1: Sistema Quotazioni** (vedi [DEVELOPMENT_ROADMAP.md](DEVELOPMENT_ROADMAP.md))

---

## 📅 **CHANGELOG**

### [0.6-alpha] - 26 Novembre 2025
**Completato:**
- ✅ Rinominati widget con indicazioni temporali (YTD, 2025, Ultimi 5 Giorni)
- ✅ Fix larghezze colonne tabella Holdings (whitespace-nowrap)
- ✅ Aggiunta colonne Target % e Note nella tabella Holdings
- ✅ Sistema di reload che mantiene vista attiva (localStorage)
- ✅ Creato documento [DEVELOPMENT_ROADMAP.md](DEVELOPMENT_ROADMAP.md) con 6 STEP dettagliati

**In Corso:**
- 🚧 Preparazione STEP 1: Sistema Quotazioni Real-time

### [0.5-alpha] - 25 Novembre 2025
**Completato:**
- ✅ Refactoring completo architettura (modularizzazione viste)
- ✅ CRUD Holdings completo (API + frontend)
- ✅ Import CSV Fineco
- ✅ Sistema permissions fix
- ✅ PortfolioManager class con calcolo metriche

---
