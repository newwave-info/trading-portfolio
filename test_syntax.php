<?php
/**
 * Test Sintassi - Verifica che i file PHP siano corretti
 */

echo "🔍 TEST SINTASSI PHP\n";
echo "===================\n\n";

$files = [
    'lib/Models/Recommendation.php',
    'lib/Services/SignalGeneratorService.php',
    'lib/Database/Repositories/RecommendationRepository.php',
    'lib/Database/Repositories/HoldingRepository.php'
];

$errors = [];

foreach ($files as $file) {
    echo "Testing: $file\n";

    if (!file_exists($file)) {
        echo "❌ File non trovato\n";
        continue;
    }

    // Test sintassi PHP
    $output = shell_exec("php -l $file 2>&1");

    if (strpos($output, 'No syntax errors') !== false) {
        echo "✅ OK - Nessun errore di sintassi\n";
    } else {
        echo "❌ ERRORE SINTASSI:\n";
        echo $output . "\n";
        $errors[] = $file;
    }
    echo "\n";
}

echo "Risultato: ";
if (empty($errors)) {
    echo "✅ TUTTI I FILE SONO CORRETTI\n";
} else {
    echo "❌ TROVATI ERRORI NEI FILE: " . implode(', ', $errors) . "\n";
}

echo "\n🔧 Prossimo passo: esegui test_rapido.php per verificare il funzionamento\n";
?>