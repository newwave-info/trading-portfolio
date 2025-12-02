<?php
/**
 * Test Frontend Recommendations Integration
 *
 * Verifica:
 * 1. Presenza di raccomandazioni nel database
 * 2. API endpoint /api/recommendations.php risponde correttamente
 * 3. Formato dati compatibile con JavaScript frontend
 */

require_once __DIR__ . '/../lib/Database/DatabaseManager.php';
require_once __DIR__ . '/../lib/Database/Repositories/RecommendationRepository.php';

echo "========================================\n";
echo "TEST FRONTEND RECOMMENDATIONS\n";
echo "========================================\n\n";

try {
    $db = DatabaseManager::getInstance();
    $recommendationRepo = new RecommendationRepository($db);
    $portfolioId = 1;

    // Test 1: Check database records
    echo "1. VERIFICA DATABASE\n";
    echo "--------------------\n";

    $result = $recommendationRepo->getFilteredRecommendations(
        $portfolioId,
        ['status' => 'ACTIVE'],
        1,
        100,
        'created_at',
        'DESC'
    );

    echo "Totale raccomandazioni ACTIVE: " . $result['total'] . "\n";

    if ($result['total'] > 0) {
        echo "\n✅ Trovate " . count($result['data']) . " raccomandazioni:\n\n";

        foreach ($result['data'] as $rec) {
            echo "  - ID: {$rec['id']}\n";
            echo "    Ticker: {$rec['ticker']}\n";
            echo "    Tipo: {$rec['type']}\n";
            echo "    Urgenza: {$rec['urgency']}\n";
            echo "    Confidence: {$rec['confidence_score']}%\n";
            echo "    Creato: {$rec['created_at']}\n";
            echo "\n";
        }
    } else {
        echo "\n⚠️  Nessuna raccomandazione ACTIVE trovata nel database\n";
        echo "    Esegui SignalGeneratorService per generare segnali\n\n";
    }

    // Test 2: Test API endpoint
    echo "\n2. TEST API ENDPOINT\n";
    echo "--------------------\n";

    $apiUrl = 'https://portfolio.newwave-media.it/api/recommendations.php?status=ACTIVE';

    echo "URL: $apiUrl\n";

    $ch = curl_init($apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'User-Agent: Mozilla/5.0 (compatible; ETF-Portfolio-Manager/2.1)',
        'Accept: application/json'
    ]);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Per test locale

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    echo "HTTP Status: $httpCode\n";

    if ($httpCode === 200) {
        echo "✅ API risponde correttamente\n\n";

        $json = json_decode($response, true);

        if ($json && isset($json['success']) && $json['success']) {
            echo "Risposta API:\n";
            echo "  - success: " . ($json['success'] ? 'true' : 'false') . "\n";
            echo "  - data count: " . count($json['data']) . "\n";
            echo "  - total: " . $json['pagination']['total'] . "\n";
            echo "  - page: " . $json['pagination']['page'] . "\n";
            echo "  - pages: " . $json['pagination']['pages'] . "\n";

            if (!empty($json['data'])) {
                echo "\n  Esempio prima raccomandazione:\n";
                $first = $json['data'][0];
                echo "    - id: {$first['id']}\n";
                echo "    - ticker: {$first['ticker']}\n";
                echo "    - type: {$first['type']}\n";
                echo "    - urgency: {$first['urgency']}\n";
                echo "    - confidence_score: {$first['confidence_score']}\n";
                echo "    - status: {$first['status']}\n";
            }
        } else {
            echo "❌ Risposta API non valida\n";
            echo "Risposta: $response\n";
        }
    } else {
        echo "❌ API non risponde correttamente (HTTP $httpCode)\n";
        echo "Risposta: $response\n";
    }

    // Test 3: Check JavaScript file
    echo "\n\n3. VERIFICA FILE JAVASCRIPT\n";
    echo "----------------------------\n";

    $jsFile = __DIR__ . '/../assets/js/recommendations.js';
    if (file_exists($jsFile)) {
        echo "✅ File recommendations.js esiste\n";
        echo "   Path: $jsFile\n";
        echo "   Size: " . filesize($jsFile) . " bytes\n";
    } else {
        echo "❌ File recommendations.js NON trovato\n";
    }

    // Test 4: Check footer.php includes JS
    echo "\n\n4. VERIFICA INCLUSIONE IN FOOTER\n";
    echo "----------------------------------\n";

    $footerFile = __DIR__ . '/../views/layouts/footer.php';
    if (file_exists($footerFile)) {
        $footerContent = file_get_contents($footerFile);
        if (strpos($footerContent, 'recommendations.js') !== false) {
            echo "✅ recommendations.js incluso in footer.php\n";
        } else {
            echo "❌ recommendations.js NON incluso in footer.php\n";
        }
    }

    echo "\n========================================\n";
    echo "TEST COMPLETATO\n";
    echo "========================================\n";

} catch (Exception $e) {
    echo "\n❌ ERRORE: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
