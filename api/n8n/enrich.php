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
require_once __DIR__ .
    "/../../lib/Database/Repositories/PortfolioRepository.php";
require_once __DIR__ .
    "/../../lib/Database/Repositories/TechnicalSnapshotRepository.php";
require_once __DIR__ .
    "/../../lib/Database/Services/PortfolioMetricsService.php";
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

    // Payload può arrivare sia come { holdings: [...] } che come { payload: { holdings: [...] } }
    if (isset($data["payload"]) && is_array($data["payload"])) {
        // n8n ci manda { payload: { workflow_id, timestamp, holdings } }
        $payload = $data["payload"];
    } else {
        $payload = $data;
    }

    // Validate required fields
    if (!isset($payload["holdings"]) || !is_array($payload["holdings"])) {
        http_response_code(400);
        echo json_encode([
            "success" => false,
            "error" => "Missing or invalid holdings array",
        ]);
        exit();
    }

    // Log workflow execution (silenced to avoid noise in error_log)
    $workflowId = $payload["workflow_id"] ?? "unknown";
    $timestamp = $payload["timestamp"] ?? date("c");

    // Calcolo data di riferimento analisi tecnica (technical_as_of)
    $technicalAsOf = null;
    try {
        $dt = new DateTime($timestamp);
        $technicalAsOf = $dt->format("Y-m-d");
    } catch (Exception $e) {
        $technicalAsOf = date("Y-m-d");
    }

    // DB-first: init repositories/services
    $db = DatabaseManager::getInstance();
    $holdingRepo = new HoldingRepository($db);
    $portfolioRepo = new PortfolioRepository($db);
    $metricsService = new PortfolioMetricsService($db);
    $technicalSnapshotRepo = new TechnicalSnapshotRepository($db);

    $updatedCount = 0;

    $holdingsFromPayload = $payload["holdings"];

    // Prepara lista ISIN per deduplica
    $receivedISINs = array_map(
        fn($h) => $h["isin"] ?? "",
        $holdingsFromPayload
    );
    $receivedISINs = array_filter($receivedISINs, fn($i) => $i !== "");

    // Check for duplicates in received data
    $isinCounts = !empty($receivedISINs) ? array_count_values($receivedISINs) : [];
    $hasDuplicates = false;
    foreach ($isinCounts as $isin => $count) {
        if ($count > 1) {
            $hasDuplicates = true;
        }
    }

    // Remove duplicates - keep only first occurrence of each ISIN
    if ($hasDuplicates) {
        $uniqueHoldings = [];
        $seenISINs = [];
        foreach ($holdingsFromPayload as $holdingItem) {
            $isin = $holdingItem["isin"] ?? null;
            if ($isin && !in_array($isin, $seenISINs, true)) {
                $uniqueHoldings[] = $holdingItem;
                $seenISINs[] = $isin;
            } elseif ($isin) {
            }
        }
        $holdingsFromPayload = $uniqueHoldings;
        error_log(
            "[n8n/enrich] Deduplicated holdings: " .
                count($uniqueHoldings) .
                " unique holdings"
        );
    }

    // Update each holding in DB (enrichment + technical snapshot)
    foreach ($holdingsFromPayload as $enrichedHolding) {
        $isin = $enrichedHolding["isin"] ?? null;

        if (!$isin) {
            continue;
        }

        $holding = $holdingRepo->findByIsin($isin, $portfolioId);
        if (!$holding) {
            continue;
        }

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
        if (isset($enrichedHolding["dividend_yield"])) {
            $updates["dividend_yield"] =
                (float) $enrichedHolding["dividend_yield"];
        }
        if (isset($enrichedHolding["annual_dividend"])) {
            $updates["annual_dividend"] =
                (float) $enrichedHolding["annual_dividend"];
        }
        if (isset($enrichedHolding["dividend_frequency"])) {
            $updates["dividend_frequency"] =
                $enrichedHolding["dividend_frequency"];
        }
        if (isset($enrichedHolding["has_dividends"])) {
            $updates["has_dividends"] =
                (bool) $enrichedHolding["has_dividends"];
        }
        if (isset($enrichedHolding["total_dividends_5y"])) {
            $updates["total_dividends_5y"] =
                (int) $enrichedHolding["total_dividends_5y"];
        }

        // Price ranges & performance
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

        /**
         * Analisi tecnica – snapshot ultimo prezzo
         */

        // Medie mobili
        if (isset($enrichedHolding["sma9"])) {
            $updates["sma9"] = (float) $enrichedHolding["sma9"];
        }
        if (isset($enrichedHolding["sma21"])) {
            $updates["sma21"] = (float) $enrichedHolding["sma21"];
        }
        if (isset($enrichedHolding["sma50"])) {
            $updates["sma50"] = (float) $enrichedHolding["sma50"];
        }
        if (isset($enrichedHolding["sma200"])) {
            $updates["sma200"] = (float) $enrichedHolding["sma200"];
        }

        if (isset($enrichedHolding["ema9"])) {
            $updates["ema9"] = (float) $enrichedHolding["ema9"];
        }
        if (isset($enrichedHolding["ema21"])) {
            $updates["ema21"] = (float) $enrichedHolding["ema21"];
        }
        if (isset($enrichedHolding["ema50"])) {
            $updates["ema50"] = (float) $enrichedHolding["ema50"];
        }
        if (isset($enrichedHolding["ema200"])) {
            $updates["ema200"] = (float) $enrichedHolding["ema200"];
        }

        // Oscillatori
        if (isset($enrichedHolding["rsi14"])) {
            $updates["rsi14"] = (float) $enrichedHolding["rsi14"];
        }
        if (isset($enrichedHolding["macd_value"])) {
            $updates["macd_value"] = (float) $enrichedHolding["macd_value"];
        }
        if (isset($enrichedHolding["macd_signal"])) {
            $updates["macd_signal"] = (float) $enrichedHolding["macd_signal"];
        }
        if (isset($enrichedHolding["macd_hist"])) {
            $updates["macd_hist"] = (float) $enrichedHolding["macd_hist"];
        }

        // Volatilità
        if (isset($enrichedHolding["atr14"])) {
            $updates["atr14"] = (float) $enrichedHolding["atr14"];
        }
        if (isset($enrichedHolding["atr14_pct"])) {
            $updates["atr14_pct"] = (float) $enrichedHolding["atr14_pct"];
        }
        if (array_key_exists("hist_vol_30d", $enrichedHolding)) {
            $updates["hist_vol_30d"] =
                $enrichedHolding["hist_vol_30d"] !== null
                    ? (float) $enrichedHolding["hist_vol_30d"]
                    : null;
        }
        if (array_key_exists("hist_vol_90d", $enrichedHolding)) {
            $updates["hist_vol_90d"] =
                $enrichedHolding["hist_vol_90d"] !== null
                    ? (float) $enrichedHolding["hist_vol_90d"]
                    : null;
        }

        // Volumi
        if (isset($enrichedHolding["vol_avg_20d"])) {
            $updates["vol_avg_20d"] = (int) $enrichedHolding["vol_avg_20d"];
        }
        if (isset($enrichedHolding["vol_ratio_current_20d"])) {
            $updates["vol_ratio_current_20d"] =
                (float) $enrichedHolding["vol_ratio_current_20d"];
        }

        // Range & posizione nel range
        if (isset($enrichedHolding["range_1m_min"])) {
            $updates["range_1m_min"] = (float) $enrichedHolding["range_1m_min"];
        }
        if (isset($enrichedHolding["range_1m_max"])) {
            $updates["range_1m_max"] = (float) $enrichedHolding["range_1m_max"];
        }
        if (isset($enrichedHolding["range_1m_percentile"])) {
            $updates["range_1m_percentile"] =
                (float) $enrichedHolding["range_1m_percentile"];
        }

        if (isset($enrichedHolding["range_3m_min"])) {
            $updates["range_3m_min"] = (float) $enrichedHolding["range_3m_min"];
        }
        if (isset($enrichedHolding["range_3m_max"])) {
            $updates["range_3m_max"] = (float) $enrichedHolding["range_3m_max"];
        }
        if (isset($enrichedHolding["range_3m_percentile"])) {
            $updates["range_3m_percentile"] =
                (float) $enrichedHolding["range_3m_percentile"];
        }

        if (isset($enrichedHolding["range_6m_min"])) {
            $updates["range_6m_min"] = (float) $enrichedHolding["range_6m_min"];
        }
        if (isset($enrichedHolding["range_6m_max"])) {
            $updates["range_6m_max"] = (float) $enrichedHolding["range_6m_max"];
        }
        if (isset($enrichedHolding["range_6m_percentile"])) {
            $updates["range_6m_percentile"] =
                (float) $enrichedHolding["range_6m_percentile"];
        }

        if (isset($enrichedHolding["range_1y_min"])) {
            $updates["range_1y_min"] = (float) $enrichedHolding["range_1y_min"];
        }
        if (isset($enrichedHolding["range_1y_max"])) {
            $updates["range_1y_max"] = (float) $enrichedHolding["range_1y_max"];
        }
        if (isset($enrichedHolding["range_1y_percentile"])) {
            $updates["range_1y_percentile"] =
                (float) $enrichedHolding["range_1y_percentile"];
        }

        // Fibonacci
        if (isset($enrichedHolding["fib_low"])) {
            $updates["fib_low"] = (float) $enrichedHolding["fib_low"];
        }
        if (isset($enrichedHolding["fib_high"])) {
            $updates["fib_high"] = (float) $enrichedHolding["fib_high"];
        }
        if (isset($enrichedHolding["fib_23_6"])) {
            $updates["fib_23_6"] = (float) $enrichedHolding["fib_23_6"];
        }
        if (isset($enrichedHolding["fib_38_2"])) {
            $updates["fib_38_2"] = (float) $enrichedHolding["fib_38_2"];
        }
        if (isset($enrichedHolding["fib_50_0"])) {
            $updates["fib_50_0"] = (float) $enrichedHolding["fib_50_0"];
        }
        if (isset($enrichedHolding["fib_61_8"])) {
            $updates["fib_61_8"] = (float) $enrichedHolding["fib_61_8"];
        }
        if (isset($enrichedHolding["fib_78_6"])) {
            $updates["fib_78_6"] = (float) $enrichedHolding["fib_78_6"];
        }

        if (isset($enrichedHolding["fib_23_6_dist_pct"])) {
            $updates["fib_23_6_dist_pct"] =
                (float) $enrichedHolding["fib_23_6_dist_pct"];
        }
        if (isset($enrichedHolding["fib_38_2_dist_pct"])) {
            $updates["fib_38_2_dist_pct"] =
                (float) $enrichedHolding["fib_38_2_dist_pct"];
        }
        if (isset($enrichedHolding["fib_50_0_dist_pct"])) {
            $updates["fib_50_0_dist_pct"] =
                (float) $enrichedHolding["fib_50_0_dist_pct"];
        }
        if (isset($enrichedHolding["fib_61_8_dist_pct"])) {
            $updates["fib_61_8_dist_pct"] =
                (float) $enrichedHolding["fib_61_8_dist_pct"];
        }
        if (isset($enrichedHolding["fib_78_6_dist_pct"])) {
            $updates["fib_78_6_dist_pct"] =
                (float) $enrichedHolding["fib_78_6_dist_pct"];
        }

        // Bande di Bollinger
        if (isset($enrichedHolding["bb_middle"])) {
            $updates["bb_middle"] = (float) $enrichedHolding["bb_middle"];
        }
        if (isset($enrichedHolding["bb_upper"])) {
            $updates["bb_upper"] = (float) $enrichedHolding["bb_upper"];
        }
        if (isset($enrichedHolding["bb_lower"])) {
            $updates["bb_lower"] = (float) $enrichedHolding["bb_lower"];
        }
        if (isset($enrichedHolding["bb_width_pct"])) {
            $updates["bb_width_pct"] = (float) $enrichedHolding["bb_width_pct"];
        }
        if (isset($enrichedHolding["bb_percent_b"])) {
            $updates["bb_percent_b"] = (float) $enrichedHolding["bb_percent_b"];
        }

        // Data ultimo calcolo tecnico
        if ($technicalAsOf !== null) {
            $updates["technical_as_of"] = $technicalAsOf;
        }

        if (!empty($updates)) {
            $holdingRepo->updateFieldsByIsin($portfolioId, $isin, $updates);
            $updatedCount++;
            error_log(
                "[n8n/enrich] Updated holding $isin: " . json_encode($updates)
            );
        }

        /**
         * Scrittura storico analisi tecnica (technical_snapshots)
         * – salviamo solo il sottoinsieme chiave per il trend LLM
         */
        $snapshotData = [
            "snapshot_date" => $technicalAsOf,
            "price" => isset($enrichedHolding["current_price"])
                ? (float) $enrichedHolding["current_price"]
                : null,
            "rsi14" => isset($enrichedHolding["rsi14"])
                ? (float) $enrichedHolding["rsi14"]
                : null,
            "macd_value" => isset($enrichedHolding["macd_value"])
                ? (float) $enrichedHolding["macd_value"]
                : null,
            "macd_signal" => isset($enrichedHolding["macd_signal"])
                ? (float) $enrichedHolding["macd_signal"]
                : null,
            "hist_vol_30d" => array_key_exists("hist_vol_30d", $enrichedHolding)
                ? ($enrichedHolding["hist_vol_30d"] !== null
                    ? (float) $enrichedHolding["hist_vol_30d"]
                    : null)
                : null,
            "atr14_pct" => isset($enrichedHolding["atr14_pct"])
                ? (float) $enrichedHolding["atr14_pct"]
                : null,
            "range_1y_percentile" => isset(
                $enrichedHolding["range_1y_percentile"]
            )
                ? (float) $enrichedHolding["range_1y_percentile"]
                : null,
            "bb_percent_b" => isset($enrichedHolding["bb_percent_b"])
                ? (float) $enrichedHolding["bb_percent_b"]
                : null,
        ];

        $technicalSnapshotRepo->upsertSnapshot(
            $portfolioId,
            $isin,
            $snapshotData
        );
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
