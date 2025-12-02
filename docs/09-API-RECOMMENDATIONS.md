# 📋 API REST - Raccomandazioni di Trading

**Documentazione completa degli endpoint API per la gestione delle raccomandazioni di trading**

## 🎯 Panoramica

L'API REST per le raccomandazioni fornisce endpoint completi per:
- Recuperare raccomandazioni con filtri avanzati
- Creare nuove raccomandazioni dal SignalGenerator
- Aggiornare lo stato delle raccomandazioni
- Eliminare raccomandazioni (soft delete)
- Ottenere statistiche aggregate

## 🔧 Configurazione

### CORS
L'API implementa CORS configurabile per produzione. Modifica l'array `$allowed_origins` in `/api/recommendations.php`:

```php
$allowed_origins = [
    'https://your-domain.com',
    'https://trading-portfolio.example.com'
];
```

### Rate Limiting
Implementato rate limiting semplice basato su file (60 richieste/minuto per IP). Per produzione, considera Redis/Memcached.

### Logging
Tutte le chiamate API vengono loggate in `/logs/api_recommendations.log`

## 📡 Endpoint

### GET /api/recommendations.php

#### Lista raccomandazioni con filtri

**Parametri Query:**
- `status` - Filtra per stato: `ACTIVE`, `EXECUTED`, `EXPIRED`, `IGNORED`
- `holding_id` - Filtra per ID holding specifico
- `urgency` - Filtra per urgenza: `IMMEDIATO`, `QUESTA_SETTIMANA`, `PROSSIME_2_SETTIMANE`, `MONITORAGGIO`
- `page` - Numero pagina (default: 1)
- `per_page` - Elementi per pagina (default: 20, max: 100)
- `order_by` - Ordinamento: `created_at`, `confidence_score`, `urgency`
- `order_dir` - Direzione: `ASC`, `DESC` (default: DESC)

**Esempio richiesta:**
```bash
curl -X GET "https://your-domain.com/api/recommendations.php?status=ACTIVE&urgency=IMMEDIATO&page=1&per_page=10"
```

**Risposta successo (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "type": "BUY_LIMIT",
      "urgency": "IMMEDIATO",
      "ticker": "IWDA.MI",
      "name": "iShares Core MSCI World",
      "trigger_price": 65.50,
      "current_price": 66.20,
      "confidence_score": 85,
      "allocation_impact_pct": 2.5,
      "days_to_expire": 5,
      "created_at": "2025-12-01 14:30:00"
    }
  ],
  "pagination": {
    "page": 1,
    "per_page": 10,
    "total": 25,
    "pages": 3
  },
  "filters": {
    "status": "ACTIVE",
    "urgency": "IMMEDIATO"
  }
}
```

#### Dettaglio singola raccomandazione

**Parametri Query:**
- `id` - ID della raccomandazione

**Esempio richiesta:**
```bash
curl -X GET "https://your-domain.com/api/recommendations.php?id=1"
```

**Risposta successo (200):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "type": "BUY_LIMIT",
    "holding_id": 5,
    "ticker": "IWDA.MI",
    "name": "iShares Core MSCI World",
    "urgency": "IMMEDIATO",
    "quantity": 10,
    "trigger_price": 65.50,
    "trigger_condition": "limit_order",
    "stop_loss": 60.00,
    "take_profit": 70.00,
    "rationale_primary": "Pullback tecnico su supporto",
    "rationale_technical": "RSI(14) = 32, supporto statico 65.20",
    "confidence_score": 85,
    "status": "ACTIVE",
    "expires_at": "2025-12-08 14:30:00",
    "days_to_expire": 5,
    "allocation_impact_pct": 2.5,
    "current_quantity": 50,
    "current_price": 66.20,
    "avg_price": 64.80,
    "created_at": "2025-12-01 14:30:00"
  }
}
```

#### Statistiche aggregate

**Parametri Query:**
- `statistics=true` - Restituisce statistiche aggregate

**Esempio richiesta:**
```bash
curl -X GET "https://your-domain.com/api/recommendations.php?statistics=true"
```

**Risposta successo (200):**
```json
{
  "success": true,
  "data": {
    "total_count": 156,
    "active_count": 8,
    "executed_count": 142,
    "expired_count": 4,
    "ignored_count": 2,
    "avg_confidence_executed": 78.5,
    "avg_confidence_active": 82.3,
    "active_days": 45
  }
}
```

### POST /api/recommendations.php

Crea una nuova raccomandazione.

**Headers richiesti:**
```
Content-Type: application/json
```

**Body richiesta:**
```json
{
  "type": "BUY_LIMIT",
  "holding_id": 5,
  "urgency": "IMMEDIATO",
  "quantity": 10,
  "trigger_price": 65.50,
  "trigger_condition": "limit_order",
  "stop_loss": 60.00,
  "take_profit": 70.00,
  "rationale_primary": "Pullback tecnico su supporto",
  "rationale_technical": "RSI(14) = 32, supporto statico 65.20",
  "confidence_score": 85,
  "expires_at": "2025-12-08T14:30:00Z"
}
```

**Campi obbligatori:**
- `type` - Tipo raccomandazione (vedi enum sotto)
- `holding_id` - ID holding (integer > 0)

**Campi opzionali:**
- `urgency` - Urgenza (default: `MONITORAGGIO`)
- `quantity` - Quantità (float >= 0)
- `trigger_price` - Prezzo trigger (float >= 0)
- `trigger_condition` - Condizione trigger (default: `market_order`)
- `stop_loss` - Stop loss (float >= 0)
- `take_profit` - Take profit (float >= 0)
- `rationale_primary` - Razionale principale
- `rationale_technical` - Analisi tecnica
- `confidence_score` - Punteggio confidenza 0-100 (default: 50)
- `expires_at` - Data scadenza ISO 8601 (default: +7 giorni)

**Esempio richiesta:**
```bash
curl -X POST "https://your-domain.com/api/recommendations.php" \
  -H "Content-Type: application/json" \
  -d '{
    "type": "BUY_LIMIT",
    "holding_id": 5,
    "urgency": "IMMEDIATO",
    "quantity": 10,
    "trigger_price": 65.50,
    "confidence_score": 85
  }'
```

**Risposta successo (201):**
```json
{
  "success": true,
  "message": "Raccomandazione creata con successo",
  "data": {
    "id": 42
  }
}
```

### PUT /api/recommendations.php?id=X

Aggiorna una raccomandazione esistente.

**Parametri Query:**
- `id` - ID della raccomandazione da aggiornare

**Headers richiesti:**
```
Content-Type: application/json
```

**Body richiesta:**
```json
{
  "status": "EXECUTED",
  "executed_price": 65.45,
  "executed_quantity": 10,
  "notes": "Eseguito su pullback, prezzo migliore del trigger"
}
```

**Campi aggiornabili:**
- `status` - Nuovo stato: `ACTIVE`, `EXECUTED`, `EXPIRED`, `IGNORED`
- `executed_price` - Prezzo esecuzione (float >= 0)
- `executed_quantity` - Quantità eseguita (float >= 0)
- `notes` - Note aggiornamento

**Esempio richiesta:**
```bash
curl -X PUT "https://your-domain.com/api/recommendations.php?id=42" \
  -H "Content-Type: application/json" \
  -d '{
    "status": "EXECUTED",
    "executed_price": 65.45,
    "notes": "Eseguito manualmente"
  }'
```

**Risposta successo (200):**
```json
{
  "success": true,
  "message": "Raccomandazione aggiornata con successo"
}
```

### DELETE /api/recommendations.php?id=X

Elimina (soft delete) una raccomandazione.

**Parametri Query:**
- `id` - ID della raccomandazione da eliminare

**Esempio richiesta:**
```bash
curl -X DELETE "https://your-domain.com/api/recommendations.php?id=42"
```

**Risposta successo (200):**
```json
{
  "success": true,
  "message": "Raccomandazione eliminata con successo"
}
```

## 📋 Tipi di Raccomandazioni

### Enum Type
- `BUY_LIMIT` - Acquista con ordine limite
- `BUY_MARKET` - Acquista al mercato
- `SELL_PARTIAL` - Vendi parzialmente
- `SELL_ALL` - Vendi tutto
- `SET_STOP_LOSS` - Imposta stop loss
- `SET_TAKE_PROFIT` - Imposta take profit
- `REBALANCE` - Ribilanciamento

### Enum Urgency
- `IMMEDIATO` - Da eseguire entro 24 ore
- `QUESTA_SETTIMANA` - Da eseguire entro 7 giorni
- `PROSSIME_2_SETTIMANE` - Da eseguire entro 14 giorni
- `MONITORAGGIO` - Solo monitoraggio

### Enum Status
- `ACTIVE` - Attiva e monitorata
- `EXECUTED` - Eseguita con successo
- `EXPIRED` - Scaduta
- `IGNORED` - Ignorata dall'utente

## ⚠️ Errori Comuni

### 400 Bad Request
```json
{
  "success": false,
  "error": "Validazione fallita: Campo type deve essere uno di: BUY_LIMIT, BUY_MARKET, SELL_PARTIAL, SELL_ALL, SET_STOP_LOSS, SET_TAKE_PROFIT, REBALANCE"
}
```

### 404 Not Found
```json
{
  "success": false,
  "error": "Raccomandazione non trovata"
}
```

### 405 Method Not Allowed
```json
{
  "success": false,
  "error": "Metodo HTTP non supportato"
}
```

### 429 Too Many Requests
```json
{
  "success": false,
  "error": "Troppe richieste. Riprova tra un minuto."
}
```

### 500 Internal Server Error
```json
{
  "success": false,
  "error": "Errore interno del server",
  "message": "Dettaglio tecnico dell'errore"
}
```

## 🔒 Sicurezza

### Rate Limiting
- **Limite**: 60 richieste per minuto per IP
- **Implementazione**: File-based (per produzione usa Redis)

### CORS
- Origini consentite configurabili
- Headers richiesti: `Content-Type`, `Authorization`, `X-Requested-With`

### Validazione
- Tutti gli input vengono validati per tipo e range
- Enum values vengono verificati
- SQL injection prevenuto tramite prepared statements

## 📊 Performance

### Paginazione
- Default: 20 elementi per pagina
- Massimo: 100 elementi per pagina
- Parametri: `page`, `per_page`

### Caching
- Nessun caching implementato (considera Redis per produzione)
- Ogni richiesta genera nuova query al database

### Query Optimization
- Utilizza `SQL_CALC_FOUND_ROWS` per conteggio efficiente
- Indici su colonne frequentemente filtrate
- JOIN ottimizzati con tabelle holdings

## 🧪 Testing

### Script di Test
Utilizza `/scripts/test-api-recommendations.php` per verificare:
- Presenza file API e dipendenze
- Struttura endpoint HTTP
- Filtri disponibili
- Funzioni repository

### Esempio test rapido:
```bash
# Test connessione base
curl -X GET "https://your-domain.com/api/recommendations.php?statistics=true"

# Test con filtri
curl -X GET "https://your-domain.com/api/recommendations.php?status=ACTIVE&page=1&per_page=5"

# Test creazione (con dati validi)
curl -X POST "https://your-domain.com/api/recommendations.php" \
  -H "Content-Type: application/json" \
  -d '{"type": "BUY_LIMIT", "holding_id": 1, "urgency": "MONITORAGGIO"}'
```

## 🔄 Integrazione

### Frontend JavaScript
```javascript
// Esempio fetch con filtri
async function getActiveRecommendations() {
  const response = await fetch('/api/recommendations.php?status=ACTIVE&urgency=IMMEDIATO');
  const data = await response.json();
  return data.data;
}

// Esempio creazione raccomandazione
async function createRecommendation(recData) {
  const response = await fetch('/api/recommendations.php', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
    },
    body: JSON.stringify(recData)
  });
  return response.json();
}
```

### Integrazione SignalGenerator
Il SignalGeneratorService può utilizzare l'API per creare raccomandazioni:

```php
// Esempio chiamata da SignalGenerator
$ch = curl_init('https://your-domain.com/api/recommendations.php');
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($recommendationData));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
```

---

**File:** `/docs/09-API-RECOMMENDATIONS.md`
**Aggiornato:** 02 Dicembre 2025
**Versione API:** 1.0.0