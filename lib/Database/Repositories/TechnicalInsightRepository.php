<?php

require_once __DIR__ . '/BaseRepository.php';

class TechnicalInsightRepository extends BaseRepository
{
    protected $table = 'technical_insights';

    /**
    * Salva un nuovo insight tecnico
    */
    public function createInsight(array $data): int
    {
        return $this->create([
            'portfolio_id' => $data['portfolio_id'],
            'isin' => $data['isin'] ?? null,
            'scope' => $data['scope'] ?? 'portfolio',
            'model' => $data['model'] ?? 'openai',
            'generated_at' => $data['generated_at'] ?? date('Y-m-d H:i:s'),
            'raw_input_snapshot' => $data['raw_input_snapshot'] ?? null,
            'insight_json' => $data['insight_json'] ?? null,
            'insight_text' => $data['insight_text'] ?? null,
        ]);
    }

    /**
     * Ultimi insight per portafoglio (scope portfolio)
     */
    public function getLatestPortfolioInsight(int $portfolioId): ?array
    {
        $sql = "
            SELECT *
            FROM {$this->table}
            WHERE portfolio_id = ? AND scope = 'portfolio'
            ORDER BY generated_at DESC, id DESC
            LIMIT 1
        ";

        return $this->fetchOne($sql, [$portfolioId]);
    }

    /**
     * Ultimo insight per strumento (scope instrument)
     */
    public function getLatestInstrumentInsight(int $portfolioId, string $isin): ?array
    {
        $sql = "
            SELECT *
            FROM {$this->table}
            WHERE portfolio_id = ? AND scope = 'instrument' AND isin = ?
            ORDER BY generated_at DESC, id DESC
            LIMIT 1
        ";

        return $this->fetchOne($sql, [$portfolioId, $isin]);
    }

    /**
     * Ultimi insight per tutti gli strumenti del portafoglio
     */
    public function getLatestInstrumentsInsights(int $portfolioId): array
    {
        $sql = "
            SELECT ti.*
            FROM {$this->table} ti
            INNER JOIN (
                SELECT isin, MAX(generated_at) AS max_gen
                FROM {$this->table}
                WHERE portfolio_id = ? AND scope = 'instrument'
                GROUP BY isin
            ) t ON ti.isin = t.isin AND ti.generated_at = t.max_gen
            WHERE ti.portfolio_id = ? AND ti.scope = 'instrument'
            ORDER BY ti.isin ASC
        ";

        return $this->fetchAll($sql, [$portfolioId, $portfolioId]);
    }
}
