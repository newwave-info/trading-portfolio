#!/usr/bin/env php
<?php
/**
 * Recalculate portfolio metrics and populate allocation_by_asset_class
 */

require_once __DIR__ . '/lib/PortfolioManager.php';

try {
    $pm = new PortfolioManager(__DIR__ . '/data/portfolio.json');

    echo "📊 Ricalcolo metriche portafoglio...\n\n";

    $pm->recalculateMetrics();
    $pm->save();

    $data = $pm->getData();

    echo "✅ Metriche aggiornate!\n\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "Portfolio Totals:\n";
    printf("  Total Value:     €%s\n", number_format($data['metadata']['total_value'], 2));
    printf("  Total Invested:  €%s\n", number_format($data['metadata']['total_invested'], 2));
    printf("  Unrealized P&L:  €%s (%+.2f%%)\n",
        number_format($data['metadata']['unrealized_pnl'], 2),
        $data['metadata']['unrealized_pnl_pct']
    );

    echo "\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "Allocation by Asset Class:\n";

    if (!empty($data['allocation_by_asset_class'])) {
        foreach ($data['allocation_by_asset_class'] as $allocation) {
            printf(
                "  %s: €%s (%.2f%%) - %d holdings\n",
                str_pad($allocation['asset_class'], 15),
                number_format($allocation['total_value'], 2),
                $allocation['percentage'],
                $allocation['holdings_count']
            );
        }
    } else {
        echo "  (nessuna allocazione)\n";
    }

    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
    echo "🎯 I grafici della dashboard ora dovrebbero visualizzare i dati!\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
