<?php
/**
 * n8n Integration API - Enrich Portfolio
 *
 * Receives enriched holdings data from n8n and updates holdings in MySQL.
 * Triggers recalculation of all metrics (P&L, allocations, snapshot, monthly perf).
 * HMAC authentication with fallback for n8n crypto issues.
 *
 * @endpoint POST /api/n8n/enrich.php
 * @auth HMAC-SHA256 (header: X-Webhook-Signature) or HMAC_DISABLED flag
 */

header("Content-Type: application/json");
require_once __DIR__ . "/../../lib/Database/DatabaseManager.php";
require_once __DIR__ . "/../../lib/Database/Repositories/HoldingRepository.php";
require_once __DIR__ . "/../../lib/Database/Repositories/PortfolioRepository.php";
require_once __DIR__ . "/../../lib/Database/Services/PortfolioMetricsService.php";
require_once __DIR__ . "/../../lib/HMACValidator.php";

// CORS headers for n8n
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header(
    "Access-Control-Allow-Headers: Content-Type, X-Webhook-Signature, User-Agent"
);

if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
    http_response_code(200);
    exit();
}

// Only allow POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode(["error" => "Method not allowed"]);
    exit();
}

try {
    // TODO: multi-portafoglio - per ora assumiamo portfolio_id=1
    $portfolioId = PortfolioRepository::DEFAULT_PORTFOLIO_ID;

    // Get raw payload
    $rawPayload = file_get_contents("php://input");
    $signature = $_SERVER["HTTP_X_WEBHOOK_SIGNATURE"] ?? "";

    // Validate HMAC (with fallback for n8n crypto issues)
    if ($signature === "HMAC_DISABLED") {
        error_log("[n8n/enrich] HMAC validation skipped (HMAC_DISABLED flag)");
        // TODO: Add IP whitelist for security when HMAC is disabled
    } elseif (!HMACValidator::validate($rawPayload, $signature)) {
        error_log("[n8n/enrich] Invalid HMAC signature received");
        http_response_code(401);
        echo json_encode([
            "success" => false,
            "error" => "Invalid HMAC signature",
        ]);
        exit();
    }

    // Parse JSON
    $data = json_decode($rawPayload, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(400);
        echo json_encode([
            "success" => false,
            "error" => "Invalid JSON",
            "details" => json_last_error_msg(),
        ]);
        exit();
    }

    // Validate required fields
    if (!isset($data["holdings"]) || !is_array($data["holdings"])) {
        http_response_code(400);
        echo json_encode([
            "success" => false,
            "error" => "Missing or invalid holdings array",
        ]);
        exit();
    }

    // Log workflow execution
    $workflowId = $data["workflow_id"] ?? "unknown";
    $timestamp = $data["timestamp"] ?? date("c");
    error_log(
        "[n8n/enrich] Received enrichment from workflow: $workflowId at $timestamp"
    );

    // DB-first: init repositories/services
    $db = DatabaseManager::getInstance();
    $holdingRepo = new HoldingRepository($db);
    $portfolioRepo = new PortfolioRepository($db);
    $metricsService = new PortfolioMetricsService($db);

    $updatedCount = 0;

    // Log received holdings for debugging
    error_log("[n8n/enrich] Received " . count($data["holdings"]) . " holdings from n8n");
    $receivedISINs = array_map(fn($h) => $h['isin'] ?? 'NO-ISIN', $data["holdings"]);
    error_log("[n8n/enrich] ISINs received: " . implode(", ", $receivedISINs));

    // Check for duplicates in received data
    $isinCounts = array_count_values($receivedISINs);
    $hasDuplicates = false;
    foreach ($isinCounts as $isin => $count) {
        if ($count > 1) {
            error_log("[n8n/enrich] WARNING: Duplicate ISIN received from n8n: {$isin} (appears {$count} times)");
            $hasDuplicates = true;
        }
    }

    // Remove duplicates - keep only first occurrence of each ISIN
    if ($hasDuplicates) {
        $uniqueHoldings = [];
        $seenISINs = [];
        foreach ($data["holdings"] as $holding) {
            $isin = $holding["isin"] ?? null;
            if ($isin && !in_array($isin, $seenISINs)) {
                $uniqueHoldings[] = $holding;
                $seenISINs[] = $isin;
            } elseif ($isin) {
                error_log("[n8n/enrich] Skipping duplicate holding: {$isin}");
            }
        }
        $data["holdings"] = $uniqueHoldings;
        error_log("[n8n/enrich] Deduplicated holdings: " . count($uniqueHoldings) . " unique holdings");
    }

    // Update each holding in DB (enrichment only)
    foreach ($data["holdings"] as $enrichedHolding) {
        $isin = $enrichedHolding["isin"] ?? null;

        if (!$isin) {
            error_log("[n8n/enrich] Warning: Holding without ISIN, skipping");
            continue;
        }

        error_log("[n8n/enrich] Processing holding: {$isin} ({$enrichedHolding['ticker']} - {$enrichedHolding['name']})");

        $holding = $holdingRepo->findByIsin($isin, $portfolioId);
        if (!$holding) {
            error_log("[n8n/enrich] Warning: ISIN $isin not found in DB, skipping");
            continue;
        }

        $updates = [];

        // Price
        if (isset($enrichedHolding["current_price"])) {
            $updates["current_price"] = (float) $enrichedHolding["current_price"];
        }

        // Classification
        if (isset($enrichedHolding["asset_class"]) && $enrichedHolding["asset_class"] !== "Unknown") {
            $updates["asset_class"] = $enrichedHolding["asset_class"];
        }
        if (isset($enrichedHolding["sector"]) && $enrichedHolding["sector"] !== "Unknown") {
            $updates["sector"] = $enrichedHolding["sector"];
        }

        // Financial metrics
        if (isset($enrichedHolding["dividend_yield"])) {
            $updates["dividend_yield"] = (float) $enrichedHolding["dividend_yield"];
        }
        if (isset($enrichedHolding["annual_dividend"])) {
            $updates["annual_dividend"] = (float) $enrichedHolding["annual_dividend"];
        }
        if (isset($enrichedHolding["dividend_frequency"])) {
            $updates["dividend_frequency"] = $enrichedHolding["dividend_frequency"];
        }
        if (isset($enrichedHolding["has_dividends"])) {
            $updates["has_dividends"] = (bool) $enrichedHolding["has_dividends"];
        }
        if (isset($enrichedHolding["total_dividends_5y"])) {
            $updates["total_dividends_5y"] = (int) $enrichedHolding["total_dividends_5y"];
        }

        // Price ranges & performance
        if (isset($enrichedHolding["fifty_two_week_high"])) {
            $updates["fifty_two_week_high"] = (float) $enrichedHolding["fifty_two_week_high"];
        }
        if (isset($enrichedHolding["fifty_two_week_low"])) {
            $updates["fifty_two_week_low"] = (float) $enrichedHolding["fifty_two_week_low"];
        }
        if (isset($enrichedHolding["ytd_change_percent"])) {
            $updates["ytd_change_percent"] = (float) $enrichedHolding["ytd_change_percent"];
        }
        if (isset($enrichedHolding["one_month_change_percent"])) {
            $updates["one_month_change_percent"] = (float) $enrichedHolding["one_month_change_percent"];
        }
        if (isset($enrichedHolding["three_month_change_percent"])) {
            $updates["three_month_change_percent"] = (float) $enrichedHolding["three_month_change_percent"];
        }
        if (isset($enrichedHolding["one_year_change_percent"])) {
            $updates["one_year_change_percent"] = (float) $enrichedHolding["one_year_change_percent"];
        }

        if (isset($enrichedHolding["previous_close"])) {
            $updates["previous_close"] = (float) $enrichedHolding["previous_close"];
        }
        if (isset($enrichedHolding["day_high"])) {
            $updates["day_high"] = (float) $enrichedHolding["day_high"];
        }
        if (isset($enrichedHolding["day_low"])) {
            $updates["day_low"] = (float) $enrichedHolding["day_low"];
        }
        if (isset($enrichedHolding["volume"])) {
            $updates["volume"] = (int) $enrichedHolding["volume"];
        }

        if (isset($enrichedHolding["price_source"])) {
            $updates["price_source"] = $enrichedHolding["price_source"];
        }
        if (isset($enrichedHolding["exchange"])) {
            $updates["exchange"] = $enrichedHolding["exchange"];
        }
        if (isset($enrichedHolding["first_trade_date"])) {
            $updates["first_trade_date"] = (int) $enrichedHolding["first_trade_date"];
        }

        if (!empty($updates)) {
            $holdingRepo->updateFieldsByIsin($portfolioId, $isin, $updates);
            $updatedCount++;
            error_log("[n8n/enrich] Updated holding $isin: " . json_encode($updates));
        }
    }

    // Recalculate metrics/snapshot/monthly performance in DB
    $metricsService->recalculateAllForPortfolio($portfolioId);

    // Metadata from DB
    $metadataDb = $portfolioRepo->getMetadata($portfolioId);

    $metadata = [
        "total_value" => $metadataDb["total_market_value"] ?? 0,
        "unrealized_pnl" => $metadataDb["total_pnl"] ?? 0,
        "unrealized_pnl_pct" => $metadataDb["total_pnl_pct"] ?? 0,
        "holdings_count" => $metadataDb["total_holdings"] ?? $updatedCount,
        "last_update" => $metadataDb["last_update"] ?? date("c"),
        "base_currency" => $metadataDb["base_currency"] ?? "EUR",
    ];

    // Log success
    error_log("[n8n/enrich] Successfully enriched $updatedCount holdings");

    // Response
    http_response_code(200);
    echo json_encode(
        [
            "success" => true,
            "message" => "Portfolio enriched successfully",
            "updated_holdings" => $updatedCount,
            "metadata" => $metadata,
        ],
        JSON_PRETTY_PRINT
    );
} catch (Exception $e) {
    error_log("[n8n/enrich] Error: " . $e->getMessage());
    error_log("[n8n/enrich] Trace: " . $e->getTraceAsString());

    http_response_code(500);
    echo json_encode([
        "success" => false,
        "error" => "Internal server error",
        "message" => $e->getMessage(),
    ]);
}
