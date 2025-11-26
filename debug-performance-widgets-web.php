<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Debug Performance Widgets</title>
    <style>
        body { font-family: monospace; padding: 20px; background: #1a1a1a; color: #0f0; }
        .box { background: #000; padding: 20px; border: 1px solid #0f0; margin: 20px 0; }
        .success { color: #0f0; }
        .error { color: #f00; }
        .warning { color: #ff0; }
        .info { color: #0af; }
        pre { background: #222; padding: 10px; overflow-x: auto; }
        h2 { color: #0af; border-bottom: 1px solid #0af; padding-bottom: 10px; }
        h3 { color: #0f0; }
    </style>
</head>
<body>
    <h1>🔍 Debug Performance Widgets</h1>

    <?php
    // Load snapshots
    $snapshotsPath = __DIR__ . '/data/snapshots.json';

    echo '<div class="box">';
    echo '<h2>📁 File Check</h2>';
    echo '<p>Snapshots file: <code>' . $snapshotsPath . '</code></p>';
    echo '<p>File exists: ' . (file_exists($snapshotsPath) ? '<span class="success">YES ✓</span>' : '<span class="error">NO ✗</span>') . '</p>';
    echo '</div>';

    if (!file_exists($snapshotsPath)) {
        echo '<div class="box error">❌ snapshots.json not found!</div>';
        exit;
    }

    $snapshotsData = json_decode(file_get_contents($snapshotsPath), true);
    $snapshots = $snapshotsData['snapshots'] ?? [];

    echo '<div class="box">';
    echo '<h2>📊 Snapshots Data</h2>';
    echo '<p>Snapshots loaded: <strong>' . count($snapshots) . '</strong></p>';

    if (empty($snapshots)) {
        echo '<p class="error">❌ No snapshots in array!</p>';
        echo '</div>';
        exit;
    }

    echo '<p class="success">✓ Snapshots array is populated</p>';
    echo '</div>';

    $first = $snapshots[0];
    $current = end($snapshots);

    echo '<div class="box">';
    echo '<h2>📸 Snapshot Info</h2>';
    echo '<h3>First Snapshot:</h3>';
    echo '<p>Date: <strong>' . $first['date'] . '</strong></p>';
    echo '<p>Value: <strong>€' . number_format($first['metadata']['total_value'], 2) . '</strong></p>';

    echo '<h3>Current Snapshot:</h3>';
    echo '<p>Date: <strong>' . $current['date'] . '</strong></p>';
    echo '<p>Value: <strong>€' . number_format($current['metadata']['total_value'], 2) . '</strong></p>';

    if ($first['date'] === $current['date']) {
        echo '<p class="warning">⚠️ First and current are the same (only 1 snapshot)</p>';
    }
    echo '</div>';

    $currentValue = $current['metadata']['total_value'];

    // 1 Mese
    echo '<div class="box">';
    echo '<h2>📅 1 MONTH PERFORMANCE</h2>';

    $oneMonthAgo = date('Y-m-d', strtotime('-30 days'));
    echo '<p>Looking for snapshot >= <code>' . $oneMonthAgo . '</code></p>';

    $snap1m = null;
    foreach ($snapshots as $s) {
        if ($s['date'] >= $oneMonthAgo) {
            $snap1m = $s;
            break;
        }
    }

    if (!$snap1m) {
        echo '<p class="warning">No snapshot found, using first snapshot as fallback</p>';
        $snap1m = $first;
    }

    echo '<p>Using snapshot: <strong>' . $snap1m['date'] . '</strong></p>';
    echo '<p>Value then: <strong>€' . number_format($snap1m['metadata']['total_value'], 2) . '</strong></p>';
    echo '<p>Value now: <strong>€' . number_format($currentValue, 2) . '</strong></p>';

    $value1m = $snap1m['metadata']['total_value'];
    $change1m = $currentValue - $value1m;
    $pct1m = $value1m > 0 ? ($change1m / $value1m) * 100 : 0;

    echo '<p>Change: <strong>€' . number_format($change1m, 2) . '</strong></p>';
    echo '<p>Change %: <strong class="' . ($pct1m >= 0 ? 'success' : 'error') . '">' . ($pct1m >= 0 ? '+' : '') . number_format($pct1m, 2) . '%</strong></p>';

    $perf_1m = [
        'pct' => ($pct1m >= 0 ? '+' : '') . number_format($pct1m, 2, ',', '.') . '%',
        'value' => '€' . number_format($value1m / 1000, 1, ',', '.') . 'k'
    ];

    echo '<h3>Widget Display:</h3>';
    echo '<p>Percentage: <strong class="success">' . $perf_1m['pct'] . '</strong></p>';
    echo '<p>vs: <strong>' . $perf_1m['value'] . '</strong></p>';
    echo '</div>';

    // 3 Mesi
    echo '<div class="box">';
    echo '<h2>📅 3 MONTHS PERFORMANCE</h2>';

    $threeMonthsAgo = date('Y-m-d', strtotime('-90 days'));
    echo '<p>Looking for snapshot >= <code>' . $threeMonthsAgo . '</code></p>';

    $snap3m = null;
    foreach ($snapshots as $s) {
        if ($s['date'] >= $threeMonthsAgo) {
            $snap3m = $s;
            break;
        }
    }

    if (!$snap3m) {
        echo '<p class="warning">No snapshot found, using first snapshot as fallback</p>';
        $snap3m = $first;
    }

    echo '<p>Using snapshot: <strong>' . $snap3m['date'] . '</strong></p>';

    $value3m = $snap3m['metadata']['total_value'];
    $change3m = $currentValue - $value3m;
    $pct3m = $value3m > 0 ? ($change3m / $value3m) * 100 : 0;

    echo '<p>Change: <strong>€' . number_format($change3m, 2) . '</strong></p>';
    echo '<p>Change %: <strong class="' . ($pct3m >= 0 ? 'success' : 'error') . '">' . ($pct3m >= 0 ? '+' : '') . number_format($pct3m, 2) . '%</strong></p>';

    $perf_3m = [
        'pct' => ($pct3m >= 0 ? '+' : '') . number_format($pct3m, 2, ',', '.') . '%',
        'value' => '€' . number_format($value3m / 1000, 1, ',', '.') . 'k'
    ];

    echo '<h3>Widget Display:</h3>';
    echo '<p>Percentage: <strong class="success">' . $perf_3m['pct'] . '</strong></p>';
    echo '<p>vs: <strong>' . $perf_3m['value'] . '</strong></p>';
    echo '</div>';

    // YTD
    echo '<div class="box">';
    echo '<h2>📅 YTD PERFORMANCE</h2>';

    $ytdStart = date('Y') . '-01-01';
    echo '<p>Looking for snapshot >= <code>' . $ytdStart . '</code></p>';

    $snapYtd = null;
    foreach ($snapshots as $s) {
        if ($s['date'] >= $ytdStart) {
            $snapYtd = $s;
            break;
        }
    }

    if (!$snapYtd) {
        echo '<p class="warning">No snapshot found, using first snapshot as fallback</p>';
        $snapYtd = $first;
    }

    echo '<p>Using snapshot: <strong>' . $snapYtd['date'] . '</strong></p>';

    $valueYtd = $snapYtd['metadata']['total_value'];
    $changeYtd = $currentValue - $valueYtd;
    $pctYtd = $valueYtd > 0 ? ($changeYtd / $valueYtd) * 100 : 0;

    echo '<p>Change: <strong>€' . number_format($changeYtd, 2) . '</strong></p>';
    echo '<p>Change %: <strong class="' . ($pctYtd >= 0 ? 'success' : 'error') . '">' . ($pctYtd >= 0 ? '+' : '') . number_format($pctYtd, 2) . '%</strong></p>';

    $perf_ytd = [
        'pct' => ($pctYtd >= 0 ? '+' : '') . number_format($pctYtd, 2, ',', '.') . '%',
        'value' => '€' . number_format($valueYtd / 1000, 1, ',', '.') . 'k'
    ];

    echo '<h3>Widget Display:</h3>';
    echo '<p>Percentage: <strong class="success">' . $perf_ytd['pct'] . '</strong></p>';
    echo '<p>vs: <strong>' . $perf_ytd['value'] . '</strong></p>';
    echo '</div>';

    // Summary
    echo '<div class="box">';
    echo '<h2>📋 SUMMARY</h2>';
    echo '<h3>Widget values that SHOULD appear in frontend:</h3>';
    echo '<table style="width: 100%; margin-top: 10px;">';
    echo '<tr><td><strong>1M:</strong></td><td><span class="success">' . $perf_1m['pct'] . '</span></td><td>vs ' . $perf_1m['value'] . '</td></tr>';
    echo '<tr><td><strong>3M:</strong></td><td><span class="success">' . $perf_3m['pct'] . '</span></td><td>vs ' . $perf_3m['value'] . '</td></tr>';
    echo '<tr><td><strong>YTD:</strong></td><td><span class="success">' . $perf_ytd['pct'] . '</span></td><td>vs ' . $perf_ytd['value'] . '</td></tr>';
    echo '</table>';

    if ($pct1m == 0 && $pct3m == 0 && $pctYtd == 0) {
        echo '<p class="info" style="margin-top: 20px;">ℹ️ All showing 0% because you only have 1 snapshot<br>(current value compared to itself = no change)</p>';
        echo '<h3>💡 To see real performance:</h3>';
        echo '<ul>';
        echo '<li>Wait for tomorrow\'s n8n enrichment at 22:00 (creates new daily snapshot)</li>';
        echo '<li>Or manually create another snapshot with different data</li>';
        echo '</ul>';
    }
    echo '</div>';

    // Check if widgets are showing "-" instead
    echo '<div class="box">';
    echo '<h2>🔍 Troubleshooting</h2>';
    echo '<p>If your widgets show "<strong>-</strong>" instead of the values above:</p>';
    echo '<ol>';
    echo '<li>Check browser console for PHP errors</li>';
    echo '<li>Verify snapshots.json has valid JSON (check file permissions)</li>';
    echo '<li>Check that performance.php is reading from the correct path</li>';
    echo '<li>Try hard refresh (Ctrl+Shift+R or Cmd+Shift+R)</li>';
    echo '</ol>';
    echo '<p>If widgets show the percentages but they\'re all 0%:</p>';
    echo '<p class="success">✓ This is CORRECT behavior with only 1 snapshot!</p>';
    echo '</div>';
    ?>

</body>
</html>
