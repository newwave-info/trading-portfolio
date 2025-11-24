# 01 – Architettura del sistema

Questo documento descrive l’architettura logica e fisica di **ETF Portfolio Manager – Fineco + n8n**, con focus su componenti, flussi principali, separazione dati utente/dati di mercato e integrazione con n8n.

---

## 1. Obiettivi architetturali

- Web‑app self‑hosted, **multi‑utente e multi‑portafoglio**, pensata inizialmente per uso personale ma estendibile a familiari/amici.
- Gestione **manuale** di tutte le operazioni su Fineco, con la web‑app usata come “centro di controllo” (tracking, analisi, suggerimenti).
- Separazione chiara tra:
  - dati specifici dell’utente/portafoglio (holdings, transazioni, snapshots)
  - dati di mercato condivisi (prezzi, anagrafiche ETF, commissioni Fineco)
- Integrazione con **n8n CE self‑hosted** per:
  - analisi tecnica
  - sentiment macro/news
  - scouting opportunità
  - suggerimenti di ribilanciamento
- Stack semplice, robusto e portabile:
  - PHP 8.2 + Apache
  - MariaDB 10.5.x
  - Docker Compose production‑ready

---

## 2. Vista high‑level

### 2.1 Diagramma logico dei componenti

┌─────────────────┐
│ Web Browser │
│ (utente finale) │
└────────┬────────┘
│ HTTP/HTTPS
▼
┌──────────────────────────────────────┐
│ PHP 8.2 + Apache (portfolio-app) │
│ - Dashboard │
│ - Gestione utenti/portafogli │
│ - CRUD Holdings/Transactions │
│ - API REST (web-app + n8n) │
│ - Session Management │
└─────────┬──────────────────┬─────────┘
│ │
│ Query SQL │ HTTP (REST, HMAC)
▼ ▼
┌─────────────────────┐ ┌───────────────────────┐
│ MariaDB 10.5 │ │ n8n Community Ed. │
│ (DB unico) │ │ (workflow engine) │
│ - Schema utente │ │ - Analisi tecnica │
│ - Schema market │ │ - News/Sentiment │
│ data condivisi │ │ - Opportunità ETF │
└─────────────────────┘ │ - Ribilanciamento │
└───────────────────────┘


---

## 3. Componenti principali

### 3.1 Web‑app PHP (portfolio‑app)

- **Linguaggio**: PHP 8.2
- **Web server**: Apache 2.4
- **Ruoli principali**:
  - Rendering delle viste (template PHP/CSS/JS) per:
    - dashboard
    - holdings
    - transazioni
    - dividendi
    - analisi
    - opportunità
  - Esposizione di REST API “interne” (consumate dal frontend e da n8n).
  - Gestione autenticazione utenti (registrazione, login, logout).
  - Gestione sessioni sicure (cookie HttpOnly, Secure, SameSite).
  - Business logic:
    - calcolo P&L realizzato/non realizzato
    - allocazioni e drift rispetto al target
    - aggregazioni per dashboard (totali, top holdings, ecc.).

La web‑app è deployata come **un singolo container** Docker (`app`) che include Apache + PHP + codice applicativo.

### 3.2 Database MariaDB 10.5

Un unico database logico (es. `etf_portfolio`) organizzato in due aree logiche:

#### 3.2.1 Area `market_data` (dati condivisi)

Dati uguali per tutti gli utenti/portafogli, aggiornati principalmente da workflow n8n:

- `market_data.quotes`
  - Ultime quotazioni per ISIN (prezzo, previous close, volume, timestamp, provider).
- `market_data.etf_info`
  - Anagrafiche ETF:
    - ISIN, ticker, nome
    - asset class, settore
    - valuta, exchange
    - expense ratio, dividend frequency.
- `market_data.fineco_commission_cache`
  - Profilo commissionale Fineco per ISIN:
    - zero commissioni / standard / USA / ecc.
    - eventuale `commission_rate`
    - `last_checked`.

Questa area permette di:
- evitare duplicazioni di dati tra utenti
- ridurre le chiamate ai provider esterni (rate limit)
- avere un’unica fonte di verità per prezzi, anagrafiche e commissioni.

#### 3.2.2 Area `utente` (dati specifici)

Dati soggettivi per singolo utente e portafoglio:

- `utente.users`
  - Utenti con:
    - email, password_hash, full_name
    - flag `is_active`
    - `created_at`, `last_login`.
- `utente.portfolios`
  - Portafogli logici per utente:
    - `user_id`, nome, descrizione
    - `base_currency`
    - flag `is_active`.
- `utente.holdings`
  - Posizioni correnti per portafoglio:
    - `portfolio_id`
    - `isin` (FK logica verso `market_data.etf_info.isin`)
    - `quantity`, `avg_price`
    - `target_allocation`, `notes`
    - `is_active`.
- `utente.transactions`
  - Operazioni storiche per portafoglio:
    - `portfolio_id`, `isin`
    - `type` (BUY/SELL/DIVIDEND)
    - `transaction_date`
    - `quantity`, `price`, `total_amount`
    - `commission`, `fx_rate`
    - `source`, `notes`.
- `utente.portfolio_snapshots`
  - Snapshot giornalieri per portafoglio:
    - `snapshot_date`
    - `total_value`, `total_invested`
    - `unrealized_pnl`, `realized_pnl`
    - `total_dividends`, `holdings_count`.

**Viste principali**:

- `utente.v_holdings_full`
  - Join tra holdings, portfolios, quotes, etf_info, commission_cache.
- `utente.v_portfolio_summary`
  - Aggregati per portafoglio (valore, investito, P&L, numero posizioni).

### 3.3 Motore di workflow n8n

- Eseguito come container Docker separato (`n8n`).
- Connesso alla stessa rete Docker della web‑app e del database.
- Responsabile di:
  - leggere dati dal backend (API o query su viste)
  - chiamare API esterne (prezzi, news, sentiment)
  - calcolare indicatori e segnali
  - scrivere output nel backend tramite endpoint autenticati HMAC.

Workflow previsti (alto livello):

- **Workflow A**: analisi tecnica giornaliera per tutti gli ISIN in portafoglio.
- **Workflow B**: scouting ETF opportunità (es. Fineco zero commissioni).
- **Workflow C**: macro/news sentiment per settori e titoli.
- **Workflow D**: advisor di ribilanciamento mensile.

---

## 4. Modello di deployment (Docker)

### 4.1 Servizi principali

- `db` (MariaDB 10.5)
  - Volume persistente `db_data`.
  - Inizializzazione schema con `db/init.sql`.
- `app` (PHP 8.2 + Apache)
  - Build da `php-app/` (Dockerfile dedicato).
  - Espone la porta 80 mappata su `8080` host (es. `http://localhost:8080`).
- `n8n`
  - Immagine ufficiale `n8nio/n8n:latest`.
  - Volume `n8n_data` per configurazione workflow.
  - Espone la porta 5678 per interfaccia web.

Tutti i servizi condividono una **network Docker interna**, isolata dall’esterno, con esposizione solo delle porte necessarie.

### 4.2 Variabili di ambiente e segreti

- Credenziali DB (host, nome, utente, password).
- Segreto HMAC condiviso tra web‑app e n8n (`N8N_WEBHOOK_SECRET`).
- API key per provider dati mercato e servizi di news/sentiment.

Le variabili sono gestite via file `.env` e non sono committate in repository.

---

## 5. Flussi principali

### 5.1 Login e selezione portafoglio

1. Utente accede a `app` (es. `http://localhost:8080`).
2. Se non autenticato:
   - redirect alla pagina di login/registrazione.
3. Dopo login:
   - caricamento elenco portafogli associati all’utente.
   - selezione portafoglio corrente (persistita in sessione).
4. Dashboard mostra:
   - snapshot del portafoglio selezionato
   - feed alert e segnali tecnici
   - top holdings.

### 5.2 Import iniziale da CSV Fineco

1. Utente seleziona “Import CSV” dalla sezione portafoglio.
2. Upload file CSV Fineco (movimenti o situazione titoli).
3. Step di mappatura colonne:
   - ISIN, tipo operazione, quantità, prezzo, commissione, data, ecc.
4. Validazione e anteprima righe:
   - segnalazione righe non valide/scartate.
5. Alla conferma:
   - creazione holdings iniziali
   - scrittura transactions storiche.
6. Al termine:
   - la dashboard riflette lo stato iniziale del portafoglio.

### 5.3 Aggiornamento quotidiano prezzi e analisi tecnica (Workflow A)

1. Trigger schedulato su n8n (es. ogni giorno sera).
2. n8n:
   - legge lista ISIN attivi dai portafogli (via API o query su viste).
   - chiama provider dati (es. API prezzi) rispettando rate limit.
   - aggiorna `market_data.quotes`.
   - calcola indicatori tecnici (EMA, MACD, RSI, BB).
   - sintetizza segnali (BUY/SELL/HOLD/WATCH + confidence).
3. n8n invia risultati al backend:
   - chiamata HTTP autenticata HMAC su endpoint `/api/analysis/results`.
4. La web‑app:
   - salva analisi in `analysis_results` (o tabella equivalente).
   - espone sintesi segnali in dashboard e pagina titolo.

### 5.4 Scouting opportunità ETF (Workflow B)

1. Trigger settimanale su n8n.
2. n8n:
   - costruisce/aggiorna universo ETF candidati (es. da lista Fineco zero commissioni).
   - esclude ISIN già presenti nei portafogli.
   - per i rimanenti:
     - recupera dati tecnici minimi
     - applica regole di filtro (trend, RSI, posizione vs EMA).
3. n8n:
   - invia lista di opportunità al backend (endpoint analisi).
4. La web‑app:
   - mostra sezione “Opportunità” filtrabile/ordinabile per segnale/confidence.

### 5.5 Macro/News sentiment (Workflow C)

1. Trigger (es. giornaliero leggero o bisettimanale).
2. n8n:
   - legge feed RSS/API di news finanziarie.
   - applica modello di sentiment (positivo/negativo/neutro).
   - mappa notizie a settori/asset class.
3. n8n:
   - correla settori alle holdings dei vari portafogli.
   - scrive aggregati di sentiment nel backend.
4. La web‑app:
   - visualizza sentiment recente per settore/titolo nella sezione analisi.

### 5.6 Advisor di ribilanciamento (Workflow D)

1. Trigger mensile (o on‑demand).
2. n8n:
   - legge snapshot e target allocation per ciascun portafoglio.
   - calcola drift (% scostamento).
   - quando drift > soglia:
     - calcola quantità teoriche di BUY/SELL per tornare verso il target.
     - stima costi di transazione usando `fineco_commission_cache`.
3. n8n:
   - registra suggerimenti di ribilanciamento nel backend.
4. La web‑app:
   - espone “piano di ribilanciamento” come lista consultiva.
   - l’utente decide se e come eseguire manualmente gli ordini su Fineco.

---

## 6. Sicurezza e confini

- **Operatività di trading**:
  - Nessun ordine automatico viene inviato a Fineco.
  - Tutte le operazioni di acquisto/vendita sono inserite manualmente dopo l’esecuzione sul broker.
- **Autenticazione utenti**:
  - Email + password (hash Argon2id + pepper).
  - Sessioni PHP con cookie sicuri (Secure, HttpOnly, SameSite).
- **Integrazione n8n**:
  - Endpoint dedicati, non pubblici, protetti da:
    - firma HMAC-SHA256 su body JSON
    - segreto condiviso in `.env`.
- **Database**:
  - Accesso limitato al container `app`.
  - Query sempre filtrate per `user_id` e `portfolio_id` per garantire isolamento dati tra utenti.

---

## 7. Performance e scalabilità

- Carico previsto:
  - numero limitato di utenti (personale/familiare)
  - numero di holdings/portafogli relativamente contenuto.
- Ottimizzazioni:
  - Uso di viste aggregate (`v_holdings_full`, `v_portfolio_summary`).
  - Indici su colonne di join frequenti (ISIN, portfolio_id, user_id, date).
  - Aggiornamenti di mercato batch via n8n (no polling continuo).
- Scalabilità orizzontale:
  - futura possibilità di separare:
    - DB su host dedicato
    - più istanze `app` dietro reverse proxy
    - n8n scalato separatamente.
  - Non necessario per lo scenario iniziale, ma l’architettura non lo impedisce.

---

## 8. Riferimenti ad altri documenti

Per maggiori dettagli:

- `docs/02-GESTIONE-UTENTI.md` – autenticazione, sessioni, sicurezza.
- `docs/03-DATABASE.md` – schema fisico completo, DDL, indici.
- `docs/04-API-REST.md` – definizione endpoint, payload ed errori.
- `docs/06-N8N-WORKFLOWS.md` – definizione dettagliata dei workflow A/B/C/D.
- `docs/07-DOCKER-SETUP.md` – configurazione Docker Compose, .env, volumi.
