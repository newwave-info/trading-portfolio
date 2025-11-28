<?php
/**
 * Dividend Enriched Repository
 *
 * Read-only repository for v_dividends_enriched VIEW.
 */

require_once __DIR__ . '/BaseRepository.php';

class DividendEnrichedRepository extends BaseRepository
{
    protected $table = 'v_dividends_enriched';

    public function __construct(DatabaseManager $db)
    {
        parent::__construct($db);
        $this->primaryKey = 'id';
    }
}
