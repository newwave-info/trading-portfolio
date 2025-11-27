<?php
/**
 * API Webhook - n8n Price Update
 *
 * Riceve aggiornamenti prezzi da n8n e aggiorna il database MySQL.
 *
 * Endpoint:
 * - POST /api/update.php
 *
 * Expected payload:
 * {
 *   "holdings": [
 *     {"ticker": "SGLD.MI", "price": 345.50, "source": "YahooFinance_v8"},
 *     {"ticker": "VHYL.MI", "price": 68.85, "source": "YahooFinance_v8"}
 *   ]
 * }
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../lib/Database/DatabaseManager.php';
require_once __DIR__ . '/../lib/Database/Repositories/HoldingRepository.php';
require_once __DIR__ . '/../lib/Database/Repositories/PortfolioRepository.php';

// CORS headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-HMAC-Signature');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

/**
 * Verify HMAC signature (optional but recommended)
 */
function verifyHmacSignature($payload, $signature) {
    $secret = getenv('N8N_WEBHOOK_SECRET');

    // If no secret is configured, skip validation
    if (!$secret || $secret === 'your_hmac_secret_key_here') {
        return true;
    }

    $expected = hash_hmac('sha256', $payload, $secret);
    return hash_equals($expected, $signature);
}

try {
    // Only accept POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Method not allowed');
    }

    // Get raw payload
    $payload = file_get_contents('php://input');

    if (empty($payload)) {
        throw new Exception('Empty payload');
    }

    // Verify HMAC signature (if provided)
    $signature = $_SERVER['HTTP_X_HMAC_SIGNATURE'] ?? null;
    if ($signature && !verifyHmacSignature($payload, $signature)) {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'error' => 'Invalid HMAC signature'
        ]);
        exit;
    }

    // Parse JSON
    $data = json_decode($payload, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON: ' . json_last_error_msg());
    }

    // Validate structure
    if (!isset($data['holdings']) || !is_array($data['holdings'])) {
        throw new Exception('Invalid payload structure: missing "holdings" array');
    }

    // Initialize repositories
    $db = DatabaseManager::getInstance();
    $holdingRepo = new HoldingRepository($db);
    $portfolioRepo = new PortfolioRepository($db);

    // Prepare price updates
    $priceUpdates = [];
    foreach ($data['holdings'] as $item) {
        // Support both "ticker" and "symbol" keys
        $ticker = $item['ticker'] ?? $item['symbol'] ?? null;
        $price = $item['price'] ?? $item['current_price'] ?? null;
        $source = $item['source'] ?? $item['price_source'] ?? 'YahooFinance_v8';

        if ($ticker && $price) {
            $priceUpdates[] = [
                'ticker' => $ticker,
                'price' => $price,
                'source' => $source
            ];
        }
    }

    if (empty($priceUpdates)) {
        throw new Exception('No valid price updates found in payload');
    }

    // Bulk update prices (uses transaction internally)
    $result = $holdingRepo->bulkUpdatePrices($priceUpdates);

    // Update portfolio last_update timestamp
    if ($result['success'] && $result['updated'] > 0) {
        $portfolioRepo->updateLastUpdate();
    }

    // Return result
    echo json_encode($result);

} catch (Exception $e) {
    error_log("n8n webhook error: " . $e->getMessage());

    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}
