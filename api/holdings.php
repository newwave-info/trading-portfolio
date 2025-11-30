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
require_once __DIR__ . '/../lib/Database/Services/PortfolioMetricsService.php';
require_once __DIR__ . '/../lib/Database/Repositories/TransactionRepository.php';

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
    $metricsService = new PortfolioMetricsService($db);
    $transactionRepo = new TransactionRepository($db);

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

            // Parse CSV e upsert holdings nel DB (DB-first)
            $csvPath = $file['tmp_name'];
            if (!file_exists($csvPath)) {
                throw new Exception('File CSV non trovato');
            }

            // Normalizza numeri (formato IT)
            $normalizeNumber = function (string $value): float {
                $value = trim($value);
                if ($value === '' || $value === '-') {
                    return 0.0;
                }
                $normalized = str_replace(['.', ' '], '', $value);
                $normalized = str_replace(',', '.', $normalized);
                return (float) $normalized;
            };

            $handle = fopen($csvPath, 'r');
            if (!$handle) {
                throw new Exception('Impossibile aprire il CSV');
            }

            // Salta le prime 3 righe di header Fineco
            for ($i = 0; $i < 3; $i++) {
                if (fgetcsv($handle, 0, ';') === false) {
                    break;
                }
            }

            $imported = 0;
            $errors = [];
            $portfolioId = PortfolioRepository::DEFAULT_PORTFOLIO_ID;

            // Transaction per upsert batch
            $db->beginTransaction();
            try {
                while (($row = fgetcsv($handle, 0, ';')) !== false) {
                    // Aspettati almeno 8 campi (Fineco: nome, ISIN, ticker, mercato, strumento, valuta, qty, avg_price, ...)
                    if (count($row) < 8) {
                        $errors[] = 'Riga ignorata (campi insufficienti): ' . implode(';', $row);
                        continue;
                    }

                    $name = trim($row[0]);
                    $ticker = strtoupper(trim($row[2] ?: $row[1]));
                    $isin = strtoupper(trim($row[1]));
                    if (empty($ticker)) {
                        $errors[] = 'Riga ignorata (ticker mancante): ' . implode(';', array_slice($row, 0, 3));
                        continue;
                    }

                    $quantity = $normalizeNumber($row[6]);
                    $avgPrice = $normalizeNumber($row[7]);

                    // Upsert su ticker per il portfolio
                    $existing = $holdingRepo->findByTicker($ticker, $portfolioId);
                    if ($existing) {
                        $holdingRepo->updateQuantityAndPrice($ticker, $quantity, $avgPrice, $portfolioId);
                    } else {
                        $holdingRepo->createHolding([
                            'ticker' => $ticker,
                            'isin' => $isin ?: null,
                            'name' => $name ?: $ticker,
                            'asset_class' => 'ETF',
                            'quantity' => $quantity,
                            'avg_price' => $avgPrice,
                            'current_price' => $avgPrice,
                            'price_source' => 'CSV Import'
                        ], $portfolioId);
                    }

                    $imported++;
                }

                $db->commit();
            } catch (Exception $e) {
                $db->rollback();
                fclose($handle);
                throw $e;
            }

            fclose($handle);

            // Ricalcola metriche/allocazioni/snapshot
            $metricsService->recalculate($portfolioId);

            echo json_encode([
                'success' => true,
                'imported' => $imported,
                'errors' => $errors
            ]);
            exit;
        }

        // Altrimenti è un upsert holding normale
        $input = json_decode(file_get_contents('php://input'), true);

        if (!$input) {
            throw new Exception('Dati non validi');
        }

        // Validazione campi obbligatori base
        $required = ['ticker', 'name', 'quantity', 'avg_price'];
        foreach ($required as $field) {
            if (!isset($input[$field]) || $input[$field] === '') {
                throw new Exception("Campo obbligatorio mancante: {$field}");
            }
        }

        $ticker = strtoupper(trim($input['ticker']));
        $isin = isset($input['isin']) ? strtoupper(trim($input['isin'])) : '';
        $isUpdate = isset($input['is_update']) ? (bool) $input['is_update'] : false;

        // ISIN: obbligatorio in create, fallback da existing/ticker in edit
        if ($isUpdate) {
            if ($isin === '') {
                $existing = $holdingRepo->findByTicker($ticker);
                if ($existing && !empty($existing['isin'])) {
                    $isin = strtoupper($existing['isin']);
                } else {
                    $isin = $ticker; // fallback per compatibilità
                }
            }
        } else {
            if ($isin === '') {
                $isin = $ticker; // fallback per evitare blocco su dati legacy
            }
        }

        // Check duplicati se non è update
        if (!$isUpdate && $holdingRepo->findByTicker($ticker)) {
            throw new Exception('Ticker già presente. Usa Modifica per aggiornare la posizione.');
        }

        // Prepara dati holding
        $holdingData = [
            'ticker' => $ticker,
            'isin' => $isin,
            'name' => trim($input['name']),
            'asset_class' => isset($input['asset_class']) ? trim($input['asset_class']) : 'ETF',
            'quantity' => (float) $input['quantity'],
            'avg_price' => (float) $input['avg_price'],
            'current_price' => isset($input['current_price']) ? (float) $input['current_price'] : null,
            'price_source' => isset($input['price_source']) ? trim($input['price_source']) : null,
        ];

        if ($isUpdate) {
            // Preleva dati correnti per calcolare delta quantità (prima dell'update)
            $existingHolding = $holdingRepo->findByTicker($ticker);
            if (!$existingHolding) {
                throw new Exception('Posizione non trovata per update');
            }
            $prevQty = (float) $existingHolding['quantity'];

            // Update existing holding
            $success = $holdingRepo->updateQuantityAndPrice(
                $ticker,
                $holdingData['quantity'],
                $holdingData['avg_price']
            );

            // Log transazione BUY/SELL in base al delta quantità
            $deltaQty = $holdingData['quantity'] - $prevQty;
            if ($deltaQty !== 0) {
                $type = $deltaQty > 0 ? 'BUY' : 'SELL';
                $transactionRepo->createTransaction([
                    'type' => $type,
                    'ticker' => $ticker,
                    'amount' => abs($deltaQty) * $holdingData['avg_price'],
                    'quantity' => abs($deltaQty),
                    'price' => $holdingData['avg_price'],
                    'notes' => 'Update holding',
                    'date' => date('Y-m-d')
                ]);
            }

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
            $transactionRepo->createTransaction([
                'type' => 'BUY',
                'ticker' => $ticker,
                'amount' => $holdingData['quantity'] * $holdingData['avg_price'],
                'quantity' => $holdingData['quantity'],
                'price' => $holdingData['avg_price'],
                'notes' => 'Nuova posizione',
                'date' => date('Y-m-d')
            ]);
        }

        if ($success) {
            $updated = $holdingRepo->findByTicker($ticker);

            // Update portfolio timestamp
            $portfolioRepo->updateLastUpdate();

            // Recalculate derived tables (allocations, snapshot, monthly perf)
            $metricsService->recalculate();

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
    // DELETE - Elimina holding (hard delete + log transazione)
    // ============================================
    if ($method === 'DELETE') {
        $ticker = isset($_GET['ticker']) ? strtoupper(trim($_GET['ticker'])) : null;
        $isin = isset($_GET['isin']) ? strtoupper(trim($_GET['isin'])) : null;

        if (!$ticker && !$isin) {
            throw new Exception('Ticker o ISIN non specificato');
        }

        $success = false;
        $deletedQty = null;
        $deletedPrice = null;
        $resolvedTicker = $ticker;

        if ($ticker) {
            $existing = $holdingRepo->findByTicker($ticker);
            if ($existing) {
                $deletedQty = (float) $existing['quantity'];
                $deletedPrice = (float) $existing['avg_price'];
                $resolvedTicker = $existing['ticker'];
                $isin = $existing['isin'] ?? $isin;
            }
            $success = $holdingRepo->hardDeleteByTicker($ticker);
        } elseif ($isin) {
            $existing = $holdingRepo->findByIsin($isin);
            if ($existing) {
                $deletedQty = (float) $existing['quantity'];
                $deletedPrice = (float) $existing['avg_price'];
                $resolvedTicker = $existing['ticker'] ?? $resolvedTicker;
            }
            $success = $holdingRepo->hardDeleteByIsin($isin);
        }

        if ($success) {
            // Log sell per chiusura posizione
            if ($deletedQty !== null && $deletedQty > 0) {
                $transactionRepo->createTransaction([
                    'type' => 'SELL',
                    'ticker' => $resolvedTicker ?? ($isin ?? ''),
                    'amount' => $deletedQty * $deletedPrice,
                    'quantity' => $deletedQty,
                    'price' => $deletedPrice,
                    'notes' => 'Chiusura posizione',
                    'date' => date('Y-m-d')
                ]);
            }

            // Update portfolio timestamp
            $portfolioRepo->updateLastUpdate();

            // Recalculate derived tables (allocations, snapshot, monthly perf)
            $metricsService->recalculate();

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
