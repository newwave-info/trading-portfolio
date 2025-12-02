<?php
/**
 * System Alerts API Endpoint
 *
 * Gestisce alert di sistema, notifiche per segnali ad alta priorità,
 * e monitoring dei workflow n8n
 *
 * Endpoints:
 * - POST /api/alerts.php                    → Crea nuovo alert
 * - POST /api/alerts.php?type=high-priority  → Alert per segnali urgenti
 * - POST /api/alerts.php?type=system-health  → Alert per problemi di sistema
 * - POST /api/alerts.php?type=rate-limit     → Alert per rate limiting
 * - GET  /api/alerts.php?status=active       → Lista alert attivi
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../lib/Database/DatabaseManager.php';
require_once __DIR__ . '/../lib/Database/Repositories/RecommendationRepository.php';

// CORS headers - configurare per produzione
$allowed_origins = [
    'http://localhost',
    'https://your-domain.com',
    'https://trading-portfolio.example.com'
];

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if (in_array($origin, $allowed_origins)) {
    header("Access-Control-Allow-Origin: $origin");
}

header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Webhook-Signature, X-Request-Timestamp');
header('Access-Control-Max-Age: 86400');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

/**
 * HMAC Authentication for n8n integration
 */
function validateHmacSignature($payload, $signature, $timestamp) {
    $secret = $_ENV['N8N_WEBHOOK_SECRET'] ?? 'default_secret_change_in_env';
    $maxTimeDiff = 300; // 5 minuti tolleranza

    // Verifica timestamp
    $currentTime = time();
    if (abs($currentTime - $timestamp) > $maxTimeDiff) {
        return false;
    }

    // Calcola HMAC
    $expectedSignature = hash_hmac('sha256', $timestamp . ':' . $payload, $secret);

    // Confronta in modo sicuro
    return hash_equals('sha256=' . $expectedSignature, $signature);
}

/**
 * Send email notification
 */
function sendEmailAlert($to, $subject, $body, $priority = 'normal') {
    $headers = [
        'From: ' . ($_ENV['ALERT_EMAIL_FROM'] ?? 'noreply@trading-portfolio.local'),
        'Reply-To: ' . ($_ENV['ALERT_EMAIL_FROM'] ?? 'noreply@trading-portfolio.local'),
        'X-Priority: ' . ($priority === 'high' ? '1' : '3'),
        'Content-Type: text/html; charset=UTF-8'
    ];

    // In produzione, usare SMTP o servizio email dedicato
    return mail($to, $subject, $body, implode("\r\n", $headers));
}

/**
 * Send Telegram notification
 */
function sendTelegramAlert($message, $chatId = null, $botToken = null) {
    $chatId = $chatId ?? $_ENV['TELEGRAM_CHAT_ID'] ?? '';
    $botToken = $botToken ?? $_ENV['TELEGRAM_BOT_TOKEN'] ?? '';

    if (empty($chatId) || empty($botToken)) {
        error_log("[ALERTS] Telegram credentials not configured");
        return false;
    }

    $url = "https://api.telegram.org/bot{$botToken}/sendMessage";
    $data = [
        'chat_id' => $chatId,
        'text' => $message,
        'parse_mode' => 'HTML'
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode === 200) {
        error_log("[ALERTS] Telegram notification sent successfully");
        return true;
    } else {
        error_log("[ALERTS] Telegram notification failed. HTTP Code: $httpCode, Response: $response");
        return false;
    }
}

/**
 * Log alert to file
 */
function logAlert($type, $severity, $message, $data = null) {
    $logFile = __DIR__ . '/../logs/alerts.log';
    $timestamp = date('Y-m-d H:i:s');
    $logEntry = "[$timestamp] [$type] [$severity] $message";

    if ($data) {
        $logEntry .= " | Data: " . json_encode($data);
    }

    $logEntry .= PHP_EOL;
    file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
}

try {
    $db = DatabaseManager::getInstance();
    $recommendationRepo = new RecommendationRepository($db);

    $method = $_SERVER['REQUEST_METHOD'];
    $input = json_decode(file_get_contents('php://input'), true) ?? [];

    // ============================================
    // POST - Crea nuovo alert
    // ============================================
    if ($method === 'POST') {
        // Verifica HMAC se da n8n
        $signature = $_SERVER['HTTP_X_WEBHOOK_SIGNATURE'] ?? '';
        $timestamp = $_SERVER['HTTP_X_REQUEST_TIMESTAMP'] ?? 0;
        $payload = file_get_contents('php://input');

        if ($signature && !validateHmacSignature($payload, $signature, $timestamp)) {
            http_response_code(401);
            echo json_encode([
                'success' => false,
                'error' => 'Invalid HMAC signature'
            ]);
            exit;
        }

        try {
            // Determina tipo di alert
            $alertType = $_GET['type'] ?? 'generic';
            $severity = $input['severity'] ?? 'info';
            $message = $input['message'] ?? '';
            $data = $input['data'] ?? null;

            // Log alert
            logAlert($alertType, $severity, $message, $data);

            switch ($alertType) {
                case 'high-priority':
                    // Alert per segnali ad alta priorità
                    $recommendation = $data['recommendation'] ?? null;

                    if (!$recommendation) {
                        throw new Exception('Recommendation data required for high-priority alerts');
                    }

                    // Prepara messaggio email
                    $emailSubject = "[URGENTE] Segnale Trading - {$recommendation['ticker']} - {$recommendation['type']}";
                    $emailBody = "
<h2>Segnale di Trading Ad Alta Priorità</h2>
<p><strong>Ticker:</strong> {$recommendation['ticker']}</p>
<p><strong>Tipo:</strong> {$recommendation['type']}</p>
<p><strong>Urgenza:</strong> {$recommendation['urgency']}</p>
<p><strong>Confidenza:</strong> {$recommendation['confidence_score']}%</p>
<p><strong>Prezzo trigger:</strong> {$recommendation['trigger_price']}</p>
<p><strong>Razionale:</strong> {$recommendation['rationale_primary']}</p>
<p><strong>Scadenza:</strong> {$recommendation['expires_at']}</p>
<p><strong>Azione suggerita:</strong> {$recommendation['type']}</p>
                    ";

                    // Prepara messaggio Telegram
                    $telegramMessage = "
🚨 <b>SEGNALE URGENTE</b>

📈 {$recommendation['ticker']} - {$recommendation['type']}
⏰ Urgenza: {$recommendation['urgency']}
🎯 Confidenza: {$recommendation['confidence_score']}%
💰 Trigger: {$recommendation['trigger_price']}

{$recommendation['rationale_primary']}

⏱️ Scade: {$recommendation['expires_at']}
                    ";

                    // Invia notifiche
                    $emailSent = sendEmailAlert(
                        $_ENV['ALERT_EMAIL_TO'] ?? 'admin@example.com',
                        $emailSubject,
                        $emailBody,
                        'high'
                    );

                    $telegramSent = sendTelegramAlert($telegramMessage);

                    echo json_encode([
                        'success' => true,
                        'message' => 'High priority alert sent',
                        'notifications' => [
                            'email' => $emailSent,
                            'telegram' => $telegramSent
                        ]
                    ]);
                    break;

                case 'system-health':
                    // Alert per problemi di sistema
                    $health = $data['health'] ?? false;
                    $issues = $data['issues'] ?? [];

                    if (!$health && !empty($issues)) {
                        $emailSubject = "[SYSTEM] Problemi di sistema rilevati";
                        $emailBody = "
<h2>Problemi di Sistema Rilevati</h2>
<ul>
";
                        foreach ($issues as $issue) {
                            $emailBody .= "<li>$issue</li>\n";
                        }
                        $emailBody .= "</ul>\n<p>Timestamp: " . date('Y-m-d H:i:s') . "</p>";

                        $telegramMessage = "⚠️ <b>PROBLEMI DI SISTEMA</b>\n\n";
                        foreach ($issues as $issue) {
                            $telegramMessage .= "• $issue\n";
                        }

                        sendEmailAlert(
                            $_ENV['ALERT_EMAIL_TO'] ?? 'admin@example.com',
                            $emailSubject,
                            $emailBody
                        );
                        sendTelegramAlert($telegramMessage);
                    }

                    echo json_encode([
                        'success' => true,
                        'message' => 'System health alert processed'
                    ]);
                    break;

                case 'rate-limit':
                    // Alert per rate limiting
                    $rateLimitInfo = $data['rate_limit'] ?? [];
                    $endpoint = $rateLimitInfo['endpoint'] ?? 'unknown';
                    $clientIp = $rateLimitInfo['client_ip'] ?? 'unknown';
                    $retryAfter = $rateLimitInfo['retry_after'] ?? 60;

                    $emailSubject = "[RATE LIMIT] Soglia superata su $endpoint";
                    $emailBody = "
<h2>Rate Limiting Alert</h2>
<p><strong>Endpoint:</strong> $endpoint</p>
<p><strong>Client IP:</strong> $clientIp</p>
<p><strong>Retry after:</strong> $retryAfter secondi</p>
<p><strong>Timestamp:</strong> " . date('Y-m-d H:i:s') . "</p>
                    ";

                    sendEmailAlert(
                        $_ENV['ALERT_EMAIL_TO'] ?? 'admin@example.com',
                        $emailSubject,
                        $emailBody
                    );

                    echo json_encode([
                        'success' => true,
                        'message' => 'Rate limit alert processed'
                    ]);
                    break;

                default:
                    // Alert generico
                    echo json_encode([
                        'success' => true,
                        'message' => 'Generic alert logged'
                    ]);
                    break;
            }

        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => 'Failed to process alert',
                'message' => $e->getMessage()
            ]);
            error_log("[ALERTS_API] Error processing alert: " . $e->getMessage());
        }
        exit;
    }

    // ============================================
    // GET - Lista alert attivi (placeholder)
    // ============================================
    if ($method === 'GET') {
        try {
            // Per ora restituiamo un placeholder
            // In futuro potremmo implementare una tabella alerts nel database

            echo json_encode([
                'success' => true,
                'data' => [
                    'alerts' => [],
                    'message' => 'Alert system active - no alerts stored in database yet'
                ],
                'timestamp' => date('Y-m-d H:i:s')
            ]);

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => 'Failed to retrieve alerts',
                'message' => $e->getMessage()
            ]);
        }
        exit;
    }

    // ============================================
    // Metodo non supportato
    // ============================================
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'error' => 'Method not supported. Use GET or POST.'
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Internal server error',
        'message' => $e->getMessage()
    ]);
    error_log("[ALERTS_API] Fatal error: " . $e->getMessage());
}