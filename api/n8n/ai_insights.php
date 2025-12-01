<?php
/**
 * API n8n - Scrittura AI Technical Insights
 * Riceve l'output LLM dal workflow n8n e lo salva in technical_insights.
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../../lib/Database/DatabaseManager.php';
require_once __DIR__ . '/../../lib/Database/Repositories/PortfolioRepository.php';
require_once __DIR__ . '/../../lib/Database/Repositories/TechnicalInsightRepository.php';
require_once __DIR__ . '/../../lib/Database/Repositories/HoldingRepository.php';

try {
    $portfolioId = PortfolioRepository::DEFAULT_PORTFOLIO_ID;
    $db = DatabaseManager::getInstance();
    $repo = new TechnicalInsightRepository($db);

    $rawPayload = file_get_contents('php://input');
    $data = json_decode($rawPayload, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new InvalidArgumentException('Invalid JSON payload: ' . json_last_error_msg());
    }

    // Payload atteso: {
    //   "model": "gpt-4o-mini",
    //   "portfolio": { ... insight ... },
    //   "instruments": [ { isin, insight_json, insight_text, raw_input_snapshot } ]
    // }

    $model = $data['model'] ?? 'openai';

    $created = [];

    // Portfolio-level insight (scope portfolio)
    if (isset($data['portfolio'])) {
        $created[] = $repo->createInsight([
            'portfolio_id' => $portfolioId,
            'scope' => 'portfolio',
            'model' => $model,
            'generated_at' => $data['generated_at'] ?? date('Y-m-d H:i:s'),
            'raw_input_snapshot' => $data['portfolio']['raw_input_snapshot'] ?? null,
            'insight_json' => $data['portfolio']['insight_json'] ?? null,
            'insight_text' => $data['portfolio']['insight_text'] ?? null,
        ]);
    }

    // Instrument-level insights (scope instrument)
    if (!empty($data['instruments']) && is_array($data['instruments'])) {
        $holdingRepo = new HoldingRepository($db);
        foreach ($data['instruments'] as $insight) {
            if (empty($insight['isin'])) {
                continue;
            }
            // Fallback: se ticker/name mancano, recupera dal DB per evitare colonne vuote
            if (empty($insight['ticker']) || empty($insight['name'])) {
                $h = $holdingRepo->findByIsin($insight['isin'], $portfolioId);
                if ($h) {
                    $insight['ticker'] = $insight['ticker'] ?? ($h['ticker'] ?? null);
                    $insight['name'] = $insight['name'] ?? ($h['name'] ?? null);
                }
            }
            $created[] = $repo->createInsight([
                'portfolio_id' => $portfolioId,
                'isin' => $insight['isin'],
                'ticker' => $insight['ticker'] ?? null,
                'instrument_name' => $insight['name'] ?? null,
                'scope' => 'instrument',
                'model' => $model,
                'generated_at' => $insight['generated_at'] ?? ($data['generated_at'] ?? date('Y-m-d H:i:s')),
                'raw_input_snapshot' => $insight['raw_input_snapshot'] ?? null,
                'insight_json' => $insight['insight_json'] ?? null,
                'insight_text' => $insight['insight_text'] ?? null,
            ]);
        }
    }

    echo json_encode([
        'success' => true,
        'created' => count($created),
        'ids' => $created,
    ], JSON_PRETTY_PRINT);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
    ]);
}
