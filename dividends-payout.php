#!/usr/bin/env php
<?php
/**
 * Automatizza il payout dividendi usando i dati arricchiti (annual_dividend, dividend_frequency).
 *
 * Uso: php dividends-payout.php
 * Da schedulare via cron per esecuzione giornaliera.
 */

require_once __DIR__ . '/lib/PortfolioManager.php';

$today = date('Y-m-d');
$day = (int) date('d');
$month = (int) date('n');
$monthShort = date('M');

$paymentsPerYearMap = [
    'Monthly' => 12,
    'Quarterly' => 4,
    'Semi-Annual' => 2,
    'Annual' => 1,
];

$payoutMonthsMap = [
    'Monthly' => range(1, 12),
    'Quarterly' => [3, 6, 9, 12],
    'Semi-Annual' => [6, 12],
    'Annual' => [12],
];

// Config: giorno del mese per lo stacco automatico (costante per semplicità)
$payoutDay = 15;

try {
    $pm = new PortfolioManager(__DIR__ . '/data/portfolio.json');
    $data = $pm->getData();

    $dividends = $data['dividends'] ?? [];
    $existingKeys = [];
    foreach ($dividends as $div) {
        if (!empty($div['pay_date']) && !empty($div['isin'])) {
            $existingKeys[$div['pay_date'] . '|' . $div['isin']] = true;
        }
    }

    $added = 0;
    foreach ($data['holdings'] as $holding) {
        $isin = $holding['isin'] ?? null;
        if (!$isin) {
            continue;
        }

        $hasDividends = $holding['has_dividends'] ?? false;
        $annualDividend = (float) ($holding['annual_dividend'] ?? 0);
        $freq = $holding['dividend_frequency'] ?? 'None';
        $quantity = (float) ($holding['quantity'] ?? 0);

        if (!$hasDividends || $annualDividend <= 0 || $quantity <= 0) {
            continue;
        }

        $paymentsPerYear = $paymentsPerYearMap[$freq] ?? 0;
        $payoutMonths = $payoutMonthsMap[$freq] ?? [];

        if ($paymentsPerYear === 0 || empty($payoutMonths)) {
            continue;
        }

        // Verifica se oggi è giorno di payout secondo la regola semplice (giorno fisso + mese previsto)
        if ($day !== $payoutDay || !in_array($month, $payoutMonths, true)) {
            continue;
        }

        $amountPerPayment = $annualDividend / $paymentsPerYear;
        $payoutAmount = $amountPerPayment * $quantity;

        if ($payoutAmount <= 0) {
            continue;
        }

        $key = $today . '|' . $isin;
        if (isset($existingKeys[$key])) {
            // Già registrato per oggi
            continue;
        }

        $dividends[] = [
            'isin' => $isin,
            'ticker' => $holding['ticker'] ?? '',
            'amount' => round($payoutAmount, 2),
            'pay_date' => $today,
            'source' => 'auto',
            'note' => 'Pagamento automatico in base a frequenza e annual_dividend'
        ];
        $existingKeys[$key] = true;
        $added++;
    }

    if ($added > 0) {
        // Ordina dividendi per data
        usort($dividends, fn($a, $b) => strcmp($a['pay_date'], $b['pay_date']));
        $data['dividends'] = $dividends;

        // Aggiorna totale dividendi in metadata
        $data['metadata']['total_dividends'] = round(array_sum(array_column($dividends, 'amount')), 2);
        $data['metadata']['last_update'] = date('Y-m-d\TH:i:s\Z');

        // Salva portfolio
        $pm->setData($data);
        $pm->save();

        // Aggiorna calendario dividendi (scala importo del mese corrente)
        $divCalPath = __DIR__ . '/data/dividends_calendar.json';
        if (file_exists($divCalPath)) {
            $cal = json_decode(file_get_contents($divCalPath), true);
            if (isset($cal['monthly_forecast']) && is_array($cal['monthly_forecast'])) {
                foreach ($cal['monthly_forecast'] as &$m) {
                    if (($m['month'] ?? '') === $monthShort) {
                        $m['amount'] = max(0, ($m['amount'] ?? 0) - array_sum(array_column($dividends, 'amount')));
                    }
                }
                unset($m);
                // Ricalcola forecast_6m e next_dividend
                $totalAmount = 0;
                $nextDividend = ['date' => '-', 'ticker' => '-', 'amount' => null];
                $currentYear = (int) date('Y');
                foreach ($cal['monthly_forecast'] as $m) {
                    $totalAmount += $m['amount'] ?? 0;
                    if (($m['amount'] ?? 0) > 0 && $nextDividend['date'] === '-') {
                        $monthNum = date('n', strtotime($m['month'] . ' 1 ' . $currentYear));
                        $nextDividend = [
                            'date' => sprintf('%04d-%02d-%02d', $currentYear, $monthNum, $payoutDay),
                            'ticker' => $cal['distributing_assets'][0]['ticker'] ?? 'N/A',
                            'amount' => $m['amount']
                        ];
                    }
                }
                $cal['forecast_6m']['total_amount'] = round($totalAmount, 2);
                $cal['forecast_6m']['period'] = $cal['forecast_6m']['period'] ?? 'Auto';
                $cal['next_dividend'] = $nextDividend;

                file_put_contents($divCalPath, json_encode($cal, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            }
        }

        echo "✅ Payout eseguito: {$added} dividendi registrati in data {$today}\n";
    } else {
        echo "ℹ️ Nessun payout eseguito per la data {$today}\n";
    }
} catch (Exception $e) {
    echo "❌ Errore: " . $e->getMessage() . "\n";
    exit(1);
}
