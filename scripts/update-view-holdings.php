<?php
/**
 * Script per aggiornare la VIEW v_holdings_enriched con tutti i campi necessari
 *
 * Questo script:
 * 1. Elimina la VIEW esistente
 * 2. Crea una nuova VIEW completa con tutti i campi dalla tabella holdings
 * 3. Verifica che i dati siano corretti
 *
 * Usage: php scripts/update-view-holdings.php
 */

require_once __DIR__ . '/../lib/Database/DatabaseManager.php';

try {
    echo "=== Aggiornamento VIEW v_holdings_enriched ===\n\n";

    // Connessione al database
    $db = DatabaseManager::getInstance();
    echo "✓ Connesso al database\n\n";

    // Elimina la VIEW esistente
    echo "1. Elimino la VIEW esistente...\n";
    $db->execute("DROP VIEW IF EXISTS v_holdings_enriched");
    echo "   ✓ VIEW eliminata\n\n";

    // Crea la nuova VIEW completa
    echo "2. Creo la nuova VIEW con tutti i campi...\n";
    $createViewSQL = "
        CREATE ALGORITHM=UNDEFINED DEFINER=`poRtUsR25`@`%` SQL SECURITY DEFINER VIEW `v_holdings_enriched` AS
        SELECT
            -- ID e identificativi
            h.id,
            h.portfolio_id,
            h.ticker,
            h.name,

            -- Classificazione
            h.asset_class,
            h.sector,

            -- Quantità e prezzi
            h.quantity,
            h.avg_price,
            h.current_price,
            h.previous_close,

            -- Valori calcolati
            h.quantity * h.avg_price AS invested,
            h.quantity * COALESCE(h.current_price, h.avg_price) AS market_value,
            h.quantity * COALESCE(h.current_price, h.avg_price) - h.quantity * h.avg_price AS pnl,
            CASE
                WHEN h.avg_price > 0
                THEN (COALESCE(h.current_price, h.avg_price) - h.avg_price) / h.avg_price * 100
                ELSE 0
            END AS pnl_pct,

            -- Range prezzi (52w, giornalieri)
            h.fifty_two_week_high,
            h.fifty_two_week_low,
            h.day_high,
            h.day_low,

            -- Performance percentuali
            h.ytd_change_percent,
            h.one_month_change_percent,
            h.three_month_change_percent,
            h.one_year_change_percent,

            -- Dividendi
            h.dividend_yield,
            h.annual_dividend,
            h.dividend_frequency,
            h.has_dividends,
            h.total_dividends_5y,

            -- Volume e mercato
            h.volume,
            h.exchange,
            h.first_trade_date,

            -- Metadata
            h.price_source,
            h.is_active,
            h.created_at,
            h.updated_at

        FROM holdings h
        WHERE h.is_active = 1
    ";

    $db->execute($createViewSQL);
    echo "   ✓ VIEW creata con successo\n\n";

    // Verifica i campi della nuova VIEW
    echo "3. Verifico i campi della nuova VIEW...\n";
    $fieldsQuery = "
        SELECT
            COLUMN_NAME,
            DATA_TYPE,
            NUMERIC_PRECISION,
            NUMERIC_SCALE
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = 'trading_portfolio'
            AND TABLE_NAME = 'v_holdings_enriched'
        ORDER BY ORDINAL_POSITION
    ";

    $fields = $db->query($fieldsQuery)->fetchAll();
    echo "   Campi nella nuova VIEW (" . count($fields) . " totali):\n";
    foreach ($fields as $field) {
        echo "   - " . $field['COLUMN_NAME'] . " (" . $field['DATA_TYPE'] . ")\n";
    }
    echo "\n";

    // Verifica alcuni record
    echo "4. Verifico alcuni record...\n";
    $testQuery = "
        SELECT
            ticker,
            name,
            asset_class,
            sector,
            quantity,
            avg_price,
            current_price,
            market_value,
            pnl,
            pnl_pct,
            dividend_yield,
            dividend_frequency,
            ytd_change_percent,
            price_source
        FROM v_holdings_enriched
        LIMIT 3
    ";

    $records = $db->query($testQuery)->fetchAll();
    echo "   Records trovati: " . count($records) . "\n";

    if (count($records) > 0) {
        echo "\n   Esempio primo record:\n";
        foreach ($records[0] as $key => $value) {
            echo "   - " . $key . ": " . $value . "\n";
        }
    }

    echo "\n=== ✅ Aggiornamento completato con successo! ===\n\n";

    echo "La VIEW v_holdings_enriched ora include tutti i campi necessari:\n";
    echo "  - Classificazione: sector, asset_class\n";
    echo "  - Dividendi: dividend_yield, annual_dividend, dividend_frequency, has_dividends, total_dividends_5y\n";
    echo "  - Range prezzi: fifty_two_week_high, fifty_two_week_low, day_high, day_low\n";
    echo "  - Performance: ytd_change_percent, one_month_change_percent, three_month_change_percent, one_year_change_percent\n";
    echo "  - Mercato: volume, exchange, first_trade_date\n";
    echo "  - Metadata: price_source, created_at, updated_at\n\n";

} catch (Exception $e) {
    echo "\n❌ ERRORE: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " (riga " . $e->getLine() . ")\n\n";
    exit(1);
}
