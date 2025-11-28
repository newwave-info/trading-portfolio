<?php
/**
 * API - Dividends Management (n8n friendly)
 *
 * Endpoint:
 * - POST /api/dividends.php
 * Payload:
 * {
 *   "dividends": [
 *     {
 *       "ticker": "VHYL.MI",
 *       "status": "FORECAST" | "RECEIVED",
 *       "ex_date": "YYYY-MM-DD",
 *       "payment_date": "YYYY-MM-DD",
 *       "amount_per_share": 0.35,
 *       "quantity": 30,
 *       "total_amount": 10.50 // opzionale, se assente calcoliamo qty * amount_per_share
 *     }
 *   ]
 * }
 */

header('Content-Type: application/json');

// CORS (opzionale per n8n)
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../lib/Database/DatabaseManager.php';
require_once __DIR__ . '/../lib/Database/Repositories/DividendRepository.php';
require_once __DIR__ . '/../lib/Database/Repositories/HoldingRepository.php';
require_once __DIR__ . '/../lib/Database/Repositories/SnapshotRepository.php';
require_once __DIR__ . '/../lib/Database/Services/PortfolioMetricsService.php';

/**
 * Recupera la quantità detenuta alla data (snapshot più recente <= data)
 */
function getQuantityAtDate(DatabaseManager $db, string $ticker, string $date): ?float
{
    $sql = "
        SELECT sh.quantity
        FROM snapshots s
        JOIN snapshot_holdings sh ON sh.snapshot_id = s.id
        WHERE s.portfolio_id = ? AND sh.ticker = ? AND s.snapshot_date <= ?
        ORDER BY s.snapshot_date DESC
        LIMIT 1
    ";
    $row = $db->fetchOne($sql, [DividendRepository::DEFAULT_PORTFOLIO_ID, $ticker, $date]);
    return $row ? (float)$row['quantity'] : null;
}

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Method not allowed');
    }

    $payload = file_get_contents('php://input');
    if (empty($payload)) {
        throw new Exception('Empty payload');
    }

    $data = json_decode($payload, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON: ' . json_last_error_msg());
    }

    if (!isset($data['dividends']) || !is_array($data['dividends'])) {
        throw new Exception('Invalid payload: missing "dividends" array');
    }

    $db = DatabaseManager::getInstance();
    $repo = new DividendRepository($db);
    $holdingRepo = new HoldingRepository($db);
    $snapshotRepo = new SnapshotRepository($db);
    $metrics = new PortfolioMetricsService($db);

    $created = 0;
    $updated = 0;
    $errors = [];

    foreach ($data['dividends'] as $div) {
        $ticker = isset($div['ticker']) ? strtoupper(trim($div['ticker'])) : null;
        $status = isset($div['status']) ? strtoupper($div['status']) : 'FORECAST';
        $exDate = $div['ex_date'] ?? null;
        $paymentDate = $div['payment_date'] ?? null;
        $qty = isset($div['quantity_at_ex_date']) ? (float)$div['quantity_at_ex_date'] : (isset($div['quantity']) ? (float)$div['quantity'] : null);
        $aps = isset($div['amount_per_share']) ? (float)$div['amount_per_share'] : null;
        $total = isset($div['total_amount']) ? (float)$div['total_amount'] : null;

        if (!$ticker || !$exDate) {
            $errors[] = ['ticker' => $ticker, 'reason' => 'Missing required fields (ticker/ex_date)'];
            continue;
        }

        // Se quantity non fornita, recupera in base allo stato
        if ($qty === null) {
            if ($status === 'RECEIVED') {
                // quantità al ex_date dallo snapshot più recente <= ex_date
                $qty = getQuantityAtDate($db, $ticker, $exDate);
            }
            // Se ancora nulla o FORECAST: usa quantità corrente dell'holding
            if ($qty === null) {
                $holding = $holdingRepo->findByTicker($ticker);
                if ($holding && isset($holding['quantity'])) {
                    $qty = (float)$holding['quantity'];
                }
            }
        }

        // Calcola total_amount se possibile
        if ($total === null && $qty !== null && $aps !== null) {
            $total = $qty * $aps;
        }

        // Se mancano sia qty che total, memorizza qty=0 e total=0 per evitare scarti
        if ($qty === null && $total === null) {
            $qty = 0;
            $total = 0;
        }

        // Upsert: chiave su (portfolio_id, ticker, ex_date, status)
        $existing = $db->fetchOne(
            "SELECT id FROM dividend_payments WHERE portfolio_id = ? AND ticker = ? AND ex_date = ? AND status = ? LIMIT 1",
            [DividendRepository::DEFAULT_PORTFOLIO_ID, $ticker, $exDate, $status]
        );

        $payloadDb = [
            'portfolio_id' => DividendRepository::DEFAULT_PORTFOLIO_ID,
            'ticker' => $ticker,
            'ex_date' => $exDate,
            'payment_date' => $paymentDate,
            'amount_per_share' => $aps,
            'total_amount' => $total,
            'quantity' => $qty,
            'status' => $status,
        ];

        if ($existing) {
            $repo->update($existing['id'], $payloadDb);
            $updated++;
        } else {
            $repo->create($payloadDb);
            $created++;
        }
    }

    // Recalcola metriche/allocazioni/snapshot/monthly_performance
    $metrics->recalculate();

    echo json_encode([
        'success' => true,
        'created' => $created,
        'updated' => $updated,
        'errors' => $errors,
        'timestamp' => date('Y-m-d H:i:s')
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
