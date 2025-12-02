# ETF Portfolio Manager

**Versione:** 0.4.2-StrategiaOperativa ✅
**Stato:** Produzione attiva con Strategia Operativa v2
**Data ultimo aggiornamento:** 02 Dicembre 2025

Strumento web completo per la gestione e analisi di portafogli ETF su Fineco Bank, con sistema avanzato di generazione segnali di trading basato su analisi tecnica e strategia Core-Satellite Risk Parity.

---

## 📊 Stato del Progetto

### ✅ **Produzione Attiva - Strategia Operativa v2 Implementata**

**Architettura Tecnica:**
- **Backend:** PHP 8.2+ con Repository Pattern
- **Database:** MySQL 8.2.29 (16 tabelle + 2 VIEWs)
- **Frontend:** Vanilla JavaScript + Chart.js
- **Automazione:** n8n Workflows per data enrichment
- **Signal Engine:** SignalGeneratorService con strategia Core-Satellite Risk Parity

### 🎯 **Sistema Raccomandazioni Operative (Database)**
- **SignalGeneratorService**: Engine completo per generazione segnali di trading
- **Tabelle**: `recommendations`, `recommendation_actions`, `macro_indicators`
- **Strategia**: Core-Satellite Risk Parity con allocazione target dinamica
- **Segnali**: BUY_LIMIT, SELL_PARTIAL, SET_STOP_LOSS, SET_TAKE_PROFIT, REBALANCE
- **Confidence Scoring**: 0-100 con graceful degradation per dati mancanti
- **Test Produzione**: ✅ Superati test completi in ambiente reale

---

## 🚀 Funzionalità Principali

### 📈 **Dashboard & Performance**
- Visualizzazione metriche portafoglio in real-time
- Grafici performance con ChartManager centralizzato
- Storico snapshots temporali per tracking performance
- Analisi allocazioni per asset class e settori

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
│   ├── 📄 05-FRONTEND.md           # Frontend guidelines
│   ├── 📄 06-N8N-WORKFLOWS.md      # Workflow automazione
│   ├── 📄 07-DATA-ENHANCEMENT-ROADMAP.md  # Miglioramenti dati
│   └── 📄 08-STRATEGIA-OPERATIVA-v2.md    # Strategia segnali trading
├── 📁 scripts/                     # Script di manutenzione e debug
│   ├── 📄 migrate-to-mysql.php     # Migrazione database
│   ├── 📄 recalculate-db-metrics.php  # Ricalcolo metriche
│   └── 📄 db-check.php             # Controllo database
├── 📁 data/                        # Dati di configurazione (legacy)
├── 📁 logs/                        # Log di sistema
└── 📁 migrations/                  # Script SQL di migrazione
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

### 📖 Documenti Principali
- **[docs/08-STRATEGIA-OPERATIVA-v2.md](docs/08-STRATEGIA-OPERATIVA-v2.md)** - Strategia Operativa v2 completa
- **[docs/07-DATA-ENHANCEMENT-ROADMAP.md](docs/07-DATA-ENHANCEMENT-ROADMAP.md)** - Roadmap miglioramenti
- **[docs/03-DATABASE.md](docs/03-DATABASE.md)** - Schema database completo
- **[docs/04-API-REST.md](docs/04-API-REST.md)** - Documentazione API endpoints

### 🔧 Setup e Configurazione
Vedere [docs/01-ARCHITETTURA.md](docs/01-ARCHITETTURA.md) per istruzioni complete di installazione.

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