<?php
/**
 * API Endpoint: Allocation History
 *
 * Restituisce lo storico dell'allocazione per ticker nel tempo
 * dalle tabelle snapshots + snapshot_holdings.
 *
 * Query Parameters:
 * - days (optional): Numero di giorni storici (default: 30, max: 90)
 *
 * Response:
 * {
 *   "success": true,
 *   "data": {
 *     "dates": ["2025-11-28", "2025-11-30", "2025-12-01"],
 *     "tickers": ["SWDA.MI", "SGLD.MI", "TDIV.MI", "VHYL.MI"],
 *     "allocations": {
 *       "SWDA.MI": [65.2, 67.5, 70.1],
 *       "SGLD.MI": [20.3, 18.2, 15.8],
 *       "TDIV.MI": [10.5, 10.3, 10.1],
 *       "VHYL.MI": [4.0, 4.0, 4.0]
 *     }
 *   },
 *   "count": 3
 * }
 */

require_once __DIR__ . '/../lib/Database/DatabaseManager.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

try {
    // Valida parametri
    $days = isset($_GET['days']) ? (int)$_GET['days'] : 30;

    // Limita giorni a max 90
    $days = min($days, 90);
    if ($days < 1) {
        $days = 30;
    }

    // Portfolio ID (hardcoded per MVP)
    $portfolioId = 1;

    // Query database
    $db = DatabaseManager::getInstance();
    $pdo = $db->getConnection();

    // Step 1: Get snapshots (date e total_market_value)
    $sqlSnapshots = "
        SELECT
            id,
            snapshot_date,
            total_market_value
        FROM snapshots
        WHERE portfolio_id = :portfolio_id
        ORDER BY snapshot_date DESC
        LIMIT :days
    ";

    $stmt = $pdo->prepare($sqlSnapshots);
    $stmt->bindValue(':portfolio_id', $portfolioId, PDO::PARAM_INT);
    $stmt->bindValue(':days', $days, PDO::PARAM_INT);
    $stmt->execute();
    $snapshots = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($snapshots)) {
        echo json_encode([
            'success' => true,
            'data' => [
                'dates' => [],
                'tickers' => [],
                'allocations' => []
            ],
            'count' => 0,
            'message' => 'No snapshots found'
        ], JSON_PRETTY_PRINT);
        exit;
    }

    // Inverti ordine per cronologia crescente
    $snapshots = array_reverse($snapshots);

    // Step 2: Get snapshot_holdings per ogni snapshot
    $snapshotIds = array_column($snapshots, 'id');
    $placeholders = implode(',', array_fill(0, count($snapshotIds), '?'));

    $sqlHoldings = "
        SELECT
            snapshot_id,
            ticker,
            market_value
        FROM snapshot_holdings
        WHERE snapshot_id IN ($placeholders)
        ORDER BY snapshot_id ASC, ticker ASC
    ";

    $stmt = $pdo->prepare($sqlHoldings);
    $stmt->execute($snapshotIds);
    $holdings = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Step 3: Organizza dati per snapshot e ticker
    $snapshotMap = [];
    foreach ($snapshots as $snap) {
        $snapshotMap[$snap['id']] = [
            'date' => $snap['snapshot_date'],
            'total_value' => (float)$snap['total_market_value'],
            'holdings' => []
        ];
    }

    foreach ($holdings as $holding) {
        $snapshotId = $holding['snapshot_id'];
        $ticker = $holding['ticker'];
        $value = (float)$holding['market_value'];

        if (isset($snapshotMap[$snapshotId])) {
            $snapshotMap[$snapshotId]['holdings'][$ticker] = $value;
        }
    }

    // Step 4: Calcola percentuali e costruisci struttura response
    $dates = [];
    $allTickers = [];
    $allocationsByTicker = [];

    foreach ($snapshotMap as $snap) {
        $dates[] = $snap['date'];
        $totalValue = $snap['total_value'];

        foreach ($snap['holdings'] as $ticker => $value) {
            if (!in_array($ticker, $allTickers)) {
                $allTickers[] = $ticker;
            }

            $percentage = $totalValue > 0 ? ($value / $totalValue) * 100 : 0;

            if (!isset($allocationsByTicker[$ticker])) {
                $allocationsByTicker[$ticker] = [];
            }

            $allocationsByTicker[$ticker][] = round($percentage, 2);
        }
    }

    // Step 5: Riempie valori mancanti con 0 (ticker non presente in alcuni snapshot)
    foreach ($allTickers as $ticker) {
        $count = count($dates);
        $existingCount = count($allocationsByTicker[$ticker]);

        // Pad con 0 se necessario
        if ($existingCount < $count) {
            $allocationsByTicker[$ticker] = array_pad($allocationsByTicker[$ticker], $count, 0);
        }
    }

    // Response
    echo json_encode([
        'success' => true,
        'data' => [
            'dates' => $dates,
            'tickers' => $allTickers,
            'allocations' => $allocationsByTicker
        ],
        'count' => count($dates),
        'days_requested' => $days
    ], JSON_PRETTY_PRINT);

} catch (PDOException $e) {
    error_log('Allocation History API Error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Database error',
        'message' => $e->getMessage()
    ]);
} catch (Exception $e) {
    error_log('Allocation History API Error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Internal server error',
        'message' => $e->getMessage()
    ]);
}
