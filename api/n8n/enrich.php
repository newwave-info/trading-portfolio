<?php
/**
 * n8n Integration API - Enrich Portfolio
 *
 * Receives enriched holdings data from n8n and updates portfolio.json.
 * Triggers recalculation of all metrics (P&L, allocations, drift).
 * HMAC authentication with fallback for n8n crypto issues.
 *
 * @endpoint POST /api/n8n/enrich.php
 * @auth HMAC-SHA256 (header: X-Webhook-Signature) or HMAC_DISABLED flag
 */

header("Content-Type: application/json");
require_once __DIR__ . "/../../lib/PortfolioManager.php";
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

    // Load portfolio
    $portfolioManager = new PortfolioManager();
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

    // Update each holding
    foreach ($data["holdings"] as $enrichedHolding) {
        $isin = $enrichedHolding["isin"] ?? null;

        if (!$isin) {
            error_log("[n8n/enrich] Warning: Holding without ISIN, skipping");
            continue;
        }

        error_log("[n8n/enrich] Processing holding: {$isin} ({$enrichedHolding['ticker']} - {$enrichedHolding['name']})");

        // Find holding in portfolio
        $holding = $portfolioManager->getHoldingByIsin($isin);

        if (!$holding) {
            error_log(
                "[n8n/enrich] Warning: ISIN $isin not found in portfolio"
            );
            continue;
        }

        // Update fields if provided
        $updates = [];

        // Price
        if (isset($enrichedHolding["current_price"])) {
            $updates["current_price"] =
                (float) $enrichedHolding["current_price"];
        }

        // Classification
        if (
            isset($enrichedHolding["asset_class"]) &&
            $enrichedHolding["asset_class"] !== "Unknown"
        ) {
            $updates["asset_class"] = $enrichedHolding["asset_class"];
        }

        if (
            isset($enrichedHolding["sector"]) &&
            $enrichedHolding["sector"] !== "Unknown"
        ) {
            $updates["sector"] = $enrichedHolding["sector"];
        }

        // Financial metrics
        if (isset($enrichedHolding["expense_ratio"])) {
            $updates["expense_ratio"] =
                (float) $enrichedHolding["expense_ratio"];
        }

        if (isset($enrichedHolding["dividend_yield"])) {
            $updates["dividend_yield"] =
                (float) $enrichedHolding["dividend_yield"];
        }

        // 🆕 DIVIDEND DATA
        if (isset($enrichedHolding["annual_dividend"])) {
            $updates["annual_dividend"] =
                (float) $enrichedHolding["annual_dividend"];
        }

        if (isset($enrichedHolding["dividend_frequency"])) {
            $updates["dividend_frequency"] =
                $enrichedHolding["dividend_frequency"];
        }

        if (isset($enrichedHolding["has_dividends"])) {
            $updates["has_dividends"] = (bool) $enrichedHolding["has_dividends"];
        }

        if (isset($enrichedHolding["total_dividends_5y"])) {
            $updates["total_dividends_5y"] =
                (int) $enrichedHolding["total_dividends_5y"];
        }

        // 🆕 PRICE RANGES & PERFORMANCE
        if (isset($enrichedHolding["fifty_two_week_high"])) {
            $updates["fifty_two_week_high"] =
                (float) $enrichedHolding["fifty_two_week_high"];
        }

        if (isset($enrichedHolding["fifty_two_week_low"])) {
            $updates["fifty_two_week_low"] =
                (float) $enrichedHolding["fifty_two_week_low"];
        }

        if (isset($enrichedHolding["ytd_change_percent"])) {
            $updates["ytd_change_percent"] =
                (float) $enrichedHolding["ytd_change_percent"];
        }

        if (isset($enrichedHolding["one_month_change_percent"])) {
            $updates["one_month_change_percent"] =
                (float) $enrichedHolding["one_month_change_percent"];
        }

        if (isset($enrichedHolding["three_month_change_percent"])) {
            $updates["three_month_change_percent"] =
                (float) $enrichedHolding["three_month_change_percent"];
        }

        if (isset($enrichedHolding["one_year_change_percent"])) {
            $updates["one_year_change_percent"] =
                (float) $enrichedHolding["one_year_change_percent"];
        }

        if (isset($enrichedHolding["previous_close"])) {
            $updates["previous_close"] =
                (float) $enrichedHolding["previous_close"];
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
            $updates["first_trade_date"] =
                (int) $enrichedHolding["first_trade_date"];
        }

        // Apply updates
        if (!empty($updates)) {
            $portfolioManager->updateHolding($isin, $updates);
            $updatedCount++;
            error_log(
                "[n8n/enrich] Updated holding $isin: " . json_encode($updates)
            );
        }
    }

    // Recalculate all metrics (P&L, allocations, drift)
    $portfolioManager->recalculateMetrics();

    // Save portfolio
    $portfolioManager->save();

    // Create daily snapshot (for performance charts)
    $snapshotsFile = __DIR__ . "/../../data/snapshots.json";
    $snapshots = file_exists($snapshotsFile)
        ? json_decode(file_get_contents($snapshotsFile), true)
        : ["snapshots" => []];

    $today = date("Y-m-d");
    $snapshotExists = false;
    foreach ($snapshots["snapshots"] as $snap) {
        if ($snap["date"] === $today) {
            $snapshotExists = true;
            break;
        }
    }

    if (!$snapshotExists) {
        $currentData = $portfolioManager->getData();
        $snapshots["snapshots"][] = [
            "date" => $today,
            "metadata" => $currentData["metadata"],
            "holdings" => $currentData["holdings"],
        ];

        // Sort by date
        usort($snapshots["snapshots"], fn($a, $b) => $a["date"] <=> $b["date"]);
        file_put_contents(
            $snapshotsFile,
            json_encode($snapshots, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );
        error_log("[n8n/enrich] Created daily snapshot for $today");

        // Update monthly_performance in portfolio.json
        $byMonth = [];
        foreach ($snapshots["snapshots"] as $snap) {
            $month = date("M", strtotime($snap["date"]));
            $year = date("Y", strtotime($snap["date"]));
            $key = $year . "-" . $month;
            if (
                !isset($byMonth[$key]) ||
                $snap["date"] > $byMonth[$key]["date"]
            ) {
                $byMonth[$key] = [
                    "month" => $month,
                    "value" => $snap["metadata"]["total_value"],
                    "date" => $snap["date"],
                ];
            }
        }
        usort($byMonth, fn($a, $b) => $a["date"] <=> $b["date"]);
        $monthlyPerformance = array_slice(
            array_map(
                fn($item) => [
                    "month" => $item["month"],
                    "value" => $item["value"],
                ],
                $byMonth
            ),
            -12
        );

        $currentData["monthly_performance"] = $monthlyPerformance;
        $portfolioManager->setData($currentData);
        $portfolioManager->save();
        error_log(
            "[n8n/enrich] Updated monthly_performance with " .
                count($monthlyPerformance) .
                " months"
        );
    }

    // 🆕 Generate dividends calendar
    try {
        $dividendsCalendar = $portfolioManager->generateDividendsCalendar();
        $dividendsFile = __DIR__ . "/../../data/dividends_calendar.json";

        file_put_contents(
            $dividendsFile,
            json_encode(
                $dividendsCalendar,
                JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
            )
        );

        error_log(
            "[n8n/enrich] Updated dividends calendar with " .
                count($dividendsCalendar["distributing_assets"]) .
                " distributing assets"
        );
    } catch (Exception $e) {
        error_log(
            "[n8n/enrich] Warning: Could not generate dividends calendar: " .
                $e->getMessage()
        );
    }

    // Get updated metadata
    $updatedData = $portfolioManager->getData();
    $metadata = $updatedData["metadata"];

    // Log success
    error_log("[n8n/enrich] Successfully enriched $updatedCount holdings");

    // Response
    http_response_code(200);
    echo json_encode(
        [
            "success" => true,
            "message" => "Portfolio enriched successfully",
            "updated_holdings" => $updatedCount,
            "metadata" => [
                "total_value" => $metadata["total_value"] ?? 0,
                "unrealized_pnl" => $metadata["unrealized_pnl"] ?? 0,
                "unrealized_pnl_pct" => $metadata["unrealized_pnl_pct"] ?? 0,
                "holdings_count" => $metadata["holdings_count"] ?? 0,
                "last_update" => $metadata["last_update"] ?? date("c"),
            ],
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
