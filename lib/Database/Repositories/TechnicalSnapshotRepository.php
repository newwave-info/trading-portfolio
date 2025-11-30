<?php

require_once __DIR__ . '/BaseRepository.php';

class TechnicalSnapshotRepository extends BaseRepository
{
    protected $table = 'technical_snapshots';

    public function __construct(DatabaseManager $db)
    {
        parent::__construct($db);
    }

    /**
     * Upsert di uno snapshot tecnico (1 riga per giorno × ISIN)
     *
     * @param int    $portfolioId
     * @param string $isin
     * @param array  $data  [
     *   'snapshot_date'       => 'YYYY-MM-DD',
     *   'price'               => float|null,
     *   'rsi14'               => float|null,
     *   'macd_value'          => float|null,
     *   'macd_signal'         => float|null,
     *   'hist_vol_30d'        => float|null,
     *   'atr14_pct'           => float|null,
     *   'range_1y_percentile' => float|null,
     *   'bb_percent_b'        => float|null,
     * ]
     */
    public function upsertSnapshot(
        int $portfolioId,
        string $isin,
        array $data
    ): void {
        $snapshotDate = $data["snapshot_date"] ?? date('Y-m-d');

        $sql = "
            INSERT INTO technical_snapshots (
                portfolio_id,
                isin,
                snapshot_date,
                price,
                rsi14,
                macd_value,
                macd_signal,
                hist_vol_30d,
                atr14_pct,
                range_1y_percentile,
                bb_percent_b
            ) VALUES (
                ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
            )
            ON DUPLICATE KEY UPDATE
                price               = VALUES(price),
                rsi14               = VALUES(rsi14),
                macd_value          = VALUES(macd_value),
                macd_signal         = VALUES(macd_signal),
                hist_vol_30d        = VALUES(hist_vol_30d),
                atr14_pct           = VALUES(atr14_pct),
                range_1y_percentile = VALUES(range_1y_percentile),
                bb_percent_b        = VALUES(bb_percent_b)
        ";

        $params = [
            $portfolioId,
            $isin,
            $snapshotDate,
            $data["price"] ?? null,
            $data["rsi14"] ?? null,
            $data["macd_value"] ?? null,
            $data["macd_signal"] ?? null,
            $data["hist_vol_30d"] ?? null,
            $data["atr14_pct"] ?? null,
            $data["range_1y_percentile"] ?? null,
            $data["bb_percent_b"] ?? null,
        ];

        $this->db->execute($sql, $params);
    }
}
