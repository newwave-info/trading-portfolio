<?php
/**
 * Script to regenerate dividends calendar manually
 */

require_once __DIR__ . '/lib/PortfolioManager.php';

try {
    $portfolioManager = new PortfolioManager();

    // Generate dividends calendar
    $dividendsCalendar = $portfolioManager->generateDividendsCalendar();

    // Save to file
    $dividendsFile = __DIR__ . '/data/dividends_calendar.json';
    file_put_contents(
        $dividendsFile,
        json_encode($dividendsCalendar, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
    );

    echo "✅ Dividends calendar regenerated successfully!\n";
    echo "File: $dividendsFile\n\n";
    echo "Summary:\n";
    echo "- Distributing assets: " . count($dividendsCalendar['distributing_assets']) . "\n";
    echo "- Portfolio yield: " . $dividendsCalendar['portfolio_yield'] . "%\n";
    echo "- 6-month forecast: €" . $dividendsCalendar['forecast_6m']['total_amount'] . "\n";
    echo "- Next dividend: " . $dividendsCalendar['next_dividend']['date'] . " (" . $dividendsCalendar['next_dividend']['ticker'] . ")\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
