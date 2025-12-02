<?php
/**
 * Signal Generator API Endpoint
 *
 * Endpoint per la generazione automatica di segnali di trading via n8n workflow
 *
 * Endpoints:
 * - POST /api/signals.php                    → Genera segnali per tutti i holdings
 * - POST /api/signals.php?portfolio_id=X     → Genera segnali per portfolio specifico
 * - POST /api/signals.php?holding_id=X       → Genera segnali per singolo holding
 * - GET  /api/signals.php?status=ACTIVE      → Verifica stato segnali attivi
 * - GET  /api/signals.php?statistics=true    → Statistiche su segnali generati
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../lib/Database/DatabaseManager.php';
require_once __DIR__ . '/../lib/Database/Repositories/HoldingRepository.php';
require_once __DIR__ . '/../lib/Database/Repositories/RecommendationRepository.php';
require_once __DIR__ . '/../lib/Services/SignalGeneratorService.php';

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

// Rate limiting per proteggere il signal generator
$rateLimitFile = __DIR__ . '/../logs/signals_rate_limit.json';
$clientIp = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
$currentTime = time();
$rateLimitWindow = 3600; // 1 ora
$maxRequests = 10; // Max 10 richieste per ora

$rateLimitData = [];
if (file_exists($rateLimitFile)) {
    $rateLimitData = json_decode(file_get_contents($rateLimitFile), true) ?? [];
}

// Pulisci vecchi record
$rateLimitData = array_filter($rateLimitData, function($record) use ($currentTime, $rateLimitWindow) {
    return ($currentTime - $record['timestamp']) < $rateLimitWindow;
});

// Conta richieste IP corrente
$clientRequests = array_filter($rateLimitData, function($record) use ($clientIp) {
    return $record['ip'] === $clientIp;
});

if (count($clientRequests) >= $maxRequests) {
    http_response_code(429);
    echo json_encode([
        'success' => false,
        'error' => 'Rate limit exceeded. Max 10 requests per hour for signal generation.'
    ]);
    exit;
}

// Registra richiesta corrente
$rateLimitData[] = [
    'ip' => $clientIp,
    'timestamp' => $currentTime,
    'endpoint' => 'signals'
];

file_put_contents($rateLimitFile, json_encode($rateLimitData));

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

try {
    // Initialize repositories
    $db = DatabaseManager::getInstance();
    $holdingRepo = new HoldingRepository($db);
    $recommendationRepo = new RecommendationRepository($db);

    $method = $_SERVER['REQUEST_METHOD'];
    $input = json_decode(file_get_contents('php://input'), true) ?? [];

    // Portfolio ID di default
    $portfolioId = isset($_GET['portfolio_id']) ? (int)$_GET['portfolio_id'] : 1;

    // ============================================
    // POST - Genera segnali
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
            // Validazione input
            $analysisType = $input['analysis_type'] ?? 'daily_generation';
            $sessionType = $input['session_type'] ?? 'n8n_scheduled';
            $confidenceThreshold = $input['confidence_threshold'] ?? 60;
            $includeRebalance = $input['include_rebalance'] ?? true;
            $maxSignals = $input['max_signals'] ?? null;
            $holdingId = isset($_GET['holding_id']) ? (int)$_GET['holding_id'] : null;

            if ($confidenceThreshold < 0 || $confidenceThreshold > 100) {
                throw new Exception('Confidence threshold must be between 0 and 100');
            }

            // Log richiesta
            $logMessage = "Signal generation request: type=$analysisType, session=$sessionType, threshold=$confidenceThreshold";
            if ($holdingId) {
                $logMessage .= ", holding_id=$holdingId";
            }
            error_log("[SIGNALS_API] " . $logMessage);

            // Inizializza SignalGeneratorService
            $signalGenerator = new SignalGeneratorService($recommendationRepo, $holdingRepo, $portfolioId);

            // Configura parametri
            $params = [
                'portfolio_id' => $portfolioId,
                'analysis_type' => $analysisType,
                'session_type' => $sessionType,
                'confidence_threshold' => $confidenceThreshold,
                'include_rebalance' => $includeRebalance,
                'max_signals' => $maxSignals,
                'holding_id' => $holdingId,
                'source' => 'api_n8n'
            ];

            // Genera segnali con il nuovo metodo
            $result = $signalGenerator->generateSignalsWithParams($params);

            // Log risultati
            error_log("[SIGNALS_API] Generated " . count($result['recommendations']) . " signals");

            echo json_encode([
                'success' => true,
                'message' => 'Signals generated successfully',
                'data' => $result,
                'metadata' => [
                    'generated_at' => date('Y-m-d H:i:s'),
                    'session_id' => uniqid('sig_'),
                    'parameters' => $params
                ]
            ]);

        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => 'Signal generation failed',
                'message' => $e->getMessage()
            ]);
            error_log("[SIGNALS_API] Error generating signals: " . $e->getMessage());
        }
        exit;
    }

    // ============================================
    // GET - Verifica stato segnali
    // ============================================
    if ($method === 'GET') {
        try {
            // Verifica se richieste statistiche
            if (isset($_GET['statistics']) && $_GET['statistics'] === 'true') {
                $stats = $recommendationRepo->getStatistics($portfolioId);

                // Aggiungi informazioni aggiuntive per monitoring
                $stats['recent_signals'] = $recommendationRepo->getRecentRecommendations($portfolioId, 1);
                $stats['expired_count'] = count($recommendationRepo->getExpiredRecommendations($portfolioId));

                echo json_encode([
                    'success' => true,
                    'data' => $stats,
                    'timestamp' => date('Y-m-d H:i:s')
                ]);
                exit;
            }

            // Filtri disponibili
            $filters = [];

            if (isset($_GET['status']) && in_array($_GET['status'], ['ACTIVE', 'EXECUTED', 'EXPIRED', 'IGNORED'])) {
                $filters['status'] = $_GET['status'];
            }

            if (isset($_GET['holding_id']) && is_numeric($_GET['holding_id'])) {
                $filters['holding_id'] = (int)$_GET['holding_id'];
            }

            if (isset($_GET['urgency']) && in_array($_GET['urgency'], ['IMMEDIATO', 'QUESTA_SETTIMANA', 'PROSSIME_2_SETTIMANE', 'MONITORAGGIO'])) {
                $filters['urgency'] = $_GET['urgency'];
            }

            // Recupera segnali con filtri
            $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
            $perPage = isset($_GET['per_page']) ? min(50, max(1, (int)$_GET['per_page'])) : 20;

            $result = $recommendationRepo->getFilteredRecommendations(
                $portfolioId,
                $filters,
                $page,
                $perPage,
                'created_at',
                'DESC'
            );

            echo json_encode([
                'success' => true,
                'data' => $result['data'],
                'pagination' => [
                    'page' => $page,
                    'per_page' => $perPage,
                    'total' => $result['total'],
                    'pages' => ceil($result['total'] / $perPage)
                ],
                'filters' => $filters,
                'timestamp' => date('Y-m-d H:i:s')
            ]);

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => 'Failed to retrieve signals status',
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
    error_log("[SIGNALS_API] Fatal error: " . $e->getMessage());
}