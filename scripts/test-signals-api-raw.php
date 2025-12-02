<?php
/**
 * Test diretto API signals.php per debug output
 */

// Cattura tutto l'output
ob_start();

// Simula richiesta POST
$_SERVER['REQUEST_METHOD'] = 'POST';
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';

// Crea input JSON simulato
$input = [
    'portfolio_id' => 1,
    'analysis_type' => 'daily_generation',
    'session_type' => 'europe_close',
    'include_rebalance' => true,
    'confidence_threshold' => 60
];

// Simula php://input
$GLOBALS['mock_input'] = json_encode($input);

// Override file_get_contents per simulare input
if (!function_exists('original_file_get_contents')) {
    function original_file_get_contents($filename, ...$args) {
        return \file_get_contents($filename, ...$args);
    }
}

echo "=== TEST SIGNALS API ===\n\n";
echo "Input simulato:\n";
echo json_encode($input, JSON_PRETTY_PRINT) . "\n\n";
echo "=== OUTPUT API ===\n\n";

try {
    // Includi l'API
    include __DIR__ . '/../api/signals.php';
} catch (Exception $e) {
    echo "ERRORE CATTURATO: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

$output = ob_get_clean();

echo $output;

echo "\n\n=== ANALISI OUTPUT ===\n";
echo "Lunghezza output: " . strlen($output) . " bytes\n";

// Controlla se è JSON valido
$decoded = json_decode($output);
if ($decoded === null && json_last_error() !== JSON_ERROR_NONE) {
    echo "❌ OUTPUT NON È JSON VALIDO\n";
    echo "Errore JSON: " . json_last_error_msg() . "\n\n";

    // Mostra primi 500 caratteri in hex per debug
    echo "Primi 500 caratteri (hex dump):\n";
    echo bin2hex(substr($output, 0, 500)) . "\n\n";

    // Cerca caratteri non-JSON all'inizio
    echo "Primi 200 caratteri (raw):\n";
    echo substr($output, 0, 200) . "\n\n";

} else {
    echo "✅ OUTPUT È JSON VALIDO\n";
    echo "JSON decodificato:\n";
    print_r($decoded);
}
