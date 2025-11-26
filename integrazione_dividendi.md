# 📋 Integrazione Dividendi: n8n → Web Dashboard

**Data:** 26 Novembre 2025  
**Versione:** v3.1  
**Stato:** ⚠️ Da implementare

---

## 🎯 Obiettivo

Integrare i dati dividendi provenienti dal workflow n8n (Yahoo Finance v8) nel tool web per popolare automaticamente:

- `portfolio.json` - Aggiungere campi dividendi agli holdings
- `dividends_calendar.json` - Generare calendario dividendi con forecast

---

## 📊 Dati disponibili da n8n

Il workflow **Portfolio Enrichment v3.1** invia questi campi per ogni holding:

### Dati Dividendi

```json
{
  "dividend_yield": 2.91,        // Rendimento dividendi %
  "annual_dividend": 2.0016,     // Dividendo annuale per quota
  "dividend_frequency": "Quarterly", // Frequenza: Quarterly/Semi-Annual/Annual/Monthly/None
  "has_dividends": true,         // Boolean: ha dividendi?
  "total_dividends_5y": 20       // Numero pagamenti ultimi 5 anni
}
```

### Dati Performance

```json
{
  "fifty_two_week_high": 69.5,
  "fifty_two_week_low": 55.76,
  "ytd_change_percent": 6.11,
  "one_month_change_percent": 1.0,
  "three_month_change_percent": 4.16,
  "one_year_change_percent": 4.5
}
```

### Dati già salvati (funzionanti)

```json
{
  "current_price": 68.8,
  "asset_class": "Equity",
  "sector": "Dividend",
  "expense_ratio": 0
}
```

---

## 🔧 Modifiche da implementare

### 1️⃣ File: `api/n8n/enrich.php`

**Posizione:** Linee 45-65 circa  
**Sezione:** "Update fields if provided"

#### Modifica A: Estendi campi salvati

**SOSTITUIRE:**
```php
// Update fields if provided
$updates = [];
if (isset($enrichedHolding["current_price"])) {
    $updates["current_price"] = (float) $enrichedHolding["current_price"];
}
if (isset($enrichedHolding["asset_class"]) && $enrichedHolding["asset_class"] !== "Unknown") {
    $updates["asset_class"] = $enrichedHolding["asset_class"];
}
if (isset($enrichedHolding["sector"]) && $enrichedHolding["sector"] !== "Unknown") {
    $updates["sector"] = $enrichedHolding["sector"];
}
if (isset($enrichedHolding["expense_ratio"])) {
    $updates["expense_ratio"] = (float) $enrichedHolding["expense_ratio"];
}
if (isset($enrichedHolding["dividend_yield"])) {
    $updates["dividend_yield"] = (float) $enrichedHolding["dividend_yield"];
}
```

**CON:**
```php
// Update fields if provided
$updates = [];

// Price
if (isset($enrichedHolding["current_price"])) {
    $updates["current_price"] = (float) $enrichedHolding["current_price"];
}

// Classification
if (isset($enrichedHolding["asset_class"]) && $enrichedHolding["asset_class"] !== "Unknown") {
    $updates["asset_class"] = $enrichedHolding["asset_class"];
}
if (isset($enrichedHolding["sector"]) && $enrichedHolding["sector"] !== "Unknown") {
    $updates["sector"] = $enrichedHolding["sector"];
}

// Financial metrics
if (isset($enrichedHolding["expense_ratio"])) {
    $updates["expense_ratio"] = (float) $enrichedHolding["expense_ratio"];
}
if (isset($enrichedHolding["dividend_yield"])) {
    $updates["dividend_yield"] = (float) $enrichedHolding["dividend_yield"];
}

// 🆕 DIVIDEND DATA
if (isset($enrichedHolding["annual_dividend"])) {
    $updates["annual_dividend"] = (float) $enrichedHolding["annual_dividend"];
}
if (isset($enrichedHolding["dividend_frequency"])) {
    $updates["dividend_frequency"] = $enrichedHolding["dividend_frequency"];
}
if (isset($enrichedHolding["has_dividends"])) {
    $updates["has_dividends"] = (bool) $enrichedHolding["has_dividends"];
}
if (isset($enrichedHolding["total_dividends_5y"])) {
    $updates["total_dividends_5y"] = (int) $enrichedHolding["total_dividends_5y"];
}

// 🆕 PRICE RANGES & PERFORMANCE
if (isset($enrichedHolding["fifty_two_week_high"])) {
    $updates["fifty_two_week_high"] = (float) $enrichedHolding["fifty_two_week_high"];
}
if (isset($enrichedHolding["fifty_two_week_low"])) {
    $updates["fifty_two_week_low"] = (float) $enrichedHolding["fifty_two_week_low"];
}
if (isset($enrichedHolding["ytd_change_percent"])) {
    $updates["ytd_change_percent"] = (float) $enrichedHolding["ytd_change_percent"];
}
if (isset($enrichedHolding["one_year_change_percent"])) {
    $updates["one_year_change_percent"] = (float) $enrichedHolding["one_year_change_percent"];
}
```

---

#### Modifica B: Genera dividends_calendar.json

**Posizione:** Dopo l'update di `monthly_performance` (linea ~180 circa)
**AGGIUNGERE PRIMA del response finale:**

```php
// 🆕 Generate dividends calendar
try {
    $dividendsCalendar = $portfolioManager->generateDividendsCalendar();
    $dividendsFile = DIR . "/../../data/dividends_calendar.json";

    file_put_contents(
        $dividendsFile,
        json_encode($dividendsCalendar, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
    );

    error_log("[n8n/enrich] Updated dividends calendar with " .
        count($dividendsCalendar["distributing_assets"]) . " distributing assets");
} catch (Exception $e) {
    error_log("[n8n/enrich] Warning: Could not generate dividends calendar: " . $e->getMessage());
}
```

---

### 2️⃣ File: `classes/PortfolioManager.php`

**Posizione:** Fine della classe, prima della chiusura `}`

#### Aggiungi 3 nuovi metodi

**AGGIUNGERE:**

```php
/**
 * Generate dividends calendar from holdings data
 *
 * Calculates next payment dates based on dividend frequency
 *
 * @return array Dividends calendar structure
 */
public function generateDividendsCalendar(): array
{
    $data = $this->getData();
    $holdings = $data["holdings"] ?? [];

    $distributingAssets = [];
    $monthlyForecast = array_fill(1, 12, 0); // Jan to Dec
    $portfolioYield = 0;
    $nextDividend = null;
    $totalInvested = $data["metadata"]["total_invested"] ?? 0;

    $today = new DateTime();
    $currentMonth = (int) $today->format('n');

    foreach ($holdings as $holding) {
        $hasDividends = $holding["has_dividends"] ?? false;
        $dividendYield = $holding["dividend_yield"] ?? 0;
        $annualDividend = $holding["annual_dividend"] ?? 0;
        $frequency = $holding["dividend_frequency"] ?? "None";
        $quantity = $holding["quantity"] ?? 0;
        $marketValue = $holding["market_value"] ?? 0;

        if (!$hasDividends || $dividendYield == 0 || $frequency === "None") {
            continue;
        }

        // Add to distributing assets
        $distributingAssets[] = [
            "ticker" => $holding["ticker"],
            "name" => $holding["name"],
            "dividend_yield" => round($dividendYield, 2),
            "annual_amount" => round($annualDividend * $quantity, 2),
            "frequency" => $frequency,
            "last_div_date" => null,
            "next_div_date" => null
        ];

        // Calculate portfolio-weighted yield
        if ($totalInvested > 0) {
            $portfolioYield += ($marketValue / $totalInvested) * $dividendYield;
        }

        // Estimate monthly distribution based on frequency
        $paymentsPerYear = $this->getPaymentsPerYear($frequency);
        $dividendPerPayment = ($annualDividend * $quantity) / $paymentsPerYear;

        // Distribute across months (simple estimation)
        $months = $this->getPaymentMonths($frequency);

        foreach ($months as $month) {
            $monthlyForecast[$month] += $dividendPerPayment;
        }

        // Find next dividend date (estimate)
        $nextMonth = null;
        foreach ($months as $month) {
            if ($month >= $currentMonth) {
                $nextMonth = $month;
                break;
            }
        }

        if ($nextMonth === null && count($months) > 0) {
            $nextMonth = $months[0]; // Next year
        }

        if ($nextMonth !== null) {
            $estimatedDate = new DateTime();
            $estimatedDate->setDate((int) $today->format('Y'), $nextMonth, 15);

            if ($nextMonth < $currentMonth) {
                $estimatedDate->modify('+1 year');
            }

            if ($nextDividend === null || $estimatedDate < new DateTime($nextDividend["date"])) {
                $nextDividend = [
                    "date" => $estimatedDate->format('Y-m-d'),
                    "ticker" => $holding["ticker"],
                    "amount" => round($dividendPerPayment, 2)
                ];
            }
        }
    }

    // Format monthly forecast for next 6 months
    $forecast6m = [];
    for ($i = 0; $i < 6; $i++) {
        $month = ($currentMonth + $i - 1) % 12 + 1;
        $forecast6m[] = [
            "month" => date('M', mktime(0, 0, 0, $month, 1)),
            "amount" => round($monthlyForecast[$month], 2)
        ];
    }

    $totalForecast6m = array_sum(array_column($forecast6m, 'amount'));

    return [
        "last_update" => date('c'),
        "forecast_6m" => [
            "total_amount" => round($totalForecast6m, 2),
            "period" => date('M') . " - " . date('M', strtotime('+5 months'))
        ],
        "portfolio_yield" => round($portfolioYield, 2),
        "next_dividend" => $nextDividend ?? [
            "date" => "-",
            "ticker" => "-",
            "amount" => 0
        ],
        "monthly_forecast" => $forecast6m,
        "distributing_assets" => $distributingAssets,
        "ai_insight" => $this->generateDividendInsight($distributingAssets, $portfolioYield)
    ];
}

/**
 * Get number of payments per year based on frequency
 *
 * @param string $frequency Dividend frequency
 *
 * @return int Number of payments per year
 */
private function getPaymentsPerYear(string $frequency): int
{
    return match($frequency) {
        'Quarterly' => 4,
        'Semi-Annual' => 2,
        'Monthly' => 12,
        'Annual' => 1,
        default => 4
    };
}

/**
 * Get payment months based on frequency
 *
 * @param string $frequency Dividend frequency
 *
 * @return array Array of month numbers (1-12)
 */
private function getPaymentMonths(string $frequency): array
{
    return match($frequency) {
        'Quarterly' => [3, 6, 9, 12], // Q1=Mar, Q2=Jun, Q3=Sep, Q4=Dec
        'Semi-Annual' => [6, 12],      // Jun, Dec
        'Monthly' => range(1, 12),     // Every month
        'Annual' => [12],              // December
        default => [3, 6, 9, 12]
    };
}

/**
 * Generate AI-style insight about dividends
 *
 * @param array $distributingAssets Array of assets with dividends
 *
 * @param float $portfolioYield Portfolio-weighted yield
 *
 * @return string Insight text
 */
private function generateDividendInsight(array $distributingAssets, float $portfolioYield): string
{
    $count = count($distributingAssets);

    if ($count === 0) {
        return "No distributing assets in portfolio.";
    }

    $avgYield = $portfolioYield;

    if ($avgYield > 4) {
        return "$count assets with strong dividend yield ({$avgYield}%). Excellent passive income potential.";
    } elseif ($avgYield > 2.5) {
        return "$count dividend-paying assets with moderate yield ({$avgYield}%). Balanced income strategy.";
    } else {
        return "$count dividend assets with conservative yield ({$avgYield}%). Focus on capital appreciation.";
    }
}
```

---

## 📝 Checklist implementazione

### Step 1: Backup

```bash
cd /path/to/portfolio
cp api/n8n/enrich.php api/n8n/enrich.php.backup
cp classes/PortfolioManager.php classes/PortfolioManager.php.backup
```

### Step 2: Modifica enrich.php

- [ ] Aprire `api/n8n/enrich.php`
- [ ] Trovare sezione "Update fields if provided" (linea ~45)
- [ ] Sostituire con nuovo codice (Modifica A)
- [ ] Trovare sezione dopo monthly_performance (linea ~180)
- [ ] Aggiungere generazione dividends_calendar (Modifica B)
- [ ] Salvare file

### Step 3: Modifica PortfolioManager.php

- [ ] Aprire `classes/PortfolioManager.php`
- [ ] Andare alla fine della classe (prima di chiusura `}`)
- [ ] Aggiungere i 4 nuovi metodi
- [ ] Salvare file

### Step 4: Test workflow

Eseguire workflow n8n manualmente
Controllare log PHP:

```bash
tail -f /var/log/php_errors.log
```

### Step 5: Verifica output

- [ ] Controllare `data/portfolio.json` - Ha campi dividend_yield, annual_dividend, etc?
- [ ] Controllare `data/dividends_calendar.json` - È popolato?
- [ ] Controllare log: `[n8n/enrich] Updated dividends calendar with X distributing assets`

---

## 🧪 Testing

### Test 1: Verifica portfolio.json

**Comando:**
```bash
cat data/portfolio.json | jq '.holdings | {
  ticker,
  dividend_yield,
  annual_dividend,
  dividend_frequency,
  has_dividends
}'
```

**Output atteso:**
```json
{
  "ticker": "VHYL.MI",
  "dividend_yield": 2.91,
  "annual_dividend": 2.0016,
  "dividend_frequency": "Quarterly",
  "has_dividends": true
}
```

---

### Test 2: Verifica dividends_calendar.json

**Comando:**
```bash
cat data/dividends_calendar.json | jq '{
  portfolio_yield,
  next_dividend,
  distributing_count: .distributing_assets | length
}'
```

**Output atteso:**
```json
{
  "portfolio_yield": 3.11,
  "next_dividend": {
    "date": "2025-12-15",
    "ticker": "VHYL.MI",
    "amount": 12.25
  },
  "distributing_count": 2
}
```

---

### Test 3: Verifica log PHP

**Cercare queste righe:**
```log
[n8n/enrich] Updated holding IE00B8GKDB10: {"dividend_yield":2.91,"annual_dividend":2.0016}
[n8n/enrich] Updated dividends calendar with 2 distributing assets
[n8n/enrich] Successfully enriched 4 holdings
```

---

## 🎯 Output finale atteso

### portfolio.json (singolo holding)

```json
{
  "name": "Vanguard FTSE All-World High Div. Yield UCITS ETF Dis",
  "isin": "IE00B8GKDB10",
  "ticker": "VHYL.MI",
  "quantity": 21,
  "current_price": 68.8,
  "market_value": 1444.8,

  "dividend_yield": 2.91,
  "annual_dividend": 2.0016,
  "dividend_frequency": "Quarterly",
  "has_dividends": true,
  "total_dividends_5y": 20,

  "fifty_two_week_high": 69.5,
  "fifty_two_week_low": 55.76,
  "ytd_change_percent": 6.11,
  "one_year_change_percent": 4.5
}
```

### dividends_calendar.json (completo)

```json
{
  "last_update": "2025-11-26T22:30:00+00:00",
  "forecast_6m": {
    "total_amount": 142.45,
    "period": "Nov - Apr"
  },
  "portfolio_yield": 3.11,
  "next_dividend": {
    "date": "2025-12-15",
    "ticker": "VHYL.MI",
    "amount": 12.25
  },
  "monthly_forecast": [
    { "month": "Nov", "amount": 0 },
    { "month": "Dec", "amount": 47.48 },
    { "month": "Jan", "amount": 0 },
    { "month": "Feb", "amount": 0 },
    { "month": "Mar", "amount": 47.48 },
    { "month": "Apr", "amount": 47.49 }
  ],
  "distributing_assets": [
    {
      "ticker": "VHYL.MI",
      "name": "Vanguard FTSE All-World High Div. Yield UCITS ETF Dis",
      "dividend_yield": 2.91,
      "annual_amount": 42.03,
      "frequency": "Quarterly",
      "last_div_date": null,
      "next_div_date": null
    },
    {
      "ticker": "TDIV.MI",
      "name": "VanEck Morningstar Developed Markets Dividend Leaders UCITS ETF",
      "dividend_yield": 3.83,
      "annual_amount": 179.00,
      "frequency": "Quarterly",
      "last_div_date": null,
      "next_div_date": null
    }
  ],
  "ai_insight": "2 dividend-paying assets with moderate yield (3.11%). Balanced income strategy."
}
```

---

## 🐛 Troubleshooting

### Problema: dividends_calendar.json rimane vuoto

**Cause possibili:**

1. `generateDividendsCalendar()` non chiamato → Verifica Modifica B in enrich.php
2. Errore PHP → Controllare log: `tail -f /var/log/php_errors.log`
3. Permessi file → `chmod 664 data/dividends_calendar.json`

**Soluzione:**

Controlla permessi:
```bash
ls -la data/dividends_calendar.json
```

Se necessario:
```bash
chown www-data:www-data data/dividends_calendar.json
chmod 664 data/dividends_calendar.json
```

---

### Problema: portfolio.json non ha campi dividendi

**Cause possibili:**

1. Modifica A non applicata correttamente in enrich.php
2. Workflow n8n non invia i dati → Controllare output nodo "Aggregate & Sign"
3. ISIN mismatch → Controllare log `[n8n/enrich] Warning: ISIN XXX not found`

**Debug:**

Verifica che n8n invii i dati:
```bash
grep "dividend_yield" /var/log/php_errors.log | tail -5
```

Verifica aggiornamento holdings:
```bash
grep "Updated holding" /var/log/php_errors.log | tail -10
```

---

### Problema: monthly_forecast tutti a 0

**Causa:** Holdings non hanno `has_dividends: true`

**Soluzione:**

Verifica holdings:
```bash
cat data/portfolio.json | jq '.holdings[] | {ticker, has_dividends, dividend_frequency}'
```

Se mancano, riesegui workflow n8n.

---

## 📚 Riferimenti

- **Workflow n8n:** Portfolio Enrichment v3.1
- **File modificati:**
  - `api/n8n/enrich.php` (2 modifiche)
  - `classes/PortfolioManager.php` (4 metodi nuovi)
- **File output:**
  - `data/portfolio.json` (aggiornato con campi dividendi)
  - `data/dividends_calendar.json` (generato da zero)

---

## ✅ Completamento

Quando implementato correttamente, il sistema:

- ✅ Salva tutti i dati dividendi in portfolio.json
- ✅ Genera automaticamente dividends_calendar.json
- ✅ Calcola portfolio yield weighted
- ✅ Stima prossimo pagamento dividendo
- ✅ Fornisce forecast 6 mesi
- ✅ Logga tutte le operazioni

**Tempo stimato implementazione:** 30-45 minuti  
**Difficoltà:** Media

---

**Fine documento** 📄
