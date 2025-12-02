<?php
/**
 * API Endpoints - Recommendations Management
 *
 * Endpoints:
 * - GET    /api/recommendations.php                    → Lista raccomandazioni con filtri
 * - GET    /api/recommendations.php?id=X                → Dettaglio singola raccomandazione
 * - POST   /api/recommendations.php                     → Crea nuova raccomandazione (da SignalGenerator)
 * - PUT    /api/recommendations.php?id=X                → Aggiorna raccomandazione (status, note)
 * - DELETE /api/recommendations.php?id=X                → Elimina raccomandazione (soft delete)
 * - GET    /api/recommendations.php?status=ACTIVE       → Filtra per status
 * - GET    /api/recommendations.php?holding_id=X        → Filtra per holding
 * - GET    /api/recommendations.php?urgency=IMMEDIATE   → Filtra per urgenza
 * - GET    /api/recommendations.php?statistics=true     → Statistiche aggregate
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../lib/Database/DatabaseManager.php';
require_once __DIR__ . '/../lib/Database/Repositories/RecommendationRepository.php';
require_once __DIR__ . '/../lib/Database/Repositories/HoldingRepository.php';
require_once __DIR__ . '/../lib/Models/Recommendation.php';

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

header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Access-Control-Max-Age: 86400');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

try {
    // Rate limiting semplice (per produzione usare Redis/memcached)
    $rateLimitFile = __DIR__ . '/../logs/api_rate_limit.json';
    $clientIp = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $currentTime = time();
    $rateLimitWindow = 60; // 1 minuto
    $maxRequests = 60; // 60 richieste per minuto

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
            'error' => 'Troppe richieste. Riprova tra un minuto.'
        ]);
        exit;
    }

    // Registra richiesta corrente
    $rateLimitData[] = [
        'ip' => $clientIp,
        'timestamp' => $currentTime,
        'endpoint' => 'recommendations'
    ];

    // Salva aggiornamenti
    file_put_contents($rateLimitFile, json_encode($rateLimitData));

    // Initialize repositories
    $db = DatabaseManager::getInstance();
    $recommendationRepo = new RecommendationRepository($db);
    $holdingRepo = new HoldingRepository($db);

    $method = $_SERVER['REQUEST_METHOD'];
    $input = json_decode(file_get_contents('php://input'), true) ?? [];

    // Portfolio ID di default (single portfolio)
    $portfolioId = 1;

    // Logging funzione
    function logApiCall($method, $endpoint, $status, $message = '', $data = null) {
        $logFile = __DIR__ . '/../logs/api_recommendations.log';
        $timestamp = date('Y-m-d H:i:s');
        $clientIp = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';

        $logEntry = "[$timestamp] $method $endpoint - Status: $status - IP: $clientIp - Agent: $userAgent";
        if ($message) {
            $logEntry .= " - Message: $message";
        }
        if ($data) {
            $logEntry .= " - Data: " . json_encode($data);
        }
        $logEntry .= PHP_EOL;

        file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
    }

    // Validazione input base
    function validateInput($data, $rules) {
        $errors = [];
        foreach ($rules as $field => $rule) {
            if (isset($rule['required']) && $rule['required'] && !isset($data[$field])) {
                $errors[] = "Campo obbligatorio mancante: $field";
                continue;
            }

            if (isset($data[$field])) {
                // Validazione tipo
                if (isset($rule['type'])) {
                    switch ($rule['type']) {
                        case 'int':
                            if (!is_numeric($data[$field]) || (int)$data[$field] != $data[$field]) {
                                $errors[] = "Campo $field deve essere un numero intero";
                            }
                            break;
                        case 'float':
                            if (!is_numeric($data[$field])) {
                                $errors[] = "Campo $field deve essere un numero";
                            }
                            break;
                        case 'string':
                            if (!is_string($data[$field])) {
                                $errors[] = "Campo $field deve essere una stringa";
                            }
                            break;
                    }
                }

                // Validazione range
                if (isset($rule['min']) && $data[$field] < $rule['min']) {
                    $errors[] = "Campo $field deve essere >= {$rule['min']}";
                }
                if (isset($rule['max']) && $data[$field] > $rule['max']) {
                    $errors[] = "Campo $field deve essere <= {$rule['max']}";
                }

                // Validazione enum
                if (isset($rule['enum']) && !in_array($data[$field], $rule['enum'])) {
                    $errors[] = "Campo $field deve essere uno di: " . implode(', ', $rule['enum']);
                }
            }
        }
        return $errors;
    }

    // ============================================
    // GET - Lista raccomandazioni o dettaglio
    // ============================================
    if ($method === 'GET') {
        // Se richiesto specifico ID
        if (isset($_GET['id']) && is_numeric($_GET['id'])) {
            $recommendation = $recommendationRepo->findById((int)$_GET['id'], $portfolioId);

            if ($recommendation) {
                $response = [
                    'success' => true,
                    'data' => $recommendation
                ];
                logApiCall($method, '/api/recommendations.php?id=' . $id, 200, 'Dettaglio raccomandazione');
                echo json_encode($response);
            } else {
                http_response_code(404);
                $response = [
                    'success' => false,
                    'error' => 'Raccomandazione non trovata'
                ];
                logApiCall($method, '/api/recommendations.php?id=' . $id, 404, 'Raccomandazione non trovata');
                echo json_encode($response);
            }
            exit;
        }

        // Se richieste statistiche
        if (isset($_GET['statistics']) && $_GET['statistics'] === 'true') {
            $stats = $recommendationRepo->getStatistics($portfolioId);
            echo json_encode([
                'success' => true,
                'data' => $stats
            ]);
            exit;
        }

        // Lista raccomandazioni con filtri
        $filters = [];

        // Filtro per status
        if (isset($_GET['status']) && in_array($_GET['status'], ['ACTIVE', 'EXECUTED', 'EXPIRED', 'IGNORED'])) {
            $filters['status'] = $_GET['status'];
        }

        // Filtro per holding_id
        if (isset($_GET['holding_id']) && is_numeric($_GET['holding_id'])) {
            $filters['holding_id'] = (int)$_GET['holding_id'];
        }

        // Filtro per urgenza
        if (isset($_GET['urgency']) && in_array($_GET['urgency'], ['IMMEDIATO', 'QUESTA_SETTIMANA', 'PROSSIME_2_SETTIMANE', 'MONITORAGGIO'])) {
            $filters['urgency'] = $_GET['urgency'];
        }

        // Paginazione
        $page = isset($_GET['page']) && is_numeric($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $perPage = isset($_GET['per_page']) && is_numeric($_GET['per_page']) ? min(100, max(1, (int)$_GET['per_page'])) : 20;

        // Ordinamento
        $orderBy = isset($_GET['order_by']) && in_array($_GET['order_by'], ['created_at', 'confidence_score', 'urgency'])
            ? $_GET['order_by']
            : 'created_at';
        $orderDir = isset($_GET['order_dir']) && strtoupper($_GET['order_dir']) === 'ASC' ? 'ASC' : 'DESC';

        // Recupera raccomandazioni con filtri
        $result = $recommendationRepo->getFilteredRecommendations(
            $portfolioId,
            $filters,
            $page,
            $perPage,
            $orderBy,
            $orderDir
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
            'filters' => $filters
        ]);
        exit;
    }

    // ============================================
    // POST - Crea nuova raccomandazione
    // ============================================
    if ($method === 'POST' && !isset($_GET['action'])) {
        try {
            // Validazione input avanzata
            $validationRules = [
                'type' => ['required' => true, 'type' => 'string', 'enum' => ['BUY_LIMIT', 'BUY_MARKET', 'SELL_PARTIAL', 'SELL_ALL', 'SET_STOP_LOSS', 'SET_TAKE_PROFIT', 'REBALANCE']],
                'holding_id' => ['required' => true, 'type' => 'int', 'min' => 1],
                'urgency' => ['type' => 'string', 'enum' => ['IMMEDIATO', 'QUESTA_SETTIMANA', 'PROSSIME_2_SETTIMANE', 'MONITORAGGIO']],
                'quantity' => ['type' => 'float', 'min' => 0],
                'trigger_price' => ['type' => 'float', 'min' => 0],
                'confidence_score' => ['type' => 'float', 'min' => 0, 'max' => 100],
                'stop_loss' => ['type' => 'float', 'min' => 0],
                'take_profit' => ['type' => 'float', 'min' => 0]
            ];

            $validationErrors = validateInput($input, $validationRules);
            if (!empty($validationErrors)) {
                throw new Exception('Validazione fallita: ' . implode(', ', $validationErrors));
            }

            // Crea oggetto Recommendation
            $recommendation = new Recommendation([
                'portfolio_id' => $portfolioId,
                'holding_id' => $input['holding_id'],
                'type' => $input['type'],
                'urgency' => $input['urgency'] ?? 'MONITORAGGIO',
                'quantity' => $input['quantity'] ?? null,
                'trigger_price' => $input['trigger_price'] ?? null,
                'trigger_condition' => $input['trigger_condition'] ?? 'market_order',
                'stop_loss' => $input['stop_loss'] ?? null,
                'take_profit' => $input['take_profit'] ?? null,
                'rationale_primary' => $input['rationale_primary'] ?? '',
                'rationale_technical' => $input['rationale_technical'] ?? '',
                'confidence_score' => $input['confidence_score'] ?? 50,
                'expires_at' => $input['expires_at'] ?? date('Y-m-d H:i:s', strtotime('+7 days'))
            ]);

            // Valida la raccomandazione
            $errors = $recommendation->validate();
            if (!empty($errors)) {
                throw new Exception('Validazione fallita: ' . implode(', ', $errors));
            }

            // Salva nel database
            $id = $recommendationRepo->create($recommendation->toArray());

            echo json_encode([
                'success' => true,
                'message' => 'Raccomandazione creata con successo',
                'data' => ['id' => $id]
            ]);

        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
        exit;
    }

    // ============================================
    // PUT - Aggiorna raccomandazione
    // ============================================
    if ($method === 'PUT' && isset($_GET['id']) && is_numeric($_GET['id'])) {
        try {
            $id = (int)$_GET['id'];

            // Validazione campi aggiornabili
            $allowedFields = ['status', 'notes', 'executed_at', 'executed_price', 'executed_quantity'];
            $validationRules = [
                'status' => ['type' => 'string', 'enum' => ['ACTIVE', 'EXECUTED', 'EXPIRED', 'IGNORED']],
                'executed_price' => ['type' => 'float', 'min' => 0],
                'executed_quantity' => ['type' => 'float', 'min' => 0],
                'notes' => ['type' => 'string']
            ];

            $updateData = [];
            foreach ($allowedFields as $field) {
                if (isset($input[$field])) {
                    // Valida campo se ha regole di validazione
                    if (isset($validationRules[$field])) {
                        $fieldErrors = validateInput([$field => $input[$field]], [$field => $validationRules[$field]]);
                        if (!empty($fieldErrors)) {
                            throw new Exception('Validazione fallita: ' . implode(', ', $fieldErrors));
                        }
                    }
                    $updateData[$field] = $input[$field];
                }
            }

            if (empty($updateData)) {
                throw new Exception('Nessun campo valido da aggiornare');
            }

            // Aggiungi timestamp aggiornamento
            $updateData['updated_at'] = date('Y-m-d H:i:s');

            $success = $recommendationRepo->update($id, $portfolioId, $updateData);

            if ($success) {
                // Log azione se status cambiato
                if (isset($input['status']) && in_array($input['status'], ['EXECUTED', 'IGNORED'])) {
                    $recommendationRepo->logAction($id, $input['status'], $input['notes'] ?? '');
                }

                echo json_encode([
                    'success' => true,
                    'message' => 'Raccomandazione aggiornata con successo'
                ]);
            } else {
                throw new Exception('Aggiornamento fallito o raccomandazione non trovata');
            }

        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
        exit;
    }

    // ============================================
    // DELETE - Elimina raccomandazione (soft delete)
    // ============================================
    if ($method === 'DELETE' && isset($_GET['id']) && is_numeric($_GET['id'])) {
        try {
            $id = (int)$_GET['id'];

            // Soft delete: setta is_active = 0
            $success = $recommendationRepo->softDelete($id, $portfolioId);

            if ($success) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Raccomandazione eliminata con successo'
                ]);
            } else {
                throw new Exception('Eliminazione fallita o raccomandazione non trovata');
            }

        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
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
        'error' => 'Metodo HTTP non supportato'
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Errore interno del server',
        'message' => $e->getMessage()
    ]);
}