<?php
/**
 * PortfolioManager - Gestione lettura/scrittura portfolio.json
 *
 * Fornisce metodi per:
 * - Leggere portfolio completo
 * - Aggiungere/modificare/eliminare holdings
 * - Importare CSV Fineco
 * - Calcolare metriche aggregate
 * - Preparare payload per n8n
 */

class PortfolioManager {

    private string $jsonPath;
    private array $data;

    public function __construct(string $jsonPath = null) {
        $this->jsonPath = $jsonPath ?? __DIR__ . '/../data/portfolio.json';
        $this->load();
    }

    /**
     * Carica portfolio da JSON
     */
    private function load(): void {
        if (!file_exists($this->jsonPath)) {
            throw new Exception("Portfolio file not found: {$this->jsonPath}");
        }

        $content = file_get_contents($this->jsonPath);
        $this->data = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("Invalid JSON in portfolio file: " . json_last_error_msg());
        }
    }

    /**
     * Salva portfolio su JSON
     */
    public function save(): bool {
        // Backup prima di salvare
        if (file_exists($this->jsonPath)) {
            copy($this->jsonPath, $this->jsonPath . '.backup');
        }

        $json = json_encode($this->data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("Error encoding JSON: " . json_last_error_msg());
        }

        return file_put_contents($this->jsonPath, $json) !== false;
    }

    /**
     * Ottieni tutti i dati del portfolio
     */
    public function getData(): array {
        return $this->data;
    }

    /**
     * Ottieni metadata
     */
    public function getMetadata(): array {
        return $this->data['metadata'] ?? [];
    }

    /**
     * Ottieni holdings
     */
    public function getHoldings(): array {
        return $this->data['holdings'] ?? [];
    }

    /**
     * Ottieni holding per ISIN
     */
    public function getHoldingByIsin(string $isin): ?array {
        foreach ($this->data['holdings'] as $holding) {
            if ($holding['isin'] === $isin) {
                return $holding;
            }
        }
        return null;
    }

    /**
     * Aggiungi o aggiorna holding
     */
    public function upsertHolding(array $holding): bool {
        // Validazione campi obbligatori
        $required = ['isin', 'ticker', 'name', 'quantity', 'avg_price'];
        foreach ($required as $field) {
            if (!isset($holding[$field])) {
                throw new Exception("Missing required field: {$field}");
            }
        }

        // Cerca se esiste già
        $found = false;
        foreach ($this->data['holdings'] as &$existingHolding) {
            if ($existingHolding['isin'] === $holding['isin']) {
                // Update
                $existingHolding = array_merge($existingHolding, $holding);
                $found = true;
                break;
            }
        }

        // Se non esiste, aggiungi
        if (!$found) {
            // Aggiungi campi default
            $holding = array_merge([
                'asset_class' => 'Unknown',
                'sector' => 'Unknown',
                'market' => 'AFF',
                'instrument_type' => 'ETF',
                'currency' => 'EUR',
                'current_price' => $holding['avg_price'], // Inizialmente uguale al prezzo medio
                'market_value' => $holding['quantity'] * $holding['avg_price'],
                'invested_value' => $holding['quantity'] * $holding['avg_price'],
                'unrealized_pnl' => 0.00,
                'pnl_percentage' => 0.00,
                'target_allocation' => 0.00,
                'current_allocation' => 0.00,
                'drift' => 0.00,
                'dividend_yield' => 0.00,
                'expense_ratio' => 0.00,
                'distributor' => '',
                'notes' => ''
            ], $holding);

            $this->data['holdings'][] = $holding;
        }

        // Ricalcola metriche
        $this->recalculateMetrics();

        return $this->save();
    }

    /**
     * Elimina holding per ISIN
     */
    public function deleteHolding(string $isin): bool {
        $this->data['holdings'] = array_filter($this->data['holdings'], function($holding) use ($isin) {
            return $holding['isin'] !== $isin;
        });

        // Re-index array
        $this->data['holdings'] = array_values($this->data['holdings']);

        // Ricalcola metriche
        $this->recalculateMetrics();

        return $this->save();
    }

    /**
     * Aggiorna solo campi specifici di un holding esistente
     * (Usato da n8n enrichment per aggiornare prezzi e metadata)
     */
    public function updateHolding(string $isin, array $updates): bool {
        $found = false;

        foreach ($this->data['holdings'] as &$holding) {
            if ($holding['isin'] === $isin) {
                // Merge updates con holding esistente
                $holding = array_merge($holding, $updates);
                $found = true;
                break;
            }
        }

        if (!$found) {
            throw new Exception("Holding with ISIN {$isin} not found");
        }

        // NON ricalcolare metriche qui - sarà fatto da enrich.php dopo tutti gli update
        return true;
    }

    /**
     * Import CSV Fineco
     *
     * Formato CSV:
     * Titolo;ISIN;Simbolo;Mercato;Strumento;Valuta;Quantità;P.zo medio di carico;Cambio di carico;Valore di carico
     */
    public function importFromCsv(string $csvPath): array {
        if (!file_exists($csvPath)) {
            throw new Exception("CSV file not found: {$csvPath}");
        }

        $file = fopen($csvPath, 'r');
        if (!$file) {
            throw new Exception("Cannot open CSV file: {$csvPath}");
        }

        $imported = 0;
        $errors = [];
        $processedIsins = [];
        $rowNumber = 0;
        $newHoldings = [];

        // Skip header lines using fgetcsv for consistency
        $skippedLines = 0;
        while ($skippedLines < 3 && ($skipRow = fgetcsv($file, 0, ';')) !== false) {
            $skippedLines++;
        }

        while (($row = fgetcsv($file, 0, ';')) !== false) {
            $rowNumber++;

            // Skip empty rows or incomplete rows
            if (empty($row) || count($row) < 10) {
                $errors[] = "Skipped row {$rowNumber}: incomplete data (" . count($row) . " fields)";
                continue;
            }

            try {
                // Normalize ISIN (remove spaces, convert to uppercase)
                $isin = strtoupper(trim($row[1]));

                // Skip if ISIN is empty
                if (empty($isin)) {
                    $errors[] = "Skipped row {$rowNumber} with empty ISIN: " . implode(';', array_slice($row, 0, 3));
                    continue;
                }

                // Skip if we already processed this ISIN (avoid duplicates)
                if (isset($processedIsins[$isin])) {
                    $errors[] = "Skipped duplicate ISIN on row {$rowNumber}: {$isin}";
                    continue;
                }

                $holding = [
                    'name' => trim($row[0]),
                    'isin' => $isin,
                    'ticker' => strtoupper(trim($row[2]) ?: $isin),
                    'market' => trim($row[3]) ?: 'AFF',
                    'instrument_type' => trim($row[4]) ?: 'ETF',
                    'currency' => strtoupper(trim($row[5]) ?: 'EUR'),
                    'quantity' => $this->normalizeNumber($row[6]),
                    'avg_price' => $this->normalizeNumber($row[7]),
                ];

                // Applica defaults e accumula senza salvare su disco ad ogni iterazione
                $newHoldings[$isin] = array_merge([
                    'asset_class' => 'Unknown',
                    'sector' => 'Unknown',
                    'current_price' => $holding['avg_price'],
                    'market_value' => $holding['quantity'] * $holding['avg_price'],
                    'invested_value' => $holding['quantity'] * $holding['avg_price'],
                    'unrealized_pnl' => 0.00,
                    'pnl_percentage' => 0.00,
                    'target_allocation' => 0.00,
                    'current_allocation' => 0.00,
                    'drift' => 0.00,
                    'dividend_yield' => 0.00,
                    'expense_ratio' => 0.00,
                    'distributor' => '',
                    'notes' => ''
                ], $holding);

                $processedIsins[$isin] = true;
                $imported++;

            } catch (Exception $e) {
                $errors[] = "Row {$rowNumber} error: " . $e->getMessage() . " - Data: " . implode(';', $row);
            }
        }

        fclose($file);

        // Sovrascrivi holdings solo se è stato importato almeno un record
        if ($imported > 0) {
            $this->data['holdings'] = array_values($newHoldings);
            $this->recalculateMetrics();
            $this->save();
        }

        return [
            'success' => $imported > 0,
            'imported' => $imported,
            'errors' => $errors
        ];
    }

    /**
     * Ricalcola metriche aggregate portfolio
     */
    public function recalculateMetrics(): void {
        $totalValue = 0;
        $totalInvested = 0;

        foreach ($this->data['holdings'] as &$holding) {
            // Calcola market value
            $holding['market_value'] = $holding['quantity'] * $holding['current_price'];
            $holding['invested_value'] = $holding['quantity'] * $holding['avg_price'];

            // Calcola P&L
            $holding['unrealized_pnl'] = $holding['market_value'] - $holding['invested_value'];
            $holding['pnl_percentage'] = $holding['invested_value'] > 0
                ? ($holding['unrealized_pnl'] / $holding['invested_value']) * 100
                : 0;

            $totalValue += $holding['market_value'];
            $totalInvested += $holding['invested_value'];
        }
        unset($holding);

        // Calcola allocazioni correnti
        foreach ($this->data['holdings'] as &$holding) {
            $holding['current_allocation'] = $totalValue > 0
                ? ($holding['market_value'] / $totalValue) * 100
                : 0;

            $holding['drift'] = $holding['current_allocation'] - $holding['target_allocation'];
        }
        unset($holding);

        // Aggiorna metadata
        $this->data['metadata']['total_value'] = round($totalValue, 2);
        $this->data['metadata']['total_invested'] = round($totalInvested, 2);
        $this->data['metadata']['unrealized_pnl'] = round($totalValue - $totalInvested, 2);
        $this->data['metadata']['unrealized_pnl_pct'] = $totalInvested > 0
            ? round((($totalValue - $totalInvested) / $totalInvested) * 100, 2)
            : 0;
        $this->data['metadata']['holdings_count'] = count($this->data['holdings']);
        $this->data['metadata']['last_update'] = date('Y-m-d\TH:i:s\Z');

        // Calcola allocation_by_asset_class per grafici
        $allocationByAssetClass = [];
        foreach ($this->data['holdings'] as $holding) {
            $assetClass = $holding['asset_class'] ?? 'Unknown';

            if (!isset($allocationByAssetClass[$assetClass])) {
                $allocationByAssetClass[$assetClass] = [
                    'asset_class' => $assetClass,
                    'total_value' => 0,
                    'percentage' => 0,
                    'holdings_count' => 0
                ];
            }

            $allocationByAssetClass[$assetClass]['total_value'] += $holding['market_value'];
            $allocationByAssetClass[$assetClass]['holdings_count']++;
        }

        // Calcola percentuali
        foreach ($allocationByAssetClass as &$allocation) {
            $allocation['total_value'] = round($allocation['total_value'], 2);
            $allocation['percentage'] = $totalValue > 0
                ? round(($allocation['total_value'] / $totalValue) * 100, 2)
                : 0;
        }

        // Converti a array indicizzato e ordina per valore decrescente
        $this->data['allocation_by_asset_class'] = array_values($allocationByAssetClass);
        usort($this->data['allocation_by_asset_class'], fn($a, $b) => $b['total_value'] <=> $a['total_value']);
    }

    /**
     * Normalizza numeri in formato italiano (rimuove separatori migliaia e converte virgola in punto)
     */
    private function normalizeNumber(string $value): float {
        $value = trim($value);
        if ($value === '' || $value === '-') {
            return 0.0;
        }

        // Rimuove separatori migliaia e converte la virgola in punto decimale
        $normalized = str_replace(['.', ' '], '', $value);
        $normalized = str_replace(',', '.', $normalized);

        return (float) $normalized;
    }

    /**
     * Aggiorna prezzi correnti da array
     * (Usato quando n8n restituisce quotazioni aggiornate)
     */
    public function updatePrices(array $prices): bool {
        // $prices = ['ISIN' => current_price, ...]

        foreach ($this->data['holdings'] as &$holding) {
            if (isset($prices[$holding['isin']])) {
                $holding['current_price'] = (float) $prices[$holding['isin']];
            }
        }

        $this->recalculateMetrics();
        return $this->save();
    }

    /**
     * Prepara payload per n8n
     *
     * Formato ottimizzato per workflow analisi
     */
    public function prepareN8nPayload(): array {
        return [
            'portfolio_id' => 'portfolio_001',
            'timestamp' => date('Y-m-d\TH:i:s\Z'),
            'holdings' => array_map(function($holding) {
                return [
                    'isin' => $holding['isin'],
                    'ticker' => $holding['ticker'],
                    'name' => $holding['name'],
                    'quantity' => $holding['quantity'],
                    'current_price' => $holding['current_price'],
                    'market_value' => $holding['market_value'],
                    'asset_class' => $holding['asset_class']
                ];
            }, $this->data['holdings']),
            'metadata' => [
                'total_value' => $this->data['metadata']['total_value'],
                'total_invested' => $this->data['metadata']['total_invested'],
                'unrealized_pnl_pct' => $this->data['metadata']['unrealized_pnl_pct']
            ],
            'webhook_secret' => $this->data['n8n_config']['hmac_secret'] ?? ''
        ];
    }

    /**
     * Ricevi risultati da n8n e aggiorna dati
     */
    public function receiveN8nResults(array $results): bool {
        // Esempio struttura results da n8n:
        // {
        //   "prices": { "IE00B3RBWM25": 89.45, ... },
        //   "technical_signals": [ ... ],
        //   "opportunities": [ ... ]
        // }

        if (isset($results['prices'])) {
            $this->updatePrices($results['prices']);
        }

        // Altri risultati possono essere salvati in sezioni dedicate del JSON
        // o in file separati (technical_analysis.json, opportunities.json, ecc.)

        return true;
    }

    /**
     * Set complete portfolio data (useful for snapshot initialization)
     */
    public function setData(array $data): void {
        $this->data = $data;
    }

    /**
     * Generate dividends calendar from holdings data
     *
     * Calculates next payment dates based on dividend frequency
     *
     * @return array Dividends calendar structure
     */
    public function generateDividendsCalendar(): array
    {
        $data = $this->getData();
        $holdings = $data["holdings"] ?? [];

        $distributingAssets = [];
        $monthlyForecast = array_fill(1, 12, 0); // Jan to Dec
        $portfolioYield = 0;
        $nextDividend = null;
        $totalInvested = $data["metadata"]["total_invested"] ?? 0;

        $today = new DateTime();
        $currentMonth = (int) $today->format('n');

        foreach ($holdings as $holding) {
            $hasDividends = $holding["has_dividends"] ?? false;
            $dividendYield = $holding["dividend_yield"] ?? 0;
            $annualDividend = $holding["annual_dividend"] ?? 0;
            $frequency = $holding["dividend_frequency"] ?? "None";
            $quantity = $holding["quantity"] ?? 0;
            $marketValue = $holding["market_value"] ?? 0;

            if (!$hasDividends || $dividendYield == 0 || $frequency === "None") {
                continue;
            }

            // Add to distributing assets
            $distributingAssets[] = [
                "ticker" => $holding["ticker"],
                "name" => $holding["name"],
                "dividend_yield" => round($dividendYield, 2),
                "annual_amount" => round($annualDividend * $quantity, 2),
                "frequency" => $frequency,
                "last_div_date" => null,
                "next_div_date" => null
            ];

            // Calculate portfolio-weighted yield
            if ($totalInvested > 0) {
                $portfolioYield += ($marketValue / $totalInvested) * $dividendYield;
            }

            // Estimate monthly distribution based on frequency
            $paymentsPerYear = $this->getPaymentsPerYear($frequency);
            $dividendPerPayment = ($annualDividend * $quantity) / $paymentsPerYear;

            // Distribute across months (simple estimation)
            $months = $this->getPaymentMonths($frequency);

            foreach ($months as $month) {
                $monthlyForecast[$month] += $dividendPerPayment;
            }

            // Find next dividend date (estimate)
            $nextMonth = null;
            foreach ($months as $month) {
                if ($month >= $currentMonth) {
                    $nextMonth = $month;
                    break;
                }
            }

            if ($nextMonth === null && count($months) > 0) {
                $nextMonth = $months[0]; // Next year
            }

            if ($nextMonth !== null) {
                $estimatedDate = new DateTime();
                $estimatedDate->setDate((int) $today->format('Y'), $nextMonth, 15);

                if ($nextMonth < $currentMonth) {
                    $estimatedDate->modify('+1 year');
                }

                if ($nextDividend === null || $estimatedDate < new DateTime($nextDividend["date"])) {
                    $nextDividend = [
                        "date" => $estimatedDate->format('Y-m-d'),
                        "ticker" => $holding["ticker"],
                        "amount" => round($dividendPerPayment, 2)
                    ];
                }
            }
        }

        // Format monthly forecast for next 6 months
        $forecast6m = [];
        for ($i = 0; $i < 6; $i++) {
            $month = ($currentMonth + $i - 1) % 12 + 1;
            $forecast6m[] = [
                "month" => date('M', mktime(0, 0, 0, $month, 1)),
                "amount" => round($monthlyForecast[$month], 2)
            ];
        }

        $totalForecast6m = array_sum(array_column($forecast6m, 'amount'));

        return [
            "last_update" => date('c'),
            "forecast_6m" => [
                "total_amount" => round($totalForecast6m, 2),
                "period" => date('M') . " - " . date('M', strtotime('+5 months'))
            ],
            "portfolio_yield" => round($portfolioYield, 2),
            "next_dividend" => $nextDividend ?? [
                "date" => "-",
                "ticker" => "-",
                "amount" => 0
            ],
            "monthly_forecast" => $forecast6m,
            "distributing_assets" => $distributingAssets,
            "ai_insight" => $this->generateDividendInsight($distributingAssets, $portfolioYield)
        ];
    }

    /**
     * Get number of payments per year based on frequency
     *
     * @param string $frequency Dividend frequency
     *
     * @return int Number of payments per year
     */
    private function getPaymentsPerYear(string $frequency): int
    {
        return match($frequency) {
            'Quarterly' => 4,
            'Semi-Annual' => 2,
            'Monthly' => 12,
            'Annual' => 1,
            default => 4
        };
    }

    /**
     * Get payment months based on frequency
     *
     * @param string $frequency Dividend frequency
     *
     * @return array Array of month numbers (1-12)
     */
    private function getPaymentMonths(string $frequency): array
    {
        return match($frequency) {
            'Quarterly' => [3, 6, 9, 12], // Q1=Mar, Q2=Jun, Q3=Sep, Q4=Dec
            'Semi-Annual' => [6, 12],      // Jun, Dec
            'Monthly' => range(1, 12),     // Every month
            'Annual' => [12],              // December
            default => [3, 6, 9, 12]
        };
    }

    /**
     * Generate AI-style insight about dividends
     *
     * @param array $distributingAssets Array of assets with dividends
     *
     * @param float $portfolioYield Portfolio-weighted yield
     *
     * @return string Insight text
     */
    private function generateDividendInsight(array $distributingAssets, float $portfolioYield): string
    {
        $count = count($distributingAssets);

        if ($count === 0) {
            return "No distributing assets in portfolio.";
        }

        $avgYield = $portfolioYield;

        if ($avgYield > 4) {
            return "$count assets with strong dividend yield ({$avgYield}%). Excellent passive income potential.";
        } elseif ($avgYield > 2.5) {
            return "$count dividend-paying assets with moderate yield ({$avgYield}%). Balanced income strategy.";
        } else {
            return "$count dividend assets with conservative yield ({$avgYield}%). Focus on capital appreciation.";
        }
    }
}
