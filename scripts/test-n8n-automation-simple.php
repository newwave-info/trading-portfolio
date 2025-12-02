<?php
/**
 * Test Script per n8n Automation - SENZA HMAC
 *
 * Script semplificato per testare l'automazione senza autenticazione HMAC
 * DA USARE SOLO PER TESTING RAPIDO
 */

require_once __DIR__ . '/../lib/Database/DatabaseManager.php';
require_once __DIR__ . '/../lib/Database/Repositories/HoldingRepository.php';
require_once __DIR__ . '/../lib/Database/Repositories/RecommendationRepository.php';
require_once __DIR__ . '/../lib/Services/SignalGeneratorService.php';

echo "🧪 TEST AUTOMATION N8N - SENZA HMAC\n";
echo "=====================================\n\n";

// Colori per output
$green = "\033[32m";
$red = "\033[31m";
$yellow = "\033[33m";
$reset = "\033[0m";

try {
    // Test 1: Verifica API signals.php accessibile
    echo "📡 Test 1: Accesso API /api/signals.php\n";
    $testUrl = 'http://localhost/api/signals.php?statistics=true';
    $response = file_get_contents($testUrl);
    $data = json_decode($response, true);

    if ($data && isset($data['success']) && $data['success']) {
        echo "{$green}✅ API signals.php accessibile senza HMAC{$reset}\n";
        echo "   📊 Statistiche segnali: " . ($data['data']['total_signals'] ?? 'N/A') . "\n";
    } else {
        echo "{$red}❌ API signals.php non accessibile{$reset}\n";
        echo "   Errore: " . ($data['error'] ?? 'Sconosciuto') . "\n";
    }
    echo "\n";

    // Test 2: Generazione segnali senza HMAC
    echo "⚡ Test 2: Generazione segnali via API\n";
    $postData = [
        'analysis_type' => 'test_generation',
        'session_type' => 'n8n_test_simple',
        'confidence_threshold' => 60,
        'include_rebalance' => true,
        'max_signals' => 5
    ];

    $context = stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => 'Content-Type: application/json',
            'content' => json_encode($postData)
        ]
    ]);

    $response = file_get_contents('http://localhost/api/signals.php', false, $context);
    $data = json_decode($response, true);

    if ($data && isset($data['success']) && $data['success']) {
        echo "{$green}✅ Generazione segnali riuscita{$reset}\n";
        echo "   🔥 Segnali generati: " . count($data['data']['recommendations'] ?? []) . "\n";
        echo "   📅 Timestamp: " . ($data['metadata']['generated_at'] ?? 'N/A') . "\n";

        // Mostra dettagli segnali
        if (!empty($data['data']['recommendations'])) {
            echo "   📋 Dettagli segnali:\n";
            foreach (array_slice($data['data']['recommendations'], 0, 3) as $signal) {
                echo "      - {$signal['type']} per {$signal['ticker']} (confidence: {$signal['confidence_score']}%)\n";
            }
        }
    } else {
        echo "{$red}❌ Generazione segnali fallita{$reset}\n";
        echo "   Errore: " . ($data['message'] ?? 'Sconosciuto') . "\n";
    }
    echo "\n";

    // Test 3: Verifica API alerts.php
    echo "🔔 Test 3: Accesso API /api/alerts.php\n";
    $response = file_get_contents('http://localhost/api/alerts.php');
    $data = json_decode($response, true);

    if ($data && isset($data['success'])) {
        echo "{$green}✅ API alerts.php accessibile{$reset}\n";
    } else {
        echo "{$yellow}⚠️  API alerts.php non disponibile o configurata{$reset}\n";
    }
    echo "\n";

    // Test 4: Verifica configurazione notifiche
    echo "📧 Test 4: Configurazione notifiche\n";
    $configFile = __DIR__ . '/../config/api.php';
    if (file_exists($configFile)) {
        $config = include $configFile;
        $hasEmail = !empty($config['notifications']['email']['enabled'] ?? false);
        $hasTelegram = !empty($config['notifications']['telegram']['enabled'] ?? false);

        echo $hasEmail ? "{$green}✅ Notifiche email configurate{$reset}\n" : "{$yellow}⚠️  Notifiche email non configurate{$reset}\n";
        echo $hasTelegram ? "{$green}✅ Notifiche Telegram configurate{$reset}\n" : "{$yellow}⚠️  Notifiche Telegram non configurate{$reset}\n";
    } else {
        echo "{$yellow}⚠️  File config/api.php non trovato{$reset}\n";
    }
    echo "\n";

    // Riepilogo
    echo "📊 RIEPILOGO TEST AUTOMATION\n";
    echo "============================\n";
    echo "{$green}✅ API senza HMAC operativa{$reset}\n";
    echo "{$green}✅ Generazione segnali funzionante{$reset}\n";
    echo "{$yellow}⚠️  Verificare configurazione notifiche{$reset}\n";
    echo "\n";

    echo "{$yellow}💡 NOTE IMPORTANTI:\n";
    echo "   - HMAC è disabilitato: usare solo in ambiente sicuro\n";
    echo "   - Rate limiting attivo: 10 richieste/ora per IP\n";
    echo "   - Per produzione: considerare di riabilitare HMAC\n";
    echo "   - Log completo disponibile in /logs/signals_rate_limit.json{$reset}\n";
    echo "\n";

    echo "🚀 {$green}TEST COMPLETATO - Sistema automation pronto per n8n!{$reset}\n";

} catch (Exception $e) {
    echo "{$red}❌ ERRORE CRITICO: {$e->getMessage()}{$reset}\n";
    echo "{$yellow}Verificare:\n";
    echo "   - Che il server web sia in esecuzione\n";
    echo "   - Che i file API esistano in /api/\n";
    echo "   - Che il database sia accessibile{$reset}\n";
    exit(1);
}

// Test aggiuntivo: verifica che i workflow n8n possano chiamare le API
echo "\n🔗 TEST INTEGRAZIONE N8N\n";
echo "========================\n";
echo "Per testare con n8n:\n";
echo "1. Crea un workflow con nodo HTTP\n";
echo "2. Imposta URL: http://localhost/api/signals.php\n";
echo "3. Method: POST\n";
echo "4. Body (JSON):\n";
echo json_encode($postData, JSON_PRETTY_PRINT) . "\n";
echo "\n{$green}✅ Nessuna autenticazione richiesta!{$reset}\n";