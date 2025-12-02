# Strategia Operativa - ETF Portfolio Manager

**Versione:** 2.0  
**Data:** 02 Dicembre 2025  
**Scopo:** Linee guida per trasformare i dati di analisi tecnica in output operativi puntuali

---

## 1. Filosofia di Base

### 1.1 Principio Fondamentale: Signal, Not Noise

Il sistema deve produrre **indicazioni operative concrete** ("alleggerisci di 5 unità", "imposta stop loss a €XX") invece di report tecnici che l'utente deve interpretare.

**Cosa vogliamo:**
- "VWCE: incrementa 3 unità se tocca €95.50 (supporto EMA200)"
- "SGLD: imposta trailing stop 5% sotto max 52W (€315.80)"
- "TDIV: take profit parziale 20% - target raggiunto"

**Cosa NON vogliamo:**
- "RSI a 72, MACD positivo, EMA9 > EMA21, Bollinger superiore..."

### 1.2 Livelli di Urgenza

| Livello | Timing | Esempio |
|---------|--------|---------|
| 🔴 **IMMEDIATO** | Entro oggi | Stop loss triggerato, crollo >5% |
| 🟠 **QUESTA SETTIMANA** | 1-5 giorni | RSI ipercomprato/ipervenduto estremo |
| 🟡 **PROSSIME 2 SETTIMANE** | 5-15 giorni | Avvicinamento a supporto/resistenza |
| 🟢 **MONITORAGGIO** | >15 giorni | Drift allocazione, ribilanciamento mensile |

### 1.3 Fonti Dati e Priorità

Il sistema utilizza **due livelli di analisi**:

| Livello | Fonte | Stato | Uso |
|---------|-------|-------|-----|
| **Analisi Tecnica** | `technical_snapshots` + `technical_insights` | ✅ Attivo | Segnali operativi core |
| **Dati Macro/News** | `macro_indicators` (futuro) | ❌ Roadmap | Modificatori e alert contestuali |

**Fase 1 (attuale):** Solo analisi tecnica per generazione segnali.  
**Fase 2 (futura):** Integrazione macro/news come modificatori del confidence score.

---

## 2. Strategia di Allocazione Target

### 2.1 Modello: Core-Satellite con Risk Parity

La strategia combina:
- **Core-Satellite**: peso maggiore agli ETF "core" (globali diversificati)
- **Risk Parity**: aggiustamento pesi in base alla volatilità storica

**Formula calcolo target:**

```
Target_i = (Base_Weight_i × Volatility_Adjustment_i) / Σ(Base_Weight × Vol_Adj)

dove:
- Base_Weight = peso strategico (core vs satellite)
- Volatility_Adjustment = 1 / (Volatilità_30d / Volatilità_Media_Portfolio)
```

### 2.2 Classificazione Holdings

| Ticker | Nome | Asset Class | Ruolo | Base Weight |
|--------|------|-------------|-------|-------------|
| VWCE | Vanguard FTSE All-World | Equity | **CORE** | 40% |
| SWDA.MI | iShares Core MSCI World | Equity | CORE | 25% |
| SGLD.MI | Invesco Physical Gold | Commodity | HEDGE | 10% |
| VHYL.MI | Vanguard FTSE All-World High Div | Dividend | SATELLITE | 10% |
| TDIV.MI | iShares US Dividend | Dividend | SATELLITE | 10% |
| SPYD.FRA | SPDR S&P US Dividend Aristocrats | Dividend | SATELLITE | 5% |

**Totale: 100%**

### 2.3 Calcolo Target con Risk Parity

```php
function calculateRiskParityTargets($holdings, $baseWeights) {
    $portfolioVolatility = calculatePortfolioVolatility($holdings);
    $adjustedWeights = [];
    
    foreach ($holdings as $ticker => $holding) {
        $vol = $holding['volatility_30d'] ?? $portfolioVolatility;
        
        // Inverso della volatilità normalizzata
        // Asset meno volatili → peso maggiore
        $volAdjustment = $portfolioVolatility / max($vol, 0.01);
        
        // Applica adjustment al peso base
        // Cap adjustment tra 0.5x e 1.5x per evitare estremi
        $volAdjustment = max(0.5, min(1.5, $volAdjustment));
        
        $adjustedWeights[$ticker] = $baseWeights[$ticker] * $volAdjustment;
    }
    
    // Normalizza a 100%
    $total = array_sum($adjustedWeights);
    foreach ($adjustedWeights as $ticker => $weight) {
        $adjustedWeights[$ticker] = round(($weight / $total) * 100, 2);
    }
    
    return $adjustedWeights;
}
```

### 2.4 Esempio Calcolo

| Ticker | Base Weight | Vol 30d | Vol Adj | Adj Weight | Target Finale |
|--------|-------------|---------|---------|------------|---------------|
| VWCE | 40% | 15% | 1.00 | 40.0 | 38.5% |
| SWDA.MI | 25% | 16% | 0.94 | 23.5 | 22.6% |
| SGLD.MI | 10% | 12% | 1.25 | 12.5 | 12.0% |
| VHYL.MI | 10% | 14% | 1.07 | 10.7 | 10.3% |
| TDIV.MI | 10% | 18% | 0.83 | 8.3 | 8.0% |
| SPYD.FRA | 5% | 20% | 0.75 | 3.75 | 3.6% |
| **Totale** | 100% | - | - | 98.75 | **100%** |

### 2.5 Ricalcolo Periodico

- **Frequenza**: Mensile (primo giorno del mese)
- **Trigger straordinario**: Volatilità di un asset cambia >50% rispetto al mese precedente
- **Storage**: Campo `target_allocation_pct` in tabella `holdings`
- **Storico**: Log in `allocation_history` per audit (opzionale)

---

## 3. Framework Decisionale

### 3.1 Matrice Segnale → Azione

Il motore combina più indicatori per generare un'azione specifica. Non bastano singoli indicatori.

```
┌─────────────────────────────────────────────────────────────────────┐
│                    MATRICE DECISIONALE                               │
├─────────────────────────────────────────────────────────────────────┤
│                                                                      │
│  TREND (EMA50/200)     MOMENTUM (RSI/MACD)     VOLATILITÀ           │
│        ↓                      ↓                    ↓                 │
│  ┌─────────┐            ┌─────────┐          ┌─────────┐            │
│  │BULLISH  │────────────│OVERSOLD │──────────│BASSA    │            │
│  │EMA50>200│            │RSI < 35 │          │Vol<15%  │            │
│  └────┬────┘            └────┬────┘          └────┬────┘            │
│       │                      │                    │                  │
│       └──────────────────────┴────────────────────┘                 │
│                              │                                       │
│                              ▼                                       │
│                    ┌─────────────────┐                              │
│                    │ AZIONE: BUY     │                              │
│                    │ Incrementa pos. │                              │
│                    │ su pullback a   │                              │
│                    │ EMA50           │                              │
│                    └─────────────────┘                              │
└─────────────────────────────────────────────────────────────────────┘
```

### 3.2 Regole di Combinazione

**SEGNALE BUY (Incremento posizione)**
```
Condizioni TUTTE vere:
├── Trend: EMA50 > EMA200 (Golden Cross o trend già bullish)
├── Momentum: RSI < 40 (pullback, non ipercomprato)
├── Prezzo: entro 3% da supporto (EMA50 o Fibonacci 38.2%)
├── Volatilità: ATR% < media 30gg (mercato "calmo")
└── Allocazione: peso attuale < target allocation - 5%

Output esempio:
"VWCE: INCREMENTA 5 unità se prezzo tocca €94.20 (EMA50).
 Budget stimato: €471. Nuovo peso: 28% (target: 30%)"
```

**SEGNALE SELL (Alleggerimento)**
```
Condizioni ALMENO 2 vere:
├── Momentum: RSI > 75 (ipercomprato)
├── Prezzo: oltre Bollinger superiore da 3+ giorni
├── Gain: P&L > +25% dalla posizione media
├── Allocazione: peso attuale > target allocation + 5%
└── Divergenza: prezzo sale ma MACD scende (bearish divergence)

Output esempio:
"SGLD: ALLEGGERISCI 10 unità (20% della posizione).
 Gain realizzato stimato: €180. Nuovo peso: 30% (target: 32%)"
```

**SEGNALE STOP LOSS**
```
Condizioni:
├── Prezzo < EMA200 (trend breakdown)
├── OPPURE Loss > -8% dal prezzo medio carico
├── OPPURE Volatilità 30d > 2x media storica

Output esempio:
"TDIV: IMPOSTA STOP LOSS a €22.50 (-8% da carico).
 Se triggerato: vendi tutto, loss max €340"
```

**SEGNALE TAKE PROFIT**
```
Condizioni:
├── Prezzo a resistenza forte (Fibonacci 61.8%, max 52W)
├── RSI > 70 con divergenza MACD
├── Gain > +15% (per posizioni < 6 mesi)
├── OPPURE Gain > +30% (per posizioni > 6 mesi)

Output esempio:
"VHYL: TAKE PROFIT parziale - vendi 15 unità (30% posizione).
 Realizzi €220 di gain. Lasci correre il resto"
```

---

## 4. Parametri Operativi per Asset Class

### 4.1 Equity ETF (es. VWCE, SWDA)

| Parametro | Valore | Note |
|-----------|--------|------|
| Stop Loss | -8% da avg_price | Più stretto per posizioni grandi |
| Take Profit | +20/+30% | Scaglionato |
| RSI Ipercomprato | >70 | Allerta, non vendita automatica |
| RSI Ipervenduto | <35 | Zona accumulo |
| Volatilità normale | 12-18% annua | Alert se >25% |
| Max peso singolo | 40% | Diversificazione |

### 4.2 Dividend ETF (es. VHYL, TDIV, SPYD)

| Parametro | Valore | Note |
|-----------|--------|------|
| Stop Loss | -10% da avg_price | Più tollerante (income focus) |
| Take Profit | +15% | Yield più importante del capital gain |
| RSI range | 40-65 | Evita ipercomprato per yield sostenibile |
| Min Yield | 3% | Sotto = rivalutare |
| Dividend cut | -20% YoY | Trigger revisione |

### 4.3 Commodity/Gold ETF (es. SGLD)

| Parametro | Valore | Note |
|-----------|--------|------|
| Stop Loss | -5% da avg_price | Asset difensivo, proteggere |
| Take Profit | +15% | Realizzo più aggressivo |
| Correlazione | <0.3 vs equity | Funzione hedge |
| Max peso | 15% | Decorrelazione, non speculazione |
| Range volatilità | 8-15% | Alert se esce dal range |

### 4.4 Bond ETF (future)

| Parametro | Valore | Note |
|-----------|--------|------|
| Stop Loss | -3% | Asset stabile |
| Duration sensitivity | Monitorare tassi BCE | Inversa correlazione |
| Min peso recessione | 20% | Aumento in risk-off |

---

## 5. Graceful Degradation

### 5.1 Principio

Il sistema deve **sempre** produrre output utili, anche con dati incompleti. Mai bloccarsi o restituire errori all'utente.

### 5.2 Gerarchia Indicatori

Gli indicatori sono classificati per **criticità**:

| Livello | Indicatori | Se mancante |
|---------|------------|-------------|
| **CRITICO** | `current_price`, `avg_price`, `quantity` | ❌ Escludi holding da analisi |
| **IMPORTANTE** | `rsi14`, `ema50`, `ema200` | ⚠️ Usa fallback, riduci confidence -20 |
| **UTILE** | `macd_value`, `bollinger_*`, `atr14` | ℹ️ Usa fallback, riduci confidence -10 |
| **OPZIONALE** | `volume_avg`, `52w_high/low` | ✓ Ignora, nessuna penalità |

### 5.3 Valori di Fallback

```php
const INDICATOR_DEFAULTS = [
    // Critici - no fallback, holding escluso
    'current_price' => null,
    'avg_price' => null,
    'quantity' => null,
    
    // Importanti - fallback conservativi
    'rsi14' => 50,              // Neutro
    'ema50' => 'current_price', // Usa prezzo corrente
    'ema200' => 'current_price',
    
    // Utili - fallback neutri
    'macd_value' => 0,
    'macd_signal' => 0,
    'macd_histogram' => 0,
    'bollinger_upper' => 'current_price * 1.02',
    'bollinger_lower' => 'current_price * 0.98',
    'bollinger_middle' => 'current_price',
    'atr14' => 'current_price * 0.02',  // 2% default
    'atr14_pct' => 2.0,
    'volatility_30d' => 15.0,           // 15% annua default
    
    // Opzionali - nessun fallback necessario
    'volume_avg' => null,
    'high_52w' => null,
    'low_52w' => null,
];
```

### 5.4 Algoritmo di Validazione

```php
function validateHoldingForSignalGeneration($holding) {
    $issues = [];
    $confidencePenalty = 0;
    $canProcess = true;
    
    // Check indicatori critici
    $criticalFields = ['current_price', 'avg_price', 'quantity'];
    foreach ($criticalFields as $field) {
        if (empty($holding[$field]) || $holding[$field] <= 0) {
            $issues[] = "CRITICAL: {$field} mancante";
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
    
    // Check indicatori importanti
    $importantFields = ['rsi14', 'ema50', 'ema200'];
    foreach ($importantFields as $field) {
        if (empty($holding[$field])) {
            $issues[] = "WARNING: {$field} fallback";
            $confidencePenalty += 20;
            $holding[$field] = getDefaultValue($field, $holding);
        }
    }
    
    // Check indicatori utili
    $usefulFields = ['macd_value', 'bollinger_upper', 'atr14_pct'];
    foreach ($usefulFields as $field) {
        if (empty($holding[$field])) {
            $issues[] = "INFO: {$field} fallback";
            $confidencePenalty += 10;
            $holding[$field] = getDefaultValue($field, $holding);
        }
    }
    
    // Cap penalità
    $confidencePenalty = min($confidencePenalty, 50);
    
    return [
        'valid' => true,
        'holding' => $holding,  // con fallback applicati
        'issues' => $issues,
        'confidence_penalty' => $confidencePenalty
    ];
}
```

### 5.5 Regole di Esclusione

Un holding viene **escluso** dalla generazione segnali se:

1. **Dati critici mancanti** (prezzo, quantità, avg_price)
2. **Ultimo snapshot > 7 giorni** (dati stale)
3. **Asset class = NULL** e non mappabile automaticamente
4. **Holding inattivo** (quantity = 0)

### 5.6 Logging Degradation

```php
function logDegradation($holdingId, $issues, $action) {
    // Log per debug e monitoring
    $logEntry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'holding_id' => $holdingId,
        'issues' => $issues,
        'action_taken' => $action,  // 'fallback_applied', 'excluded', 'processed_with_penalty'
    ];
    
    // Salva in cron_logs o tabella dedicata
    error_log(json_encode($logEntry));
}
```

### 5.7 Comunicazione all'Utente

Quando un segnale è generato con dati incompleti:

```
┌─────────────────────────────────────────────────────────────────────┐
│ 🟠 VWCE - Vanguard FTSE All-World                      ⏱ 5 giorni │
├─────────────────────────────────────────────────────────────────────┤
│                                                                      │
│  📈 INCREMENTA 5 unità @ €94.20                        💰 €471     │
│                                                                      │
│  ⚠️ Confidence ridotta: alcuni indicatori non disponibili          │
│     (MACD, Bollinger non aggiornati)                                │
│                                                                      │
│  Confidence: ██████░░░░ 58/100  (originale: 78)                     │
│                                                                      │
└─────────────────────────────────────────────────────────────────────┘
```

---

## 6. Algoritmi di Calcolo

### 6.1 Calcolo Stop Loss Dinamico

```php
function calculateDynamicStopLoss($holding) {
    // Validazione input
    $validation = validateHoldingForSignalGeneration($holding);
    if (!$validation['valid']) {
        return ['error' => $validation['reason']];
    }
    $holding = $validation['holding'];  // con fallback
    
    $avgPrice = $holding['avg_price'];
    $currentPrice = $holding['current_price'];
    $atr = $holding['atr_percent'] ?? 2.0;
    $assetClass = $holding['asset_class'] ?? 'Equity';  // default Equity
    
    // Base stop per asset class
    $baseStop = match($assetClass) {
        'Equity' => 0.08,
        'Dividend' => 0.10,
        'Commodity' => 0.05,
        'Bond' => 0.03,
        default => 0.08
    };
    
    // Aggiusta per volatilità corrente
    $volatilityMultiplier = max(1, $atr / 2);
    $adjustedStop = $baseStop * $volatilityMultiplier;
    
    // Cap massimo
    $adjustedStop = min($adjustedStop, 0.15);
    
    // Calcola prezzo stop
    $stopPrice = $avgPrice * (1 - $adjustedStop);
    
    // Se in profit, usa trailing stop
    if ($currentPrice > $avgPrice * 1.10) {
        $trailingStop = $currentPrice * (1 - $baseStop * 0.7);
        $stopPrice = max($stopPrice, $trailingStop);
    }
    
    return [
        'stop_price' => round($stopPrice, 2),
        'stop_percent' => round($adjustedStop * 100, 1),
        'type' => $currentPrice > $avgPrice * 1.10 ? 'trailing' : 'fixed',
        'confidence_penalty' => $validation['confidence_penalty']
    ];
}
```

### 6.2 Calcolo Quantità Operazione

```php
function calculateTradeQuantity($holding, $action, $portfolioValue) {
    $currentQty = $holding['quantity'];
    $currentPrice = $holding['current_price'];
    $targetAllocation = $holding['target_allocation_pct'] / 100;
    $currentValue = $currentQty * $currentPrice;
    $currentAllocation = $currentValue / $portfolioValue;
    
    // Gap da target
    $allocationGap = $targetAllocation - $currentAllocation;
    
    if ($action === 'BUY') {
        if ($allocationGap <= 0.02) {  // Meno di 2% sottopeso: no azione
            return 0;
        }
        
        // Quanto serve per raggiungere target
        $targetValue = $portfolioValue * $targetAllocation;
        $gapValue = $targetValue - $currentValue;
        
        // Non superare il 50% del gap in una singola operazione
        $maxBuyValue = $gapValue * 0.5;
        $suggestedQty = floor($maxBuyValue / $currentPrice);
        
        // Minimo 1 unità, massimo che porta a target
        return max(1, min($suggestedQty, floor($gapValue / $currentPrice)));
        
    } elseif ($action === 'SELL') {
        if ($allocationGap >= -0.02) {  // Meno di 2% sovrapeso: no azione
            return 0;
        }
        
        $excessAllocation = abs($allocationGap);
        
        // Vendi 30-50% dell'eccesso
        $sellRatio = $excessAllocation > 0.10 ? 0.5 : 0.3;
        $excessValue = $currentValue - ($portfolioValue * $targetAllocation);
        $suggestedQty = floor(($excessValue * $sellRatio) / $currentPrice);
        
        return max(1, $suggestedQty);
    }
    
    return 0;
}
```

### 6.3 Scoring Segnale (Confidence)

```php
function calculateSignalConfidence($holding, $signalType, $validationPenalty = 0) {
    $score = 50;  // base
    
    // Fattori tecnici
    $rsi = $holding['rsi14'] ?? 50;
    $ema50 = $holding['ema50'] ?? $holding['current_price'];
    $ema200 = $holding['ema200'] ?? $holding['current_price'];
    $trendEma = $ema50 > $ema200;
    $macdHistogram = $holding['macd_histogram'] ?? 0;
    $macdPositive = $macdHistogram > 0;
    
    // Bollinger position (0 = lower, 0.5 = middle, 1 = upper)
    $bbUpper = $holding['bollinger_upper'] ?? $holding['current_price'] * 1.02;
    $bbLower = $holding['bollinger_lower'] ?? $holding['current_price'] * 0.98;
    $bbRange = $bbUpper - $bbLower;
    $bollingerPosition = $bbRange > 0 
        ? ($holding['current_price'] - $bbLower) / $bbRange 
        : 0.5;
    
    if ($signalType === 'BUY') {
        // RSI favorevole
        if ($rsi < 35) $score += 20;
        elseif ($rsi < 45) $score += 10;
        elseif ($rsi > 65) $score -= 15;
        
        // Trend bullish
        if ($trendEma) $score += 15;
        else $score -= 10;
        
        // MACD
        if ($macdPositive) $score += 10;
        else $score -= 5;
        
        // Prezzo vicino a supporto
        if ($bollingerPosition < 0.3) $score += 15;
        elseif ($bollingerPosition > 0.8) $score -= 20;
        
    } elseif ($signalType === 'SELL') {
        // Inverti logica
        if ($rsi > 70) $score += 20;
        elseif ($rsi > 60) $score += 10;
        elseif ($rsi < 40) $score -= 15;
        
        if (!$trendEma) $score += 10;
        if (!$macdPositive) $score += 10;
        if ($bollingerPosition > 0.9) $score += 15;
        
    } elseif ($signalType === 'STOP_LOSS') {
        // Stop loss sempre alta priorità se condizioni soddisfatte
        $score = 80;
        
        // Loss attuale
        $pnlPct = (($holding['current_price'] - $holding['avg_price']) / $holding['avg_price']) * 100;
        if ($pnlPct < -8) $score += 15;
        if ($pnlPct < -12) $score += 5;  // extra urgenza
        
    } elseif ($signalType === 'TAKE_PROFIT') {
        $score = 60;
        
        $pnlPct = (($holding['current_price'] - $holding['avg_price']) / $holding['avg_price']) * 100;
        if ($pnlPct > 25) $score += 20;
        if ($pnlPct > 35) $score += 10;
        if ($rsi > 75) $score += 10;
    }
    
    // Applica penalità da validazione dati
    $score -= $validationPenalty;
    
    // Normalizza 0-100
    return max(0, min(100, $score));
}
```

---

## 7. Output Operativi

### 7.1 Struttura Raccomandazione

```json
{
  "id": "rec_20251202_001",
  "created_at": "2025-12-02T09:00:00Z",
  "expires_at": "2025-12-09T09:00:00Z",
  "urgency": "QUESTA_SETTIMANA",
  "holding": {
    "isin": "IE00B3RBWM25",
    "ticker": "VWCE",
    "name": "Vanguard FTSE All-World"
  },
  "action": {
    "type": "BUY_LIMIT",
    "quantity": 5,
    "trigger_price": 94.20,
    "trigger_condition": "limit_order",
    "current_price": 96.50,
    "estimated_cost": 471.00
  },
  "rationale": {
    "primary": "Pullback su EMA50 in trend bullish",
    "technical_summary": "RSI 38 (ipervenduto), MACD positivo, prezzo -2.4% da EMA50",
    "allocation_impact": "Peso da 25% a 28% (target 30%)"
  },
  "risk_management": {
    "stop_loss": 86.50,
    "stop_loss_percent": -8.2,
    "take_profit_1": 103.00,
    "take_profit_2": 110.00,
    "max_loss_eur": 38.50
  },
  "confidence_score": 78,
  "data_quality": {
    "complete": false,
    "missing_indicators": ["macd_histogram"],
    "penalty_applied": 10
  },
  "valid_until_price_change": 3.0
}
```

### 7.2 Tipologie di Raccomandazione

| Tipo | Icona | Descrizione |
|------|-------|-------------|
| `BUY_LIMIT` | 📈 | Incrementa con ordine limite |
| `BUY_MARKET` | ⚡ | Incrementa a mercato (urgente) |
| `SELL_PARTIAL` | 📉 | Alleggerisci % posizione |
| `SELL_ALL` | 🚨 | Chiudi posizione |
| `SET_STOP_LOSS` | 🛡️ | Imposta/aggiorna stop |
| `SET_TAKE_PROFIT` | 🎯 | Imposta take profit |
| `HOLD_MONITOR` | 👁️ | Mantieni, monitora livello X |
| `REBALANCE` | ⚖️ | Ribilanciamento allocazione |

---

## 8. Architettura Tecnica

### 8.1 Schema Database

```sql
-- Target allocation in holdings (già esistente, aggiungere campo)
ALTER TABLE holdings
ADD COLUMN target_allocation_pct DECIMAL(5,2) DEFAULT NULL
COMMENT 'Target allocation % (es. 30.00 = 30%)';

ALTER TABLE holdings
ADD COLUMN role ENUM('CORE', 'SATELLITE', 'HEDGE') DEFAULT 'SATELLITE'
COMMENT 'Ruolo nel portafoglio per strategia core-satellite';

-- Raccomandazioni operative
CREATE TABLE recommendations (
    id INT PRIMARY KEY AUTO_INCREMENT,
    portfolio_id INT NOT NULL,
    holding_id INT,
    
    type ENUM('BUY_LIMIT', 'BUY_MARKET', 'SELL_PARTIAL', 'SELL_ALL', 
              'SET_STOP_LOSS', 'SET_TAKE_PROFIT', 'HOLD_MONITOR', 
              'REBALANCE', 'MACRO_ALERT') NOT NULL,
    urgency ENUM('IMMEDIATO', 'QUESTA_SETTIMANA', 'PROSSIME_2_SETTIMANE', 
                 'MONITORAGGIO') NOT NULL,
    
    quantity INT,
    trigger_price DECIMAL(10,4),
    trigger_condition VARCHAR(50),
    stop_loss DECIMAL(10,4),
    take_profit DECIMAL(10,4),
    
    rationale_primary TEXT,
    rationale_technical TEXT,
    
    confidence_score TINYINT,
    data_quality_issues TEXT COMMENT 'JSON array di indicatori mancanti',
    
    status ENUM('ACTIVE', 'EXECUTED', 'EXPIRED', 'IGNORED', 'SUPERSEDED') DEFAULT 'ACTIVE',
    
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    expires_at DATETIME,
    executed_at DATETIME,
    executed_price DECIMAL(10,4),
    executed_quantity INT,
    
    FOREIGN KEY (portfolio_id) REFERENCES portfolios(id),
    FOREIGN KEY (holding_id) REFERENCES holdings(id),
    INDEX idx_status_urgency (status, urgency),
    INDEX idx_portfolio_active (portfolio_id, status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Log azioni utente
CREATE TABLE recommendation_actions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    recommendation_id INT NOT NULL,
    action ENUM('VIEWED', 'EXECUTED', 'IGNORED', 'POSTPONED') NOT NULL,
    notes TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (recommendation_id) REFERENCES recommendations(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Indicatori macro (Fase 2 - futuro)
CREATE TABLE macro_indicators (
    id INT PRIMARY KEY AUTO_INCREMENT,
    indicator_name VARCHAR(50) NOT NULL,
    indicator_value DECIMAL(10,4),
    source VARCHAR(100),
    recorded_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_name_date (indicator_name, recorded_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### 8.2 Retention Policy

| Tabella | Retention | Cleanup | Motivo |
|---------|-----------|---------|--------|
| `technical_snapshots` | 90 giorni | Cron settimanale | Indicatori storici per trend |
| `snapshots` + `snapshot_holdings` | 2 anni | Cron mensile | Performance tracking |
| `recommendations` (ACTIVE) | Fino a expires_at | Auto | Gestito da status |
| `recommendations` (EXECUTED/EXPIRED/IGNORED) | 1 anno | Cron mensile | Audit trail |
| `recommendation_actions` | 1 anno | Cron mensile | Analytics engagement |
| `macro_indicators` | 2 anni | Cron mensile | Analisi storica |
| `transactions` | **Permanente** | Mai | Fiscalità |
| `holdings` | **Permanente** | Mai | Core data |
| `dividend_payments` | **Permanente** | Mai | Fiscalità |

### 8.3 Script Cleanup

```php
// scripts/cleanup-old-data.php
// Da schedulare settimanalmente

$db = DatabaseManager::getInstance();

// 1. Technical snapshots > 90 giorni
$db->query("
    DELETE FROM technical_snapshots 
    WHERE snapshot_date < DATE_SUB(NOW(), INTERVAL 90 DAY)
");

// 2. Recommendations chiuse > 1 anno
$db->query("
    DELETE FROM recommendation_actions 
    WHERE recommendation_id IN (
        SELECT id FROM recommendations 
        WHERE status IN ('EXECUTED', 'EXPIRED', 'IGNORED') 
        AND created_at < DATE_SUB(NOW(), INTERVAL 1 YEAR)
    )
");

$db->query("
    DELETE FROM recommendations 
    WHERE status IN ('EXECUTED', 'EXPIRED', 'IGNORED') 
    AND created_at < DATE_SUB(NOW(), INTERVAL 1 YEAR)
");

// 3. Macro indicators > 2 anni
$db->query("
    DELETE FROM macro_indicators 
    WHERE recorded_at < DATE_SUB(NOW(), INTERVAL 2 YEAR)
");

// 4. Snapshots > 2 anni
$db->query("
    DELETE sh FROM snapshot_holdings sh
    INNER JOIN snapshots s ON sh.snapshot_id = s.id
    WHERE s.snapshot_date < DATE_SUB(NOW(), INTERVAL 2 YEAR)
");

$db->query("
    DELETE FROM snapshots 
    WHERE snapshot_date < DATE_SUB(NOW(), INTERVAL 2 YEAR)
");

echo "Cleanup completato: " . date('Y-m-d H:i:s');
```

---

## 9. Workflow n8n - Signal Generator

### 9.1 Struttura Workflow

```
┌──────────────────────────────────────────────────────────────────┐
│                    WORKFLOW: Signal Generator                     │
│                    Frequenza: Daily @ 20:00                       │
├──────────────────────────────────────────────────────────────────┤
│                                                                   │
│  1. SCHEDULE TRIGGER                                             │
│     Cron: 0 20 * * * (ogni giorno alle 20:00)                   │
│                                                                   │
│  2. FETCH SIGNAL CONTEXT                                         │
│     GET /api/n8n/signal-context.php                              │
│     → Portfolio value, holdings con target allocation,           │
│       indicatori tecnici, allocation gaps                        │
│                                                                   │
│  3. GENERATE SIGNALS                                             │
│     POST /api/n8n/generate-signals.php                           │
│     → Invoca SignalGeneratorService                              │
│     → Ritorna array raccomandazioni generate                     │
│                                                                   │
│  4. FILTER & DEDUPLICATE                                         │
│     - Rimuovi segnali con confidence < 50                        │
│     - Rimuovi duplicati (stesso holding + tipo entro 48h)        │
│                                                                   │
│  5. PERSIST RECOMMENDATIONS                                       │
│     Per ogni segnale:                                            │
│     POST /api/recommendations.php                                 │
│                                                                   │
│  6. CLEANUP OLD                                                   │
│     PUT /api/recommendations.php?action=expire                   │
│     → expires_at < NOW() → status = EXPIRED                      │
│     → Supersede vecchi segnali stesso holding/tipo               │
│                                                                   │
│  7. (OPZIONALE) NOTIFY                                           │
│     Se urgency = IMMEDIATO:                                       │
│     → Email / Telegram / Push notification                       │
│                                                                   │
└──────────────────────────────────────────────────────────────────┘
```

### 9.2 Endpoint /api/n8n/signal-context.php

```php
// Response structure
{
    "portfolio": {
        "id": 1,
        "total_value": 10575.85,
        "total_invested": 9733.27,
        "pnl_pct": 8.65
    },
    "holdings": [
        {
            "id": 1,
            "isin": "IE00B3RBWM25",
            "ticker": "VWCE",
            "name": "Vanguard FTSE All-World",
            "asset_class": "Equity",
            "role": "CORE",
            
            // Posizione
            "quantity": 50,
            "avg_price": 92.50,
            "current_price": 96.50,
            "market_value": 4825.00,
            "pnl_pct": 4.32,
            
            // Allocazione
            "current_allocation_pct": 45.6,
            "target_allocation_pct": 38.5,
            "allocation_gap_pct": 7.1,  // sovrapeso
            
            // Indicatori tecnici
            "rsi14": 62,
            "ema50": 94.20,
            "ema200": 88.50,
            "macd_value": 1.25,
            "macd_signal": 0.95,
            "macd_histogram": 0.30,
            "bollinger_upper": 99.80,
            "bollinger_middle": 95.40,
            "bollinger_lower": 91.00,
            "atr14_pct": 1.8,
            "volatility_30d": 14.5,
            "high_52w": 102.30,
            "low_52w": 78.20,
            
            // Data quality
            "last_technical_update": "2025-12-02T06:00:00Z",
            "indicators_complete": true
        }
        // ... altri holdings
    ],
    "allocation_summary": {
        "by_asset_class": {
            "Equity": { "current": 65.2, "target": 61.1 },
            "Commodity": { "current": 12.0, "target": 12.0 },
            "Dividend": { "current": 22.8, "target": 21.9 }
        },
        "by_role": {
            "CORE": { "current": 65.2, "target": 61.1 },
            "SATELLITE": { "current": 22.8, "target": 26.9 },
            "HEDGE": { "current": 12.0, "target": 12.0 }
        }
    }
}
```

---

## 10. Metriche di Successo

### 10.1 KPI Sistema

| Metrica | Target | Misura |
|---------|--------|--------|
| Precision segnali | >70% | Segnali EXECUTED con profit / totale EXECUTED |
| Recall urgenze | >90% | Movimenti >5% anticipati da segnale |
| Confidence calibration | R² > 0.6 | Correlazione confidence vs outcome reale |
| User engagement | >50% | Raccomandazioni EXECUTED o IGNORED (non ignorate) |
| Latency | <5 min | Tempo da condizione a raccomandazione visibile |
| Data completeness | >90% | Holdings con tutti indicatori vs totale holdings |

### 10.2 View Performance Tracking

```sql
CREATE VIEW v_recommendation_performance AS
SELECT 
    r.id,
    r.type,
    r.confidence_score,
    r.trigger_price,
    r.executed_price,
    r.executed_at,
    h.ticker,
    h.current_price as current_price_now,
    
    -- Outcome
    CASE 
        WHEN r.type IN ('BUY_LIMIT', 'BUY_MARKET') THEN
            (h.current_price - r.executed_price) / r.executed_price * 100
        WHEN r.type IN ('SELL_PARTIAL', 'SELL_ALL') THEN
            (r.executed_price - h.avg_price) / h.avg_price * 100
    END as realized_pnl_pct,
    
    -- Hit stop?
    CASE WHEN h.current_price <= r.stop_loss THEN 1 ELSE 0 END as hit_stop,
    
    -- Hit TP?
    CASE WHEN h.current_price >= r.take_profit THEN 1 ELSE 0 END as hit_tp,
    
    -- Success?
    CASE 
        WHEN r.type IN ('BUY_LIMIT', 'BUY_MARKET') 
             AND h.current_price > r.executed_price THEN 1
        WHEN r.type IN ('SELL_PARTIAL', 'SELL_ALL') 
             AND h.current_price < r.executed_price THEN 1
        ELSE 0
    END as is_success
    
FROM recommendations r
LEFT JOIN holdings h ON r.holding_id = h.id
WHERE r.status = 'EXECUTED';
```

---

## 11. Integrazione Dati Macro (Roadmap Fase 2)

### 11.1 Fonti Dati Pianificate

| Fonte | Dati | Frequenza | Impatto |
|-------|------|-----------|---------|
| **Fear & Greed Index** | Sentiment mercato | Daily | Modifica aggressività segnali |
| **VIX** | Volatilità attesa | Daily | Allarga/stringe stop loss |
| **Tassi BCE/FED** | Politica monetaria | Mensile | Asset allocation bond/equity |
| **PMI Manifatturiero** | Ciclo economico | Mensile | Settori ciclici vs difensivi |
| **Inflazione EU/USA** | Macro trend | Mensile | Gold, inflation-linked |

### 11.2 Modificatori Macro (Futuro)

```php
function applyMacroModifiers($recommendation, $macroData) {
    $fearGreed = $macroData['fear_greed_index'] ?? 50;
    $vix = $macroData['vix'] ?? 20;
    
    // Fear & Greed modifica confidence
    if ($recommendation['action']['type'] === 'BUY') {
        if ($fearGreed < 25) {
            $recommendation['confidence_score'] += 10;
            $recommendation['rationale']['macro'] = "Extreme Fear = opportunità contrarian";
        } elseif ($fearGreed > 75) {
            $recommendation['confidence_score'] -= 15;
            $recommendation['rationale']['macro'] = "Mercato euforico, cautela";
        }
    }
    
    // VIX modifica stop loss
    if ($vix > 30) {
        $recommendation['risk_management']['stop_loss'] *= 0.95;
        $recommendation['rationale']['volatility_adjustment'] = "VIX alto, stop allargato";
    }
    
    return $recommendation;
}
```

---

## 12. Disclaimer e Limitazioni

### 12.1 Cosa il Sistema NON Fa

- ❌ Non esegue ordini automaticamente
- ❌ Non fornisce consulenza finanziaria
- ❌ Non garantisce profitti
- ❌ Non sostituisce il giudizio dell'investitore

### 12.2 Responsabilità Utente

L'utente DEVE:
1. Verificare ogni raccomandazione prima di eseguirla
2. Considerare la propria situazione finanziaria
3. Consultare un consulente per decisioni importanti
4. Mantenere stop loss come disciplina personale
5. Diversificare oltre quanto suggerito dal sistema

### 12.3 Limiti Tecnici

- Dati tecnici aggiornati 1x/giorno (non real-time)
- Indicatori basati su chiusure giornaliere
- Nessuna analisi fondamentale (P/E, bilanci)
- Macro data con delay (fonti gratuite) - Fase 2
- Target allocation calcolato mensilmente

---

## 13. Piano Implementazione

### 13.1 Fasi e Tempistiche

| Fase | Descrizione | Effort | Stato | Note |
|------|-------------|--------|--------|------|
| **0** | Data Foundation (target allocation, asset class) | 1-2h | ✅ **COMPLETATA** | Decisioni prese e database aggiornato |
| **1** | DB Schema (recommendations, actions) | 30min | ✅ **COMPLETATA** | Tabelle create con viste e relazioni |
| **2** | Repository Layer | 1h | ✅ **COMPLETATA** | Repository pattern implementato con BaseRepository |
| **3** | SignalGeneratorService (core logic) | 4-6h | ✅ **COMPLETATA** | Core engine funzionante con graceful degradation |
| **4** | API Layer (/api/recommendations.php) | 2h | 🔄 **IN CORSO** | Endpoint da implementare |
| **5** | Workflow n8n Signal Generator | 1-2h | ⏳ **PENDING** | Attesa completamento API |
| **6** | Frontend integration | 1h | ⏳ **PENDING** | Attesa completamento API |
| **7** | Monitoring & tuning | Ongoing | ⏳ **PENDING** | Post-deploy |

**Progresso: 4/7 fasi completate (57%)**

### 13.2 Decisioni Consolidate

| Decisione | Scelta | Motivazione |
|-----------|--------|-------------|
| Target Allocation Storage | Campo in `holdings` | Semplice, performante, no JOIN |
| Strategia Allocation | Core-Satellite + Risk Parity | Bilanciamento stabilità/adattamento |
| LLM per Macro/News | Fase 2 (dopo) | Focus su core tecnico prima |
| AI Insights Tecnici | Mantieni esistente | Già funzionante via n8n |
| User Actions (EXECUTED/IGNORED) | Sì, da subito | Feedback loop essenziale |

### 13.3 Quick Start

```sql
-- 1. Popolare asset_class
UPDATE holdings SET asset_class = 'Equity' WHERE ticker IN ('SWDA.MI', 'VWCE');
UPDATE holdings SET asset_class = 'Dividend' WHERE ticker IN ('VHYL.MI', 'TDIV.MI', 'SPYD.FRA');
UPDATE holdings SET asset_class = 'Commodity' WHERE ticker = 'SGLD.MI';

-- 2. Aggiungere campi target
ALTER TABLE holdings
ADD COLUMN target_allocation_pct DECIMAL(5,2) DEFAULT NULL,
ADD COLUMN role ENUM('CORE', 'SATELLITE', 'HEDGE') DEFAULT 'SATELLITE';

-- 3. Popolare target iniziali (da ricalcolare con risk parity)
UPDATE holdings SET target_allocation_pct = 38.5, role = 'CORE' WHERE ticker = 'VWCE';
UPDATE holdings SET target_allocation_pct = 22.6, role = 'CORE' WHERE ticker = 'SWDA.MI';
UPDATE holdings SET target_allocation_pct = 12.0, role = 'HEDGE' WHERE ticker = 'SGLD.MI';
UPDATE holdings SET target_allocation_pct = 10.3, role = 'SATELLITE' WHERE ticker = 'VHYL.MI';
UPDATE holdings SET target_allocation_pct = 8.0, role = 'SATELLITE' WHERE ticker = 'TDIV.MI';
UPDATE holdings SET target_allocation_pct = 8.6, role = 'SATELLITE' WHERE ticker = 'SPYD.FRA';
```

---

## 14. Log Implementazione

### 14.1 Fase 3 - SignalGeneratorService ✅ COMPLETATA (02 Dic 2025)

**Issue Risolti:**
- ✅ Parse error: syntax error, unexpected token "&" in Recommendation.php - Fix: HTML entities &amp;&amp; → &&
- ✅ Fatal error: Call to private DatabaseManager::__construct() - Fix: Usato singleton pattern DatabaseManager::getInstance()
- ✅ Warning: Undefined array key "target_allocation_pct" - Fix: Aggiunti campi mancanti in HoldingRepository mapping

**Componenti Implementati:**
- ✅ SignalGeneratorService con strategia Core-Satellite Risk Parity
- ✅ Graceful degradation per dati mancanti
- ✅ Generazione segnali: BUY/SELL/STOP_LOSS/TAKE_PROFIT/REBALANCE
- ✅ Confidence scoring con penalità per dati incompleti
- ✅ Repository pattern con BaseRepository
- ✅ Test in produzione superati

**Test Effettuati:**
- ✅ Database connectivity test
- ✅ Holdings data retrieval con campi target_allocation_pct e role
- ✅ Signal generation con segnali reali
- ✅ Confidence scoring validation
- ✅ Error handling e graceful degradation

**File Creati/Corretti:**
- `lib/Models/Recommendation.php` - Fix HTML entities
- `lib/Database/Repositories/HoldingRepository.php` - Aggiunti campi target_allocation_pct e role
- `lib/Services/SignalGeneratorService.php` - Core engine implementato
- `lib/Database/Repositories/RecommendationRepository.php` - Repository pattern
- `lib/Database/Repositories/BaseRepository.php` - Base repository pattern

---

**Fine Documento**

*Versione 2.0 - Strategia Core-Satellite Risk Parity, Graceful Degradation, Decisioni Consolidate*
