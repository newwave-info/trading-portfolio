# 📊 Data Enhancement & Visualizzazioni - Roadmap Miglioramenti

**Creato:** 01 Dicembre 2025
**Versione:** 1.0
**Autore:** Analisi Database & Frontend Gap Analysis

---

## 📖 Panoramica

Questo documento definisce la roadmap per sfruttare i **dati disponibili nel database ma non visualizzati nel frontend**, identificati tramite analisi del dump SQL `trading_portfolio.sql` e audit del codice esistente.

**Obiettivo:** Massimizzare il valore dei dati già raccolti dal workflow di enrichment n8n, migliorando l'esperienza utente e fornendo insight più profondi per decisioni di trading informate.

---

## 🔍 Analisi Gap: Dati Disponibili ma Non Utilizzati

### Tabelle Database Principali

| Tabella | Record | Utilizzo Frontend | Gap Identificati |
|---------|--------|-------------------|------------------|
| `holdings` | 4 | ✅ Usata parzialmente | 25+ campi non visualizzati |
| `technical_snapshots` | 8 | ❌ Non usata | Storico giornaliero indicatori |
| `snapshot_holdings` | 6 | ❌ Non usata | Evoluzione allocazione nel tempo |
| `technical_insights` | 23 | ✅ Usata (fix 01/12) | Ora funzionante |
| `dividend_payments` | 142+ | ✅ Usata via VIEW | Completamente integrata |

### Campi `holdings` Non Visualizzati (25 campi)

#### 🔴 **ALTA PRIORITÀ - Analisi Tecnica Avanzata**

1. **Livelli di Fibonacci** (10 campi):
   - `fib_low`, `fib_high` - Estremi del range
   - `fib_23_6`, `fib_38_2`, `fib_50_0`, `fib_61_8`, `fib_78_6` - Livelli di ritracciamento
   - `fib_23_6_dist_pct`, `fib_38_2_dist_pct`, `fib_50_0_dist_pct`, `fib_61_8_dist_pct`, `fib_78_6_dist_pct` - Distanza % dal prezzo corrente
   - **Utilizzo:** Identificazione supporti/resistenze automatici
   - **Impatto:** Alto (trading decisionale)

2. **Bollinger Bands Dettagli** (4 campi):
   - `bb_middle`, `bb_upper`, `bb_lower` - Valori bande
   - `bb_width_pct` - Ampiezza bande (volatilità)
   - **Utilizzo:** Previsione breakout (bande strette = volatilità compressa)
   - **Impatto:** Alto (timing entry/exit)
   - **Note:** Attualmente mostrato solo `bb_percent_b` (posizione relativa)

3. **Range Dettagliati** (8 campi):
   - `range_1m_min`, `range_1m_max`, `range_1m_percentile`
   - `range_3m_min`, `range_3m_max`, `range_3m_percentile`
   - `range_6m_min`, `range_6m_max`, `range_6m_percentile`
   - **Utilizzo:** Analisi multi-timeframe, identificazione estensioni anomale
   - **Impatto:** Medio (conferma trend)
   - **Note:** Attualmente mostrato solo `range_1y_percentile`

#### 🟡 **MEDIA PRIORITÀ - Dati Intraday & Volume**

4. **Intraday** (3 campi):
   - `day_high`, `day_low` - Range giornata corrente
   - `previous_close` - Chiusura precedente
   - **Utilizzo:** Analisi intraday, gap detection
   - **Impatto:** Medio (trader giornalieri)

5. **Volume e Liquidità** (3 campi):
   - `volume` - Volume scambiato corrente
   - `vol_avg_20d` - Media mobile 20 giorni
   - `vol_ratio_current_20d` - Rapporto volume corrente/media
   - **Utilizzo:** Alert volume anomalo (>2x o <0.5x), conferma breakout
   - **Impatto:** Medio (validazione segnali)

6. **52-Week High/Low** (2 campi):
   - `fifty_two_week_high`, `fifty_two_week_low`
   - **Utilizzo:** Badge visivi "Near 52W High/Low"
   - **Impatto:** Alto (psicologia mercato)
   - **Note:** Dati presenti in `portfolio_data.php` ma non mostrati

#### 🟢 **BASSA PRIORITÀ - Metadati e Alternative**

7. **SMA (Simple Moving Averages)** (4 campi):
   - `sma9`, `sma21`, `sma50`, `sma200`
   - **Utilizzo:** Toggle EMA/SMA nei grafici
   - **Impatto:** Basso (preferenza personale)
   - **Note:** Attualmente usate solo EMA

8. **Metadati Strumento** (3 campi):
   - `exchange` - Borsa di quotazione
   - `first_trade_date` - Data primo scambio
   - `price_source` - Fonte prezzo (yahoo, manual, etc.)
   - **Utilizzo:** Modal dettaglio strumento
   - **Impatto:** Basso (info contestuali)

9. **Volatilità Storica 90d** (1 campo):
   - `hist_vol_90d`
   - **Utilizzo:** Confronto volatilità short-term vs long-term
   - **Impatto:** Basso (già disponibile 30d)

---

## 🎯 Roadmap Implementazione

### **FASE 1 - Quick Wins** (Stima: 1-2 giorni, Priorità: 🔴 ALTA)

**Obiettivo:** Massimo impatto con minimo sforzo, zero query aggiuntive al DB

#### 1.1 Livelli di Fibonacci - Vista Analisi Tecnica
**File:** `views/tabs/technical.php`

**Implementazione:**
- Aggiungere colonna espandibile "Fibonacci" nella tabella analisi tecnica
- Click su icona 📐 espande tooltip/dropdown con:
  - Livelli: 23.6%, 38.2%, 50%, 61.8%, 78.6%
  - Distanza % dal prezzo corrente per ogni livello
  - Evidenziare in verde il livello più vicino (potenziale supporto/resistenza immediata)

**Codice di esempio:**
```php
<?php if (!empty($row['fib_50_0'])): ?>
    <div class="fib-levels">
        <button class="fib-toggle">📐</button>
        <div class="fib-dropdown hidden">
            <div class="fib-level <?php echo abs($row['fib_23_6_dist_pct']) < 3 ? 'fib-near' : ''; ?>">
                23.6%: €<?php echo number_format($row['fib_23_6'], 2); ?>
                <span class="fib-dist">(<?php echo number_format($row['fib_23_6_dist_pct'], 1); ?>%)</span>
            </div>
            <!-- Ripetere per altri livelli -->
        </div>
    </div>
<?php endif; ?>
```

**CSS richiesto:**
```css
.fib-near { background: #dcfce7; font-weight: bold; }
.fib-dropdown { position: absolute; z-index: 10; background: white; border: 1px solid #e5e7eb; }
```

**Benefici:**
- 🎯 Supporti/resistenze automatici senza analisi manuale
- ⚡ Entry/exit point precisi
- 📊 Dati già disponibili, nessuna query extra

---

#### 1.2 Bollinger Width - Indicatore Volatilità
**File:** `views/tabs/technical.php`

**Implementazione:**
- Aggiungere colonna "BB Width" dopo "Bollinger"
- Color-coding:
  - Verde: `bb_width_pct < 5%` (volatilità bassa, possibile breakout)
  - Giallo: `5% ≤ bb_width_pct ≤ 10%` (volatilità normale)
  - Rosso: `bb_width_pct > 10%` (volatilità alta, mercato agitato)

**Codice di esempio:**
```php
<?php
$bbWidth = $row['bb_width_pct'] ?? null;
$bbClass = 'text-gray-700';
if ($bbWidth !== null) {
    if ($bbWidth < 5) {
        $bbClass = 'text-positive font-bold'; // Verde = compressione
    } elseif ($bbWidth > 10) {
        $bbClass = 'text-negative'; // Rosso = volatilità alta
    }
}
?>
<td class="px-4 py-3 text-right <?php echo $bbClass; ?>">
    <?php echo $bbWidth !== null ? number_format($bbWidth, 2) . '%' : '-'; ?>
</td>
```

**Tooltip esplicativo:**
```html
<span title="BB Width < 5% = Volatilità compressa, possibile breakout imminente">ℹ️</span>
```

**Benefici:**
- 🚀 Identifica asset pronti per movimento forte
- 📈 Timing ottimale per entry aggressivi
- ⚠️ Alert rischio (volatilità elevata)

---

#### 1.3 Badge "Near 52W High/Low"
**File:** `views/tabs/holdings.php`

**Implementazione:**
- Aggiungere badge accanto al ticker nella tabella holdings
- Logica:
  - Badge verde 📈 "Near 52W High" se `(current_price / fifty_two_week_high) > 0.95`
  - Badge rosso 📉 "Near 52W Low" se `(current_price / fifty_two_week_low) < 1.05`

**Codice di esempio:**
```php
<?php
$near52wHigh = false;
$near52wLow = false;
if ($holding['fifty_two_week_high'] > 0) {
    $near52wHigh = ($holding['current_price'] / $holding['fifty_two_week_high']) > 0.95;
}
if ($holding['fifty_two_week_low'] > 0) {
    $near52wLow = ($holding['current_price'] / $holding['fifty_two_week_low']) < 1.05;
}
?>
<td class="px-4 py-3">
    <div class="flex items-center gap-2">
        <span class="font-semibold text-purple"><?php echo htmlspecialchars($holding['ticker']); ?></span>
        <?php if ($near52wHigh): ?>
            <span class="badge badge-success text-[9px]">📈 Near 52W High</span>
        <?php elseif ($near52wLow): ?>
            <span class="badge badge-danger text-[9px]">📉 Near 52W Low</span>
        <?php endif; ?>
    </div>
</td>
```

**CSS richiesto:**
```css
.badge { padding: 2px 6px; border-radius: 4px; font-weight: 600; }
.badge-success { background: #dcfce7; color: #16a34a; }
.badge-danger { background: #fee2e2; color: #dc2626; }
```

**Benefici:**
- 👁️ Visibilità immediata livelli psicologici
- 🎯 Alert breakout/breakdown
- 📊 Comprensione contesto prezzo senza calcoli manuali

---

#### 1.4 Day Range - Widget Dashboard
**File:** `views/tabs/dashboard.php`

**Implementazione:**
- Nuovo widget "Performance Intraday" nella prima riga dashboard
- Mostrare per ogni holding:
  - `previous_close` → `day_low` ← `current_price` → `day_high`
  - Gap % rispetto chiusura precedente
  - Range % giornata

**Mockup widget:**
```
┌─────────────────────────────────┐
│ 📊 Performance Intraday         │
├─────────────────────────────────┤
│ SWDA.MI                         │
│ ├─ Prev Close: €110.50         │
│ ├─ Range: €109.80 - €111.20    │
│ └─ Gap: +0.17% ▲                │
│                                 │
│ SGLD.MI                         │
│ ├─ Prev Close: €345.20         │
│ ├─ Range: €342.50 - €349.80    │
│ └─ Gap: +2.03% ▲                │
└─────────────────────────────────┘
```

**Codice di esempio:**
```php
<div class="widget-card widget-purple p-4">
    <div class="text-[11px] font-semibold text-gray-600 uppercase mb-3">📊 Performance Intraday</div>
    <?php foreach ($top_holdings as $holding): ?>
        <?php
        $gap = 0;
        if ($holding['previous_close'] > 0) {
            $gap = (($holding['current_price'] - $holding['previous_close']) / $holding['previous_close']) * 100;
        }
        ?>
        <div class="mb-3 p-2 bg-gray-50 rounded">
            <div class="font-semibold text-primary text-sm"><?php echo $holding['ticker']; ?></div>
            <div class="text-[11px] text-gray-600">
                Prev Close: €<?php echo number_format($holding['previous_close'], 2); ?>
            </div>
            <div class="text-[11px] text-gray-600">
                Range: €<?php echo number_format($holding['day_low'], 2); ?> - €<?php echo number_format($holding['day_high'], 2); ?>
            </div>
            <div class="text-[11px] font-semibold <?php echo $gap >= 0 ? 'text-positive' : 'text-negative'; ?>">
                Gap: <?php echo $gap >= 0 ? '+' : ''; ?><?php echo number_format($gap, 2); ?>% <?php echo $gap >= 0 ? '▲' : '▼'; ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>
```

**Benefici:**
- 📈 Monitoring real-time movimento intraday
- 🎯 Identificazione gap up/down (potenziali breakout)
- ⚡ Decisioni intraday informate

---

### **FASE 2 - Grafici Storici** (Stima: 3-5 giorni, Priorità: 🟡 MEDIA)

**Obiettivo:** Sfruttare `technical_snapshots` per analisi trend indicatori nel tempo

#### 2.1 Pagina "Grafici Tecnici"
**File:** Nuovo file `views/tabs/charts.php`

**Features:**
1. **Selezione Strumento:** Dropdown con ticker holdings
2. **Grafici Storici (30-60 giorni):**
   - RSI 14 con bande 30/70 (ipervenduto/ipercomprato)
   - MACD (istogramma + linea segnale)
   - Volatilità 30d (trend)
   - Bollinger %B (posizione nel tempo)
   - Prezzo + EMA9/21/50/200 overlay

**API Endpoint Richiesto:**
```php
// File: api/technical-history.php
GET /api/technical-history.php?isin=IE00B4L5Y983&days=30

Response:
{
  "success": true,
  "data": [
    {
      "snapshot_date": "2025-11-30",
      "price": 111.43,
      "rsi14": 53.11,
      "macd_value": 1.1461,
      "macd_signal": 1.1223,
      "hist_vol_30d": 14.07,
      "bb_percent_b": 0.7322
    },
    // ... ultimi 30 giorni
  ]
}
```

**Implementazione Endpoint:**
```php
<?php
require_once __DIR__ . '/../lib/Database/DatabaseManager.php';

header('Content-Type: application/json');

$isin = $_GET['isin'] ?? null;
$days = min((int)($_GET['days'] ?? 30), 90); // Max 90 giorni

if (!$isin) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Missing ISIN']);
    exit;
}

$db = DatabaseManager::getInstance();
$sql = "
    SELECT
        snapshot_date,
        price,
        rsi14,
        macd_value,
        macd_signal,
        hist_vol_30d,
        atr14_pct,
        range_1y_percentile,
        bb_percent_b
    FROM technical_snapshots
    WHERE isin = ? AND portfolio_id = 1
    ORDER BY snapshot_date DESC
    LIMIT ?
";

$stmt = $db->prepare($sql);
$stmt->execute([$isin, $days]);
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode([
    'success' => true,
    'data' => array_reverse($data) // Ordine cronologico per grafici
]);
```

**Grafici Chart.js:**
```javascript
// File: assets/js/technical-charts.js
async function loadTechnicalHistory(isin, days = 30) {
    const response = await fetch(`/api/technical-history.php?isin=${isin}&days=${days}`);
    const json = await response.json();

    if (!json.success) return;

    const data = json.data;
    const labels = data.map(d => d.snapshot_date);

    // Grafico RSI con bande
    new Chart(document.getElementById('rsiChart'), {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'RSI 14',
                    data: data.map(d => d.rsi14),
                    borderColor: '#7c3aed',
                    borderWidth: 2,
                    fill: false
                },
                {
                    label: 'Ipercomprato (70)',
                    data: Array(labels.length).fill(70),
                    borderColor: '#dc2626',
                    borderDash: [5, 5],
                    borderWidth: 1,
                    fill: false
                },
                {
                    label: 'Ipervenduto (30)',
                    data: Array(labels.length).fill(30),
                    borderColor: '#16a34a',
                    borderDash: [5, 5],
                    borderWidth: 1,
                    fill: false
                }
            ]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    min: 0,
                    max: 100
                }
            }
        }
    });

    // Altri grafici...
}
```

**Benefici:**
- 📈 Identificazione divergenze RSI/Prezzo (segnali reversal)
- 🎯 Trend indicatori tecnici (momentum in forza/debolezza)
- 📊 Backtesting visuale strategie
- 🔍 Analisi multi-timeframe

---

#### 2.2 Grafico Evoluzione Allocazione
**File:** `views/tabs/performance.php`

**Features:**
- Stacked area chart con % allocazione per ticker nel tempo
- Dati da `snapshot_holdings` + `snapshots`

**Query Richiesta:**
```sql
SELECT
    s.snapshot_date,
    sh.ticker,
    sh.market_value,
    s.total_market_value,
    (sh.market_value / s.total_market_value * 100) AS allocation_pct
FROM snapshot_holdings sh
JOIN snapshots s ON sh.snapshot_id = s.id
WHERE s.portfolio_id = 1
ORDER BY s.snapshot_date ASC, sh.ticker ASC
```

**Implementazione Chart:**
```javascript
// Stacked Area Chart - Allocazione nel tempo
const allocationEvolutionChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: dates, // ['2025-11-28', '2025-11-30', ...]
        datasets: [
            {
                label: 'SWDA.MI',
                data: [65, 67, 70], // % allocazione per data
                backgroundColor: 'rgba(124, 58, 237, 0.5)',
                borderColor: '#7c3aed',
                fill: true
            },
            {
                label: 'SGLD.MI',
                data: [20, 18, 15],
                backgroundColor: 'rgba(251, 191, 36, 0.5)',
                borderColor: '#fbbf24',
                fill: true
            },
            // Altri ticker...
        ]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                stacked: true,
                min: 0,
                max: 100,
                ticks: {
                    callback: value => value + '%'
                }
            },
            x: {
                stacked: true
            }
        }
    }
});
```

**Benefici:**
- 🔄 Visualizzare ribilanciamenti passati
- 📊 Tracking esposizione per asset nel tempo
- 🎯 Identificare trend di concentrazione/diversificazione

---

### **FASE 3 - Enhancement UX** (Stima: 2-3 giorni, Priorità: 🟢 BASSA)

**Obiettivo:** Nice-to-have features per migliorare usabilità

#### 3.1 Alert Volume Anomalo
**File:** `views/tabs/holdings.php`

**Implementazione:**
- Badge "⚠️ Volume Anomalo" quando `vol_ratio_current_20d > 2` o `< 0.5`
- Tooltip: "Volume 2.5x sopra media 20d - possibile notizia importante"

**Codice:**
```php
<?php
$volRatio = $holding['vol_ratio_current_20d'] ?? 1;
$volAnomaly = $volRatio > 2 || $volRatio < 0.5;
?>
<?php if ($volAnomaly): ?>
    <span class="badge badge-warning" title="Volume <?php echo number_format($volRatio, 1); ?>x media 20d">
        ⚠️ Volume Anomalo
    </span>
<?php endif; ?>
```

---

#### 3.2 Toggle EMA vs SMA
**File:** Nuovo `views/tabs/charts.php` (se implementata Fase 2)

**Implementazione:**
- Toggle switch "EMA / SMA" sopra i grafici
- Al click, ridisegna grafico con SMA invece di EMA

**Benefici:**
- Preferenza personale trader (alcuni preferiscono SMA per smoothing maggiore)
- Confronto strategia trend-following

---

#### 3.3 Modal Dettaglio Strumento
**File:** `views/tabs/holdings.php`

**Implementazione:**
- Pulsante "ℹ️ Info" su ogni riga tabella holdings
- Click apre modal con tutti i metadati:
  - Exchange
  - First Trade Date
  - Price Source
  - Asset Class
  - Sector
  - ISIN
  - Volatilità storica 30d/90d
  - Range 1M/3M/6M/1Y

**Benefici:**
- 🔍 Informazioni contestuali complete
- 📚 Educazione utente su caratteristiche strumento

---

## 📊 Priorità e Timing

### Effort vs Impact Matrix

```
High Impact ▲
           │
    1.1    │    1.3
    (Fib)  │  (52W)
           │
    1.2    │    2.1
    (BB)   │ (Charts)
           │
-----------┼-----------► High Effort
           │
    1.4    │    3.1
  (Range)  │  (Volume)
           │
    3.2    │    3.3
   (SMA)   │  (Modal)
           │
Low Impact ▼
```

### Timeline Consigliata

| Fase | Durata | Dipendenze | Deliverable |
|------|--------|------------|-------------|
| **Fase 1** | 1-2 giorni | Nessuna | 4 feature visibili immediatamente |
| **Fase 2** | 3-5 giorni | Fase 1 completata | Nuova pagina grafici + API |
| **Fase 3** | 2-3 giorni | Fase 2 (parziale) | Enhancement UX opzionali |

**Totale Stima:** 6-10 giorni lavorativi per implementazione completa

---

## 🎯 Metriche di Successo

### KPI Post-Implementazione Fase 1
- ✅ **Fibonacci levels** visibili in <2 click dalla vista Analisi Tecnica
- ✅ **BB Width** color-coded aiuta a identificare 80% asset in compressione
- ✅ **52W badges** visibili su 100% holdings nella tabella
- ✅ **Day range** aggiornato real-time su dashboard

### KPI Post-Implementazione Fase 2
- ✅ **Grafici storici** accessibili per ogni holding
- ✅ **30 giorni di dati** visualizzati per RSI, MACD, Volatilità
- ✅ **Evoluzione allocazione** mostra ribilanciamenti passati

### Feedback Utente
- 📈 **Riduzione tempo decisionale** (meno calcoli manuali)
- 🎯 **Aumento fiducia entry/exit** (dati visivi supportano scelte)
- ⚡ **Velocità identificazione opportunità** (alert automatici)

---

## 🔄 Manutenzione e Aggiornamenti

### Popolamento `technical_snapshots`
**Responsabile:** Workflow n8n Enrichment
**Frequenza:** Giornaliera @ 22:00
**Script:** `workflow/AI Technical Insights.json`

**Verifiche Periodiche:**
- [ ] Controllare che `technical_snapshots` si popoli giornalmente
- [ ] Validare coerenza dati (no valori NULL anomali)
- [ ] Pulizia snapshot >90 giorni (storage optimization)

### Aggiornamento Documentazione
Quando nuove feature vengono implementate:
1. Aggiornare `PROJECT_STATUS.md` (sezione COMPLETATO)
2. Aggiornare `README.md` (Features)
3. Aggiornare `STYLE_GUIDE.md` (se nuovi pattern UI)
4. Questo documento (checklist implementazione)

---

## ✅ Checklist Implementazione

### Fase 1 - Quick Wins
- [ ] **1.1 Fibonacci Levels**
  - [ ] Aggiungere colonna in `views/tabs/technical.php`
  - [ ] Implementare logica toggle/dropdown
  - [ ] CSS per evidenziare livello più vicino
  - [ ] Test con almeno 3 holdings

- [ ] **1.2 Bollinger Width**
  - [ ] Aggiungere colonna "BB Width" in tabella
  - [ ] Implementare color-coding (verde/giallo/rosso)
  - [ ] Tooltip esplicativo
  - [ ] Test con bb_width_pct < 5% e > 10%

- [ ] **1.3 Badge 52W High/Low**
  - [ ] Modificare rendering ticker in `views/tabs/holdings.php`
  - [ ] Logica calcolo distanza da 52W high/low
  - [ ] CSS badge verde/rosso
  - [ ] Test con holding vicino a massimi/minimi

- [ ] **1.4 Day Range Widget**
  - [ ] Creare widget in `views/tabs/dashboard.php`
  - [ ] Layout intraday range + gap %
  - [ ] Color-coding gap positivo/negativo
  - [ ] Responsive design mobile

### Fase 2 - Grafici Storici
- [ ] **2.1 Pagina Grafici Tecnici**
  - [ ] Creare `views/tabs/charts.php`
  - [ ] Implementare endpoint `api/technical-history.php`
  - [ ] Dropdown selezione strumento
  - [ ] Grafico RSI con bande 30/70
  - [ ] Grafico MACD (istogramma + segnale)
  - [ ] Grafico volatilità 30d
  - [ ] Grafico Bollinger %B
  - [ ] Grafico prezzo + EMA overlay
  - [ ] Aggiungere link nel menu sidebar

- [ ] **2.2 Grafico Evoluzione Allocazione**
  - [ ] Query `snapshot_holdings` + `snapshots`
  - [ ] Stacked area chart con Chart.js
  - [ ] Integrazione in `views/tabs/performance.php`
  - [ ] Legend interattiva (mostra/nascondi ticker)

### Fase 3 - Enhancement UX
- [ ] **3.1 Alert Volume Anomalo**
  - [ ] Badge "Volume Anomalo" in `views/tabs/holdings.php`
  - [ ] Tooltip esplicativo ratio
  - [ ] CSS badge warning

- [ ] **3.2 Toggle EMA/SMA**
  - [ ] Switch UI in `views/tabs/charts.php`
  - [ ] Logica ridisegno grafico
  - [ ] Persistenza preferenza (localStorage)

- [ ] **3.3 Modal Dettaglio Strumento**
  - [ ] Pulsante "ℹ️ Info" su ogni riga holdings
  - [ ] Modal con tutti metadati
  - [ ] Layout responsive
  - [ ] Close button + click outside

---

## 📞 Supporto e Riferimenti

**Documenti Collegati:**
- [PROJECT_STATUS.md](../PROJECT_STATUS.md) - Stato avanzamento progetto
- [03-DATABASE.md](03-DATABASE.md) - Schema database completo
- [05-FRONTEND.md](05-FRONTEND.md) - Architettura frontend
- [AI_Technical_Analysis_Architecture.md](../AI_Technical_Analysis_Architecture.md) - AI insights

**Contatti:**
- Sviluppatore: [Vedi PROJECT_STATUS.md]
- Repository: [GitHub URL]

---

**Versione Documento:** 1.0
**Ultimo Aggiornamento:** 01 Dicembre 2025
**Prossima Revisione:** Post Fase 1 (stimata 15 Dicembre 2025)
