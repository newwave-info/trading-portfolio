<?php
require_once __DIR__ . '/lib/PortfolioManager.php';

$pm = new PortfolioManager();
$pm->recalculateMetrics();
$pm->save();

$calendar = $pm->generateDividendsCalendar();
file_put_contents(__DIR__ . '/data/dividends_calendar.json', json_encode($calendar, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

echo "Calendar regenerated!\n";
echo json_encode($calendar, JSON_PRETTY_PRINT);
