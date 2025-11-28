<?php
/**
 * DB Check - Diagnostics for derived tables/views
 *
 * Usage: php scripts/db-check.php
 * Output: JSON con conteggi e sample rows
 */

// Esegui dal root del progetto
chdir(__DIR__ . '/..');

require_once __DIR__ . '/../lib/Database/DatabaseManager.php';
require_once __DIR__ . '/../lib/Database/Repositories/PortfolioRepository.php';
require_once __DIR__ . '/../lib/Database/Repositories/SnapshotRepository.php';

header('Content-Type: application/json');

try {
    $db = DatabaseManager::getInstance();
    $pdo = $db->getConnection();

    // Conteggi tabelle derivate
    $tables = ['allocation_by_asset_class', 'monthly_performance', 'snapshots', 'snapshot_holdings'];
    $counts = [];
    foreach ($tables as $tbl) {
        $stmt = $pdo->query("SELECT COUNT(*) AS c FROM {$tbl}");
        $counts[$tbl] = (int) $stmt->fetchColumn();
    }

    // Sample rows (max 5) per tabella derivata
    $samples = [];
    foreach ($tables as $tbl) {
        $stmt = $pdo->query("SELECT * FROM {$tbl} ORDER BY id DESC LIMIT 5");
        $samples[$tbl] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Metadati live dalla VIEW
    $metaRepo = new PortfolioRepository($db);
    $metadata = $metaRepo->getMetadata();

    // Ultimi 5 snapshots (se presenti)
    $snapRepo = new SnapshotRepository($db);
    $latestSnapshots = $snapRepo->getDailySnapshots(5);

    echo json_encode([
        'counts' => $counts,
        'samples' => $samples,
        'metadata_view' => $metadata,
        'latest_snapshots' => $latestSnapshots
    ], JSON_PRETTY_PRINT);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ], JSON_PRETTY_PRINT);
}
