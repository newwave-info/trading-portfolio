#!/usr/bin/env php
<?php
/**
 * Test API Recommendations
 *
 * Script per testare l'endpoint /api/recommendations.php
 */

echo "🧪 Test API Raccomandazioni\n";
echo "==========================\n\n";

// Test 1: Verifica che il file esista e sia leggibile
$apiFile = __DIR__ . '/../api/recommendations.php';
if (!file_exists($apiFile)) {
    echo "❌ File API non trovato: $apiFile\n";
    exit(1);
}

echo "✅ File API trovato: $apiFile\n";

// Test 2: Verifica che i file necessari esistano
$requiredFiles = [
    __DIR__ . '/../lib/Database/DatabaseManager.php',
    __DIR__ . '/../lib/Database/Repositories/RecommendationRepository.php',
    __DIR__ . '/../lib/Database/Repositories/HoldingRepository.php',
    __DIR__ . '/../lib/Models/Recommendation.php'
];

$allFilesPresent = true;
foreach ($requiredFiles as $file) {
    if (!file_exists($file)) {
        echo "❌ File mancante: $file\n";
        $allFilesPresent = false;
    } else {
        echo "✅ File presente: " . basename($file) . "\n";
    }
}

if (!$allFilesPresent) {
    echo "\n❌ Test fallito: file mancanti\n";
    exit(1);
}

echo "\n✅ Tutti i file necessari sono presenti\n";

// Test 3: Verifica struttura base API
echo "\n📋 Analisi struttura API:\n";
$apiContent = file_get_contents($apiFile);

// Controlla presenza metodi HTTP
$methods = ['GET', 'POST', 'PUT', 'DELETE'];
foreach ($methods as $method) {
    if (strpos($apiContent, "REQUEST_METHOD === '$method'") !== false) {
        echo "✅ Metodo $method supportato\n";
    } else {
        echo "⚠️  Metodo $method non trovato\n";
    }
}

// Controlla filtri
$filters = ['status', 'holding_id', 'urgency', 'page', 'per_page'];
echo "\n🔍 Filtri disponibili:\n";
foreach ($filters as $filter) {
    if (strpos($apiContent, "_GET['$filter']") !== false) {
        echo "✅ Filtro $filter implementato\n";
    } else {
        echo "⚠️  Filtro $filter non trovato\n";
    }
}

// Test 4: Verifica presenza funzioni necessarie
echo "\n🔧 Funzioni repository:\n";
$functions = [
    'getFilteredRecommendations',
    'findById',
    'softDelete',
    'logAction',
    'getStatistics'
];

foreach ($functions as $function) {
    if (strpos($apiContent, $function) !== false) {
        echo "✅ Funzione $function utilizzata\n";
    } else {
        echo "⚠️  Funzione $function non trovata\n";
    }
}

echo "\n✅ Analisi completata\n";
echo "\n📌 Per testare l'API:\n";
echo "1. Apri browser: https://your-domain.com/api/recommendations.php?statistics=true\n";
echo "2. Prova filtri: ?status=ACTIVE\u0026urgency=IMMEDIATO\u0026page=1\u0026per_page=10\n";
echo "3. Verifica con curl: curl https://your-domain.com/api/recommendations.php\n";
echo "4. Test creazione: usa POST con JSON valido\n";
echo "\n📚 Documentazione completa: /docs/09-API-RECOMMENDATIONS.md\n";
echo "\n🎉 Test struttura completato con successo!\n";
echo "⚠️  Ricorda: configura CORS e domini consentiti per produzione\n";