# ETF Portfolio Manager

**Versione:** 0.5.3 (frontend flows + bugfix animazioni)
**Stato:** Produzione attiva con Automazione n8n Completa
**Data ultimo aggiornamento:** 02 Dicembre 2025

Strumento web completo per la gestione e analisi di portafogli ETF con **generazione automatica di segnali di trading** via n8n workflows. Implementa strategia Core-Satellite Risk Parity con sistema di notifiche email/Telegram per opportunità ad alta priorità.

---

## 📊 Stato del Progetto

### ✅ **Produzione Attiva - Automazione n8n Completa**

**Architettura Tecnica:**
- **Backend:** PHP 8.2+ con Repository Pattern e API REST complete
- **Database:** MySQL 8.2.29 (16 tabelle + 2 VIEWs)
- **Frontend:** Vanilla JavaScript + Chart.js
- **Automazione:** n8n Workflows per generazione automatica segnali
- **Signal Engine:** SignalGeneratorService con strategia Core-Satellite Risk Parity
- **Notifiche:** Email + Telegram per alert ad alta priorità

### 🤖 **Sistema di Automazione Completo (n8n + API)**
- **6 Workflow n8n totali**:
  - ✅ 2 Prerequisiti attivi: Portfolio Enrichment + AI Technical Insights
  - ✅ 2 Implementati (Fase 5): Generazione giornaliera + Schedulazione intraday
  - 🔄 2 Da completare: Alert notifiche + Monitoring
- **API REST**: `/api/signals.php` + `/api/alerts.php` per automazione completa
- **Notifiche Real-time**: Email per segnali urgenti (confidence > 80%), Telegram per update rapidi
- **Sicurezza**: Rate limiting (10 req/ora), CORS configurabile, HMAC disabilitato
- **Monitoring**: Health check ogni 4 ore, logging completo, statistiche performance

### 🎯 **Sistema Raccomandazioni Operative (Database + API)**
- **SignalGeneratorService**: Engine completo per generazione segnali di trading
- **Tabelle**: `recommendations`, `recommendation_actions`, `macro_indicators`
- **Strategia**: Core-Satellite Risk Parity con allocazione target dinamica
- **Segnali**: BUY_LIMIT, SELL_PARTIAL, SET_STOP_LOSS, SET_TAKE_PROFIT, REBALANCE
- **Confidence Scoring**: 0-100 con graceful degradation per dati mancanti
- **Test Produzione**: ✅ Superati test completi in ambiente reale

---

## 🚀 Funzionalità Principali

### 📈 **Performance & Allocazioni**
- KPI 7/30/90 giorni basati su snapshot DB (fallback mensile per 30/90)
- Grafici performance ChartManager (7/30/90 giorni, valore + %) con animazioni di crescita dai valori base (niente fade/slide)
- Storico snapshots temporali per tracking performance
- Vista dedicata “Flussi & Allocazioni” con grafico allocazione per ticker (API live, niente JSON)

### 💼 **Gestione Holdings**
- CRUD completo holdings con API REST
- Importazione CSV da Fineco Bank
- Prezzi aggiornati automaticamente via n8n (Yahoo Finance v8)
- Campi tecnici: SMA, EMA, RSI, MACD, Bollinger Bands

### 🤖 **Analisi Tecnica Avanzata**
- Indicatori tecnici per ogni holding (RSI, MACD, EMA, Bollinger)
- AI Technical Insights con punteggi e raccomandazioni
- Vista analisi tecnica con KPI e trend analysis
- Grafici storici tecnici (RSI, MACD, Volatilità, Bollinger %B)

### 💰 **Sistema Dividendi**
- Calendario dividendi con forecast 6 mesi
- Tracking distribuzioni ricevute
- Payout automatico via script schedulabile
- Sincronizzazione dati dividendi da n8n

### 🎯 **Strategia Operativa v2**
- **Signal Generator**: Genera segnali di trading automatici
- **Core-Satellite Strategy**: Allocazione target basata su volatilità
- **Risk Parity**: Aggiustamento pesi in base a volatilità storica
- **Segnali Supportati**:
  - BUY_LIMIT: Acquista su pullback tecnico
  - SELL_PARTIAL: Vendi parte posizione in caso di overweight
  - SET_STOP_LOSS: Imposta stop loss dinamico
  - SET_TAKE_PROFIT: Take profit a resistenze
  - REBALANCE: Ribilanciamento allocazione target

### 🤖 **Automazione n8n Completa**
- **4 Workflow Automatici**:
  - **Generazione Giornaliera**: Esegue SignalGeneratorService ogni giorno alle 19:30 CET
  - **Schedulazione Multi-Orario**: Analisi a 08:00, 13:00, 18:00 CET per opportunità intraday
  - **Alert Notifiche**: Email/Telegram per segnali IMMEDIATO con confidence > 80%
  - **Monitoring**: Health check ogni 4 ore con log dettagliati
- **API REST per Automazione**: `/api/signals.php` + `/api/alerts.php`
- **Notifiche Multi-Canale**: Email per alert urgenti, Telegram per update rapidi
- **Sicurezza**: HMAC authentication, rate limiting, logging completo
- **Configurazione**: File `.env.example` con tutte le variabili necessarie

---

## 📁 Struttura del Progetto

```
/Users/nicola/Documents/GitHub/trading-portfolio/
├── 📄 index.php                    # Homepage principale
├── 📄 config.php                   # Configurazioni globali
├── 📄 admin-recalculate.php        # Tool amministrativi
├── 📄 dividends-payout.php         # Gestione automatica dividendi
├── 📄 recalculate-metrics.php      # Ricalcolo metriche
├── 📁 api/                         # Endpoint REST API
│   ├── 📄 recommendations.php      # API gestione raccomandazioni (Fase 4)
│   ├── 📄 signals.php              # API generazione segnali (Fase 5)
│   └── 📄 alerts.php               # API notifiche e alert (Fase 5)
├── 📁 lib/                         # Librerie PHP (Repository Pattern)
│   ├── 📁 Database/                # Data Layer
│   │   ├── 📄 DatabaseManager.php  # Singleton PDO wrapper
│   │   ├── 📁 Repositories/        # Repository Pattern
│   │   ├── 📁 Models/              # Modelli dati
│   │   └── 📁 Services/            # Servizi business logic
│   ├── 📁 Services/                # Servizi applicativi
│   │   └── 📄 SignalGeneratorService.php  # Strategia Operativa v2
│   └── 📁 Utils/                   # Utilità varie
├── 📁 assets/                      # Risorse frontend (CSS, JS, images)
├── 📁 docs/                        # Documentazione completa
│   ├── 📄 01-ARCHITETTURA.md       # Architettura sistema
│   ├── 📄 02-GESTIONE-UTENTI.md    # Gestione multi-utente
│   ├── 📄 03-DATABASE.md           # Schema database
│   ├── 📄 04-API-REST.md           # Documentazione API
│   ├── 📄 06-N8N-WORKFLOWS.md      # Workflow automazione (base)
│   ├── 📄 08-STRATEGIA-OPERATIVA-v2.md    # Strategia segnali trading
│   ├── 📄 09-API-RECOMMENDATIONS.md       # API REST raccomandazioni (Fase 4)
│   └── 📄 10-N8N-WORKFLOWS-PHASE5.md      # Automazione n8n completa (Fase 5)
├── 📁 scripts/                     # Script di manutenzione e debug
│   ├── 📄 migrate-to-mysql.php     # Migrazione database
│   ├── 📄 recalculate-db-metrics.php  # Ricalcolo metriche
│   ├── 📄 db-check.php             # Controllo database
│   ├── 📄 test-api-recommendations.php  # Test API raccomandazioni (Fase 4)
│   ├── 📄 test-n8n-automation.php       # Test automazione n8n (Fase 5)
│   └── 📄 test-signal-generation.php    # Test SignalGeneratorService
├── 📁 config/                      # Configurazioni
│   └── 📄 api.php                  # Configurazione API e notifiche
├── 📁 logs/                        # Log di sistema (creato automaticamente)
│   ├── 📄 api_recommendations.log  # Log chiamate API
│   ├── 📄 api_rate_limit.json      # Rate limiting data
│   └── 📄 alerts.log               # Log alert system
├── 📁 data/                        # Dati di configurazione (legacy)
├── 📁 migrations/                  # Script SQL di migrazione
├── 📁 workflow/                    # Backup JSON workflow n8n (6 totali)
│   ├── 📄 Portfolio Enrichment v3.0 + analisi tecnica.json  # [PREREQUISITO] Arricchimento portfolio
│   ├── 📄 AI Technical Insights.json                         # [PREREQUISITO] Analisi tecnica AI
│   ├── 📄 signal-generation-daily.json                       # [FASE 5] ✅ Segnali giornalieri
│   └── 📄 signal-generation-intraday.json                    # [FASE 5] ✅ Segnali intraday
└── 📄 .env.example                 # Template variabili ambiente
```

---

## 🤝 Linee Guida per Contribuire

### 📋 Convenzioni di Progetto

**Script di test/debug:**
- ⚠️ **NON creare file di test/debug in root**
- ✅ Usare sempre la cartella `/scripts/` per script temporanei
- ✅ Nominare con prefisso descrittivo: `test-`, `debug-`, `verify-`
- 🗑️ Rimuovere script temporanei dopo l'uso

**Query SQL:**
- ✅ Sempre nella cartella `/scripts/` o `/migrations/`
- ✅ Nominare con versione: `YYYY_MM_DD_descrizione.sql`
- ✅ Documentare con commenti -- descrizione e scopo

**Documentazione:**
- ✅ Sempre nella cartella `/docs/`
- ✅ Numerare sequenzialmente: `01-`, `02-`, `03-`, ecc.
- ✅ Usare formato Markdown con titolo descrittivo

### 🔄 Workflow di Sviluppo

1. **Nuove funzionalità**: Creare branch separato
2. **Test**: Usare `/scripts/` per test temporanei
3. **Documentazione**: Aggiornare `/docs/` prima del merge
4. **Database**: Creare migrazione in `/migrations/`
5. **Pulizia**: Rimuovere file temporanei prima del commit

---

## 📚 Documentazione

### 📖 **Documenti Principali (Aggiornati alla Fase 5)**
- **[README.md](README.md)** - Panoramica generale e funzionalità (aggiornato 02 Dic 2025)
- **[docs/08-STRATEGIA-OPERATIVA-v2.md](docs/08-STRATEGIA-OPERATIVA-v2.md)** - Strategia Operativa v2 completa
- **[docs/09-API-RECOMMENDATIONS.md](docs/09-API-RECOMMENDATIONS.md)** - API REST raccomandazioni (Fase 4)
- **[docs/10-N8N-WORKFLOWS-PHASE5.md](docs/10-N8N-WORKFLOWS-PHASE5.md)** - Automazione n8n completa (Fase 5)

### 🔧 **Setup e Configurazione**
- **[docs/01-ARCHITETTURA.md](docs/01-ARCHITETTURA.md)** - Istruzioni complete di installazione
- **[docs/03-DATABASE.md](docs/03-DATABASE.md)** - Schema database completo
- **[docs/04-API-REST.md](docs/04-API-REST.md)** - Documentazione API endpoints
- **[.env.example](.env.example)** - Template configurazione ambiente

### 📊 **Stato Avanzamento Fasi**
- **✅ Fase 0-3**: Database, Repository, Models, SignalGeneratorService - COMPLETATE
- **✅ Fase 4**: API Layer REST completata (`/api/signals.php`, `/api/recommendations.php`, `/api/alerts.php`)
- **🔄 Fase 5**: Workflow n8n Automation - 4/6 workflow (2 prerequisiti + 2 Fase 5 implementati)
  - ✅ Portfolio Enrichment v3.0 (prerequisito)
  - ✅ AI Technical Insights (prerequisito)
  - ✅ Signal Generation Daily (implementato 02 Dic 2025)
  - ✅ Signal Generation Intraday (implementato 02 Dic 2025)
  - 🔄 Alerts & Notifications (da implementare)
  - 🔄 Monitoring & Health Check (da implementare)
- **📋 Fase 6**: Frontend Integration (in preparazione)

### 🔧 Setup e Configurazione

#### Installazione Base
Vedere [docs/01-ARCHITETTURA.md](docs/01-ARCHITETTURA.md) per istruzioni complete di installazione.

#### Configurazione Automazione n8n (Fase 5)
1. **Copia file ambiente**: `cp .env.example .env`
2. **Configura variabili**: modifica `.env` con le tue credenziali
3. **Importa workflow**: segui le istruzioni in [docs/10-N8N-WORKFLOWS-PHASE5.md](docs/10-N8N-WORKFLOWS-PHASE5.md)
4. **Configura notifiche**: imposta email/Telegram in `config/api.php`
5. **Testa l'automazione**: esegui `php scripts/test-n8n-automation.php`

**📁 Backup Workflow n8n:**
I file JSON dei workflow n8n sono salvati come backup nella cartella `/workflow/`:

**Prerequisiti (sempre attivi)**:
- `Portfolio Enrichment v3.0 + analisi tecnica.json` - Arricchimento portfolio con indicatori tecnici (RSI, MACD, EMA, Bollinger Bands)
- `AI Technical Insights.json` - Analisi tecnica avanzata con insights generati da AI

**Fase 5 - Automazione Segnali**:
- `signal-generation-daily.json` - ✅ Generazione segnali giornaliera (19:30 CET)
- `signal-generation-intraday.json` - ✅ Schedulazione multi-orario (08:00, 13:00, 18:00 CET)
- `alerts-notifications.json` - 🔄 Da implementare (notifiche alert ad alta priorità)
- `monitoring-health-check.json` - 🔄 Da implementare (health check sistema)

**Flusso dati**: Portfolio Enrichment → AI Technical Insights → Signal Generation → Alerts & Monitoring

Questi file permettono di ripristinare o reimportare i workflow in una nuova istanza di n8n.

---

## ⚠️ Sicurezza e Disclaimer

**IMPORTANTE:** Questo è uno strumento di analisi personale, non fornisce consulenza finanziaria. Le raccomandazioni generate sono suggerimenti basati su algoritmi tecnici e non garantiscono performance future. L'utente è responsabile di verificare ogni decisione di investimento.

---

## 📞 Contatti e Supporto

**Repository:** [GitHub - trading-portfolio](https://github.com/your-username/trading-portfolio)
**Issue Tracking:** Usare GitHub Issues per bug report e feature request
**Documentazione:** Tutta la documentazione è in `/docs/`

---

**Ultimo aggiornamento:** 02 Dicembre 2025
**Versione:** 0.4.2-StrategiaOperativa (MySQL + SignalGeneratorService + Repository Pattern)