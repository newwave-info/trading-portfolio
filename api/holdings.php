<?php
/**
 * API Endpoints - Holdings Management
 *
 * Endpoints:
 * - GET    /api/holdings.php          → Lista holdings
 * - POST   /api/holdings.php          → Crea/aggiorna holding
 * - DELETE /api/holdings.php?isin=X   → Elimina holding
 * - POST   /api/holdings.php?action=import → Import CSV
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../lib/PortfolioManager.php';

// CORS headers (se necessario per sviluppo locale)
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

try {
    $portfolioManager = new PortfolioManager();
    $method = $_SERVER['REQUEST_METHOD'];

    // ============================================
    // GET - Lista holdings
    // ============================================
    if ($method === 'GET') {
        $holdings = $portfolioManager->getHoldings();

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

            // Salva temporaneamente
            $tmpPath = sys_get_temp_dir() . '/' . uniqid('portfolio_') . '.csv';
            move_uploaded_file($file['tmp_name'], $tmpPath);

            // Import
            $result = $portfolioManager->importFromCsv($tmpPath);

            // Rimuovi file temporaneo
            unlink($tmpPath);

            echo json_encode([
                'success' => $result['success'],
                'imported' => $result['imported'],
                'errors' => $result['errors'],
                'message' => $result['imported'] > 0
                    ? "Importate {$result['imported']} posizioni con successo"
                    : 'Nessuna posizione importata'
            ]);
            exit;
        }

        // Altrimenti è un upsert holding normale
        $input = json_decode(file_get_contents('php://input'), true);

        if (!$input) {
            throw new Exception('Dati non validi');
        }

        // Validazione campi obbligatori
        $required = ['isin', 'ticker', 'name', 'quantity', 'avg_price'];
        foreach ($required as $field) {
            if (!isset($input[$field]) || $input[$field] === '') {
                throw new Exception("Campo obbligatorio mancante: {$field}");
            }
        }

        // Sanitizzazione
        $holding = [
            'isin' => strtoupper(trim($input['isin'])),
            'ticker' => strtoupper(trim($input['ticker'])),
            'name' => trim($input['name']),
            'quantity' => (float) $input['quantity'],
            'avg_price' => (float) $input['avg_price'],
            'target_allocation' => isset($input['target_allocation']) ? (float) $input['target_allocation'] : 0.00,
            'notes' => isset($input['notes']) ? trim($input['notes']) : ''
        ];

        // Campi opzionali
        if (isset($input['asset_class'])) $holding['asset_class'] = trim($input['asset_class']);
        if (isset($input['sector'])) $holding['sector'] = trim($input['sector']);
        if (isset($input['market'])) $holding['market'] = trim($input['market']);
        if (isset($input['instrument_type'])) $holding['instrument_type'] = trim($input['instrument_type']);
        if (isset($input['currency'])) $holding['currency'] = strtoupper(trim($input['currency']));
        if (isset($input['dividend_yield'])) $holding['dividend_yield'] = (float) $input['dividend_yield'];
        if (isset($input['expense_ratio'])) $holding['expense_ratio'] = (float) $input['expense_ratio'];
        if (isset($input['distributor'])) $holding['distributor'] = trim($input['distributor']);

        $success = $portfolioManager->upsertHolding($holding);

        if ($success) {
            $updated = $portfolioManager->getHoldingByIsin($holding['isin']);

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
    // DELETE - Elimina holding
    // ============================================
    if ($method === 'DELETE') {
        if (!isset($_GET['isin'])) {
            throw new Exception('ISIN non specificato');
        }

        $isin = strtoupper(trim($_GET['isin']));
        $success = $portfolioManager->deleteHolding($isin);

        if ($success) {
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
