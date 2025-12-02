# 📋 Changelog

Tutti i cambiamenti significativi del progetto verranno documentati in questo file.

Il formato è basato su [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
e il progetto segue il [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

---

## [0.5.0] - 2025-12-02 - **Fase 5: Automazione n8n Completa**

### 🚀 Aggiunto

#### Sistema di Automazione Completo
- **4 Workflow n8n** per automazione segnali di trading:
  - **Workflow E**: Generazione automatica segnali giornaliera (19:30 CET)
  - **Workflow F**: Schedulazione multi-orario (08:00, 13:00, 18:00 CET)
  - **Workflow G**: Alert notifiche per segnali ad alta priorità
  - **Workflow H**: Monitoring e health check ogni 4 ore

#### API REST per Automazione
- **Nuovo endpoint `/api/signals.php`**:
  - Generazione segnali con parametri configurabili
  - Supporta HMAC authentication per sicurezza
  - Rate limiting: 10 richieste/ora per IP
  - Parametri: portfolio_id, analysis_type, confidence_threshold, max_signals

- **Nuovo endpoint `/api/alerts.php`**:
  - Sistema notifiche complete (Email + Telegram)
  - Supporta alert di tipo: high-priority, system-health, rate-limit
  - Template email HTML per segnali urgenti
  - Integrazione Telegram con formattazione HTML

#### SignalGeneratorService Esteso
- **Nuovo metodo `generateSignalsWithParams()`**:
  - Parametri configurabili per integrazione API
  - Supporta generazione per singolo holding o portfolio completo
  - Confidence threshold configurabile
  - Statistiche dettagliate di generazione
  - Metadata completo per ogni segnale generato

#### Sistema di Notifiche Multi-Canale
- **Email notifications** per segnali ad alta priorità:
  - Template HTML professionale
  - Priorità configurabile (high/normal)
  - Informazioni complete: ticker, tipo, urgenza, confidence, prezzi

- **Telegram notifications** per update rapidi:
  - Formattazione HTML con emoji
  - Messaggi brevi e concisi
  - Configurazione via variabili ambiente

#### Configurazione e Sicurezza
- **File `.env.example`** con tutte le variabili necessarie:
  - API keys per servizi esterni
  - Configurazione SMTP per email
  - Credenziali Telegram Bot
  - Parametri di sicurezza e rate limiting

- **File `config/api.php`** per configurazione centralizzata:
  - Impostazioni CORS configurabili
  - Parametri rate limiting
  - Configurazione notifiche
  - Validazione e sicurezza

### 🔧 Modificato

#### Architettura API
- **Migliorata sicurezza** con HMAC authentication per webhook n8n
- **Rate limiting** implementato su endpoints critici
- **CORS configurabile** per produzione
- **Logging completo** di tutte le chiamate API

#### SignalGeneratorService
- **Esteso per supportare chiamate API** con parametri dinamici
- **Aggiunto tracking** di analysis_type e session_type
- **Migliorata gestione errori** con dettagli su salvataggio fallito
- **Aggiunto source tracking** per distinguere origine segnali

#### Repository Pattern
- **Estesa validation** su RecommendationRepository
- **Aggiunti metodi** per statistiche e monitoring
- **Migliorata gestione** filtri e ordinamento

### 📁 File Aggiunti
- `/api/signals.php` - Endpoint generazione segnali
- `/api/alerts.php` - Endpoint sistema notifiche
- `/config/api.php` - Configurazione API e notifiche
- `/.env.example` - Template variabili ambiente
- `/docs/10-N8N-WORKFLOWS-PHASE5.md` - Documentazione workflow n8n
- `/scripts/test-n8n-automation.php` - Script test automazione

### 📊 Metriche
- **4 workflow n8n** completamente documentati
- **3 API endpoints** REST con sicurezza e validazione
- **2 canali notifiche** (Email + Telegram) operativi
- **10 richieste/ora** rate limit per signal generation
- **4 ore** intervallo monitoring health check

### 🔍 Testing
- **Script di test completo**: `test-n8n-automation.php`
- **Verifica struttura API**: endpoints, autenticazione, rate limiting
- **Test notifiche**: email e Telegram configuration check
- **Documentazione workflow**: istruzioni step-by-step per n8n

---

## [0.4.2] - 2025-12-02 - **Fase 4: API Layer REST**

### 🚀 Aggiunto
- **API REST completa** per gestione raccomandazioni: `/api/recommendations.php`
- **CRUD operations**: GET, POST, PUT, DELETE con validazione
- **Filtri avanzati**: per status, holding_id, urgenza
- **Paginazione e ordinamento**: page, per_page, order_by, order_dir
- **Statistiche aggregate**: endpoint dedicato per metrics
- **Rate limiting**: 60 richieste/minuto per IP
- **CORS sicuro**: origini configurabili per produzione
- **Validazione input**: type checking, range validation, enum validation
- **Logging completo**: tracciamento chiamate API con IP e user agent

### 📁 File Aggiunti
- `/api/recommendations.php` - Endpoint REST principale
- `/docs/09-API-RECOMMENDATIONS.md` - Documentazione API completa
- `/scripts/test-api-recommendations.php` - Script validazione API

---

## [0.4.1] - 2025-11-27 - **Fix Critici e Stabilizzazione**

### 🛠️ Corretto
- **Parse error HTML entities** in Recommendation.php
- **Singleton pattern** DatabaseManager correttamente implementato
- **Campi mancanti** target_allocation_pct e role in HoldingRepository

---

## [0.4.0] - 2025-11-27 - **Fase 3: Strategia Operativa v2**

### 🚀 Aggiunto
- **SignalGeneratorService** completo con strategia Core-Satellite Risk Parity
- **Sistema raccomandazioni** con tabelle MySQL dedicate
- **5 tipi di segnali** di trading supportati
- **Confidence scoring** 0-100 con graceful degradation
- **Test in produzione** superati con successo

### 📁 File Aggiunti
- `/lib/Services/SignalGeneratorService.php` - Engine generazione segnali
- `/docs/08-STRATEGIA-OPERATIVA-v2.md` - Documentazione strategia completa

---

## [0.3.0] - 2025-11-20 - **Migrazione MySQL e Repository Pattern**

### 🚀 Aggiunto
- **Repository Pattern** completo per tutte le entità
- **MySQL migration** da SQLite con 16 tabelle
- **DatabaseManager** singleton per connessioni PDO
- **Technical analysis** con indicatori aggiornati

---

## [0.2.0] - 2025-11-15 - **Analisi Tecnica Avanzata**

### 🚀 Aggiunto
- **Indicatori tecnici** RSI, MACD, EMA, Bollinger Bands
- **AI Technical Insights** con scoring automatico
- **Grafici storici** tecnici con Chart.js
- **Vista analisi tecnica** dedicata

---

## [0.1.0] - 2025-11-10 - **Release Iniziale**

### 🚀 Aggiunto
- **Dashboard principale** con metriche portfolio
- **Gestione holdings** con CRUD operations
- **Importazione CSV** da Fineco Bank
- **Sistema dividendi** con forecast
- **Grafici performance** con ChartManager

---

**Nota**: Per informazioni dettagliate su ogni versione, vedere i file di documentazione specifici nella cartella `/docs/`.\n\n**Ultimo aggiornamento:** 02 Dicembre 2025\n**Prossimo aggiornamento previsto:** Con completamento Fase 6 (Frontend Integration)