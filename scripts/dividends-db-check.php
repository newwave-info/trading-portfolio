<?php
/**
 * Dividends DB Check
 *
 * Usage: php scripts/dividends-db-check.php
 * Output: JSON con count, ultimi ricevuti, prossimi forecast, monthly breakdown.
 */

chdir(__DIR__ . '/..');

require_once __DIR__ . '/../lib/Database/DatabaseManager.php';
require_once __DIR__ . '/../lib/Database/Repositories/DividendRepository.php';

header('Content-Type: application/json');

try {
    $db = DatabaseManager::getInstance();
    $repo = new DividendRepository($db);

    $today = date('Y-m-d');
    $sixMonthsLater = date('Y-m-d', strtotime('+6 months'));
    $year = (int) date('Y');

    $counts = [
        'all' => $repo->count(['portfolio_id' => DividendRepository::DEFAULT_PORTFOLIO_ID]),
        'received' => $repo->count(['portfolio_id' => DividendRepository::DEFAULT_PORTFOLIO_ID, 'status' => 'RECEIVED']),
        'forecast' => $repo->count(['portfolio_id' => DividendRepository::DEFAULT_PORTFOLIO_ID, 'status' => 'FORECAST']),
    ];

    $latestReceived = $repo->getReceived($year . '-01-01', $year . '-12-31');
    $latestReceived = array_slice($latestReceived, 0, 5);

    $upcoming = $repo->getForecast($today, $sixMonthsLater);
    $upcoming = array_slice($upcoming, 0, 5);

    $monthly = $repo->getMonthlyData($year);

    echo json_encode([
        'counts' => $counts,
        'latest_received' => $latestReceived,
        'upcoming_forecast' => $upcoming,
        'monthly_data' => $monthly
    ], JSON_PRETTY_PRINT);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ], JSON_PRETTY_PRINT);
}
