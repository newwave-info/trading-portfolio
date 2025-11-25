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
    private function save(): bool {
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
        $imported = 0;
        $errors = [];

        // Skip prime 3 righe (header Fineco)
        fgets($file); // Portafoglio di sintesi
        fgets($file); // Riga vuota
        $headers = fgetcsv($file, 0, ';'); // Headers

        // Reset holdings
        $this->data['holdings'] = [];

        while (($row = fgetcsv($file, 0, ';')) !== false) {
            if (count($row) < 10) continue; // Skip righe incomplete

            try {
                $holding = [
                    'name' => trim($row[0]),
                    'isin' => trim($row[1]),
                    'ticker' => trim($row[2]) ?: trim($row[1]), // Se simbolo vuoto, usa ISIN
                    'market' => trim($row[3]),
                    'instrument_type' => trim($row[4]),
                    'currency' => trim($row[5]),
                    'quantity' => (float) str_replace(',', '.', $row[6]),
                    'avg_price' => (float) str_replace(',', '.', $row[7]),
                ];

                $this->upsertHolding($holding);
                $imported++;

            } catch (Exception $e) {
                $errors[] = "Row error: " . $e->getMessage() . " - Data: " . implode(';', $row);
            }
        }

        fclose($file);

        return [
            'success' => $imported > 0,
            'imported' => $imported,
            'errors' => $errors
        ];
    }

    /**
     * Ricalcola metriche aggregate portfolio
     */
    private function recalculateMetrics(): void {
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

        // Calcola allocazioni correnti
        foreach ($this->data['holdings'] as &$holding) {
            $holding['current_allocation'] = $totalValue > 0
                ? ($holding['market_value'] / $totalValue) * 100
                : 0;

            $holding['drift'] = $holding['current_allocation'] - $holding['target_allocation'];
        }

        // Aggiorna metadata
        $this->data['metadata']['total_value'] = round($totalValue, 2);
        $this->data['metadata']['total_invested'] = round($totalInvested, 2);
        $this->data['metadata']['unrealized_pnl'] = round($totalValue - $totalInvested, 2);
        $this->data['metadata']['unrealized_pnl_pct'] = $totalInvested > 0
            ? round((($totalValue - $totalInvested) / $totalInvested) * 100, 2)
            : 0;
        $this->data['metadata']['last_update'] = date('Y-m-d\TH:i:s\Z');
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
}
