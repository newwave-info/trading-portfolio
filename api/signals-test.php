<?php
/**
 * Signal Generator API Endpoint - TEST MODE
 *
 * Versione di test senza HMAC authentication per sviluppo
 * DA USARE SOLO IN AMBIENTE DI TEST!
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../lib/Database/DatabaseManager.php';
require_once __DIR__ . '/../lib/Database/Repositories/HoldingRepository.php';
require_once __DIR__ . '/../lib/Database/Repositories/RecommendationRepository.php';
require_once __DIR__ . '/../lib/Services/SignalGeneratorService.php';

// CORS headers - configurare per produzione
$allowed_origins = [
    'http://localhost',
    'https://portfolio.newwave-media.it',
    'https://n8n.newwave-media.it'
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

// MESSAGGIO DI AVVISO - QUESTO È UN ENDPOINT DI TEST
echo json_encode([
    'warning' => 'TEST MODE - HMAC authentication disabled',
    'message' => 'Usare solo per sviluppo. Per produzione, configurare HMAC authentication.',
    'documentation' => 'Vedere docs/10-N8N-WORKFLOWS-PHASE5.md per configurazione HMAC'
]);

try {
    $db = DatabaseManager::getInstance();
    $holdingRepo = new HoldingRepository($db);
    $recommendationRepo = new RecommendationRepository($db);

    $method = $_SERVER['REQUEST_METHOD'];
    $input = json_decode(file_get_contents('php://input'), true) ?? [];
    $portfolioId = isset($_GET['portfolio_id']) ? (int)$_GET['portfolio_id'] : 1;

    // ============================================
    // POST - Genera segnali (SENZA HMAC per test)
    // ============================================
    if ($method === 'POST') {
        try {
            // Log di avviso
            error_log("[SIGNALS_TEST] Test mode request received from IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));

            $analysisType = $input['analysis_type'] ?? 'daily_generation';
            $sessionType = $input['session_type'] ?? 'n8n_test';
            $confidenceThreshold = $input['confidence_threshold'] ?? 60;
            $includeRebalance = $input['include_rebalance'] ?? true;
            $maxSignals = $input['max_signals'] ?? null;
            $holdingId = isset($_GET['holding_id']) ? (int)$_GET['holding_id'] : null;

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
                'source' => 'n8n_test'
            ];

            // Genera segnali
            $result = $signalGenerator->generateSignalsWithParams($params);

            echo json_encode([
                'success' => true,
                'message' => 'Signals generated successfully (TEST MODE)',
                'data' => $result,
                'metadata' => [
                    'generated_at' => date('Y-m-d H:i:s'),
                    'session_id' => uniqid('test_'),
                    'parameters' => $params,
                    'test_mode' => true,
                    'warning' => 'This is a test endpoint - use /api/signals.php for production'
                ]
            ]);

        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => 'Signal generation failed (TEST MODE)',
                'message' => $e->getMessage()
            ]);
            error_log("[SIGNALS_TEST] Error generating signals: " . $e->getMessage());
        }
        exit;
    }

    // ============================================
    // GET - Verifica stato segnali (senza auth)
    // ============================================
    if ($method === 'GET') {
        try {
            if (isset($_GET['statistics']) && $_GET['statistics'] === 'true') {
                $stats = $recommendationRepo->getStatistics($portfolioId);
                $stats['recent_signals'] = $recommendationRepo->getRecentRecommendations($portfolioId, 1);
                $stats['test_mode'] = true;

                echo json_encode([
                    'success' => true,
                    'data' => $stats,
                    'timestamp' => date('Y-m-d H:i:s'),
                    'test_mode' => true
                ]);
                exit;
            }

            // Resto del codice GET uguale a signals.php ma con test_mode flag
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
                'timestamp' => date('Y-m-d H:i:s'),
                'test_mode' => true
            ]);

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => 'Failed to retrieve signals status (TEST MODE)',
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
        'error' => 'Method not supported. Use GET or POST.',
        'test_mode' => true
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Internal server error (TEST MODE)',
        'message' => $e->getMessage()
    ]);
    error_log("[SIGNALS_TEST] Fatal error: " . $e->getMessage());
}