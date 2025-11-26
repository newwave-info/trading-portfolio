<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Debug Charts Data</title>
    <style>
        body { font-family: monospace; padding: 20px; background: #1a1a1a; color: #0f0; }
        .box { background: #000; padding: 20px; border: 1px solid #0f0; margin: 20px 0; }
        .success { color: #0f0; }
        .error { color: #f00; }
        .warning { color: #ff0; }
        pre { background: #222; padding: 10px; overflow-x: auto; }
    </style>
</head>
<body>
    <h1>🔍 Debug Charts Data</h1>

    <?php
    require_once __DIR__ . '/lib/PortfolioManager.php';

    // Load portfolio
    $pm = new PortfolioManager(__DIR__ . '/data/portfolio.json');
    $data = $pm->getData();
    $metadata = $data['metadata'];
    $top_holdings = $data['holdings'];

    echo '<div class="box">';
    echo '<h2>📊 Portfolio Data</h2>';
    printf('<p>Total Value: €%s</p>', number_format($metadata['total_value'], 2));
    printf('<p>Total Invested: €%s</p>', number_format($metadata['total_invested'], 2));
    printf('<p>Holdings: %d</p>', $metadata['holdings_count']);
    echo '</div>';

    // Load snapshots
    $snapshotsPath = __DIR__ . '/data/snapshots.json';
    $snapshotsData = json_decode(file_get_contents($snapshotsPath), true);
    $snapshots = $snapshotsData['snapshots'] ?? [];

    echo '<div class="box">';
    echo '<h2>📸 Snapshots</h2>';
    printf('<p>Count: %d</p>', count($snapshots));
    if (!empty($snapshots)) {
        printf('<p>First: %s</p>', $snapshots[0]['date']);
        printf('<p>Last: %s</p>', end($snapshots)['date']);
    } else {
        echo '<p class="error">⚠️ No snapshots found!</p>';
    }
    echo '</div>';

    // Generate history_data (same logic as performance.php)
    $history_data = [];
    $snapshots_slice = array_slice($snapshots, -30);

    $prevValue = null;
    $prevInvested = $metadata['total_invested'];

    foreach ($snapshots_slice as $snap) {
        $value = $snap['metadata']['total_value'];
        $invested = $snap['metadata']['total_invested'] ?? $prevInvested;
        $cumul_gain = $value - $invested;
        $gain_pct = $invested > 0 ? ($cumul_gain / $invested) * 100 : 0;

        $day_change = $prevValue !== null ? $value - $prevValue : 0;
        $day_pct = $prevValue !== null && $prevValue > 0 ? ($day_change / $prevValue) * 100 : 0;

        $history_data[] = [
            'date' => date('d/m/Y', strtotime($snap['date'])),
            'value' => $value,
            'cumul_gain' => $cumul_gain,
            'gain_pct' => $gain_pct,
            'open_pos' => $snap['metadata']['holdings_count'] ?? count($top_holdings),
            'day_change' => $day_change,
            'day_pct' => $day_pct
        ];

        $prevValue = $value;
        $prevInvested = $invested;
    }

    echo '<div class="box">';
    echo '<h2>📈 History Data</h2>';
    printf('<p>Rows generated: %d</p>', count($history_data));
    if (!empty($history_data)) {
        echo '<p class="success">✅ History data is populated</p>';
        echo '<pre>' . json_encode($history_data, JSON_PRETTY_PRINT) . '</pre>';
    } else {
        echo '<p class="error">❌ History data is EMPTY!</p>';
    }
    echo '</div>';

    // Generate chart data
    $chart_labels = [];
    $chart_values = [];
    $chart_cumul_gain = [];

    if (!empty($history_data)) {
        $chart_labels = array_column($history_data, 'date');
        $chart_values = array_column($history_data, 'value');
        $chart_cumul_gain = array_column($history_data, 'cumul_gain');
    }

    echo '<div class="box">';
    echo '<h2>📊 Chart Arrays (JSON for JavaScript)</h2>';
    echo '<h3>chart_labels:</h3>';
    echo '<pre>' . json_encode($chart_labels) . '</pre>';
    echo '<h3>chart_values:</h3>';
    echo '<pre>' . json_encode($chart_values) . '</pre>';
    echo '<h3>chart_cumul_gain:</h3>';
    echo '<pre>' . json_encode($chart_cumul_gain) . '</pre>';

    if (empty($chart_labels)) {
        echo '<p class="error">❌ Chart arrays are EMPTY - graphs will NOT render!</p>';
    } else {
        echo '<p class="success">✅ Chart arrays are populated - graphs SHOULD render!</p>';
        printf('<p>Expected points in graphs: %d</p>', count($chart_labels));
    }
    echo '</div>';

    // Check monthly_performance
    echo '<div class="box">';
    echo '<h2>📅 Monthly Performance</h2>';
    $monthly_perf = $data['monthly_performance'] ?? [];
    printf('<p>Months: %d</p>', count($monthly_perf));
    if (!empty($monthly_perf)) {
        echo '<pre>' . json_encode($monthly_perf, JSON_PRETTY_PRINT) . '</pre>';
    } else {
        echo '<p class="warning">⚠️ Monthly performance is empty</p>';
    }
    echo '</div>';

    // Check allocation
    echo '<div class="box">';
    echo '<h2>🎯 Allocation by Asset Class</h2>';
    $allocation = $data['allocation_by_asset_class'] ?? [];
    printf('<p>Classes: %d</p>', count($allocation));
    if (!empty($allocation)) {
        echo '<pre>' . json_encode($allocation, JSON_PRETTY_PRINT) . '</pre>';
    } else {
        echo '<p class="warning">⚠️ Allocation is empty</p>';
    }
    echo '</div>';
    ?>

    <div class="box">
        <h2>🔧 Next Steps</h2>
        <ol>
            <li>If history_data is empty: Run <code>php initialize-snapshots.php</code></li>
            <li>If chart arrays are empty: Check snapshots.json file</li>
            <li>If arrays are populated but graphs don't show: Check browser console for JavaScript errors</li>
            <li>Clear browser cache and reload</li>
        </ol>
    </div>

</body>
</html>
