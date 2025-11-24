# 04 – API REST

Questo documento descrive gli endpoint REST della web‑app PHP, organizzati per aree funzionali. Ogni endpoint specifica:
- metodo HTTP
- URL pattern
- autenticazione richiesta
- payload request/response
- codici di errore

**Base URL**: `http://localhost:8080/api` (in sviluppo) o `https://portfolio.example.com/api` (in produzione).

---

## 1. Autenticazione

### 1.1 Login

**POST** `/auth/login`

Autentica un utente e crea una sessione.

**Request**:
{
"email": "mario.rossi@example.com",
"password": "SecureP@ss123456",
"remember_me": false
}


**Response (200 OK)**:
{
"success": true,
"message": "Login completato",
"user_id": 1,
"email": "mario.rossi@example.com",
"session_id": "etf_portfolio_session=abc123..."
}


**Errori**:
- `400 Bad Request`: dati incompleti (email o password mancante)
- `401 Unauthorized`: credenziali non valide
- `429 Too Many Requests`: rate limiting (>5 tentativi falliti in 15 min)

---

### 1.2 Registrazione

**POST** `/auth/register`

Registra un nuovo utente.

**Request**:
{
"email": "mario.rossi@example.com",
"password": "SecureP@ss123456",
"full_name": "Mario Rossi"
}


**Validazioni**:
- email formato valido, non già esistente
- password ≥ 12 caratteri, complessità (maiuscole + numeri + simboli)

**Response (201 Created)**:
{
"success": true,
"message": "Utente registrato con successo",
"user_id": 2
}


**Errori**:
- `400 Bad Request`: validazione client fallita (email non valida, password debole)
- `409 Conflict`: email già registrata
- `422 Unprocessable Entity`: dati incompleti

---

### 1.3 Logout

**POST** `/auth/logout`

Termina la sessione dell'utente.

**Request**: (nessun body)

**Response (200 OK)**:
{
"success": true,
"message": "Logout completato"
}


---

## 2. Area Holdings

### 2.1 Lista holdings

**GET** `/holdings`

Restituisce tutte le posizioni dell'utente in un portafoglio.

**Query parameters**:
- `portfolio_id` (obbligatorio): ID portafoglio
- `is_active` (opzionale, default=1): filtro active/inactive

**Response (200 OK)**:
{
"success": true,
"data": [
{
"holding_id": 1,
"portfolio_id": 1,
"isin": "IE00B4L5Y983",
"ticker": "VWRL",
"instrument_name": "Vanguard FTSE All-World UCITS ETF",
"asset_class": "Equity",
"sector": "Global",
"quantity": 100.000000,
"avg_price": 95.50,
"current_price": 98.75,
"market_value": 9875.00,
"invested_value": 9550.00,
"unrealized_pnl": 325.00,
"pnl_percentage": 3.40,
"target_allocation": 40.00,
"commission_profile": "ZERO"
}
// ... altri holdings
]
}


**Errori**:
- `401 Unauthorized`: sessione non valida
- `403 Forbidden`: portfolio_id non appartiene all'utente
- `404 Not Found`: portfolio non trovato

---

### 2.2 Dettaglio holding

**GET** `/holdings/{holding_id}`

Restituisce dettagli completi di una singola posizione.

**Response (200 OK)**:
{
"success": true,
"data": {
"holding_id": 1,
"portfolio_id": 1,
"isin": "IE00B4L5Y983",
"ticker": "VWRL",
"instrument_name": "Vanguard FTSE All-World UCITS ETF",
"asset_class": "Equity",
"sector": "Global",
"quantity": 100.000000,
"avg_price": 95.50,
"current_price": 98.75,
"target_allocation": 40.00,
"notes": "Fondo core diversificato globale",
"commission_profile": "ZERO",
"commission_rate": 0.00,
"created_at": "2025-11-20T10:30:00Z",
"updated_at": "2025-11-24T14:15:00Z"
}
}


---

### 2.3 Crea holding

**POST** `/holdings`

Crea una nuova posizione in portafoglio (con eventuale prima transazione BUY implicita).

**Request**:
{
"portfolio_id": 1,
"isin": "IE00B4L5Y983",
"quantity": 100.000000,
"avg_price": 95.50,
"purchase_date": "2025-11-20",
"target_allocation": 40.00,
"notes": "Acquisto iniziale"
}


**Response (201 Created)**:
{
"success": true,
"message": "Holding creato con successo",
"holding_id": 1,
"transaction_id": 1
}


**Errori**:
- `400 Bad Request`: dati incompleti/non validi
- `409 Conflict`: ISIN già presente nel portafoglio

---

### 2.4 Aggiorna holding

**PUT** `/holdings/{holding_id}`

Aggiorna metadati di una posizione (asset class, sector, target allocation, note) **senza** alterare lo storico transazioni.

**Request**:
{
"target_allocation": 45.00,
"notes": "Aumentato target a 45%"
}


**Response (200 OK)**:
{
"success": true,
"message": "Holding aggiornato con successo"
}


---

### 2.5 Elimina holding

**DELETE** `/holdings/{holding_id}`

Soft delete (default) o hard delete con conferma esplicita.

**Query parameters**:
- `hard_delete` (opzionale, default=false): se true, rimozione definitiva

**Request** (per hard delete):
{
"confirm_hard_delete": true,
"reason": "Errore di input"
}


**Response (200 OK)**:
{
"success": true,
"message": "Holding eliminato (soft delete / hard delete)"
}


**Errori**:
- `400 Bad Request`: hard_delete richiede conferma
- `403 Forbidden`: hard delete solo da soft delete, con motivo

---

## 3. Area Transactions

### 3.1 Lista transazioni

**GET** `/transactions`

Restituisce lo storico delle operazioni per un portafoglio, filtrabile.

**Query parameters**:
- `portfolio_id` (obbligatorio)
- `isin` (opzionale): filtra per strumento
- `type` (opzionale): BUY, SELL, DIVIDEND
- `from_date` (opzionale): YYYY-MM-DD
- `to_date` (opzionale): YYYY-MM-DD
- `limit` (default=100), `offset` (default=0): paginazione

**Response (200 OK)**:
{
"success": true,
"total": 42,
"limit": 100,
"offset": 0,
"data": [
{
"transaction_id": 1,
"portfolio_id": 1,
"isin": "IE00B4L5Y983",
"ticker": "VWRL",
"type": "BUY",
"transaction_date": "2025-11-20",
"quantity": 100.000000,
"price": 95.50,
"total_amount": 9550.00,
"commission": 0.00,
"fx_rate": 1.000000,
"source": "MANUAL",
"notes": "Acquisto iniziale",
"created_at": "2025-11-20T10:30:00Z"
}
// ... altre transazioni
]
}


---

### 3.2 Inserisci transazione

**POST** `/transactions`

Crea una nuova operazione e aggiorna di conseguenza la posizione (holdings).

**Request**:
{
"portfolio_id": 1,
"isin": "IE00B4L5Y983",
"type": "BUY",
"transaction_date": "2025-11-24",
"quantity": 50.000000,
"price": 98.75,
"total_amount": 4937.50,
"commission": 0.00,
"fx_rate": 1.000000,
"notes": "Integrazione posizione"
}


**Validazioni**:
- Tipo operazione valido (BUY/SELL/DIVIDEND)
- Se SELL: `quantity` ≤ quantità posseduta attualmente
- Data coerente (non futura, non troppo vecchia)
- Importi non negativi

**Response (201 Created)**:
{
"success": true,
"message": "Transazione creata e holdings aggiornato",
"transaction_id": 2,
"holding_id": 1,
"updated_quantity": 150.000000,
"updated_avg_price": 96.33
}


**Errori**:
- `400 Bad Request`: validazione fallita
- `409 Conflict`: SELL con quantity > posseduta

---

### 3.3 Import CSV

**POST** `/transactions/import-csv`

Carica un file CSV da Fineco e mapsa le colonne per importare operazioni in batch.

**Form Data**:
- `portfolio_id` (number): ID portafoglio destinazione
- `csv_file` (file): file CSV da Fineco

**Step 1: Validazione e anteprima**

**Response (200 OK)**:
{
"success": true,
"step": "preview",
"rows": [
{
"row_number": 1,
"isin": "IE00B4L5Y983",
"type": "BUY",
"transaction_date": "2025-11-20",
"quantity": 100.0,
"price": 95.50,
"commission": 0.00,
"status": "valid"
},
{
"row_number": 2,
"isin": "INVALID_ISIN",
"status": "error",
"error": "ISIN non trovato in market_data.etf_info"
}
],
"summary": {
"total_rows": 2,
"valid_rows": 1,
"error_rows": 1
}
}


**Step 2: Conferma import**

**POST** `/transactions/import-csv/confirm`

**Request**:
{
"portfolio_id": 1,
"import_session_id": "sess_abc123",
"skip_errors": true
}


**Response (200 OK)**:
{
"success": true,
"message": "Import completato",
"imported_rows": 1,
"skipped_rows": 1,
"holdings_created": 1,
"holdings_updated": 0
}


---

## 4. Area Portfolio

### 4.1 Snapshot attuale

**GET** `/portfolio/snapshot`

Restituisce le metriche aggregate correnti del portafoglio.

**Query parameters**:
- `portfolio_id` (obbligatorio)

**Response (200 OK)**:
{
"success": true,
"data": {
"portfolio_id": 1,
"portfolio_name": "Personale",
"base_currency": "EUR",
"total_value": 150000.00,
"total_invested": 145000.00,
"unrealized_pnl": 5000.00,
"unrealized_pnl_percentage": 3.45,
"realized_pnl": 500.00,
"total_dividends": 250.00,
"holdings_count": 3,
"allocation": [
{
"isin": "IE00B4L5Y983",
"ticker": "VWRL",
"name": "Vanguard FTSE All-World",
"percentage": 40.00,
"target_percentage": 40.00,
"drift": 0.00,
"market_value": 60000.00
}
// ... altri strumenti
]
}
}


---

### 4.2 Storico giornaliero

**GET** `/portfolio/history`

Restituisce snapshot storici giornalieri per grafici di performance nel tempo.

**Query parameters**:
- `portfolio_id` (obbligatorio)
- `days` (default=365): numero giorni indietro
- `frequency` (default=daily): daily, weekly, monthly

**Response (200 OK)**:
{
"success": true,
"data": [
{
"snapshot_date": "2025-11-24",
"total_value": 150000.00,
"total_invested": 145000.00,
"unrealized_pnl": 5000.00,
"realized_pnl": 500.00,
"total_dividends": 250.00,
"holdings_count": 3
},
// ... snapshot precedenti
]
}


---

## 5. Area Analisi

### 5.1 Ricevi risultati workflow (n8n)

**POST** `/analysis/results`

Endpoint per ricevere i risultati aggregati dei workflow n8n.
**Autenticazione**: HMAC-SHA256 signature header.

**Headers**:
Content-Type: application/json
X-Webhook-Signature: sha256=<HMAC>


**Request body**:
{
"workflow_id": "technical_analysis_daily",
"timestamp": "2025-11-24T15:30:00Z",
"results": [
{
"isin": "IE00B4L5Y983",
"analysis_type": "TECHNICAL",
"signal": "BUY",
"confidence_score": 0.87,
"data": {
"ema_9": 98.50,
"ema_21": 97.80,
"ema_50": 96.50,
"rsi": 65,
"macd_histogram": 0.45,
"bb_upper": 100.20,
"bb_lower": 95.30
}
}
// ... altri risultati
]
}


**Response (201 Created)**:
{
"success": true,
"message": "Risultati analisi ricevuti",
"records_inserted": 1
}


**Errori**:
- `401 Unauthorized`: firma HMAC non valida
- `400 Bad Request`: payload non conforme
- `422 Unprocessable Entity`: dati non processabili

---

### 5.2 Leggi analisi recenti

**GET** `/analysis/latest`

Restituisce gli ultimi risultati di analisi per ISIN o portafoglio.

**Query parameters**:
- `portfolio_id` (opzionale): filtra analisi del portafoglio
- `isin` (opzionale): filtra per ISIN specifico
- `analysis_type` (opzionale): TECHNICAL, MACRO_SENTIMENT, OPPORTUNITY, REBALANCING

**Response (200 OK)**:
{
"success": true,
"data": [
{
"analysis_id": 1,
"isin": "IE00B4L5Y983",
"analysis_type": "TECHNICAL",
"signal": "BUY",
"confidence_score": 0.87,
"analyzed_at": "2025-11-24T15:30:00Z",
"data": {
"ema_9": 98.50,
"rsi": 65
}
}
// ... altre analisi
]
}


---

## 6. Area Commissioni Fineco

### 6.1 Leggi profilo commissionale

**GET** `/commissions/{isin}`

Restituisce il profilo commissionale di un ISIN.

**Response (200 OK)**:
{
"success": true,
"data": {
"isin": "IE00B4L5Y983",
"commission_profile": "ZERO",
"commission_rate": 0.00,
"last_checked": "2025-11-24T10:00:00Z"
}
}


---

### 6.2 Aggiorna cache commissioni

**PUT** `/commissions/{isin}`

Aggiorna il profilo commissionale (usato da n8n dopo scraping).

**Request**:
{
"commission_profile": "ZERO",
"commission_rate": 0.00,
"notes": "Aggiornato da web scraping Fineco"
}


**Response (200 OK)**:
{
"success": true,
"message": "Profilo commissionale aggiornato"
}


---

## 7. Error handling

Tutti gli endpoint seguono questo formato di errore:

**HTTP Status Code**: 4xx (client) o 5xx (server)

**Response**:
{
"success": false,
"error": "Descrizione errore",
"error_code": "SPECIFIC_ERROR_CODE",
"details": {
// eventuali dettagli aggiuntivi
}
}


**Codici di errore comuni**:
- `UNAUTHORIZED`: sessione non valida / token HMAC non valido
- `FORBIDDEN`: accesso negato (es. portfolio non appartiene all'utente)
- `NOT_FOUND`: risorsa non trovata
- `VALIDATION_ERROR`: validazione input fallita
- `CONFLICT`: conflitto logico (es. SELL > quantità posseduta)
- `INTERNAL_SERVER_ERROR`: errore server

---

## 8. Rate limiting

- **Login**: 5 tentativi falliti per email/IP in 15 minuti.
- **API generiche**: 100 richieste per IP in 60 secondi (header `X-RateLimit-*` in risposta).

---

## 9. CORS e sicurezza header

In produzione, configurare:

Access-Control-Allow-Origin: https://portfolio.example.com
Access-Control-Allow-Methods: GET, POST, PUT, DELETE
Access-Control-Allow-Headers: Content-Type, X-CSRF-Token
X-Content-Type-Options: nosniff
X-Frame-Options: DENY
Strict-Transport-Security: max-age=31536000; includeSubDomains


---

## 10. Testing

Per testare gli endpoint in locale:

Login
curl -X POST http://localhost:8080/api/auth/login
-H "Content-Type: application/json"
-d '{
"email": "mario.rossi@example.com",
"password": "SecureP@ss123456"
}'

Lista holdings
curl -X GET "http://localhost:8080/api/holdings?portfolio_id=1"
-H "Cookie: etf_portfolio_session=..."

Crea transazione
curl -X POST http://localhost:8080/api/transactions
-H "Content-Type: application/json"
-H "Cookie: etf_portfolio_session=..."
-d '{
"portfolio_id": 1,
"isin": "IE00B4L5Y983",
"type": "BUY",
"transaction_date": "2025-11-24",
"quantity": 50,
"price": 98.75,
"total_amount": 4937.50
}'


---
