<?php
/**
 * API - Dividends Management (n8n friendly, DB-first)
 *
 * Endpoint:
 *  POST /api/dividends.php
 *
 * Payload:
 * {
 *   "dividends": [
 *     {
 *       "ticker": "VHYL.MI",
 *       "status": "FORECAST" | "RECEIVED",
 *       "ex_date": "YYYY-MM-DD",
 *       "payment_date": "YYYY-MM-DD",
 *       "amount_per_share": 0.35,
 *       "quantity": 30,                // opzionale
 *       "quantity_at_ex_date": 30,     // opzionale, preferita se presente
 *       "total_amount": 10.50          // opzionale, se assente calcoliamo qty * amount_per_share
 *     }
 *   ]
 * }
 */

header("Content-Type: application/json");

// CORS (per n8n)
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header(
    "Access-Control-Allow-Headers: Content-Type, X-Webhook-Signature, User-Agent"
);

if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
    http_response_code(200);
    exit();
}

require_once __DIR__ . "/../lib/Database/DatabaseManager.php";
require_once __DIR__ . "/../lib/Database/Repositories/DividendRepository.php";
require_once __DIR__ . "/../lib/Database/Repositories/HoldingRepository.php";
require_once __DIR__ . "/../lib/Database/Repositories/SnapshotRepository.php";
require_once __DIR__ . "/../lib/Database/Services/PortfolioMetricsService.php";
require_once __DIR__ . "/../lib/HMACValidator.php";

/**
 * Recupera la quantità detenuta alla data (snapshot più recente <= data)
 *
 * @param DatabaseManager $db
 * @param int $portfolioId
 * @param string $ticker
 * @param string $date Y-m-d
 * @return float|null
 */
function getQuantityAtDate(
    DatabaseManager $db,
    int $portfolioId,
    string $ticker,
    string $date
): ?float {
    $sql = "
        SELECT sh.quantity
        FROM snapshots s
        JOIN snapshot_holdings sh ON sh.snapshot_id = s.id
        WHERE s.portfolio_id = ? AND sh.ticker = ? AND s.snapshot_date <= ?
        ORDER BY s.snapshot_date DESC
        LIMIT 1
    ";

    $row = $db->fetchOne($sql, [$portfolioId, $ticker, $date]);
    return $row ? (float) $row["quantity"] : null;
}

try {
    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        http_response_code(405);
        echo json_encode(["success" => false, "error" => "Method not allowed"]);
        exit();
    }

    // Portfolio ID (single-portfolio app per ora)
    $portfolioId = DividendRepository::DEFAULT_PORTFOLIO_ID ?? 1;

    // Raw payload + HMAC
    $rawPayload = file_get_contents("php://input");
    if ($rawPayload === false || $rawPayload === "") {
        throw new Exception("Empty payload");
    }

    $signature = $_SERVER["HTTP_X_WEBHOOK_SIGNATURE"] ?? "";

    if ($signature === "HMAC_DISABLED") {
        error_log("[dividends] HMAC validation skipped (HMAC_DISABLED flag)");
        // TODO: IP whitelist
    } elseif (!HMACValidator::validate($rawPayload, $signature)) {
        error_log("[dividends] Invalid HMAC signature received");
        http_response_code(401);
        echo json_encode([
            "success" => false,
            "error" => "Invalid HMAC signature",
        ]);
        exit();
    }

    // Decode JSON
    $data = json_decode($rawPayload, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception("Invalid JSON: " . json_last_error_msg());
    }

    if (!isset($data["dividends"]) || !is_array($data["dividends"])) {
        throw new Exception('Invalid payload: missing "dividends" array');
    }

    $db = DatabaseManager::getInstance();
    $divRepo = new DividendRepository($db);
    $holdingRepo = new HoldingRepository($db);
    $snapshotRepo = new SnapshotRepository($db); // se serve in futuro
    $metrics = new PortfolioMetricsService($db);

    $created = 0;
    $updated = 0;
    $errors = [];

    foreach ($data["dividends"] as $div) {
        $ticker = isset($div["ticker"])
            ? strtoupper(trim($div["ticker"]))
            : null;
        $status = isset($div["status"])
            ? strtoupper(trim($div["status"]))
            : "FORECAST";
        $exDate = $div["ex_date"] ?? null;
        $payDate = $div["payment_date"] ?? null;

        if (!$ticker || !$exDate) {
            $errors[] = [
                "ticker" => $ticker,
                "reason" => "Missing required fields (ticker/ex_date)",
            ];
            continue;
        }

        if ($status !== "FORECAST" && $status !== "RECEIVED") {
            $status = "FORECAST";
        }

        // Quantità: preferisci quantity_at_ex_date, poi quantity, poi snapshot/holding corrente
        $qty = null;
        if (isset($div["quantity_at_ex_date"])) {
            $qty = (float) $div["quantity_at_ex_date"];
        } elseif (isset($div["quantity"])) {
            $qty = (float) $div["quantity"];
        }

        if ($qty === null) {
            if ($status === "RECEIVED") {
                // quantità al ex_date dallo snapshot
                $qty = getQuantityAtDate($db, $portfolioId, $ticker, $exDate);
            }

            // Se ancora nulla o FORECAST → usa quantità corrente sull'holding
            if ($qty === null) {
                $holding = $holdingRepo->findByTicker($ticker, $portfolioId);
                if ($holding && isset($holding["quantity"])) {
                    $qty = (float) $holding["quantity"];
                }
            }
        }

        $aps = isset($div["amount_per_share"])
            ? (float) $div["amount_per_share"]
            : null;
        $total = isset($div["total_amount"])
            ? (float) $div["total_amount"]
            : null;

        // Calcola total_amount se possibile
        if ($total === null && $qty !== null && $aps !== null) {
            $total = $qty * $aps;
        }

        // Se mancano sia qty che total, fissiamo entrambi a 0 per non scartare il record
        if ($qty === null && $total === null) {
            $qty = 0.0;
            $total = 0.0;
        }

        // Upsert su (portfolio_id, ticker, ex_date, status)
        $existing = $db->fetchOne(
            "SELECT id 
             FROM dividend_payments 
             WHERE portfolio_id = ? AND ticker = ? AND ex_date = ? AND status = ?
             LIMIT 1",
            [$portfolioId, $ticker, $exDate, $status]
        );

        $payloadDb = [
            "portfolio_id" => $portfolioId,
            "ticker" => $ticker,
            "ex_date" => $exDate,
            "payment_date" => $payDate,
            "amount_per_share" => $aps,
            "total_amount" => $total,
            "quantity" => $qty,
            "status" => $status,
        ];

        try {
            if ($existing) {
                $divRepo->update($existing["id"], $payloadDb);
                $updated++;
            } else {
                $divRepo->create($payloadDb);
                $created++;
            }
        } catch (Exception $e) {
            $errors[] = [
                "ticker" => $ticker,
                "reason" => "DB error: " . $e->getMessage(),
            ];
        }
    }

    // Recalcola metriche/allocazioni/snapshot/monthly_performance nel DB
    $metrics->recalculateAllForPortfolio($portfolioId);

    http_response_code(200);
    echo json_encode(
        [
            "success" => true,
            "created" => $created,
            "updated" => $updated,
            "errors" => $errors,
            "timestamp" => date("Y-m-d H:i:s"),
        ],
        JSON_PRETTY_PRINT
    );
} catch (Exception $e) {
    error_log("[dividends] Error: " . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "error" => $e->getMessage(),
    ]);
}
