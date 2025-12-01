<?php
/**
 * API Frontend - Technical Insights
 * Restituisce gli insight AI più recenti (portfolio + strumenti) per la UI.
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../../lib/Database/DatabaseManager.php';
require_once __DIR__ . '/../../lib/Database/Repositories/PortfolioRepository.php';
require_once __DIR__ . '/../../lib/Database/Repositories/TechnicalInsightRepository.php';

try {
    $portfolioId = PortfolioRepository::DEFAULT_PORTFOLIO_ID;
    $db = DatabaseManager::getInstance();
    $repo = new TechnicalInsightRepository($db);

    $isinFilter = isset($_GET['isin']) ? trim($_GET['isin']) : null;

    $portfolioInsight = $repo->getLatestPortfolioInsight($portfolioId);
    $instrumentInsights = [];
    if ($isinFilter) {
        $single = $repo->getLatestInstrumentInsight($portfolioId, $isinFilter);
        if ($single) {
            $instrumentInsights[] = $single;
        }
    } else {
        $instrumentInsights = $repo->getLatestInstrumentsInsights($portfolioId);
    }

    echo json_encode([
        'success' => true,
        'portfolio' => $portfolioInsight,
        'instruments' => $instrumentInsights,
    ], JSON_PRETTY_PRINT);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
    ]);
}
