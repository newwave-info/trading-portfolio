#!/usr/bin/env php
<?php
/**
 * Initialize portfolio snapshots and monthly performance
 *
 * Creates the first snapshot with current portfolio data.
 * This is needed to populate:
 * - Andamento Mensile chart (monthly_performance)
 * - Performance & flussi tab (snapshots)
 *
 * Run ONCE after first portfolio setup, then n8n will handle daily updates.
 */

require_once __DIR__ . '/lib/PortfolioManager.php';

try {
    echo "📸 Inizializzazione Snapshots & Monthly Performance\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

    // Load current portfolio
    $pm = new PortfolioManager(__DIR__ . '/data/portfolio.json');
    $data = $pm->getData();

    // 1. Create initial snapshot
    echo "1️⃣  Creazione snapshot iniziale...\n";

    $snapshotsFile = __DIR__ . '/data/snapshots.json';
    $snapshots = file_exists($snapshotsFile)
        ? json_decode(file_get_contents($snapshotsFile), true)
        : ['snapshots' => []];

    $today = date('Y-m-d');

    // Check if snapshot for today already exists
    $existsToday = false;
    foreach ($snapshots['snapshots'] as $snap) {
        if ($snap['date'] === $today) {
            $existsToday = true;
            break;
        }
    }

    if ($existsToday) {
        echo "   ⚠️  Snapshot per oggi ($today) già esistente, skip\n";
    } else {
        // Add snapshot for today
        $snapshots['snapshots'][] = [
            'date' => $today,
            'metadata' => $data['metadata'],
            'holdings' => $data['holdings']
        ];

        // Sort by date
        usort($snapshots['snapshots'], fn($a, $b) => $a['date'] <=> $b['date']);

        file_put_contents($snapshotsFile, json_encode($snapshots, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        echo "   ✅ Snapshot creato per $today\n";
    }

    // 2. Generate monthly_performance from snapshots
    echo "\n2️⃣  Generazione monthly_performance...\n";

    if (!empty($snapshots['snapshots'])) {
        $byMonth = [];

        foreach ($snapshots['snapshots'] as $snap) {
            $month = date('M', strtotime($snap['date']));
            $year = date('Y', strtotime($snap['date']));
            $key = $year . '-' . $month;

            // Take last snapshot of each month
            if (!isset($byMonth[$key]) || $snap['date'] > $byMonth[$key]['date']) {
                $byMonth[$key] = [
                    'month' => $month,
                    'value' => $snap['metadata']['total_value'],
                    'date' => $snap['date']
                ];
            }
        }

        // Sort by date and take last 12 months
        usort($byMonth, fn($a, $b) => $a['date'] <=> $b['date']);
        $monthlyPerformance = array_slice(array_map(fn($item) => [
            'month' => $item['month'],
            'value' => $item['value']
        ], $byMonth), -12);

        // Update portfolio.json with monthly_performance
        $data['monthly_performance'] = $monthlyPerformance;
        $pm->setData($data);
        $pm->save();

        echo "   ✅ monthly_performance popolato con " . count($monthlyPerformance) . " mesi\n";
    } else {
        echo "   ⚠️  Nessuno snapshot disponibile\n";
    }

    // 3. Summary
    echo "\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "📊 Riepilogo:\n\n";

    printf("  Snapshots totali:        %d\n", count($snapshots['snapshots']));
    printf("  Monthly performance:     %d mesi\n", count($data['monthly_performance']));
    printf("  Portfolio value:         €%s\n", number_format($data['metadata']['total_value'], 2));

    if (!empty($snapshots['snapshots'])) {
        $firstSnap = $snapshots['snapshots'][0];
        $lastSnap = end($snapshots['snapshots']);
        printf("  Primo snapshot:          %s\n", $firstSnap['date']);
        printf("  Ultimo snapshot:         %s\n", $lastSnap['date']);
    }

    echo "\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
    echo "✅ Inizializzazione completata!\n\n";
    echo "🎯 Ora i grafici dovrebbero visualizzare:\n";
    echo "   - Andamento Mensile (2025): grafico con punto iniziale\n";
    echo "   - Performance & flussi: metriche di performance\n\n";
    echo "💡 D'ora in poi, n8n aggiornerà automaticamente questi dati ogni giorno.\n";
    echo "   Per aggiungere snapshot manualmente: php add-snapshot.php\n\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
