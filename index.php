<?php
/**
 * ETF Portfolio Manager - Main Entry Point
 *
 * Questo file funge da controller principale per l'applicazione.
 * Include i dati, layout e viste in modo modulare per facilitare la manutenzione.
 *
 * Struttura:
 * - data/portfolio_data.php: Dati statici del portafoglio (sarà sostituito con DB)
 * - views/layouts/header.php: HTML head e top header
 * - views/layouts/sidebar.php: Sidebar navigation
 * - views/tabs/*.php: Viste per ogni sezione
 * - views/layouts/footer.php: Chiusura HTML e script finali
 */

// Carica i dati del portafoglio
require_once __DIR__ . '/data/portfolio_data.php';

// Calcolo score portfolio (logica che potrebbe andare in un service)
$portfolio_score = 74; // Calcolo dinamico in seguito
$score_label = $portfolio_score >= 75 ? 'Ottimo' : ($portfolio_score >= 60 ? 'Buono' : 'Migliorabile');

// Includi header
require_once __DIR__ . '/views/layouts/header.php';

// Includi sidebar
require_once __DIR__ . '/views/layouts/sidebar.php';

// Includi tutte le viste (tabs)
require_once __DIR__ . '/views/tabs/dashboard.php';
require_once __DIR__ . '/views/tabs/holdings.php';
require_once __DIR__ . '/views/tabs/performance.php';
require_once __DIR__ . '/views/tabs/technical.php';
require_once __DIR__ . '/views/tabs/dividends.php';
require_once __DIR__ . '/views/tabs/recommendations.php';

// Includi footer
require_once __DIR__ . '/views/layouts/footer.php';
