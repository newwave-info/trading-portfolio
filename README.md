# ETF Portfolio Manager – Fineco + n8n

Strumento self‑hosted per la gestione strutturata di un portafoglio ETF detenuto su Fineco Bank, con analisi automatizzate, tracciamento dividendi/commissioni e suggerimenti operativi via n8n.

---

## Stack tecnico

| Componente | Versione | Ruolo |
|-----------|----------|-------|
| PHP | 8.2 | Backend REST API + Frontend (OOP semplice, niente framework pesanti) |
| MariaDB | 10.5.29 | Database relazionale (schema separato: market_data + utente) |
| n8n | Latest CE | Workflow automation (analisi, news, suggerimenti) |
| Docker | Latest | Containerizzazione e orchestrazione (production-ready) |
| Apache | 2.4 | Web server |

---

## Architettura

```
┌─────────────────┐
│  Web Browser    │
└────────┬────────┘
         │ HTTP/HTTPS
         ▼
┌──────────────────────────────────────┐
│   PHP 8.2 + Apache (portfolio-app)   │
│   - Dashboard                         │
│   - CRUD Holdings/Transactions        │
│   - API REST (HMAC per n8n)           │
│   - Session Management                │
└─────────┬──────────────────┬─────────┘
          │                  │
          │ Query            │ HTTP
          ▼                  ▼
┌──────────────┐    ┌──────────────────┐
│ MariaDB 10.5 │    │ n8n Community Ed.│
│     (DB)     │    │   (Workflows)    │
│ - market_data│    │     A/B/C/D      │
│ - utente     │    └──────────────────┘
└──────────────┘
```

**Due schemi database separati:**

- **market_data**: quotazioni, ETF info, commissioni Fineco (condivisi tra tutti gli utenti)
- **utente**: users, portfolios, holdings, transactions, snapshots (specifici per utente/portafoglio)

---

## Che cosa è

ETF Portfolio Manager è una **web‑app PHP 8.2 + MariaDB 10.5** (eseguita in Docker) che funziona come centro di controllo per investitori retail italiani che gestiscono portafogli ETF su Fineco.

**Caratteristiche principali:**

- ✅ Gestione posizioni, transazioni e dividendi (operatività manuale su Fineco, registrazione in app)
- ✅ Dashboard unificata con P&L, allocazioni, drift da target
- ✅ Autenticazione multi‑utente (registrazione, login/logout, sessioni sicure)
- ✅ Multi‑portafoglio per utente (es. "Pensione", "Trading")
- ✅ Integrazione n8n per analisi tecnica giornaliera, macro/news sentiment, opportunità ETF
- ✅ Import CSV iniziale da Fineco, edit/add/delete manuale successivo
- ✅ Storico operazioni e snapshots portafoglio nel tempo
- ✅ Tracciamento costi, commissioni Fineco, dividendi percepiti

**Cosa NON è:**

- ❌ Non esegue ordini automatici (tutti manualmente su Fineco)
- ❌ Non è una copia di eToro, Moneyfarm o altri robo-advisor
- ❌ Non fornisce consulenza finanziaria (è strumento di controllo e analisi)

---

## Funzionalità principali

### 👤 Gestione Utenti

- Registrazione con validazione password robusta
- Login con hashing Argon2id + pepper
- Sessioni sicure (HttpOnly, Secure, SameSite)
- Logout con invalidazione server-side
- Supporto multi-utente con isolamento dati per `user_id`

### 💼 Gestione Portafogli

- Multipli portafogli per utente (es. "Lungo Termine", "Trading Attivo")
- Base currency per portafoglio
- Soft delete portafogli

### 📊 Dashboard Portafoglio

- Valore totale, P&L realizzato/non realizzato
- Allocazione target vs attuale con drift indicator
- Top holdings, segnali tecnici sintetici
- Feed alert da workflow n8n

### 💼 Gestione Posizioni

- CRUD holdings (add, edit, delete soft/hard)
- Registrazione transazioni (BUY/SELL/DIVIDEND)
- Import CSV da Fineco con mappatura colonne
- Timeline storico operazioni filtrabile ed esportabile

### 💰 Tracciamento Dividendi

- Registrazione ex-date, data pagamento, importi
- Calcolo dividend yield per titolo e portafoglio
- Storico distribuzioni
- Ritenute fiscali

### 🤖 Analisi Automatizzate (n8n)

- **Workflow A - Tecnica**: EMA, MACD, RSI, Bollinger Bands giornalieri → segnali BUY/SELL/HOLD/WATCH
- **Workflow B - Opportunità**: ETF Fineco a zero commissioni non in portafoglio → segnali + confidence
- **Workflow C - Macro/News**: Sentiment analysis su feed finanziari → aggregato per settore/titolo
- **Workflow D - Ribilanciamento**: Calcolo drift mensile vs target → piano consultivo con stima costi

---

## API logiche

### Area holdings

- `GET /api/holdings` - lista posizioni per utente/portafoglio
- `POST /api/holdings` - crea nuova posizione
- `PUT /api/holdings/{id}` - aggiorna metadati (asset class, target allocation)
- `DELETE /api/holdings/{id}` - soft delete (default) o hard delete con conferma

### Area transactions

- `GET /api/transactions` - storico con filtri (portafoglio, titolo, date, tipo)
- `POST /api/transactions` - inserisci operazione singola (aggiorna holdings)
- `POST /api/transactions/import-csv` - import CSV Fineco con report errori

### Area portfolio

- `GET /api/portfolio/snapshot` - metriche aggregate attuali
- `GET /api/portfolio/history` - storico giornaliero per grafici performance

### Area analysis

- `POST /api/analysis/results` - riceve risultati workflow n8n (HMAC autenticato)
- `GET /api/analysis/latest` - ultimi risultati per ISIN o portafoglio completo

### Area commissioni Fineco

- `GET /api/commissions/{isin}` - legge profilo commissionale cache
- `PUT /api/commissions/{isin}` - aggiorna cache (usa n8n per scraping)

---

## Configurazione e setup

### Prerequisiti

- Docker + Docker Compose
- Account Fineco (per operatività manuale)
- API keys: Alpha Vantage (o equivalente) per dati tecnici

### Environment variables (`.env`)

```bash
# Database
DB_HOST=db
DB_PORT=3306
DB_NAME=etf_portfolio
DB_USER=portfolio_user
DB_PASS=secure_password

# n8n HMAC secret (condiviso con n8n workflows)
N8N_WEBHOOK_SECRET=your_32_char_random_secret

# API Keys
ALPHA_VANTAGE_API_KEY=your_key
```

### Docker Compose (base)

```yaml
services:
  db:
    image: mariadb:10.5
    env_file: .env
    volumes:
      - db_data:/var/lib/mysql
    ports:
      - "3306:3306"

  app:
    build: ./php-app
    depends_on:
      - db
    env_file: .env
    ports:
      - "8080:80"

  n8n:
    image: n8nio/n8n:latest
    env_file: .env
    volumes:
      - n8n_data:/home/node/.n8n
    ports:
      - "5678:5678"

volumes:
  db_data:
  n8n_data:
```

### Primo avvio

1. `docker compose up -d`
2. Naviga su `http://localhost:8080`
3. Registra primo utente
4. Crea primo portafoglio
5. Import CSV Fineco iniziale
6. Configura workflow n8n (`http://localhost:5678`)
7. Attiva scheduler workflow

---

## Struttura progetto

```
etf-portfolio-manager/
├── README.md
├── docker-compose.yml
├── .env.example
├── php-app/
│   ├── public/
│   │   ├── index.php
│   │   ├── css/           # Template CSS responsive
│   │   └── js/            # JS per interazioni
│   ├── src/
│   │   ├── api/           # Endpoint REST
│   │   ├── lib/           # Classi utility (DB, Auth, HMAC)
│   │   ├── services/      # Business logic
│   │   └── config.php     # Config loader
│   ├── composer.json
│   └── .htaccess
├── db/
│   └── init.sql           # Schema MariaDB
├── docs/
│   ├── 01-ARCHITETTURA.md
│   ├── 02-GESTIONE-UTENTI.md
│   ├── 03-DATABASE.md
│   ├── 04-API-REST.md
│   ├── 05-FRONTEND.md
│   └── 06-N8N-WORKFLOWS.md
└── n8n-workflows/
    ├── workflow-technical-analysis.json
    ├── workflow-opportunities-scouting.json
    ├── workflow-macro-news.json
    └── workflow-rebalancing-advisor.json
```

---

## Documentazione

La documentazione tecnica è organizzata in `docs/`. **IMPORTANTE**: Prima di procedere con qualsiasi integrazione, modifica o operazione sul progetto, consulta sempre la documentazione relativa.

| File | Contenuto |
|------|-----------|
| [`docs/01-ARCHITETTURA.md`](docs/01-ARCHITETTURA.md) | Panoramica componenti, flussi, integrazioni, schemi DB |
| [`docs/02-GESTIONE-UTENTI.md`](docs/02-GESTIONE-UTENTI.md) | Registrazione, login, sessioni, sicurezza password, HMAC |
| [`docs/03-DATABASE.md`](docs/03-DATABASE.md) | Schema fisico MariaDB, indici, viste, prepared statements |
| [`docs/04-API-REST.md`](docs/04-API-REST.md) | Endpoint, autenticazione, payload, errori, rate limiting |
| [`docs/05-FRONTEND.md`](docs/05-FRONTEND.md) | Layout dashboard, componenti, template PHP, responsive |
| [`docs/06-N8N-WORKFLOWS.md`](docs/06-N8N-WORKFLOWS.md) | Workflow A/B/C/D, trigger, logica, nodi, error handling |

### ⚠️ Linee guida per sviluppatori e manutentori

**OBBLIGATORIO - Prima di qualsiasi operazione:**

1. **Consulta sempre la documentazione** prima di:
   - Modificare codice, database schema, o workflow
   - Integrare nuove funzionalità o servizi esterni
   - Effettuare operazioni di deployment o configurazione
   - Apportare modifiche all'architettura del sistema

2. **Mantenere la documentazione AGGIORNATA**:
   - Ogni modifica al codice deve riflettersi nella documentazione
   - I nuovi endpoint API devono essere documentati in `docs/04-API-REST.md`
   - Le modifiche al database in `docs/03-DATABASE.md`
   - I cambiamenti architetturali in `docs/01-ARCHITETTURA.md`
   - Workflow n8n aggiornati in `docs/06-N8N-WORKFLOWS.md`

3. **Procedure di aggiornamento documentazione**:
   ```bash
   # Dopo ogni modifica significativa:
   1. Identifica quali documenti necessitano aggiornamento
   2. Modifica i file .md in docs/ mantenendo sintassi markdown corretta
   3. Verifica che non ci siano line endings CR (usare LF)
   4. Rimuovi eventuali istanze di "text" isolate
   5. Testa la visualizzazione su GitHub/GitLab
   6. Commit con messaggio chiaro che includa la lista dei file aggiornati
   ```

4. **Quality check documentazione**:
   - Controlla sintassi markdown (linting automatico se disponibile)
   - Verifica che i link interni siano corretti
   - Mantieni coerenza terminologica tra documenti
   - Assicurati che esempi di codice siano testati e funzionanti

5. **Priorità di lettura per nuovi contributori**:
   1. `README.md` (questo file) - panoramica generale
   2. `docs/01-ARCHITETTURA.md` - comprensione sistema
   3. `docs/03-DATABASE.md` - schema dati
   4. `docs/04-API-REST.md` - integrazioni
   5. `docs/02-GESTIONE-UTENTI.md` - sicurezza
   6. `docs/05-FRONTEND.md` + `docs/06-N8N-WORKFLOWS.md` - specifiche

---

## Utilizzo quotidiano

1. **Login** con email/password (sessione 24h)
2. **Dashboard**: Visualizza portfolio, P&L, drift, alert
3. **Registra operazione**: Dopo ogni trade su Fineco, inserisci transazione (BUY/SELL/DIVIDEND)
4. **Monitora analisi**: Leggi segnali tecnici, opportunità, macro sentiment
5. **Mensile**: Verifica piano di ribilanciamento (se drift >5%)
6. **Logout**: Termina sessione quando non in uso

---

## Roadmap

- [ ] Supporto multi-valuta (USD, GBP, CHF) con FX automatico
- [ ] Analisi di rischio (volatilità, Sharpe ratio, drawdown, correlazioni)
- [ ] Ottimizzazione fiscale italiana (mod. 730, PFU)
- [ ] PWA e notifiche push per alert critici
- [ ] RBAC (ruoli: viewer, editor, admin per portafoglio)
- [ ] OAuth Fineco (se API diventa pubblica)

---

## Sicurezza

- ✅ Password hashing Argon2id + pepper
- ✅ Sessioni regenerate dopo login
- ✅ Cookie Secure, HttpOnly, SameSite=Strict
- ✅ Rate limiting login (5 tentativi/15 min per IP)
- ✅ Webhook HMAC-SHA256 per n8n → backend
- ✅ Prepared statements su tutte le query
- ✅ Validazione input lato server
- ⚠️ Per production: aggiungi SSL/TLS, WAF, fail2ban

---

## Disclaimer

**Questo progetto è per uso personale e didattico. Non costituisce consulenza finanziaria.**
L'utente rimane pienamente responsabile delle decisioni di investimento.
Fineco Bank è marchio registrato. Nessuna affiliazione né sponsorizzazione.

---

**Ultimo aggiornamento**: 24 Nov 2025
