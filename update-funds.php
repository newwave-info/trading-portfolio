#!/usr/bin/env php
<?php
/**
 * Quick script to manually update fund (Fondo) prices
 *
 * Funds don't have public tickers so can't be auto-updated via APIs.
 * Get real NAV prices from:
 * - Pictet: https://www.assetmanagement.pictet/it/italy
 * - Morningstar: https://www.morningstar.it/
 *
 * Usage: php update-funds.php
 */

require_once __DIR__ . '/lib/PortfolioManager.php';

try {
    $manager = new PortfolioManager(__DIR__ . '/data/portfolio.json');

    echo "📊 Current Fund Prices:\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

    // Get current fund prices
    foreach ($manager->getHoldings() as $holding) {
        if ($holding['instrument_type'] === 'Fondo') {
            printf(
                "%s\n  ISIN: %s\n  Current Price: €%.2f\n\n",
                $holding['name'],
                $holding['isin'],
                $holding['current_price']
            );
        }
    }

    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

    // ========================================
    // UPDATE PRICES HERE
    // ========================================
    // Get real NAV prices from fund provider websites
    // Then uncomment and update these lines:

    /*
    echo "🔄 Updating fund prices...\n\n";

    // PICTET ROBOT R EUR (LU1279334483)
    // Check: https://www.assetmanagement.pictet/it/italy
    $manager->updateHolding('LU1279334483', [
        'current_price' => 52.30  // ← UPDATE WITH REAL NAV PRICE
    ]);
    echo "✅ Updated PICTET ROBOT R EUR\n";

    // PICTET BIOTECH R EUR (LU0255977539)
    $manager->updateHolding('LU0255977539', [
        'current_price' => 41.85  // ← UPDATE WITH REAL NAV PRICE
    ]);
    echo "✅ Updated PICTET BIOTECH R EUR\n";

    // CORE DIVIDEND E E IN (LU0575777627)
    $manager->updateHolding('LU0575777627', [
        'current_price' => 7.95  // ← UPDATE WITH REAL NAV PRICE
    ]);
    echo "✅ Updated CORE DIVIDEND E E IN\n";

    // Recalculate metrics and save
    echo "\n🔄 Recalculating portfolio metrics...\n";
    $manager->recalculateMetrics();
    $manager->save();

    echo "✅ Portfolio saved!\n";
    */

    echo "ℹ️  To update prices:\n";
    echo "   1. Get real NAV prices from fund provider websites\n";
    echo "   2. Edit this file and uncomment the UPDATE PRICES section\n";
    echo "   3. Replace prices with real values\n";
    echo "   4. Run: php update-funds.php\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
