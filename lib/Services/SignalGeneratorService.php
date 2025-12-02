<?php
/**
 * Signal Generator Service
 *
 * Generates trading signals based on technical analysis and portfolio allocation.
 * Implements the Core-Satellite Risk Parity strategy from Strategia Operativa v2.
 *
 * @package TradingPortfolio\Services
 * @version 1.0.0
 */

require_once __DIR__ . '/../Database/Repositories/RecommendationRepository.php';
require_once __DIR__ . '/../Database/Repositories/HoldingRepository.php';
require_once __DIR__ . '/../Models/Recommendation.php';

class SignalGeneratorService
{
    /**
     * @var RecommendationRepository
     */
    private $recommendationRepo;

    /**
     * @var HoldingRepository
     */
    private $holdingRepo;

    /**
     * @var int Default portfolio ID
     */
    private $portfolioId;

    /**
     * @var array Portfolio configuration
     */
    private $portfolioConfig;

    /**
     * @var float Total portfolio value
     */
    private $portfolioValue;

    /**
     * Constructor
     *
     * @param RecommendationRepository $recommendationRepo
     * @param HoldingRepository $holdingRepo
     * @param int $portfolioId
     */
    public function __construct(
        RecommendationRepository $recommendationRepo,
        HoldingRepository $holdingRepo,
        $portfolioId = 1
    ) {
        $this->recommendationRepo = $recommendationRepo;
        $this->holdingRepo = $holdingRepo;
        $this->portfolioId = $portfolioId;
        $this->portfolioConfig = $this->loadPortfolioConfig();
    }

    /**
     * Generate signals for all holdings
     *
     * @return array Generated recommendations
     */
    public function generateSignals()
    {
        $recommendations = [];
        $holdings = $this->holdingRepo->getEnrichedHoldings($this->portfolioId);
        $this->portfolioValue = $this->calculatePortfolioValue($holdings);

        foreach ($holdings as $holding) {
            // Validate holding data
            $validation = $this->validateHolding($holding);
            if (!$validation['valid']) {
                $this->logSignalGeneration($holding['ticker'], 'SKIPPED', $validation['reason']);
                continue;
            }

            $holding = $validation['holding']; // With fallbacks applied
            $confidencePenalty = $validation['confidence_penalty'];

            // Generate signals for this holding
            $signals = $this->generateSignalsForHolding($holding, $confidencePenalty);

            foreach ($signals as $signal) {
                $recommendations[] = $signal;
            }
        }

        // Filter and deduplicate
        $recommendations = $this->filterAndDeduplicate($recommendations);

        // Persist recommendations
        $savedRecommendations = [];
        foreach ($recommendations as $recommendation) {
            $id = $this->saveRecommendation($recommendation);
            if ($id) {
                $recommendation->set('id', $id);
                $savedRecommendations[] = $recommendation;
            }
        }

        return $savedRecommendations;
    }

    /**
     * Generate signals for specific holding
     *
     * @param array $holding
     * @param int $confidencePenalty
     * @return array
     */
    private function generateSignalsForHolding($holding, $confidencePenalty = 0)
    {
        $signals = [];
        $ticker = $holding['ticker'];

        // Check for existing recent recommendations
        if ($this->hasRecentRecommendation($holding['id'])) {
            $this->logSignalGeneration($ticker, 'SKIPPED', 'Recent recommendation exists');
            return $signals;
        }

        // Generate buy signals
        $buySignal = $this->generateBuySignal($holding, $confidencePenalty);
        if ($buySignal) {
            $signals[] = $buySignal;
        }

        // Generate sell signals
        $sellSignal = $this->generateSellSignal($holding, $confidencePenalty);
        if ($sellSignal) {
            $signals[] = $sellSignal;
        }

        // Generate stop loss signal
        $stopLossSignal = $this->generateStopLossSignal($holding, $confidencePenalty);
        if ($stopLossSignal) {
            $signals[] = $stopLossSignal;
        }

        // Generate take profit signal
        $takeProfitSignal = $this->generateTakeProfitSignal($holding, $confidencePenalty);
        if ($takeProfitSignal) {
            $signals[] = $takeProfitSignal;
        }

        // Generate rebalancing signal
        $rebalanceSignal = $this->generateRebalanceSignal($holding, $confidencePenalty);
        if ($rebalanceSignal) {
            $signals[] = $rebalanceSignal;
        }

        return $signals;
    }

    /**
     * Generate buy signal
     *
     * @param array $holding
     * @param int $confidencePenalty
     * @return Recommendation|null
     */
    private function generateBuySignal($holding, $confidencePenalty)
    {
        // Check allocation gap
        $currentAllocation = $this->calculateCurrentAllocation($holding);
        $targetAllocation = $holding['target_allocation_pct'] / 100;
        $allocationGap = $targetAllocation - $currentAllocation;

        if ($allocationGap < 0.02) { // Less than 2% underweight
            return null;
        }

        // Technical conditions
        $trend = $this->analyzeTrend($holding);
        $momentum = $this->analyzeMomentum($holding);
        $volatility = $this->analyzeVolatility($holding);
        $support = $this->findSupportLevel($holding);

        // Buy signal conditions
        $conditions = [
            'trend_bullish' => $trend === 'BULLISH',
            'momentum_oversold' => $momentum['rsi'] < 40,
            'price_near_support' => $support && abs($holding['current_price'] - $support) / $holding['current_price'] < 0.03,
            'volatility_normal' => $volatility['atr_pct'] < 2.5,
            'allocation_underweight' => $allocationGap > 0.05
        ];

        $requiredConditions = 3; // Need at least 3 conditions
        $metConditions = array_sum($conditions);

        if ($metConditions < $requiredConditions) {
            return null;
        }

        // Calculate quantity
        $quantity = $this->calculateBuyQuantity($holding, $allocationGap);
        if ($quantity <= 0) {
            return null;
        }

        // Calculate confidence score
        $confidence = $this->calculateBuyConfidence($holding, $conditions, $confidencePenalty);
        if ($confidence < 50) {
            return null;
        }

        // Calculate stop loss and take profit
        $stopLoss = $this->calculateStopLoss($holding);
        $takeProfit = $this->calculateTakeProfit($holding, 'buy');

        // Create recommendation
        $recommendation = new Recommendation([
            'portfolio_id' => $this->portfolioId,
            'holding_id' => $holding['id'],
            'type' => Recommendation::TYPE_BUY_LIMIT,
            'urgency' => $this->determineUrgency($conditions, $confidence),
            'quantity' => $quantity,
            'trigger_price' => $support ?: $holding['current_price'] * 0.98,
            'trigger_condition' => 'limit_order',
            'stop_loss' => $stopLoss,
            'take_profit' => $takeProfit,
            'rationale_primary' => sprintf(
                "%s: INCREMENTA %d unità se prezzo tocca €%.2f (%s)",
                $holding['ticker'],
                $quantity,
                $support ?: $holding['current_price'] * 0.98,
                $support ? 'supporto tecnico' : 'pullback 2%'
            ),
            'rationale_technical' => sprintf(
                "RSI %.1f (%s), MACD positivo, prezzo -2.4% da EMA50",
                $holding['rsi14'] ?? 0,
                $momentum['rsi'] < 35 ? 'ipervenduto' : 'neutro'
            ),
            'confidence_score' => $confidence,
            'expires_at' => $this->calculateExpirationDate(Recommendation::URGENCY_NEXT_2_WEEKS)
        ]);

        $this->logSignalGeneration($holding['ticker'], 'BUY', "Confidence: $confidence, Conditions: $metConditions/$requiredConditions");

        return $recommendation;
    }

    /**
     * Generate sell signal
     *
     * @param array $holding
     * @param int $confidencePenalty
     * @return Recommendation|null
     */
    private function generateSellSignal($holding, $confidencePenalty)
    {
        // Check allocation gap
        $currentAllocation = $this->calculateCurrentAllocation($holding);
        $targetAllocation = $holding['target_allocation_pct'] / 100;
        $allocationGap = $currentAllocation - $targetAllocation;

        if ($allocationGap < 0.02) { // Less than 2% overweight
            return null;
        }

        // Technical conditions
        $momentum = $this->analyzeMomentum($holding);
        $resistance = $this->findResistanceLevel($holding);
        $pnl = $this->calculatePnL($holding);

        // Sell signal conditions (need at least 2)
        $conditions = [
            'rsi_overbought' => $momentum['rsi'] > 75,
            'price_above_resistance' => $resistance && $holding['current_price'] > $resistance * 1.02,
            'high_gain' => $pnl > 25,
            'allocation_overweight' => $allocationGap > 0.05,
            'bearish_divergence' => $this->detectBearishDivergence($holding)
        ];

        $requiredConditions = 2;
        $metConditions = array_sum($conditions);

        if ($metConditions < $requiredConditions) {
            return null;
        }

        // Calculate quantity (sell 30-50% of excess)
        $quantity = $this->calculateSellQuantity($holding, $allocationGap);
        if ($quantity <= 0) {
            return null;
        }

        // Calculate confidence score
        $confidence = $this->calculateSellConfidence($holding, $conditions, $confidencePenalty);
        if ($confidence < 50) {
            return null;
        }

        // Create recommendation
        $recommendation = new Recommendation([
            'portfolio_id' => $this->portfolioId,
            'holding_id' => $holding['id'],
            'type' => Recommendation::TYPE_SELL_PARTIAL,
            'urgency' => $this->determineUrgency($conditions, $confidence),
            'quantity' => $quantity,
            'trigger_price' => $holding['current_price'],
            'trigger_condition' => 'market_order',
            'rationale_primary' => sprintf(
                "%s: ALLEGGERISCI %d unità (%.0f%% della posizione)",
                $holding['ticker'],
                $quantity,
                ($quantity / $holding['quantity']) * 100
            ),
            'rationale_technical' => sprintf(
                "RSI %.1f (%s), P&L %.1f%%, Sovrapeso %.1f%% (target: %.1f%%)",
                $holding['rsi14'] ?? 0,
                $momentum['rsi'] > 70 ? 'ipercomprato' : 'neutro',
                $pnl,
                $currentAllocation * 100,
                $targetAllocation * 100
            ),
            'confidence_score' => $confidence,
            'expires_at' => $this->calculateExpirationDate(Recommendation::URGENCY_THIS_WEEK)
        ]);

        $this->logSignalGeneration($holding['ticker'], 'SELL', "Confidence: $confidence, Conditions: $metConditions/$requiredConditions");

        return $recommendation;
    }

    /**
     * Generate stop loss signal
     *
     * @param array $holding
     * @param int $confidencePenalty
     * @return Recommendation|null
     */
    private function generateStopLossSignal($holding, $confidencePenalty)
    {
        $currentPrice = $holding['current_price'];
        $avgPrice = $holding['avg_price'];
        $pnl = (($currentPrice - $avgPrice) / $avgPrice) * 100;

        // Stop loss conditions
        $conditions = [
            'price_below_ema200' => $currentPrice < $holding['ema200'],
            'loss_exceeds_8pct' => $pnl < -8,
            'high_volatility' => ($holding['atr14_pct'] ?? 0) > 2.5
        ];

        $metConditions = array_sum($conditions);

        if ($metConditions === 0) {
            return null;
        }

        // Calculate stop loss level
        $stopLoss = $this->calculateStopLoss($holding);

        $recommendation = new Recommendation([
            'portfolio_id' => $this->portfolioId,
            'holding_id' => $holding['id'],
            'type' => Recommendation::TYPE_SET_STOP_LOSS,
            'urgency' => Recommendation::URGENCY_IMMEDIATE,
            'trigger_price' => $stopLoss,
            'trigger_condition' => 'stop_loss',
            'stop_loss' => $stopLoss,
            'rationale_primary' => sprintf(
                "%s: IMPOSTA STOP LOSS a €%.2f (%.1f%% da carico)",
                $holding['ticker'],
                $stopLoss,
                (($stopLoss - $avgPrice) / $avgPrice) * 100
            ),
            'rationale_technical' => sprintf(
                "Prezzo €%.2f, P&L %.1f%%, EMA200 €%.2f, ATR %.1f%%",
                $currentPrice,
                $pnl,
                $holding['ema200'] ?? 0,
                $holding['atr14_pct'] ?? 0
            ),
            'confidence_score' => 80 - $confidencePenalty,
            'expires_at' => $this->calculateExpirationDate(Recommendation::URGENCY_IMMEDIATE)
        ]);

        $this->logSignalGeneration($holding['ticker'], 'STOP_LOSS', "Stop at €$stopLoss");

        return $recommendation;
    }

    /**
     * Generate take profit signal
     *
     * @param array $holding
     * @param int $confidencePenalty
     * @return Recommendation|null
     */
    private function generateTakeProfitSignal($holding, $confidencePenalty)
    {
        $currentPrice = $holding['current_price'];
        $avgPrice = $holding['avg_price'];
        $pnl = (($currentPrice - $avgPrice) / $avgPrice) * 100;

        if ($pnl < 15) {
            return null;
        }

        // Find resistance levels
        $resistance = $this->findResistanceLevel($holding);
        $fiftyTwoWeekHigh = $holding['fifty_two_week_high'];

        // Take profit conditions
        $conditions = [
            'near_resistance' => $resistance && $currentPrice >= $resistance * 0.98,
            'near_52w_high' => $fiftyTwoWeekHigh && $currentPrice >= $fiftyTwoWeekHigh * 0.98,
            'high_rsi' => ($holding['rsi14'] ?? 0) > 70,
            'significant_gain' => $pnl > 25
        ];

        $requiredConditions = 2;
        $metConditions = array_sum($conditions);

        if ($metConditions < $requiredConditions) {
            return null;
        }

        // Calculate quantity (take profit on 15-30% of position)
        $quantity = max(1, floor($holding['quantity'] * 0.2));
        $takeProfit = $resistance ?: $fiftyTwoWeekHigh ?: $currentPrice * 1.05;

        $recommendation = new Recommendation([
            'portfolio_id' => $this->portfolioId,
            'holding_id' => $holding['id'],
            'type' => Recommendation::TYPE_SET_TAKE_PROFIT,
            'urgency' => Recommendation::URGENCY_THIS_WEEK,
            'quantity' => $quantity,
            'trigger_price' => $takeProfit,
            'trigger_condition' => 'limit_order',
            'take_profit' => $takeProfit,
            'rationale_primary' => sprintf(
                "%s: TAKE PROFIT parziale - vendi %d unità (%.0f%% posizione)",
                $holding['ticker'],
                $quantity,
                ($quantity / $holding['quantity']) * 100
            ),
            'rationale_technical' => sprintf(
                "P&L %.1f%%, RSI %.1f, Resistenza €%.2f, Max 52W €%.2f",
                $pnl,
                $holding['rsi14'] ?? 0,
                $resistance ?: 0,
                $fiftyTwoWeekHigh ?: 0
            ),
            'confidence_score' => 60 - $confidencePenalty,
            'expires_at' => $this->calculateExpirationDate(Recommendation::URGENCY_THIS_WEEK)
        ]);

        $this->logSignalGeneration($holding['ticker'], 'TAKE_PROFIT', "TP at €$takeProfit");

        return $recommendation;
    }

    /**
     * Generate rebalancing signal
     *
     * @param array $holding
     * @param int $confidencePenalty
     * @return Recommendation|null
     */
    private function generateRebalanceSignal($holding, $confidencePenalty)
    {
        $currentAllocation = $this->calculateCurrentAllocation($holding);
        $targetAllocation = $holding['target_allocation_pct'] / 100;
        $allocationGap = abs($currentAllocation - $targetAllocation);

        if ($allocationGap < 0.05) { // Less than 5% deviation
            return null;
        }

        // Check if this is a significant deviation (triggered monthly or >10% change)
        if ($allocationGap < 0.10) {
            return null;
        }

        $isOverweight = $currentAllocation > $targetAllocation;

        $recommendation = new Recommendation([
            'portfolio_id' => $this->portfolioId,
            'holding_id' => $holding['id'],
            'type' => $isOverweight ? Recommendation::TYPE_SELL_PARTIAL : Recommendation::TYPE_BUY_LIMIT,
            'urgency' => Recommendation::URGENCY_MONITORING,
            'rationale_primary' => sprintf(
                "%s: RIBILANCIAMENTO - peso %.1f%% vs target %.1f%%",
                $holding['ticker'],
                $currentAllocation * 100,
                $targetAllocation * 100
            ),
            'rationale_technical' => sprintf(
                "Deviazione allocazione %.1f%%, necessario %s",
                $allocationGap * 100,
                $isOverweight ? 'alleggerimento' : 'incremento'
            ),
            'confidence_score' => 50 - $confidencePenalty,
            'expires_at' => $this->calculateExpirationDate(Recommendation::URGENCY_NEXT_2_WEEKS)
        ]);

        $this->logSignalGeneration($holding['ticker'], 'REBALANCE', sprintf("Gap: %.1f%%", $allocationGap * 100));

        return $recommendation;
    }

    /**
     * Validate holding data with graceful degradation
     *
     * @param array $holding
     * @return array
     */
    private function validateHolding($holding)
    {
        $issues = [];
        $confidencePenalty = 0;
        $canProcess = true;

        // Critical fields - cannot process without these
        $criticalFields = ['current_price', 'avg_price', 'quantity'];
        foreach ($criticalFields as $field) {
            if (empty($holding[$field]) || $holding[$field] <= 0) {
                $issues[] = "CRITICAL: $field mancante";
                $canProcess = false;
            }
        }

        if (!$canProcess) {
            return [
                'valid' => false,
                'reason' => 'Dati critici mancanti: ' . implode(', ', $issues),
                'confidence_penalty' => 100
            ];
        }

        // Important fields - use fallbacks
        $importantFields = ['rsi14', 'ema50', 'ema200'];
        foreach ($importantFields as $field) {
            if (empty($holding[$field])) {
                $issues[] = "WARNING: $field fallback";
                $confidencePenalty += 20;
                $holding[$field] = $this->getFallbackValue($field, $holding);
            }
        }

        // Useful fields - use fallbacks
        $usefulFields = ['macd_hist', 'bb_upper', 'atr14_pct'];
        foreach ($usefulFields as $field) {
            if (empty($holding[$field])) {
                $issues[] = "INFO: $field fallback";
                $confidencePenalty += 10;
                $holding[$field] = $this->getFallbackValue($field, $holding);
            }
        }

        // Cap penalty
        $confidencePenalty = min($confidencePenalty, 50);

        return [
            'valid' => true,
            'holding' => $holding,  // con fallback applicati
            'issues' => $issues,
            'confidence_penalty' => $confidencePenalty
        ];
    }

    /**
     * Get fallback value for missing indicator
     *
     * @param string $field
     * @param array $holding
     * @return mixed
     */
    private function getFallbackValue($field, $holding)
    {
        $fallbacks = [
            'rsi14' => 50,
            'ema50' => $holding['current_price'],
            'ema200' => $holding['current_price'],
            'macd_hist' => 0,
            'bb_upper' => $holding['current_price'] * 1.02,
            'atr14_pct' => 2.0,
            'hist_vol_30d' => 15.0
        ];

        return $fallbacks[$field] ?? null;
    }

    /**
     * Analyze trend using EMAs
     *
     * @param array $holding
     * @return string
     */
    private function analyzeTrend($holding)
    {
        $ema50 = $holding['ema50'] ?? 0;
        $ema200 = $holding['ema200'] ?? 0;
        $currentPrice = $holding['current_price'];

        if ($ema50 > $ema200) {
            return 'BULLISH';
        } elseif ($ema50 < $ema200) {
            return 'BEARISH';
        } else {
            return 'NEUTRAL';
        }
    }

    /**
     * Analyze momentum using RSI and MACD
     *
     * @param array $holding
     * @return array
     */
    private function analyzeMomentum($holding)
    {
        $rsi = $holding['rsi14'] ?? 50;
        $macdHist = $holding['macd_hist'] ?? 0;

        return [
            'rsi' => $rsi,
            'macd' => $macdHist,
            'status' => $rsi > 70 ? 'OVERBOUGHT' : ($rsi < 35 ? 'OVERSOLD' : 'NEUTRAL')
        ];
    }

    /**
     * Analyze volatility
     *
     * @param array $holding
     * @return array
     */
    private function analyzeVolatility($holding)
    {
        $atrPct = $holding['atr14_pct'] ?? 2.0;
        $histVol = $holding['hist_vol_30d'] ?? 15.0;

        return [
            'atr_pct' => $atrPct,
            'hist_vol_30d' => $histVol,
            'status' => $atrPct > 2.5 ? 'HIGH' : ($atrPct < 1.5 ? 'LOW' : 'NORMAL')
        ];
    }

    /**
     * Find support level
     *
     * @param array $holding
     * @return float|null
     */
    private function findSupportLevel($holding)
    {
        $currentPrice = $holding['current_price'];
        $ema50 = $holding['ema50'] ?? 0;

        // Check if price is within 2% of EMA50
        if ($ema50 > 0 && abs($currentPrice - $ema50) / $currentPrice < 0.02) {
            return $ema50;
        }

        // Check Fibonacci levels
        $fibLevels = [
            'fib_38_2' => 0.382,
            'fib_50_0' => 0.500,
            'fib_61_8' => 0.618
        ];

        foreach ($fibLevels as $level => $ratio) {
            if (!empty($holding[$level])) {
                $fibLevel = $holding[$level];
                if (abs($currentPrice - $fibLevel) / $currentPrice < 0.02) {
                    return $fibLevel;
                }
            }
        }

        return null;
    }

    /**
     * Find resistance level
     *
     * @param array $holding
     * @return float|null
     */
    private function findResistanceLevel($holding)
    {
        $currentPrice = $holding['current_price'];

        // Check 52-week high
        $fiftyTwoWeekHigh = $holding['fifty_two_week_high'];
        if ($fiftyTwoWeekHigh && $currentPrice >= $fiftyTwoWeekHigh * 0.98) {
            return $fiftyTwoWeekHigh;
        }

        // Check Bollinger upper band
        $bbUpper = $holding['bb_upper'];
        if ($bbUpper && $currentPrice >= $bbUpper * 0.98) {
            return $bbUpper;
        }

        // Check Fibonacci levels
        $fibLevels = ['fib_61_8', 'fib_78_6'];
        foreach ($fibLevels as $level) {
            if (!empty($holding[$level])) {
                $fibLevel = $holding[$level];
                if ($currentPrice >= $fibLevel * 0.98) {
                    return $fibLevel;
                }
            }
        }

        return null;
    }

    /**
     * Calculate P&L percentage
     *
     * @param array $holding
     * @return float
     */
    private function calculatePnL($holding)
    {
        return (($holding['current_price'] - $holding['avg_price']) / $holding['avg_price']) * 100;
    }

    /**
     * Calculate current allocation percentage
     *
     * @param array $holding
     * @return float
     */
    private function calculateCurrentAllocation($holding)
    {
        $holdingValue = $holding['quantity'] * $holding['current_price'];
        return $holdingValue / $this->portfolioValue;
    }

    /**
     * Calculate portfolio value
     *
     * @param array $holdings
     * @return float
     */
    private function calculatePortfolioValue($holdings)
    {
        $value = 0;
        foreach ($holdings as $holding) {
            $value += $holding['quantity'] * $holding['current_price'];
        }
        return $value;
    }

    /**
     * Calculate buy quantity
     *
     * @param array $holding
     * @param float $allocationGap
     * @return int
     */
    private function calculateBuyQuantity($holding, $allocationGap)
    {
        $targetValue = $this->portfolioValue * ($holding['target_allocation_pct'] / 100);
        $currentValue = $holding['quantity'] * $holding['current_price'];
        $gapValue = $targetValue - $currentValue;

        // Don't exceed 50% of gap in single operation
        $maxBuyValue = $gapValue * 0.5;
        $suggestedQty = floor($maxBuyValue / $holding['current_price']);

        return max(1, min($suggestedQty, floor($gapValue / $holding['current_price'])));
    }

    /**
     * Calculate sell quantity
     *
     * @param array $holding
     * @param float $allocationGap
     * @return int
     */
    private function calculateSellQuantity($holding, $allocationGap)
    {
        $excessAllocation = abs($allocationGap);
        $sellRatio = $excessAllocation > 0.10 ? 0.5 : 0.3;
        $excessValue = ($holding['quantity'] * $holding['current_price']) -
                       ($this->portfolioValue * ($holding['target_allocation_pct'] / 100));
        $suggestedQty = floor(($excessValue * $sellRatio) / $holding['current_price']);

        return max(1, $suggestedQty);
    }

    /**
     * Calculate stop loss level
     *
     * @param array $holding
     * @return float
     */
    private function calculateStopLoss($holding)
    {
        $avgPrice = $holding['avg_price'];
        $currentPrice = $holding['current_price'];
        $atrPct = $holding['atr14_pct'] ?? 2.0;
        $assetClass = $holding['asset_class'] ?? 'Equity';

        // Base stop loss by asset class
        $baseStops = [
            'Equity' => 0.08,
            'Dividend' => 0.10,
            'Commodity' => 0.05,
            'Bond' => 0.03
        ];

        $baseStop = $baseStops[$assetClass] ?? 0.08;

        // Adjust for volatility
        $volatilityMultiplier = max(1, $atrPct / 2);
        $adjustedStop = $baseStop * $volatilityMultiplier;
        $adjustedStop = min($adjustedStop, 0.15); // Cap at 15%

        $stopPrice = $avgPrice * (1 - $adjustedStop);

        // If in profit, use trailing stop
        if ($currentPrice > $avgPrice * 1.10) {
            $trailingStop = $currentPrice * (1 - $baseStop * 0.7);
            $stopPrice = max($stopPrice, $trailingStop);
        }

        return round($stopPrice, 2);
    }

    /**
     * Calculate take profit level
     *
     * @param array $holding
     * @param string $signalType
     * @return float
     */
    private function calculateTakeProfit($holding, $signalType)
    {
        $currentPrice = $holding['current_price'];
        $resistance = $this->findResistanceLevel($holding);

        if ($resistance) {
            return $resistance;
        }

        // Default take profit levels
        if ($signalType === 'buy') {
            return $currentPrice * 1.15; // 15% gain
        } else {
            return $currentPrice * .95; // 5% below current for sells
        }
    }

    /**
     * Calculate buy confidence score
     *
     * @param array $holding
     * @param array $conditions
     * @param int $penalty
     * @return int
     */
    private function calculateBuyConfidence($holding, $conditions, $penalty)
    {
        $score = 50; // Base score

        // RSI factor
        $rsi = $holding['rsi14'] ?? 50;
        if ($rsi < 35) $score += 20;
        elseif ($rsi < 45) $score += 10;
        elseif ($rsi > 65) $score -= 15;

        // Trend factor
        if ($conditions['trend_bullish']) $score += 15;
        else $score -= 10;

        // MACD factor
        $macdHist = $holding['macd_hist'] ?? 0;
        if ($macdHist > 0) $score += 10;
        else $score -= 5;

        // Support factor
        if ($conditions['price_near_support']) $score += 15;

        // Apply penalty
        $score -= $penalty;

        return max(0, min(100, $score));
    }

    /**
     * Calculate sell confidence score
     *
     * @param array $holding
     * @param array $conditions
     * @param int $penalty
     * @return int
     */
    private function calculateSellConfidence($holding, $conditions, $penalty)
    {
        $score = 50; // Base score

        // RSI factor
        $rsi = $holding['rsi14'] ?? 50;
        if ($rsi > 70) $score += 20;
        elseif ($rsi > 60) $score += 10;
        elseif ($rsi < 40) $score -= 15;

        // High gain factor
        if ($conditions['high_gain']) $score += 15;

        // Overweight factor
        if ($conditions['allocation_overweight']) $score += 10;

        // Resistance factor
        if ($conditions['price_above_resistance']) $score += 10;

        // Apply penalty
        $score -= $penalty;

        return max(0, min(100, $score));
    }

    /**
     * Detect bearish divergence
     *
     * @param array $holding
     * @return bool
     */
    private function detectBearishDivergence($holding)
    {
        // Simplified: price up but MACD down
        $macdHist = $holding['macd_hist'] ?? 0;
        $priceChange = $holding['one_month_change_percent'] ?? 0;

        return $priceChange > 5 && $macdHist < -0.1;
    }

    /**
     * Determine urgency level
     *
     * @param array $conditions
     * @param int $confidence
     * @return string
     */
    private function determineUrgency($conditions, $confidence)
    {
        // High confidence or critical conditions = immediate
        if ($confidence >= 80 || isset($conditions['price_below_ema200']) || isset($conditions['loss_exceeds_8pct'])) {
            return Recommendation::URGENCY_IMMEDIATE;
        }

        // Medium confidence = this week
        if ($confidence >= 60) {
            return Recommendation::URGENCY_THIS_WEEK;
        }

        // Low confidence = monitor
        return Recommendation::URGENCY_NEXT_2_WEEKS;
    }

    /**
     * Calculate expiration date
     *
     * @param string $urgency
     * @return string
     */
    private function calculateExpirationDate($urgency)
    {
        $days = [
            Recommendation::URGENCY_IMMEDIATE => 1,
            Recommendation::URGENCY_THIS_WEEK => 7,
            Recommendation::URGENCY_NEXT_2_WEEKS => 14,
            Recommendation::URGENCY_MONITORING => 30
        ];

        $daysToAdd = $days[$urgency] ?? 7;
        return date('Y-m-d H:i:s', strtotime("+$daysToAdd days"));
    }

    /**
     * Check for recent recommendations
     *
     * @param int $holdingId
     * @return bool
     */
    private function hasRecentRecommendation($holdingId)
    {
        return $this->recommendationRepo->hasRecentRecommendation($holdingId, 'BUY_LIMIT', 48) ||
               $this->recommendationRepo->hasRecentRecommendation($holdingId, 'SELL_PARTIAL', 48);
    }

    /**
     * Filter and deduplicate recommendations
     *
     * @param array $recommendations
     * @return array
     */
    private function filterAndDeduplicate($recommendations)
    {
        $filtered = [];
        $seen = [];

        foreach ($recommendations as $recommendation) {
            // Skip low confidence
            if ($recommendation->get('confidence_score', 0) < 50) {
                continue;
            }

            // Create unique key
            $key = $recommendation->get('holding_id') . '_' . $recommendation->get('type');

            if (!isset($seen[$key])) {
                $seen[$key] = true;
                $filtered[] = $recommendation;
            }
        }

        return $filtered;
    }

    /**
     * Save recommendation to database
     *
     * @param Recommendation $recommendation
     * @return int|null
     */
    private function saveRecommendation($recommendation)
    {
        // Validate recommendation
        $errors = $recommendation->validate();
        if (!empty($errors)) {
            $this->logSignalGeneration('SYSTEM', 'ERROR', 'Validation failed: ' . implode(', ', $errors));
            return null;
        }

        try {
            $data = $recommendation->toArray();
            return $this->recommendationRepo->create($data);
        } catch (Exception $e) {
            $this->logSignalGeneration('SYSTEM', 'ERROR', 'Database error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Load portfolio configuration
     *
     * @return array
     */
    private function loadPortfolioConfig()
    {
        return [
            'min_confidence' => 50,
            'max_recommendations_per_holding' => 2,
            'recommendation_cooldown_hours' => 48,
            'allocation_tolerance' => 0.02, // 2%
            'rebalance_threshold' => 0.05 // 5%
        ];
    }

    /**
     * Log signal generation
     *
     * @param string $ticker
     * @param string $action
     * @param string $message
     */
    private function logSignalGeneration($ticker, $action, $message)
    {
        $logMessage = sprintf(
            "[%s] %s - %s: %s\n",
            date('Y-m-d H:i:s'),
            $ticker,
            $action,
            $message
        );

        error_log($logMessage, 3, __DIR__ . '/../../logs/signal_generation.log');
    }

    /**
     * Get signal generation statistics
     *
     * @return array
     */
    public function getStatistics()
    {
        return $this->recommendationRepo->getStatistics($this->portfolioId);
    }

    /**
     * Clean up old recommendations
     *
     * @return int Affected rows
     */
    public function cleanupOldRecommendations()
    {
        return $this->recommendationRepo->expireOldRecommendations($this->portfolioId);
    }
}
?>

/**
 * Log signal generation
 *
 * @param string $ticker
 * @param string $action
 * @param string $message
 */
private function logSignalGeneration($ticker, $action, $message)
{
    $logMessage = sprintf(
        "[%s] %s - %s: %s\n",
        date('Y-m-d H:i:s'),
        $ticker,
        $action,
        $message
    );

    error_log($logMessage, 3, __DIR__ . '/../../logs/signal_generation.log');
}

/**
 * Get signal generation statistics
 *
 * @return array
 */
public function getStatistics()
{
    return $this->recommendationRepo->getStatistics($this->portfolioId);
}

/**
 * Clean up old recommendations
 *
 * @return int Affected rows
 */
public function cleanupOldRecommendations()
{
    return $this->recommendationRepo->expireOldRecommendations($this->portfolioId);
}
?>

/**
 * Signal Generator Service
 *
 * Generates trading signals based on technical analysis and portfolio allocation.
 * Implements the Core-Satellite Risk Parity strategy from Strategia Operativa v2.
 *
 * @package TradingPortfolio\Services
 * @version 1.0.0
 */

require_once __DIR__ . '/../Database/Repositories/RecommendationRepository.php';
require_once __DIR__ . '/../Database/Repositories/HoldingRepository.php';
require_once __DIR__ . '/../Models/Recommendation.php';

class SignalGeneratorService
{
    /**
     * @var RecommendationRepository
     */
    private $recommendationRepo;

    /**
     * @var HoldingRepository
     */
    private $holdingRepo;

    /**
     * @var int Default portfolio ID
     */
    private $portfolioId;

    /**
     * @var array Portfolio configuration
     */
    private $portfolioConfig;

    /**
     * @var float Total portfolio value
     */
    private $portfolioValue;

    /**
     * Constructor
     *
     * @param RecommendationRepository $recommendationRepo
     * @param HoldingRepository $holdingRepo
     * @param int $portfolioId
     */
    public function __construct(
        RecommendationRepository $recommendationRepo,
        HoldingRepository $holdingRepo,
        $portfolioId = 1
    ) {
        $this->recommendationRepo = $recommendationRepo;
        $this->holdingRepo = $holdingRepo;
        $this->portfolioId = $portfolioId;
        $this->portfolioConfig = $this->loadPortfolioConfig();
    }

    /**
     * Generate signals for all holdings
     *
     * @return array Generated recommendations
     */
    public function generateSignals()
    {
        $recommendations = [];
        $holdings = $this->holdingRepo->getEnrichedHoldings($this->portfolioId);
        $this->portfolioValue = $this->calculatePortfolioValue($holdings);

        foreach ($holdings as $holding) {
            // Validate holding data
            $validation = $this->validateHolding($holding);
            if (!$validation['valid']) {
                $this->logSignalGeneration($holding['ticker'], 'SKIPPED', $validation['reason']);
                continue;
            }

            $holding = $validation['holding']; // With fallbacks applied
            $confidencePenalty = $validation['confidence_penalty'];

            // Generate signals for this holding
            $signals = $this->generateSignalsForHolding($holding, $confidencePenalty);

            foreach ($signals as $signal) {
                $recommendations[] = $signal;
            }
        }

        // Filter and deduplicate
        $recommendations = $this->filterAndDeduplicate($recommendations);

        // Persist recommendations
        $savedRecommendations = [];
        foreach ($recommendations as $recommendation) {
            $id = $this->saveRecommendation($recommendation);
            if ($id) {
                $recommendation->set('id', $id);
                $savedRecommendations[] = $recommendation;
            }
        }

        return $savedRecommendations;
    }

    /**
     * Get signal generation statistics
     *
     * @return array
     */
    public function getStatistics()
    {
        return $this->recommendationRepo->getStatistics($this->portfolioId);
    }

    /**
     * Clean up old recommendations
     *
     * @return int Affected rows
     */
    public function cleanupOldRecommendations()
    {
        return $this->recommendationRepo->expireOldRecommendations($this->portfolioId);
    }
}
?>

/**
 * Signal Generator Service
 *
 * Generates trading signals based on technical analysis and portfolio allocation.
 * Implements the Core-Satellite Risk Parity strategy from Strategia Operativa v2.
 *
 * @package TradingPortfolio\Services
 * @version 1.0.0
 */

require_once __DIR__ . '/../Database/Repositories/RecommendationRepository.php';
require_once __DIR__ . '/../Database/Repositories/HoldingRepository.php';
require_once __DIR__ . '/../Models/Recommendation.php';

class SignalGeneratorService
{
    /**
     * @var RecommendationRepository
     */
    private $recommendationRepo;

    /**
     * @var HoldingRepository
     */
    private $holdingRepo;

    /**
     * @var int Default portfolio ID
     */
    private $portfolioId;

    /**
     * @var array Portfolio configuration
     */
    private $portfolioConfig;

    /**
     * @var float Total portfolio value
     */
    private $portfolioValue;

    /**
     * Constructor
     *
     * @param RecommendationRepository $recommendationRepo
     * @param HoldingRepository $holdingRepo
     * @param int $portfolioId
     */
    public function __construct(
        RecommendationRepository $recommendationRepo,
        HoldingRepository $holdingRepo,
        $portfolioId = 1
    ) {
        $this->recommendationRepo = $recommendationRepo;
        $this->holdingRepo = $holdingRepo;
        $this->portfolioId = $portfolioId;
        $this->portfolioConfig = $this->loadPortfolioConfig();
    }

    /**
     * Generate signals for all holdings
     *
     * @return array Generated recommendations
     */
    public function generateSignals()
    {
        $recommendations = [];
        $holdings = $this->holdingRepo->getEnrichedHoldings($this->portfolioId);
        $this->portfolioValue = $this->calculatePortfolioValue($holdings);

        foreach ($holdings as $holding) {
            // Validate holding data
            $validation = $this->validateHolding($holding);
            if (!$validation['valid']) {
                $this->logSignalGeneration($holding['ticker'], 'SKIPPED', $validation['reason']);
                continue;
            }

            $holding = $validation['holding']; // With fallbacks applied
            $confidencePenalty = $validation['confidence_penalty'];

            // Generate signals for this holding
            $signals = $this->generateSignalsForHolding($holding, $confidencePenalty);

            foreach ($signals as $signal) {
                $recommendations[] = $signal;
            }
        }

        // Filter and deduplicate
        $recommendations = $this->filterAndDeduplicate($recommendations);

        // Persist recommendations
        $savedRecommendations = [];
        foreach ($recommendations as $recommendation) {
            $id = $this->saveRecommendation($recommendation);
            if ($id) {
                $recommendation->set('id', $id);
                $savedRecommendations[] = $recommendation;
            }
        }

        return $savedRecommendations;
    }

    /**
     * Get signal generation statistics
     *
     * @return array
     */
    public function getStatistics()
    {
        return $this->recommendationRepo->getStatistics($this->portfolioId);
    }

    /**
     * Clean up old recommendations
     *
     * @return int Affected rows
     */
    public function cleanupOldRecommendations()
    {
        return $this->recommendationRepo->expireOldRecommendations($this->portfolioId);
    }
}
?>