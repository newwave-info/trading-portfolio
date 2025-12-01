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
        $rawInput = isset($data['raw_input_snapshot'])
            ? json_encode($data['raw_input_snapshot'], JSON_UNESCAPED_UNICODE)
            : null;
        $insightJson = isset($data['insight_json'])
            ? json_encode($data['insight_json'], JSON_UNESCAPED_UNICODE)
            : null;

        return $this->create([
            'portfolio_id' => $data['portfolio_id'],
            'isin' => $data['isin'] ?? null,
            'scope' => $data['scope'] ?? 'portfolio',
            'model' => $data['model'] ?? 'openai',
            'generated_at' => $data['generated_at'] ?? date('Y-m-d H:i:s'),
            'raw_input_snapshot' => $rawInput,
            'insight_json' => $insightJson,
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

        $row = $this->fetchOne($sql, [$portfolioId]);
        return $this->decodeJsonFields($row);
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

        $row = $this->fetchOne($sql, [$portfolioId, $isin]);
        return $this->decodeJsonFields($row);
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

        $rows = $this->fetchAll($sql, [$portfolioId, $portfolioId]);
        return array_map([$this, 'decodeJsonFields'], $rows);
    }

    /**
     * Decode JSON fields (raw_input_snapshot, insight_json)
     */
    private function decodeJsonFields(?array $row): ?array
    {
        if (!$row) {
            return null;
        }

        foreach (['raw_input_snapshot', 'insight_json'] as $field) {
            if (isset($row[$field]) && is_string($row[$field])) {
                $decoded = json_decode($row[$field], true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $row[$field] = $decoded;
                }
            }
        }

        return $row;
    }
}
