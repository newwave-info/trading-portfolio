<?php
/**
 * API Endpoint: Technical History
 *
 * Restituisce lo storico degli indicatori tecnici per uno strumento specifico
 * dalla tabella technical_snapshots.
 *
 * Query Parameters:
 * - isin (required): ISIN dello strumento
 * - days (optional): Numero di giorni storici (default: 30, max: 90)
 *
 * Response:
 * {
 *   "success": true,
 *   "data": [
 *     {
 *       "snapshot_date": "2025-11-30",
 *       "price": 111.43,
 *       "rsi14": 53.11,
 *       "macd_value": 1.1461,
 *       "macd_signal": 1.1223,
 *       "hist_vol_30d": 14.07,
 *       "atr14_pct": 1.25,
 *       "range_1y_percentile": 98.01,
 *       "bb_percent_b": 0.7322
 *     },
 *     ...
 *   ],
 *   "count": 30
 * }
 */

require_once __DIR__ . '/../lib/Database/DatabaseManager.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

try {
    // Valida parametri
    $isin = $_GET['isin'] ?? null;
    $days = isset($_GET['days']) ? (int)$_GET['days'] : 30;

    // Validazione ISIN
    if (!$isin) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Missing required parameter: isin'
        ]);
        exit;
    }

    // Limita giorni a max 90
    $days = min($days, 90);
    if ($days < 1) {
        $days = 30;
    }

    // Portfolio ID (hardcoded per MVP, in futuro da sessione utente)
    $portfolioId = 1;

    // Query database
    $db = DatabaseManager::getInstance();
    $pdo = $db->getConnection();

    $sql = "
        SELECT
            snapshot_date,
            price,
            rsi14,
            macd_value,
            macd_signal,
            hist_vol_30d,
            atr14_pct,
            range_1y_percentile,
            bb_percent_b
        FROM technical_snapshots
        WHERE isin = :isin
        AND portfolio_id = :portfolio_id
        ORDER BY snapshot_date DESC
        LIMIT :days
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':isin', $isin, PDO::PARAM_STR);
    $stmt->bindValue(':portfolio_id', $portfolioId, PDO::PARAM_INT);
    $stmt->bindValue(':days', $days, PDO::PARAM_INT);
    $stmt->execute();

    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Inverti ordine per avere cronologia crescente (per grafici)
    $data = array_reverse($data);

    // Converti valori numerici da string a float/null
    $data = array_map(function($row) {
        return [
            'snapshot_date' => $row['snapshot_date'],
            'price' => $row['price'] !== null ? (float)$row['price'] : null,
            'rsi14' => $row['rsi14'] !== null ? (float)$row['rsi14'] : null,
            'macd_value' => $row['macd_value'] !== null ? (float)$row['macd_value'] : null,
            'macd_signal' => $row['macd_signal'] !== null ? (float)$row['macd_signal'] : null,
            'hist_vol_30d' => $row['hist_vol_30d'] !== null ? (float)$row['hist_vol_30d'] : null,
            'atr14_pct' => $row['atr14_pct'] !== null ? (float)$row['atr14_pct'] : null,
            'range_1y_percentile' => $row['range_1y_percentile'] !== null ? (float)$row['range_1y_percentile'] : null,
            'bb_percent_b' => $row['bb_percent_b'] !== null ? (float)$row['bb_percent_b'] : null
        ];
    }, $data);

    // Response
    echo json_encode([
        'success' => true,
        'data' => $data,
        'count' => count($data),
        'isin' => $isin,
        'days_requested' => $days
    ], JSON_PRETTY_PRINT);

} catch (PDOException $e) {
    error_log('Technical History API Error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Database error',
        'message' => $e->getMessage()
    ]);
} catch (Exception $e) {
    error_log('Technical History API Error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Internal server error',
        'message' => $e->getMessage()
    ]);
}
