# 02 – Gestione Utenti

Questo documento descrive il sistema di autenticazione, gestione sessioni, sicurezza delle password e controllo di accesso per **ETF Portfolio Manager**.

---

## 1. Requisiti funzionali

- Registrazione di nuovi utenti con email e password.
- Login/logout con validazione credenziali.
- Sessioni persistenti e sicure (durata, invalidazione, cookie).
- Recupero password (opzionale, nelle estensioni future).
- Profilo utente (nome completo, contatti, preferenze).
- Logica di isolamento: ogni utente vede solo i propri dati (portafogli, holdings, transazioni).

---

## 2. Modello dati

### 2.1 Tabella `utente.users`

CREATE TABLE utente.users (
id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
email VARCHAR(255) NOT NULL UNIQUE,
password_hash VARCHAR(255) NOT NULL COMMENT 'Argon2id hash + pepper',
full_name VARCHAR(255) DEFAULT NULL,
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
last_login TIMESTAMP NULL DEFAULT NULL,
is_active BOOLEAN DEFAULT TRUE COMMENT 'Soft delete',
INDEX idx_email (email),
INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


**Note:**
- `id`: chiave primaria univoca per ogni utente.
- `email`: unica nel sistema, usata come identificativo per login.
- `password_hash`: hash calcolato via `password_hash()` (PHP 7.2+).
- `full_name`: nome visualizzato nelle viste (opzionale).
- `created_at`: timestamp di registrazione.
- `last_login`: traccia ultimo accesso (utile per analisi d'uso).
- `is_active`: soft delete; utenti disattivati non possono più accedere.

---

## 3. Flusso di registrazione

### 3.1 Pagina di registrazione

**URL**: `GET /register.php`

Presenta form con campi:
- Email (validazione: formato email RFC 5322, lunghezza < 255)
- Password (validazione: lunghezza ≥ 12 caratteri, complessità, no sequenze comuni)
- Conferma Password
- Full Name (opzionale)

**Validazioni lato client (JS)**:
- email valida
- password ≥ 12 char
- password == confermazione
- no spazi in eccesso

### 3.2 Endpoint di registrazione

**POST** `/api/auth/register`

**Payload**:
{
"email": "mario.rossi@example.com",
"password": "SecureP@ss123456",
"full_name": "Mario Rossi"
}


**Validazioni lato server**:
1. Email:
   - formato valido (regex o filter_var)
   - lunghezza < 255
   - non già registrata (query `SELECT COUNT(*) FROM utente.users WHERE email = ?`)
2. Password:
   - lunghezza ≥ 12 caratteri
   - complessità (almeno 3 di: maiuscole, minuscole, numeri, simboli)
   - non contiene sequenze banali (es. "password", "123456")
   - check contro password-breach database (opzionale, es. have-i-been-pwned API)
3. Full Name (se fornito):
   - lunghezza ≤ 255
   - caratteri UTF-8 validi.

**Hashing password**:
// Generare hash con Argon2id (default in password_hash da PHP 7.2.1)
$passwordHash = password_hash(
$password . $pepper,
PASSWORD_ARGON2ID,
[
'memory_cost' => 65536,
'time_cost' => 4,
'threads' => 2
]
);


**Pepper** (costante in config, NON in DB):
define('PASSWORD_PEPPER', 'your_secret_pepper_64_chars_here...');


**Risposta (201 Created)**:
{
"success": true,
"message": "Utente registrato con successo",
"user_id": 1
}


**Errori comuni (HTTP 400, 409, 422)**:
- `400`: validazione client fallita (email non valida, password debole)
- `409`: email già registrata
- `422`: dati incompleti

---

## 4. Flusso di login

### 4.1 Pagina di login

**URL**: `GET /login.php`

Presenta form con:
- Email
- Password
- Remember me (checkbox, opzionale, per sessioni estese)

### 4.2 Endpoint di login

**POST** `/api/auth/login`

**Payload**:
{
"email": "mario.rossi@example.com",
"password": "SecureP@ss123456",
"remember_me": false
}


**Validazioni**:
1. Email e password non vuoti.
2. Query: `SELECT id, password_hash FROM utente.users WHERE email = ? AND is_active = TRUE`
3. Se no result: log tentativo fallito (per rate limiting) e ritorna errore generico.
4. Se trovato:
   - verifica password con `password_verify($password . $pepper, $storedHash)`
   - se non valida: log tentativo fallito, errore generico.

### 4.3 Creazione sessione

Dopo verifica password valida:

// 1. Rigenerazione SID (prevenire session fixation)
session_regenerate_id(true);

// 2. Memorizzare dati essenziali in $_SESSION
$_SESSION['user_id'] = $userId;
$_SESSION['email'] = $email;
$_SESSION['full_name'] = $fullName;
$_SESSION['logged_in'] = true;
$_SESSION['login_time'] = time();

// 3. Impostare cookie di sessione con flag di sicurezza
session_set_cookie_params([
'lifetime' => 0, // Scade con browser
'path' => '/',
'domain' => '', // Default (host corrente)
'secure' => true, // HTTPS only (in production)
'httponly' => true, // No accesso JavaScript
'samesite' => 'Strict' // CSRF protection
]);

// 4. Aggiornare last_login
$updateStmt = $pdo->prepare('UPDATE utente.users SET last_login = NOW() WHERE id = ?');
$updateStmt->execute([$userId]);


**Risposta (200 OK)**:
{
"success": true,
"message": "Login completato",
"user_id": 1,
"email": "mario.rossi@example.com"
}


**Rate limiting**:
- Massimo 5 tentativi falliti per email/IP in 15 minuti.
- Dopo 5 fallimenti: bloccare per 15 minuti.
- Implementare usando cache locale o Redis.

### 4.4 Remember me (opzionale)

Se `remember_me = true` e password valida:
- Creare un token random (64 bytes hex).
- Salvare in tabella separata `utente.remember_tokens`:
CREATE TABLE utente.remember_tokens (
id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
user_id INT UNSIGNED NOT NULL,
token_hash VARCHAR(255) NOT NULL,
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
expires_at TIMESTAMP DEFAULT (DATE_ADD(NOW(), INTERVAL 30 DAY)),
FOREIGN KEY (user_id) REFERENCES utente.users(id) ON DELETE CASCADE,
INDEX idx_user_id (user_id)
);

- Inviare token nel cookie `remember_token` (secure, httponly, samesite=strict).
- Al prossimo caricamento, se sessione scaduta ma remember_token valido:
- validare token
- rigenerare sessione (come sopra)
- aggiornare `last_login`.

---

## 5. Flusso di logout

### 5.1 Endpoint logout

**POST** `/api/auth/logout`

// 1. Se presente remember_token, invalida nella DB
if (isset($_COOKIE['remember_token'])) {
$deleteStmt = $pdo->prepare('DELETE FROM utente.remember_tokens WHERE token_hash = ?');
$deleteStmt->execute([hash('sha256', $_COOKIE['remember_token'])]);
}

// 2. Pulisci $_SESSION
session_destroy();

// 3. Cancella cookie di sessione
setcookie(session_name(), '', time() - 3600, '/');
setcookie('remember_token', '', time() - 3600, '/');


**Risposta (200 OK)**:
{
"success": true,
"message": "Logout completato"
}


---

## 6. Gestione sessioni

### 6.1 Configurazione session.ini

File di configurazione PHP (`php.ini` o `.htaccess`):

session.name = "etf_portfolio_session"
session.use_strict_mode = On ; Accetta solo SID conosciuti
session.use_cookies = On
session.use_only_cookies = On ; No session ID in URL
session.cookie_httponly = On ; No JavaScript access
session.cookie_secure = On ; HTTPS only (production)
session.cookie_samesite = "Strict" ; CSRF protection
session.cookie_lifetime = 0 ; Scade con browser
session.gc_maxlifetime = 86400 ; Garbage collect dopo 24h
session.sid_length = 64 ; 64 char SID (più sicuro)


### 6.2 Verifiche di sicurezza lato server

All'inizio di ogni request (middleware):

// File: src/lib/SessionManager.php

class SessionManager {
const SESSION_TIMEOUT = 86400; // 24 ore

public static function validateSession() {
    // 1. Verifica $_SESSION non vuota
    if (empty($_SESSION['user_id']) || empty($_SESSION['logged_in'])) {
        return false;
    }
    
    // 2. Verifica timeout inattività
    if (isset($_SESSION['login_time']) && 
        (time() - $_SESSION['login_time']) > self::SESSION_TIMEOUT) {
        session_destroy();
        return false;
    }
    
    // 3. Verifica User-Agent (prevenire hijacking)
    $currentUA = hash('sha256', $_SERVER['HTTP_USER_AGENT'] ?? '');
    if (!isset($_SESSION['user_agent_hash'])) {
        $_SESSION['user_agent_hash'] = $currentUA;
    } elseif ($_SESSION['user_agent_hash'] !== $currentUA) {
        // Log possibile hijacking
        error_log("Session hijack attempt: user_id={$_SESSION['user_id']}");
        session_destroy();
        return false;
    }
    
    // 4. Verifica utente esista ancora e sia attivo
    $stmt = $pdo->prepare('SELECT is_active FROM utente.users WHERE id = ?');
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    
    if (!$user || !$user['is_active']) {
        session_destroy();
        return false;
    }
    
    // 5. Aggiorna login_time (sliding expiration)
    $_SESSION['login_time'] = time();
    
    return true;
}
}


### 6.3 Integrazione nei routing

Ogni endpoint privato deve verificare sessione:

// File: src/api/dashboard.php

require_once '../lib/SessionManager.php';

if (!SessionManager::validateSession()) {
http_response_code(401);
echo json_encode(['error' => 'Unauthorized']);
exit;
}

$userId = $_SESSION['user_id'];
// ... logica endpoint


---

## 7. Controllo di accesso (Authorization)

### 7.1 Principio: user_id filtering

Ogni query deve filtrare per `user_id` della sessione corrente:

// ✅ CORRETTO: filtra per user_id
$stmt = $pdo->prepare(
'SELECT * FROM utente.holdings h
JOIN utente.portfolios p ON h.portfolio_id = p.id
WHERE p.user_id = ? AND h.id = ?'
);
$stmt->execute([$_SESSION['user_id'], $holdingId]);

// ❌ SBAGLIATO: non filtra per user_id (security issue!)
$stmt = $pdo->prepare('SELECT * FROM utente.holdings WHERE id = ?');
$stmt->execute([$holdingId]);


### 7.2 Middleware di autorizzazione

// File: src/lib/AuthorizationMiddleware.php

class AuthorizationMiddleware {
public static function requirePortfolioAccess($portfolioId) {
$stmt = $pdo->prepare(
'SELECT id FROM utente.portfolios WHERE id = ? AND user_id = ?'
);
$stmt->execute([$portfolioId, $_SESSION['user_id']]);

    if (!$stmt->fetch()) {
        http_response_code(403);
        echo json_encode(['error' => 'Access denied']);
        exit;
    }
}
}


Utilizzo:
AuthorizationMiddleware::requirePortfolioAccess($portfolioId);
// Prosegui con sicurezza che portfolio appartiene all'utente


---

## 8. Sicurezza aggiuntive

### 8.1 CSRF Token

Per form POST/PUT/DELETE esposti da template HTML:

// Generare token
session_start();
if (!isset($_SESSION['csrf_token'])) {
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<form method="POST" action="/api/holdings"> <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>"> <!-- altri campi --> </form> ```
Validazione lato server:

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_POST['csrf_token'] ?? '', $_SESSION['csrf_token'] ?? '')) {
        http_response_code(403);
        echo json_encode(['error' => 'CSRF token invalid']);
        exit;
    }
}
8.2 Input validation e sanitization
Sempre validare e sanitizzare input:

// ✅ Validazione email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    throw new Exception('Invalid email');
}

// ✅ Prepared statements (prevenire SQL injection)
$stmt = $pdo->prepare('SELECT * FROM utente.users WHERE email = ?');
$stmt->execute([$email]);

// ✅ Sanitizzazione output (prevenire XSS)
echo htmlspecialchars($fullName, ENT_QUOTES, 'UTF-8');
8.3 Logging di sicurezza
Registrare eventi sensibili:

// File: src/lib/SecurityLogger.php

class SecurityLogger {
    public static function logLoginAttempt($email, $success) {
        $log = [
            'timestamp' => date('Y-m-d H:i:s'),
            'event' => 'LOGIN_ATTEMPT',
            'email' => $email,
            'success' => $success,
            'ip' => $_SERVER['REMOTE_ADDR'],
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ];
        error_log(json_encode($log));
    }
    
    public static function logAccessDenied($userId, $resource) {
        $log = [
            'timestamp' => date('Y-m-d H:i:s'),
            'event' => 'ACCESS_DENIED',
            'user_id' => $userId,
            'resource' => $resource,
            'ip' => $_SERVER['REMOTE_ADDR']
        ];
        error_log(json_encode($log));
    }
}
9. Integrazione n8n con autenticazione backend
9.1 Webhook HMAC
n8n invia risultati di workflow al backend autenticati via HMAC-SHA256.

Endpoint ricevimento:

POST /api/analysis/results
Content-Type: application/json
X-Webhook-Signature: sha256=<HMAC>

{
  "workflow_id": "technical_analysis_daily",
  "timestamp": "2025-11-24T15:30:00Z",
  "results": [...]
}
Validazione HMAC lato backend:

// File: src/lib/HMACValidator.php

class HMACValidator {
    const WEBHOOK_SECRET = 'your_32_char_random_secret_from_env';
    
    public static function validateWebhook($payload, $signature) {
        // 1. Calcola HMAC del payload
        $computed = 'sha256=' . hash_hmac(
            'sha256',
            $payload,
            self::WEBHOOK_SECRET
        );
        
        // 2. Confronta timing-safe
        return hash_equals($computed, $signature);
    }
}

// Utilizzo in endpoint
$rawPayload = file_get_contents('php://input');
$signature = $_SERVER['HTTP_X_WEBHOOK_SIGNATURE'] ?? '';

if (!HMACValidator::validateWebhook($rawPayload, $signature)) {
    http_response_code(401);
    echo json_encode(['error' => 'Webhook signature invalid']);
    exit;
}

$data = json_decode($rawPayload, true);
// Processa risultati n8n
9.2 Configurazione n8n (lato workflow)
Nel workflow n8n, configurare il nodo HTTP POST:

URL: http://app:80/api/analysis/results
Method: POST
Headers:
  Content-Type: application/json
  X-Webhook-Signature: <formula HMAC>
Body: <JSON results>
Formula HMAC in n8n (JavaScript):

const crypto = require('crypto');
const secret = process.env.N8N_WEBHOOK_SECRET;
const payload = JSON.stringify(items.json);
const hmac = crypto.createHmac('sha256', secret)
    .update(payload)
    .digest('hex');
return {
    signature: 'sha256=' + hmac
};
10. Checklist sicurezza
 Password hashing con Argon2id (PHP 7.2+)

 Pepper configurato in costante (non in DB)

 Sessioni rigenerati dopo login

 Cookie secure, httponly, samesite=strict

 Rate limiting login (5 tentativi/15 min)

 User-Agent hashing per session hijack

 Tutte le query filtrate per user_id

 CSRF token su form POST/PUT/DELETE

 Input validation e sanitization

 Output escaping (prevenire XSS)

 Logging accessi e anomalie

 HMAC webhook n8n implementato e testato

 SSL/TLS in production

 WAF o fail2ban
