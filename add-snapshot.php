#!/usr/bin/env php
<?php
/**
 * Add a snapshot of current portfolio state
 *
 * Usage: php add-snapshot.php [date]
 * Example: php add-snapshot.php 2025-11-20
 *
 * If no date is provided, uses today's date.
 * Useful for manual historical data entry or testing.
 */

require_once __DIR__ . '/lib/PortfolioManager.php';

try {
    // Get date from argument or use today
    $date = $argv[1] ?? date('Y-m-d');

    // Validate date format
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        die("❌ Invalid date format. Use: YYYY-MM-DD\n");
    }

    echo "📸 Creazione snapshot per $date...\n\n";

    // Load current portfolio
    $pm = new PortfolioManager(__DIR__ . '/data/portfolio.json');
    $data = $pm->getData();

    // Load snapshots
    $snapshotsFile = __DIR__ . '/data/snapshots.json';
    $snapshots = file_exists($snapshotsFile)
        ? json_decode(file_get_contents($snapshotsFile), true)
        : ['snapshots' => []];

    // Check if snapshot already exists for this date
    foreach ($snapshots['snapshots'] as $snap) {
        if ($snap['date'] === $date) {
            die("⚠️  Snapshot per $date già esistente. Usa un'altra data o elimina quello esistente.\n");
        }
    }

    // Add snapshot
    $snapshots['snapshots'][] = [
        'date' => $date,
        'metadata' => $data['metadata'],
        'holdings' => $data['holdings']
    ];

    // Sort by date
    usort($snapshots['snapshots'], fn($a, $b) => $a['date'] <=> $b['date']);

    // Save
    file_put_contents($snapshotsFile, json_encode($snapshots, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

    echo "✅ Snapshot creato!\n\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    printf("Data:           %s\n", $date);
    printf("Total Value:    €%s\n", number_format($data['metadata']['total_value'], 2));
    printf("Holdings:       %d\n", $data['metadata']['holdings_count']);
    printf("Snapshots tot:  %d\n", count($snapshots['snapshots']));
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

    echo "💡 Per aggiornare i grafici con i nuovi dati:\n";
    echo "   php initialize-snapshots.php\n\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
