<?php
/**
 * Migration Script - JSON to MySQL
 *
 * Migrates all data from JSON files to MySQL database.
 *
 * Usage:
 *   php scripts/migrate-to-mysql.php --validate-only  (dry run)
 *   php scripts/migrate-to-mysql.php --execute         (execute migration)
 *
 * @version 0.3.0-MySQL
 */

// Change to project root directory
chdir(__DIR__ . '/..');

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include required files
require_once __DIR__ . '/../lib/Database/DatabaseManager.php';
require_once __DIR__ . '/../lib/Database/Repositories/PortfolioRepository.php';
require_once __DIR__ . '/../lib/Database/Repositories/HoldingRepository.php';
require_once __DIR__ . '/../lib/Database/Repositories/TransactionRepository.php';
require_once __DIR__ . '/../lib/Database/Repositories/DividendRepository.php';
require_once __DIR__ . '/../lib/Database/Repositories/SnapshotRepository.php';

// ============================================================================
// CONFIGURATION
// ============================================================================

define('PORTFOLIO_JSON', __DIR__ . '/../data/portfolio.json');
define('SNAPSHOTS_JSON', __DIR__ . '/../data/snapshots.json');

// ============================================================================
// COLORS FOR CLI OUTPUT
// ============================================================================

class Color {
    const RESET = "\033[0m";
    const GREEN = "\033[32m";
    const RED = "\033[31m";
    const YELLOW = "\033[33m";
    const BLUE = "\033[34m";
    const CYAN = "\033[36m";
    const BOLD = "\033[1m";
}

// ============================================================================
// HELPER FUNCTIONS
// ============================================================================

function printSuccess($message) {
    echo Color::GREEN . "✓ " . $message . Color::RESET . PHP_EOL;
}

function printError($message) {
    echo Color::RED . "✗ " . $message . Color::RESET . PHP_EOL;
}

function printWarning($message) {
    echo Color::YELLOW . "⚠ " . $message . Color::RESET . PHP_EOL;
}

function printInfo($message) {
    echo Color::BLUE . "ℹ " . $message . Color::RESET . PHP_EOL;
}

function printHeader($message) {
    echo PHP_EOL . Color::BOLD . Color::CYAN . "=== " . $message . " ===" . Color::RESET . PHP_EOL . PHP_EOL;
}

function printStep($step, $total, $message) {
    echo Color::CYAN . "[{$step}/{$total}] " . Color::RESET . $message . PHP_EOL;
}

// ============================================================================
// MAIN MIGRATION CLASS
// ============================================================================

class MySQLMigration {

    private $db;
    private $portfolioRepo;
    private $holdingRepo;
    private $transactionRepo;
    private $dividendRepo;
    private $snapshotRepo;

    private $validateOnly = false;
    private $portfolioId = 1;

    private $stats = [
        'portfolios' => 0,
        'holdings' => 0,
        'transactions' => 0,
        'dividends' => 0,
        'snapshots' => 0,
        'snapshot_holdings' => 0,
        'allocations' => 0,
        'monthly_performance' => 0,
        'errors' => []
    ];

    public function __construct($validateOnly = false) {
        $this->validateOnly = $validateOnly;

        printHeader("MySQL Migration Tool");

        if ($this->validateOnly) {
            printWarning("VALIDATION MODE - No data will be written");
        } else {
            printInfo("EXECUTION MODE - Data will be migrated to MySQL");
        }
    }

    public function run() {
        try {
            // Step 1: Validate environment
            printStep(1, 6, "Validating environment");
            $this->validateEnvironment();

            // Step 2: Connect to database
            printStep(2, 6, "Connecting to database");
            $this->connectDatabase();

            // Step 3: Validate JSON files
            printStep(3, 6, "Validating JSON files");
            $portfolioData = $this->loadPortfolioJSON();
            $snapshotsData = $this->loadSnapshotsJSON();

            // Step 4: Migrate data
            if (!$this->validateOnly) {
                printStep(4, 6, "Migrating data to MySQL");
                $this->migrateData($portfolioData, $snapshotsData);
            } else {
                printStep(4, 6, "Skipping migration (validation mode)");
            }

            // Step 5: Validate migration
            if (!$this->validateOnly) {
                printStep(5, 6, "Validating migration");
                $this->validateMigration($portfolioData, $snapshotsData);
            }

            // Step 6: Print summary
            printStep(6, 6, "Migration complete");
            $this->printSummary();

            return true;

        } catch (Exception $e) {
            printError("Migration failed: " . $e->getMessage());
            printError("Stack trace: " . $e->getTraceAsString());
            return false;
        }
    }

    private function validateEnvironment() {
        // Check if .env exists
        if (!file_exists(__DIR__ . '/../.env')) {
            throw new Exception("File .env non trovato. Crea il file .env con le credenziali del database.");
        }

        printSuccess("File .env trovato");

        // Check if JSON files exist
        if (!file_exists(PORTFOLIO_JSON)) {
            throw new Exception("File portfolio.json non trovato: " . PORTFOLIO_JSON);
        }

        printSuccess("File portfolio.json trovato");

        if (!file_exists(SNAPSHOTS_JSON)) {
            printWarning("File snapshots.json non trovato (opzionale)");
        } else {
            printSuccess("File snapshots.json trovato");
        }
    }

    private function connectDatabase() {
        try {
            $this->db = DatabaseManager::getInstance();

            // Test connection
            $this->db->getConnection()->query("SELECT 1");

            printSuccess("Connessione al database riuscita");

            // Initialize repositories
            $this->portfolioRepo = new PortfolioRepository($this->db);
            $this->holdingRepo = new HoldingRepository($this->db);
            $this->transactionRepo = new TransactionRepository($this->db);
            $this->dividendRepo = new DividendRepository($this->db);
            $this->snapshotRepo = new SnapshotRepository($this->db);

            printSuccess("Repository inizializzati");

        } catch (Exception $e) {
            throw new Exception("Errore connessione database: " . $e->getMessage());
        }
    }

    private function loadPortfolioJSON() {
        $json = file_get_contents(PORTFOLIO_JSON);
        $data = json_decode($json, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("Errore parsing portfolio.json: " . json_last_error_msg());
        }

        printSuccess("portfolio.json caricato (" . count($data['holdings']) . " holdings)");

        return $data;
    }

    private function loadSnapshotsJSON() {
        if (!file_exists(SNAPSHOTS_JSON)) {
            return [];
        }

        $json = file_get_contents(SNAPSHOTS_JSON);
        $data = json_decode($json, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("Errore parsing snapshots.json: " . json_last_error_msg());
        }

        printSuccess("snapshots.json caricato (" . count($data) . " snapshots)");

        return $data;
    }

    private function migrateData($portfolioData, $snapshotsData) {
        printHeader("Migrazione Dati");

        try {
            $this->db->beginTransaction();

            // 1. Create portfolio
            $this->migratePortfolio($portfolioData['metadata']);

            // 2. Migrate holdings
            $this->migrateHoldings($portfolioData['holdings']);

            // 3. Migrate transactions
            $this->migrateTransactions($portfolioData['transactions'] ?? []);

            // 4. Migrate dividends
            $this->migrateDividends($portfolioData['dividends'] ?? []);

            // 5. Migrate allocations
            $this->migrateAllocations($portfolioData['allocation_by_asset_class']);

            // 6. Migrate monthly performance
            $this->migrateMonthlyPerformance($portfolioData['monthly_performance']);

            // 7. Migrate snapshots
            $this->migrateSnapshots($snapshotsData);

            $this->db->commit();
            printSuccess("Transazione completata con successo");

        } catch (Exception $e) {
            $this->db->rollback();
            throw new Exception("Errore durante la migrazione: " . $e->getMessage());
        }
    }

    private function migratePortfolio($metadata) {
        printInfo("Migrazione portfolio...");

        // Check if portfolio already exists
        $existing = $this->portfolioRepo->find($this->portfolioId);

        if ($existing) {
            printWarning("Portfolio già esistente (ID: {$this->portfolioId}), skip creazione");
        } else {
            $this->db->execute(
                "INSERT INTO portfolios (id, name, owner) VALUES (?, ?, ?)",
                [
                    $this->portfolioId,
                    $metadata['portfolio_name'] ?? 'My Portfolio',
                    $metadata['owner'] ?? 'Owner'
                ]
            );

            $this->stats['portfolios']++;
            printSuccess("Portfolio creato");
        }
    }

    private function migrateHoldings($holdings) {
        printInfo("Migrazione holdings (" . count($holdings) . " records)...");

        foreach ($holdings as $holding) {
            // Map asset_class (Commodity/Equity -> ETF for simplicity)
            $assetClass = $holding['asset_class'];
            if (!in_array($assetClass, ['ETF', 'Stock', 'Bond', 'Cash', 'Other'])) {
                $assetClass = 'ETF'; // Default
            }

            $this->db->execute(
                "INSERT INTO holdings
                 (portfolio_id, ticker, name, asset_class, quantity, avg_price, current_price, price_source, is_active)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1)
                 ON DUPLICATE KEY UPDATE
                    quantity = VALUES(quantity),
                    avg_price = VALUES(avg_price),
                    current_price = VALUES(current_price),
                    price_source = VALUES(price_source)",
                [
                    $this->portfolioId,
                    $holding['ticker'],
                    $holding['name'],
                    $assetClass,
                    $holding['quantity'],
                    $holding['avg_price'],
                    $holding['current_price'],
                    $holding['price_source'] ?? 'YahooFinance_v8'
                ]
            );

            $this->stats['holdings']++;
        }

        printSuccess("Holdings migrati: {$this->stats['holdings']}");
    }

    private function migrateTransactions($transactions) {
        printInfo("Migrazione transazioni (" . count($transactions) . " records)...");

        if (empty($transactions)) {
            printWarning("Nessuna transazione da migrare");
            return;
        }

        foreach ($transactions as $tx) {
            $this->db->execute(
                "INSERT INTO transactions
                 (portfolio_id, ticker, transaction_date, type, quantity, price, amount, fees, notes)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)",
                [
                    $this->portfolioId,
                    $tx['ticker'] ?? '',
                    $tx['date'] ?? date('Y-m-d'),
                    $tx['type'] ?? 'BUY',
                    $tx['quantity'] ?? 0,
                    $tx['price'] ?? 0,
                    $tx['amount'] ?? 0,
                    $tx['fees'] ?? 0,
                    $tx['notes'] ?? null
                ]
            );

            $this->stats['transactions']++;
        }

        printSuccess("Transazioni migrate: {$this->stats['transactions']}");
    }

    private function migrateDividends($dividends) {
        printInfo("Migrazione dividendi (" . count($dividends) . " records)...");

        if (empty($dividends)) {
            printWarning("Nessun dividendo da migrare");
            return;
        }

        foreach ($dividends as $div) {
            $this->db->execute(
                "INSERT INTO dividend_payments
                 (portfolio_id, ticker, ex_date, payment_date, amount_per_share, total_amount, quantity, status)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
                [
                    $this->portfolioId,
                    $div['ticker'] ?? '',
                    $div['ex_date'] ?? date('Y-m-d'),
                    $div['payment_date'] ?? null,
                    $div['amount_per_share'] ?? 0,
                    $div['total_amount'] ?? 0,
                    $div['quantity'] ?? 0,
                    $div['status'] ?? 'FORECAST'
                ]
            );

            $this->stats['dividends']++;
        }

        printSuccess("Dividendi migrati: {$this->stats['dividends']}");
    }

    private function migrateAllocations($allocations) {
        printInfo("Migrazione allocazioni (" . count($allocations) . " records)...");

        foreach ($allocations as $alloc) {
            $this->db->execute(
                "INSERT INTO allocation_by_asset_class
                 (portfolio_id, asset_class, market_value, percentage)
                 VALUES (?, ?, ?, ?)
                 ON DUPLICATE KEY UPDATE
                    market_value = VALUES(market_value),
                    percentage = VALUES(percentage)",
                [
                    $this->portfolioId,
                    $alloc['asset_class'],
                    $alloc['total_value'],
                    $alloc['percentage']
                ]
            );

            $this->stats['allocations']++;
        }

        printSuccess("Allocazioni migrate: {$this->stats['allocations']}");
    }

    private function migrateMonthlyPerformance($performance) {
        printInfo("Migrazione performance mensile (" . count($performance) . " records)...");

        $year = date('Y');
        $monthMapping = [
            'Jan' => 1, 'Feb' => 2, 'Mar' => 3, 'Apr' => 4,
            'May' => 5, 'Jun' => 6, 'Jul' => 7, 'Aug' => 8,
            'Sep' => 9, 'Oct' => 10, 'Nov' => 11, 'Dec' => 12
        ];

        foreach ($performance as $perf) {
            $monthLabel = $perf['month'];
            $monthNum = $monthMapping[$monthLabel] ?? 1;

            // Calculate basic metrics (these will be recalculated properly later)
            $value = $perf['value'] ?? 0;

            $this->db->execute(
                "INSERT INTO monthly_performance
                 (portfolio_id, year, month, month_label, total_value, total_invested, total_gain, gain_pct)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                 ON DUPLICATE KEY UPDATE
                    total_value = VALUES(total_value)",
                [
                    $this->portfolioId,
                    $year,
                    $monthNum,
                    $monthLabel,
                    $value,
                    0, // TODO: calculate from transactions
                    0, // TODO: calculate
                    0  // TODO: calculate
                ]
            );

            $this->stats['monthly_performance']++;
        }

        printSuccess("Performance mensile migrata: {$this->stats['monthly_performance']}");
    }

    private function migrateSnapshots($snapshots) {
        printInfo("Migrazione snapshots (" . count($snapshots) . " records)...");

        if (empty($snapshots)) {
            printWarning("Nessuno snapshot da migrare");
            return;
        }

        foreach ($snapshots as $snapshot) {
            // Create snapshot
            $snapshotId = $this->db->execute(
                "INSERT INTO snapshots
                 (portfolio_id, snapshot_date, total_invested, total_market_value, total_pnl, total_pnl_pct, total_dividends_received, metadata)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
                [
                    $this->portfolioId,
                    $snapshot['date'] ?? date('Y-m-d'),
                    $snapshot['metadata']['total_invested'] ?? 0,
                    $snapshot['metadata']['total_value'] ?? 0,
                    $snapshot['metadata']['unrealized_pnl'] ?? 0,
                    $snapshot['metadata']['unrealized_pnl_pct'] ?? 0,
                    $snapshot['metadata']['total_dividends'] ?? 0,
                    null
                ]
            );

            $snapshotId = $this->db->lastInsertId();
            $this->stats['snapshots']++;

            // Migrate holdings for this snapshot
            if (isset($snapshot['holdings']) && is_array($snapshot['holdings'])) {
                foreach ($snapshot['holdings'] as $holding) {
                    $this->db->execute(
                        "INSERT INTO snapshot_holdings
                         (snapshot_id, ticker, quantity, avg_price, current_price, market_value, invested, pnl, pnl_pct)
                         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)",
                        [
                            $snapshotId,
                            $holding['ticker'],
                            $holding['quantity'],
                            $holding['avg_price'],
                            $holding['current_price'] ?? $holding['avg_price'],
                            $holding['market_value'] ?? 0,
                            $holding['invested_value'] ?? 0,
                            $holding['unrealized_pnl'] ?? 0,
                            $holding['pnl_percentage'] ?? 0
                        ]
                    );

                    $this->stats['snapshot_holdings']++;
                }
            }
        }

        printSuccess("Snapshots migrati: {$this->stats['snapshots']}");
        printSuccess("Snapshot holdings migrati: {$this->stats['snapshot_holdings']}");
    }

    private function validateMigration($portfolioData, $snapshotsData) {
        printHeader("Validazione Migrazione");

        // Validate holdings count
        $holdingsCount = $this->db->fetchColumn(
            "SELECT COUNT(*) FROM holdings WHERE portfolio_id = ?",
            [$this->portfolioId]
        );

        if ($holdingsCount == count($portfolioData['holdings'])) {
            printSuccess("Holdings count: $holdingsCount ✓");
        } else {
            printError("Holdings count mismatch: DB=$holdingsCount, JSON=" . count($portfolioData['holdings']));
        }

        // Validate total invested
        $totalInvested = $this->holdingRepo->getTotalInvested();
        $jsonInvested = $portfolioData['metadata']['total_invested'];

        if (abs($totalInvested - $jsonInvested) < 0.01) {
            printSuccess("Total invested: €" . number_format($totalInvested, 2) . " ✓");
        } else {
            printWarning("Total invested mismatch: DB=€$totalInvested, JSON=€$jsonInvested");
        }

        // Validate total value
        $totalValue = $this->holdingRepo->getTotalValue();
        $jsonValue = $portfolioData['metadata']['total_value'];

        if (abs($totalValue - $jsonValue) < 0.01) {
            printSuccess("Total value: €" . number_format($totalValue, 2) . " ✓");
        } else {
            printWarning("Total value mismatch: DB=€$totalValue, JSON=€$jsonValue");
        }
    }

    private function printSummary() {
        printHeader("Riepilogo Migrazione");

        echo "Portfolios:           {$this->stats['portfolios']}" . PHP_EOL;
        echo "Holdings:             {$this->stats['holdings']}" . PHP_EOL;
        echo "Transactions:         {$this->stats['transactions']}" . PHP_EOL;
        echo "Dividends:            {$this->stats['dividends']}" . PHP_EOL;
        echo "Allocations:          {$this->stats['allocations']}" . PHP_EOL;
        echo "Monthly Performance:  {$this->stats['monthly_performance']}" . PHP_EOL;
        echo "Snapshots:            {$this->stats['snapshots']}" . PHP_EOL;
        echo "Snapshot Holdings:    {$this->stats['snapshot_holdings']}" . PHP_EOL;

        if (!empty($this->stats['errors'])) {
            echo PHP_EOL;
            printWarning("Errors: " . count($this->stats['errors']));
            foreach ($this->stats['errors'] as $error) {
                printError("  - $error");
            }
        }

        echo PHP_EOL;
        if ($this->validateOnly) {
            printInfo("VALIDATION COMPLETE - No data was written");
        } else {
            printSuccess("MIGRATION COMPLETE!");
        }
    }
}

// ============================================================================
// MAIN EXECUTION
// ============================================================================

// Parse command line arguments
$validateOnly = in_array('--validate-only', $argv);
$execute = in_array('--execute', $argv);

if (!$validateOnly && !$execute) {
    echo Color::YELLOW . "Usage:" . Color::RESET . PHP_EOL;
    echo "  php scripts/migrate-to-mysql.php --validate-only  (dry run)" . PHP_EOL;
    echo "  php scripts/migrate-to-mysql.php --execute         (execute migration)" . PHP_EOL;
    echo PHP_EOL;
    exit(1);
}

// Run migration
$migration = new MySQLMigration($validateOnly);
$success = $migration->run();

exit($success ? 0 : 1);
