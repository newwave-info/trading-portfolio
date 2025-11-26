<?php
/**
 * n8n Integration API - Get Portfolio
 *
 * Returns current portfolio holdings for n8n enrichment workflow.
 * Protected by HMAC-SHA256 authentication (optional for GET).
 *
 * @endpoint GET /api/n8n/portfolio.php
 * @auth HMAC-SHA256 (header: X-Webhook-Signature) - optional
 */

header('Content-Type: application/json');
require_once __DIR__ . '/../../lib/PortfolioManager.php';
require_once __DIR__ . '/../../lib/HMACValidator.php';

// CORS headers for n8n (if needed)
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Webhook-Signature');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Only allow GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

try {
    // Optional HMAC validation for GET requests
    // Note: For GET requests, we use empty payload since there's no body
    // This is less secure than POST with body validation, but simpler for read-only operations
    $signature = $_SERVER['HTTP_X_WEBHOOK_SIGNATURE'] ?? '';

    if (!empty($signature)) {
        // Validate signature if provided (optional security layer)
        $payload = ''; // Empty payload for GET

        if (!HMACValidator::validate($payload, $signature)) {
            error_log("[n8n/portfolio] HMAC validation failed");
            http_response_code(401);
            echo json_encode([
                'success' => false,
                'error' => 'Invalid HMAC signature'
            ]);
            exit;
        }
    } else {
        // No signature provided - allow access (simple mode)
        // For production, consider adding IP whitelist or API key
        error_log("[n8n/portfolio] No HMAC signature provided, allowing access (simple mode)");
    }

    // Load portfolio
    $portfolioManager = new PortfolioManager();
    $data = $portfolioManager->getData();

    // Prepare response
    $response = [
        'success' => true,
        'metadata' => [
            'portfolio_name' => $data['metadata']['portfolio_name'] ?? 'Portfolio',
            'base_currency' => $data['metadata']['base_currency'] ?? 'EUR',
            'total_value' => $data['metadata']['total_value'] ?? 0,
            'last_update' => $data['metadata']['last_update'] ?? date('c'),
            'holdings_count' => count($data['holdings'])
        ],
        'holdings' => []
    ];

    // Format holdings for n8n
    foreach ($data['holdings'] as $holding) {
        $response['holdings'][] = [
            'isin' => $holding['isin'],
            'ticker' => $holding['ticker'] ?? '',
            'name' => $holding['name'] ?? '',
            'quantity' => $holding['quantity'] ?? 0,
            'avg_price' => $holding['avg_price'] ?? 0,
            'current_price' => $holding['current_price'] ?? 0,
            'asset_class' => $holding['asset_class'] ?? 'Unknown',
            'sector' => $holding['sector'] ?? 'Unknown',
            'instrument_type' => $holding['instrument_type'] ?? 'Unknown',
            'market' => $holding['market'] ?? '',
            'currency' => $holding['currency'] ?? 'EUR'
        ];
    }

    http_response_code(200);
    echo json_encode($response, JSON_PRETTY_PRINT);

} catch (Exception $e) {
    error_log("[n8n/portfolio] Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Internal server error',
        'message' => $e->getMessage()
    ]);
}
