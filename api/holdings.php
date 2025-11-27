<?php
/**
 * API Endpoints - Holdings Management
 *
 * Endpoints:
 * - GET    /api/holdings.php          → Lista holdings
 * - POST   /api/holdings.php          → Crea/aggiorna holding
 * - DELETE /api/holdings.php?ticker=X → Elimina holding
 * - POST   /api/holdings.php?action=import → Import CSV
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../lib/Database/DatabaseManager.php';
require_once __DIR__ . '/../lib/Database/Repositories/HoldingRepository.php';
require_once __DIR__ . '/../lib/Database/Repositories/PortfolioRepository.php';

// CORS headers (se necessario per sviluppo locale)
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

try {
    // Initialize repositories
    $db = DatabaseManager::getInstance();
    $holdingRepo = new HoldingRepository($db);
    $portfolioRepo = new PortfolioRepository($db);

    $method = $_SERVER['REQUEST_METHOD'];

    // ============================================
    // GET - Lista holdings
    // ============================================
    if ($method === 'GET') {
        $holdings = $holdingRepo->getEnrichedHoldings();

        echo json_encode([
            'success' => true,
            'data' => $holdings,
            'count' => count($holdings)
        ]);
        exit;
    }

    // ============================================
    // POST - Crea/aggiorna holding O Import CSV
    // ============================================
    if ($method === 'POST') {
        // Check se è import CSV
        if (isset($_GET['action']) && $_GET['action'] === 'import') {
            // Import CSV
            if (!isset($_FILES['csv_file'])) {
                throw new Exception('Nessun file CSV caricato');
            }

            $file = $_FILES['csv_file'];

            // Validazione file
            if ($file['error'] !== UPLOAD_ERR_OK) {
                throw new Exception('Errore upload file');
            }

            // Verifica estensione
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if ($ext !== 'csv') {
                throw new Exception('Il file deve essere in formato CSV');
            }

            // TODO: Implementa import CSV per MySQL
            throw new Exception('Import CSV non ancora implementato per MySQL');
        }

        // Altrimenti è un upsert holding normale
        $input = json_decode(file_get_contents('php://input'), true);

        if (!$input) {
            throw new Exception('Dati non validi');
        }

        // Validazione campi obbligatori
        $required = ['ticker', 'name', 'quantity', 'avg_price'];
        foreach ($required as $field) {
            if (!isset($input[$field]) || $input[$field] === '') {
                throw new Exception("Campo obbligatorio mancante: {$field}");
            }
        }

        $ticker = strtoupper(trim($input['ticker']));
        $isUpdate = isset($input['is_update']) ? (bool) $input['is_update'] : false;

        // Check duplicati se non è update
        if (!$isUpdate && $holdingRepo->findByTicker($ticker)) {
            throw new Exception('Ticker già presente. Usa Modifica per aggiornare la posizione.');
        }

        // Prepara dati holding
        $holdingData = [
            'ticker' => $ticker,
            'name' => trim($input['name']),
            'asset_class' => isset($input['asset_class']) ? trim($input['asset_class']) : 'ETF',
            'quantity' => (float) $input['quantity'],
            'avg_price' => (float) $input['avg_price'],
            'current_price' => isset($input['current_price']) ? (float) $input['current_price'] : null,
            'price_source' => isset($input['price_source']) ? trim($input['price_source']) : null,
        ];

        if ($isUpdate) {
            // Update existing holding
            $success = $holdingRepo->updateQuantityAndPrice(
                $ticker,
                $holdingData['quantity'],
                $holdingData['avg_price']
            );

            if ($holdingData['current_price']) {
                $holdingRepo->updatePrice(
                    $ticker,
                    $holdingData['current_price'],
                    $holdingData['price_source']
                );
            }
        } else {
            // Create new holding
            $success = $holdingRepo->createHolding($holdingData);
        }

        if ($success) {
            $updated = $holdingRepo->findByTicker($ticker);

            // Update portfolio timestamp
            $portfolioRepo->updateLastUpdate();

            echo json_encode([
                'success' => true,
                'data' => $updated,
                'message' => 'Posizione salvata con successo'
            ]);
        } else {
            throw new Exception('Errore salvataggio posizione');
        }
        exit;
    }

    // ============================================
    // DELETE - Elimina holding (soft delete)
    // ============================================
    if ($method === 'DELETE') {
        if (!isset($_GET['ticker'])) {
            throw new Exception('Ticker non specificato');
        }

        $ticker = strtoupper(trim($_GET['ticker']));
        $success = $holdingRepo->softDelete($ticker);

        if ($success) {
            // Update portfolio timestamp
            $portfolioRepo->updateLastUpdate();

            echo json_encode([
                'success' => true,
                'message' => 'Posizione eliminata con successo'
            ]);
        } else {
            throw new Exception('Errore eliminazione posizione');
        }
        exit;
    }

    // Metodo non supportato
    throw new Exception('Metodo non supportato');

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
