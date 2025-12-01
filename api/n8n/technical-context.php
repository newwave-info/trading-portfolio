<?php
/**
 * API n8n - Technical Context
 * Restituisce il contesto tecnico (corrente + trend breve) per l'LLM.
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../../lib/Database/DatabaseManager.php';
require_once __DIR__ . '/../../lib/Database/Repositories/HoldingRepository.php';
require_once __DIR__ . '/../../lib/Database/Repositories/PortfolioRepository.php';
require_once __DIR__ . '/../../lib/Database/Repositories/TechnicalSnapshotRepository.php';

try {
    $portfolioId = PortfolioRepository::DEFAULT_PORTFOLIO_ID;
    $db = DatabaseManager::getInstance();
    $holdingRepo = new HoldingRepository($db);

    // Carica holdings arricchite (indicatori inclusi)
    $holdings = $holdingRepo->getEnrichedHoldings($portfolioId);

    // Trasforma holdings nel payload atteso
    $payloadHoldings = array_map(function ($h) {
        return [
            'ticker' => $h['ticker'],
            'isin' => $h['isin'] ?? null,
            'name' => $h['name'],
            'asset_class' => $h['asset_class'],
            'sector' => $h['sector'],
            'price' => $h['current_price'],
            'ema9' => $h['ema9'] ?? null,
            'ema21' => $h['ema21'] ?? null,
            'ema50' => $h['ema50'] ?? null,
            'ema200' => $h['ema200'] ?? null,
            'rsi14' => $h['rsi14'] ?? null,
            'macd_value' => $h['macd_value'] ?? null,
            'macd_signal' => $h['macd_signal'] ?? null,
            'macd_hist' => $h['macd_hist'] ?? null,
            'atr14' => $h['atr14'] ?? null,
            'atr14_pct' => $h['atr14_pct'] ?? null,
            'hist_vol_30d' => $h['hist_vol_30d'] ?? null,
            'hist_vol_90d' => $h['hist_vol_90d'] ?? null,
            'range_1m_percentile' => $h['range_1m_percentile'] ?? null,
            'range_3m_percentile' => $h['range_3m_percentile'] ?? null,
            'range_1y_percentile' => $h['range_1y_percentile'] ?? null,
            'bb_percent_b' => $h['bb_percent_b'] ?? null,
            'fifty_two_week_high' => $h['fifty_two_week_high'] ?? null,
            'fifty_two_week_low' => $h['fifty_two_week_low'] ?? null,
            'ytd_change_percent' => $h['ytd_change_percent'] ?? null,
            'one_month_change_percent' => $h['one_month_change_percent'] ?? null,
            'three_month_change_percent' => $h['three_month_change_percent'] ?? null,
            'one_year_change_percent' => $h['one_year_change_percent'] ?? null,
        ];
    }, $holdings);

    // Trend storico breve per LLM (ultimi 30 giorni)
    $technicalSnapshotRepo = new TechnicalSnapshotRepository($db);
    // Nota: non abbiamo ancora un metodo dedicato; al momento restituiamo vuoto per compatibilità
    $trendData = [];

    echo json_encode([
        'success' => true,
        'portfolio_id' => $portfolioId,
        'holdings' => $payloadHoldings,
        'trend' => $trendData,
    ], JSON_PRETTY_PRINT);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
    ]);
}
