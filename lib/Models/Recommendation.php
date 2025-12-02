<?php
/**
 * Recommendation Model
 *
 * Represents a trading recommendation/signal with business logic.
 *
 * @package TradingPortfolio\Models
 * @version 1.0.0
 */

class Recommendation
{
    const TYPE_BUY_LIMIT = 'BUY_LIMIT';
    const TYPE_BUY_MARKET = 'BUY_MARKET';
    const TYPE_SELL_PARTIAL = 'SELL_PARTIAL';
    const TYPE_SELL_ALL = 'SELL_ALL';
    const TYPE_SET_STOP_LOSS = 'SET_STOP_LOSS';
    const TYPE_SET_TAKE_PROFIT = 'SET_TAKE_PROFIT';
    const TYPE_HOLD_MONITOR = 'HOLD_MONITOR';
    const TYPE_REBALANCE = 'REBALANCE';
    const TYPE_MACRO_ALERT = 'MACRO_ALERT';

    const URGENCY_IMMEDIATE = 'IMMEDIATO';
    const URGENCY_THIS_WEEK = 'QUESTA_SETTIMANA';
    const URGENCY_NEXT_2_WEEKS = 'PROSSIME_2_SETTIMANE';
    const URGENCY_MONITORING = 'MONITORAGGIO';

    const STATUS_ACTIVE = 'ACTIVE';
    const STATUS_EXECUTED = 'EXECUTED';
    const STATUS_EXPIRED = 'EXPIRED';
    const STATUS_IGNORED = 'IGNORED';
    const STATUS_SUPERSEDED = 'SUPERSEDED';

    /**
     * @var array Recommendation data
     */
    private $data;

    /**
     * Constructor
     *
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    /**
     * Create from array
     *
     * @param array $data
     * @return self
     */
    public static function fromArray(array $data)
    {
        return new self($data);
    }

    /**
     * Get all recommendation types
     *
     * @return array
     */
    public static function getTypes()
    {
        return [
            self::TYPE_BUY_LIMIT,
            self::TYPE_BUY_MARKET,
            self::TYPE_SELL_PARTIAL,
            self::TYPE_SELL_ALL,
            self::TYPE_SET_STOP_LOSS,
            self::TYPE_SET_TAKE_PROFIT,
            self::TYPE_HOLD_MONITOR,
            self::TYPE_REBALANCE,
            self::TYPE_MACRO_ALERT
        ];
    }

    /**
     * Get all urgency levels
     *
     * @return array
     */
    public static function getUrgencies()
    {
        return [
            self::URGENCY_IMMEDIATE,
            self::URGENCY_THIS_WEEK,
            self::URGENCY_NEXT_2_WEEKS,
            self::URGENCY_MONITORING
        ];
    }

    /**
     * Get all statuses
     *
     * @return array
     */
    public static function getStatuses()
    {
        return [
            self::STATUS_ACTIVE,
            self::STATUS_EXECUTED,
            self::STATUS_EXPIRED,
            self::STATUS_IGNORED,
            self::STATUS_SUPERSEDED
        ];
    }

    /**
     * Get type icon
     *
     * @param string $type
     * @return string
     */
    public static function getTypeIcon($type)
    {
        $icons = [
            self::TYPE_BUY_LIMIT => '📈',
            self::TYPE_BUY_MARKET => '⚡',
            self::TYPE_SELL_PARTIAL => '📉',
            self::TYPE_SELL_ALL => '🚨',
            self::TYPE_SET_STOP_LOSS => '🛡️',
            self::TYPE_SET_TAKE_PROFIT => '🎯',
            self::TYPE_HOLD_MONITOR => '👁️',
            self::TYPE_REBALANCE => '⚖️',
            self::TYPE_MACRO_ALERT => '📊'
        ];

        return $icons[$type] ?? '❓';
    }

    /**
     * Get urgency color
     *
     * @param string $urgency
     * @return string
     */
    public static function getUrgencyColor($urgency)
    {
        $colors = [
            self::URGENCY_IMMEDIATE => '🔴',
            self::URGENCY_THIS_WEEK => '🟠',
            self::URGENCY_NEXT_2_WEEKS => '🟡',
            self::URGENCY_MONITORING => '🟢'
        ];

        return $colors[$urgency] ?? '⚪';
    }

    /**
     * Check if recommendation is actionable
     *
     * @return bool
     */
    public function isActionable()
    {
        return in_array($this->data['status'] ?? '', [self::STATUS_ACTIVE, self::STATUS_EXECUTED]) &&
               (!isset($this->data['expires_at']) || strtotime($this->data['expires_at']) > time());
    }

    /**
     * Check if recommendation is expired
     *
     * @return bool
     */
    public function isExpired()
    {
        if (!isset($this->data['expires_at'])) {
            return false;
        }

        return strtotime($this->data['expires_at']) < time();
    }

    /**
     * Get days until expiration
     *
     * @return int|null
     */
    public function getDaysToExpire()
    {
        if (!isset($this->data['expires_at'])) {
            return null;
        }

        $expires = strtotime($this->data['expires_at']);
        $now = time();

        return $expires > $now ? ceil(($expires - $now) / 86400) : 0;
    }

    /**
     * Get formatted recommendation title
     *
     * @return string
     */
    public function getTitle()
    {
        $type = $this->data['type'] ?? '';
        $ticker = $this->data['ticker'] ?? '';
        $quantity = $this->data['quantity'] ?? 0;
        $price = $this->data['trigger_price'] ?? 0;

        switch ($type) {
            case self::TYPE_BUY_LIMIT:
                return sprintf("Compra %d unità @ €%.2f", $quantity, $price);
            case self::TYPE_BUY_MARKET:
                return sprintf("Compra %d unità a mercato", $quantity);
            case self::TYPE_SELL_PARTIAL:
                return sprintf("Vendi %d unità (parziale)", $quantity);
            case self::TYPE_SELL_ALL:
                return "Vendi tutto";
            case self::TYPE_SET_STOP_LOSS:
                return sprintf("Stop loss @ €%.2f", $price);
            case self::TYPE_SET_TAKE_PROFIT:
                return sprintf("Take profit @ €%.2f", $price);
            case self::TYPE_HOLD_MONITOR:
                return "Monitora";
            case self::TYPE_REBALANCE:
                return "Ribilancia";
            default:
                return ucwords(str_replace('_', ' ', strtolower($type)));
        }
    }

    /**
     * Get formatted estimated cost/value
     *
     * @return string
     */
    public function getEstimatedValue()
    {
        $type = $this->data['type'] ?? '';
        $quantity = $this->data['quantity'] ?? 0;
        $price = $this->data['trigger_price'] ?? 0;

        if (!$quantity || !$price) {
            return '';
        }

        $value = $quantity * $price;

        if (in_array($type, [self::TYPE_BUY_LIMIT, self::TYPE_BUY_MARKET])) {
            return sprintf("💰 €%.2f", $value);
        } elseif (in_array($type, [self::TYPE_SELL_PARTIAL, self::TYPE_SELL_ALL])) {
            return sprintf("💰 €%.2f", $value);
        }

        return '';
    }

    /**
     * Validate recommendation data
     *
     * @return array Errors array
     */
    public function validate()
    {
        $errors = [];

        // Required fields
        $requiredFields = ['portfolio_id', 'type', 'urgency', 'confidence_score'];
        foreach ($requiredFields as $field) {
            if (!isset($this->data[$field]) || empty($this->data[$field])) {
                $errors[] = "Campo richiesto mancante: $field";
            }
        }

        // Validate type
        if (isset($this->data['type']) && !in_array($this->data['type'], self::getTypes())) {
            $errors[] = "Tipo raccomandazione non valido: " . $this->data['type'];
        }

        // Validate urgency
        if (isset($this->data['urgency']) && !in_array($this->data['urgency'], self::getUrgencies())) {
            $errors[] = "Urgenza non valida: " . $this->data['urgency'];
        }

        // Validate confidence score
        if (isset($this->data['confidence_score'])) {
            $score = (int)$this->data['confidence_score'];
            if ($score < 0 || $score > 100) {
                $errors[] = "Confidence score deve essere tra 0 e 100";
            }
        }

        // Validate dates
        if (isset($this->data['expires_at']) && isset($this->data['created_at'])) {
            if (strtotime($this->data['expires_at']) <= strtotime($this->data['created_at'])) {
                $errors[] = "Data scadenza deve essere successiva a data creazione";
            }
        }

        return $errors;
    }

    /**
     * Convert to array
     *
     * @return array
     */
    public function toArray()
    {
        return $this->data;
    }

    /**
     * Get specific field
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get($key, $default = null)
    {
        return $this->data[$key] ?? $default;
    }

    /**
     * Set specific field
     *
     * @param string $key
     * @param mixed $value
     * @return self
     */
    public function set($key, $value)
    {
        $this->data[$key] = $value;
        return $this;
    }

    /**
     * Get data quality issues as array
     *
     * @return array
     */
    public function getDataQualityIssues()
    {
        if (!isset($this->data['data_quality_issues'])) {
            return [];
        }

        $issues = json_decode($this->data['data_quality_issues'], true);
        return is_array($issues) ? $issues : [];
    }

    /**
     * Set data quality issues
     *
     * @param array $issues
     * @return self
     */
    public function setDataQualityIssues(array $issues)
    {
        $this->data['data_quality_issues'] = json_encode($issues);
        return $this;
    }

    /**
     * Check if has data quality issues
     *
     * @return bool
     */
    public function hasDataQualityIssues()
    {
        return !empty($this->data['data_quality_issues']);
    }

    /**
     * Get confidence with visual indicator
     *
     * @return string
     */
    public function getConfidenceVisual()
    {
        $score = (int)($this->data['confidence_score'] ?? 0);
        $bars = round($score / 10);
        $emptyBars = 10 - $bars;

        return str_repeat('█', $bars) . str_repeat('░', $emptyBars) . " $score/100";
    }

    /**
     * Get formatted rationale
     *
     * @return string
     */
    public function getFormattedRationale()
    {
        $rationale = [];

        if (isset($this->data['rationale_primary']) && !empty($this->data['rationale_primary'])) {
            $rationale[] = $this->data['rationale_primary'];
        }

        if (isset($this->data['rationale_technical']) && !empty($this->data['rationale_technical'])) {
            $rationale[] = $this->data['rationale_technical'];
        }

        return implode("\n", $rationale);
    }

    /**
     * Get risk management summary
     *
     * @return array
     */
    public function getRiskManagement()
    {
        return [
            'stop_loss' => $this->data['stop_loss'] ?? null,
            'take_profit' => $this->data['take_profit'] ?? null,
            'risk_reward_ratio' => $this->calculateRiskRewardRatio()
        ];
    }

    /**
     * Calculate risk/reward ratio
     *
     * @return float|null
     */
    private function calculateRiskRewardRatio()
    {
        $entry = $this->data['trigger_price'] ?? 0;
        $stop = $this->data['stop_loss'] ?? 0;
        $target = $this->data['take_profit'] ?? 0;

        if (!$entry || !$stop || !$target) {
            return null;
        }

        $risk = abs($entry - $stop);
        $reward = abs($target - $entry);

        return $risk > 0 ? round($reward / $risk, 2) : null;
    }

    /**
     * Check if recommendation is high confidence
     *
     * @return bool
     */
    public function isHighConfidence()
    {
        return ($this->data['confidence_score'] ?? 0) >= 70;
    }

    /**
     * Check if recommendation is immediate action
     *
     * @return bool
     */
    public function isImmediateAction()
    {
        return ($this->data['urgency'] ?? '') === self::URGENCY_IMMEDIATE;
    }

    /**
     * Get formatted summary
     *
     * @return string
     */
    public function getSummary()
    {
        $icon = self::getTypeIcon($this->data['type'] ?? '');
        $urgency = self::getUrgencyColor($this->data['urgency'] ?? '');
        $title = $this->getTitle();
        $value = $this->getEstimatedValue();

        return sprintf("%s %s %s %s", $urgency, $icon, $title, $value);
    }

    /**
     * Convert to JSON
     *
     * @return string
     */
    public function toJson()
    {
        return json_encode($this->data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    /**
     * Create from JSON
     *
     * @param string $json
     * @return self
     */
    public static function fromJson($json)
    {
        $data = json_decode($json, true);
        return new self($data ?: []);
    }

    /**
     * Get display data for UI
     *
     * @return array
     */
    public function getDisplayData()
    {
        return [
            'id' => $this->data['id'] ?? null,
            'type' => $this->data['type'] ?? '',
            'type_icon' => self::getTypeIcon($this->data['type'] ?? ''),
            'urgency' => $this->data['urgency'] ?? '',
            'urgency_color' => self::getUrgencyColor($this->data['urgency'] ?? ''),
            'ticker' => $this->data['ticker'] ?? '',
            'name' => $this->data['name'] ?? '',
            'title' => $this->getTitle(),
            'quantity' => $this->data['quantity'] ?? 0,
            'trigger_price' => $this->data['trigger_price'] ?? 0,
            'estimated_value' => $this->getEstimatedValue(),
            'confidence_score' => $this->data['confidence_score'] ?? 0,
            'confidence_visual' => $this->getConfidenceVisual(),
            'days_to_expire' => $this->getDaysToExpire(),
            'status' => $this->data['status'] ?? '',
            'is_high_confidence' => $this->isHighConfidence(),
            'is_immediate' => $this->isImmediateAction(),
            'has_quality_issues' => $this->hasDataQualityIssues(),
            'summary' => $this->getSummary()
        ];
    }
}