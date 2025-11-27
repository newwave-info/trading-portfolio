<?php
/**
 * Debug script for dividends data
 */

require_once __DIR__ . '/data/portfolio_data.php';

echo "=== DIVIDENDS DEBUG ===\n\n";

echo "1. Dividends Calendar Data:\n";
echo "   - Loaded: " . (!empty($dividends_calendar_data) ? "YES" : "NO") . "\n";
echo "   - Monthly forecast count: " . count($dividends_calendar_data['monthly_forecast'] ?? []) . "\n";
echo "   - Distributing assets: " . count($dividends_calendar_data['distributing_assets'] ?? []) . "\n\n";

echo "2. Monthly Forecast:\n";
if (!empty($dividends_calendar_data['monthly_forecast'])) {
    foreach ($dividends_calendar_data['monthly_forecast'] as $m) {
        echo "   - {$m['month']}: €{$m['amount']}\n";
    }
} else {
    echo "   EMPTY!\n";
}
echo "\n";

echo "3. Historical Dividends:\n";
echo "   - Count: " . count($dividends) . "\n";
if (empty($dividends)) {
    echo "   EMPTY (this is expected for now)\n";
}
echo "\n";

// Simulate the chart data preparation
$months_labels = ['Gen', 'Feb', 'Mar', 'Apr', 'Mag', 'Giu', 'Lug', 'Ago', 'Set', 'Ott', 'Nov', 'Dic'];
$months_map = ['Jan' => 0, 'Feb' => 1, 'Mar' => 2, 'Apr' => 3, 'May' => 4, 'Jun' => 5,
               'Jul' => 6, 'Aug' => 7, 'Sep' => 8, 'Oct' => 9, 'Nov' => 10, 'Dec' => 11];

$monthly_received = array_fill(0, 12, 0);
$monthly_forecast = array_fill(0, 12, 0);

// Populate forecast
if (!empty($dividends_calendar_data['monthly_forecast'])) {
    foreach ($dividends_calendar_data['monthly_forecast'] as $forecast) {
        $monthName = $forecast['month'] ?? '';
        $amount = $forecast['amount'] ?? 0;
        if (isset($months_map[$monthName]) && $amount > 0) {
            $monthIdx = $months_map[$monthName];
            if ($monthly_received[$monthIdx] == 0) {
                $monthly_forecast[$monthIdx] = $amount;
            }
        }
    }
}

echo "4. Processed Monthly Forecast Array:\n";
for ($i = 0; $i < 12; $i++) {
    if ($monthly_forecast[$i] > 0) {
        echo "   - {$months_labels[$i]} (index $i): €{$monthly_forecast[$i]}\n";
    }
}

if (array_sum($monthly_forecast) == 0) {
    echo "   WARNING: All values are ZERO!\n";
}

echo "\n5. JSON Output for JavaScript:\n";
echo "   monthly_received: " . json_encode(array_values($monthly_received)) . "\n";
echo "   monthly_forecast: " . json_encode(array_values($monthly_forecast)) . "\n";
