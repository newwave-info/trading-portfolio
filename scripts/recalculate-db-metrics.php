<?php
/**
 * Recalculate derived tables (allocations, snapshot today, monthly performance) from DB holdings.
 *
 * Usage: php scripts/recalculate-db-metrics.php
 */

chdir(__DIR__ . '/..');

require_once __DIR__ . '/../lib/Database/DatabaseManager.php';
require_once __DIR__ . '/../lib/Database/Services/PortfolioMetricsService.php';

try {
    $db = DatabaseManager::getInstance();
    $service = new PortfolioMetricsService($db);

    $result = $service->recalculate();

    if ($result['success']) {
        echo "✅ Ricalcolo completato\n";
        echo "Allocations: {$result['allocations']} | Snapshot: {$result['snapshot']} | Monthly records: {$result['monthly_records']}\n";
    } else {
        echo "⚠️ Nessun dato ricalcolato: {$result['message']}\n";
    }
} catch (Exception $e) {
    fwrite(STDERR, "❌ Errore: " . $e->getMessage() . "\n");
    exit(1);
}
