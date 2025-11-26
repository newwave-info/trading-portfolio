<?php
/**
 * Admin tool: Recalculate portfolio metrics
 * Access: https://portfolio.newwave-media.it/admin-recalculate.php
 *
 * SECURITY: Add authentication in production!
 */

require_once __DIR__ . '/lib/PortfolioManager.php';

header('Content-Type: text/html; charset=utf-8');

// Optional: Simple password protection
$ADMIN_PASSWORD = 'changeme'; // ← CHANGE THIS!
if (isset($_GET['password']) && $_GET['password'] !== $ADMIN_PASSWORD) {
    http_response_code(403);
    die('❌ Access denied');
}

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Recalculate Metrics</title>
    <style>
        body { font-family: monospace; padding: 20px; background: #1a1a1a; color: #0f0; }
        .box { background: #000; padding: 20px; border: 1px solid #0f0; margin: 20px 0; }
        .success { color: #0f0; }
        .error { color: #f00; }
        table { border-collapse: collapse; width: 100%; margin: 10px 0; }
        th, td { text-align: left; padding: 8px; border-bottom: 1px solid #333; }
        th { color: #0ff; }
    </style>
</head>
<body>
    <h1>📊 Portfolio Metrics Recalculation</h1>

    <?php
    try {
        $pm = new PortfolioManager(__DIR__ . '/data/portfolio.json');

        echo '<div class="box">';
        echo '<p class="success">🔄 Ricalcolo metriche in corso...</p>';

        $pm->recalculateMetrics();
        $pm->save();

        $data = $pm->getData();

        echo '<p class="success">✅ Metriche aggiornate con successo!</p>';
        echo '</div>';

        // Portfolio totals
        echo '<div class="box">';
        echo '<h2>Portfolio Summary</h2>';
        echo '<table>';
        echo '<tr><th>Metric</th><th>Value</th></tr>';
        printf('<tr><td>Total Value</td><td>€%s</td></tr>', number_format($data['metadata']['total_value'], 2));
        printf('<tr><td>Total Invested</td><td>€%s</td></tr>', number_format($data['metadata']['total_invested'], 2));
        printf('<tr><td>Unrealized P&L</td><td class="%s">€%s (%+.2f%%)</td></tr>',
            $data['metadata']['unrealized_pnl'] >= 0 ? 'success' : 'error',
            number_format($data['metadata']['unrealized_pnl'], 2),
            $data['metadata']['unrealized_pnl_pct']
        );
        printf('<tr><td>Holdings Count</td><td>%d</td></tr>', $data['metadata']['holdings_count']);
        printf('<tr><td>Last Update</td><td>%s</td></tr>', $data['metadata']['last_update']);
        echo '</table>';
        echo '</div>';

        // Allocation by asset class
        echo '<div class="box">';
        echo '<h2>Allocation by Asset Class</h2>';

        if (!empty($data['allocation_by_asset_class'])) {
            echo '<table>';
            echo '<tr><th>Asset Class</th><th>Total Value</th><th>Percentage</th><th>Holdings</th></tr>';

            foreach ($data['allocation_by_asset_class'] as $allocation) {
                printf(
                    '<tr><td>%s</td><td>€%s</td><td>%.2f%%</td><td>%d</td></tr>',
                    htmlspecialchars($allocation['asset_class']),
                    number_format($allocation['total_value'], 2),
                    $allocation['percentage'],
                    $allocation['holdings_count']
                );
            }

            echo '</table>';
            echo '<p class="success">✅ allocation_by_asset_class popolato correttamente!</p>';
        } else {
            echo '<p class="error">⚠️ allocation_by_asset_class è vuoto</p>';
        }

        echo '</div>';

        echo '<div class="box">';
        echo '<p class="success">🎯 I grafici della dashboard ora dovrebbero visualizzare i dati!</p>';
        echo '<p>Torna alla <a href="index.php" style="color:#0ff">dashboard</a></p>';
        echo '</div>';

    } catch (Exception $e) {
        echo '<div class="box">';
        echo '<p class="error">❌ Error: ' . htmlspecialchars($e->getMessage()) . '</p>';
        echo '</div>';
    }
    ?>

</body>
</html>
