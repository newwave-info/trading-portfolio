# Testing Dividends Integration

**Data**: 27 Novembre 2025
**Versione**: v3.1 Implementation
**Status**: ✅ Codice implementato, pronto per test

---

## 📝 Modifiche Implementate

### 1. `api/n8n/enrich.php` (linee 107-181)

✅ **Aggiunti campi dividendi**:
- `annual_dividend` (float)
- `dividend_frequency` (string: Quarterly/Semi-Annual/Annual/Monthly/None)
- `has_dividends` (boolean)
- `total_dividends_5y` (int)

✅ **Aggiunti campi performance**:
- `fifty_two_week_high` (float)
- `fifty_two_week_low` (float)
- `ytd_change_percent` (float)
- `one_year_change_percent` (float)

✅ **Generazione calendario dividendi** (linee 269-292):
- Chiamata a `PortfolioManager->generateDividendsCalendar()`
- Salvataggio in `data/dividends_calendar.json`
- Logging del numero di asset distribuenti

### 2. `lib/PortfolioManager.php` (linee 387-571)

✅ **Nuovi metodi**:
- `generateDividendsCalendar()` - Genera calendario completo
- `getPaymentsPerYear()` - Helper frequenza pagamenti
- `getPaymentMonths()` - Helper mesi pagamento
- `generateDividendInsight()` - Generazione insight AI-style

---

## 🧪 Come Testare

### Pre-requisiti
- Workflow n8n v3.1 già configurato e funzionante
- File `data/portfolio.json` con holdings esistenti
- File `data/dividends_calendar.json` presente (anche vuoto)

### Test 1: Esecuzione manuale workflow n8n

1. **Accedi a n8n** (http://localhost:5678 o tuo dominio)
2. **Apri workflow** "Portfolio Enrichment v3.1"
3. **Click "Execute Workflow"** (pulsante in alto a destra)
4. **Attendi completamento** (circa 10-30 secondi)

**Output atteso nei log n8n**:
```
✓ Yahoo Finance API call successful
✓ Dividend data extracted
✓ Holdings enriched with dividend fields
✓ Webhook POST to enrich.php
```

### Test 2: Verifica portfolio.json

**Comando**:
```bash
cd /path/to/trading-portfolio
cat data/portfolio.json | jq '.holdings[0] | {
  ticker,
  dividend_yield,
  annual_dividend,
  dividend_frequency,
  has_dividends,
  fifty_two_week_high
}'
```

**Output atteso** (esempio per VHYL.MI):
```json
{
  "ticker": "VHYL.MI",
  "dividend_yield": 2.91,
  "annual_dividend": 2.0016,
  "dividend_frequency": "Quarterly",
  "has_dividends": true,
  "fifty_two_week_high": 69.5
}
```

**❌ Se i valori sono ancora 0/null**: Il workflow non ha salvato i dati. Verifica log PHP.

### Test 3: Verifica dividends_calendar.json

**Comando**:
```bash
cat data/dividends_calendar.json | jq '{
  portfolio_yield,
  next_dividend,
  distributing_count: .distributing_assets | length,
  forecast_total: .forecast_6m.total_amount
}'
```

**Output atteso**:
```json
{
  "portfolio_yield": 3.11,
  "next_dividend": {
    "date": "2025-12-15",
    "ticker": "VHYL.MI",
    "amount": 10.51
  },
  "distributing_count": 2,
  "forecast_total": 63.04
}
```

**❌ Se distributing_count = 0**: Nessun holding ha `has_dividends: true`. Verifica che n8n abbia inviato i dati corretti.

### Test 4: Verifica log PHP

**Su server remoto**:
```bash
# Log Apache
sudo tail -f /var/log/apache2/error.log | grep "n8n/enrich"

# Log Nginx + PHP-FPM
sudo tail -f /var/log/nginx/error.log
sudo tail -f /var/log/php8.1-fpm.log
```

**Su macOS locale (MAMP/XAMPP)**:
```bash
tail -f /Applications/MAMP/logs/php_error.log
```

**Righe attese**:
```log
[n8n/enrich] Received enrichment from workflow: portfolio_enrichment_v3.1 at 2025-11-27T10:30:00Z
[n8n/enrich] Updated holding IE00B8GKDB10: {"dividend_yield":2.91,"annual_dividend":2.0016,"dividend_frequency":"Quarterly","has_dividends":true}
[n8n/enrich] Updated dividends calendar with 2 distributing assets
[n8n/enrich] Successfully enriched 4 holdings
```

---

## 🐛 Troubleshooting

### Problema 1: dividends_calendar.json rimane vuoto

**Causa**: Metodo `generateDividendsCalendar()` non chiamato o errore PHP.

**Debug**:
1. Controlla log PHP per errori di sintassi:
   ```bash
   tail -50 /var/log/apache2/error.log | grep -i "parse error\|fatal"
   ```

2. Se errore "Call to undefined method", verifica che i nuovi metodi siano in `PortfolioManager.php`:
   ```bash
   grep "generateDividendsCalendar" lib/PortfolioManager.php
   ```

3. Test manuale del metodo:
   ```bash
   php -r "
   require 'lib/PortfolioManager.php';
   \$pm = new PortfolioManager();
   \$calendar = \$pm->generateDividendsCalendar();
   echo json_encode(\$calendar, JSON_PRETTY_PRINT);
   "
   ```

### Problema 2: portfolio.json non ha campi dividendi

**Causa**: Workflow n8n non invia i dati o enrich.php non li salva.

**Debug**:
1. Verifica payload n8n nel workflow:
   - Apri workflow in n8n
   - Click su nodo finale "Aggregate & Sign"
   - Verifica output JSON contenga `annual_dividend`, `has_dividends`, ecc.

2. Verifica enrich.php riceve i dati:
   - Aggiungi log temporaneo in `enrich.php` linea 89:
   ```php
   error_log("[DEBUG] Received holding: " . json_encode($enrichedHolding));
   ```
   - Esegui workflow e controlla log

3. Verifica campi siano salvati:
   ```bash
   grep "annual_dividend\|has_dividends" data/portfolio.json
   ```

### Problema 3: Errore PHP "syntax error, unexpected 'match'"

**Causa**: PHP version < 8.0 (match expression introdotto in PHP 8.0).

**Fix**: Sostituisci `match()` con `switch`:

In `lib/PortfolioManager.php`, metodo `getPaymentsPerYear()`:
```php
private function getPaymentsPerYear(string $frequency): int
{
    switch($frequency) {
        case 'Quarterly': return 4;
        case 'Semi-Annual': return 2;
        case 'Monthly': return 12;
        case 'Annual': return 1;
        default: return 4;
    }
}
```

E metodo `getPaymentMonths()`:
```php
private function getPaymentMonths(string $frequency): array
{
    switch($frequency) {
        case 'Quarterly': return [3, 6, 9, 12];
        case 'Semi-Annual': return [6, 12];
        case 'Monthly': return range(1, 12);
        case 'Annual': return [12];
        default: return [3, 6, 9, 12];
    }
}
```

### Problema 4: monthly_forecast tutti a 0

**Causa**: Holdings non hanno `has_dividends: true`.

**Fix**:
1. Verifica che workflow n8n imposti correttamente `has_dividends`:
   ```javascript
   // Nel nodo n8n "Code - Enrich Dividends"
   const hasDividends = item.dividend_yield && item.dividend_yield > 0;
   item.has_dividends = hasDividends;
   ```

2. Se necessario, imposta manualmente in `portfolio.json` per test:
   ```json
   {
     "ticker": "VHYL.MI",
     "dividend_yield": 2.91,
     "annual_dividend": 2.0016,
     "dividend_frequency": "Quarterly",
     "has_dividends": true
   }
   ```

3. Rigenera calendario:
   ```bash
   curl -X POST http://your-app.test/api/n8n/enrich.php \
     -H "X-Webhook-Signature: HMAC_DISABLED" \
     -H "Content-Type: application/json" \
     -d '{"workflow_id":"manual_test","holdings":[]}'
   ```

---

## ✅ Checklist Completamento

### Fase 1: Implementazione
- [x] Modificato `api/n8n/enrich.php` per salvare campi dividendi
- [x] Aggiunto salvataggio campi performance (52-week, YTD%)
- [x] Aggiunta chiamata `generateDividendsCalendar()` in enrich.php
- [x] Implementato metodo `generateDividendsCalendar()` in PortfolioManager
- [x] Implementati helper methods (getPaymentsPerYear, getPaymentMonths)
- [x] Implementato `generateDividendInsight()` per AI insights

### Fase 2: Testing (da fare)
- [ ] Eseguito workflow n8n manualmente
- [ ] Verificato `portfolio.json` contiene campi dividendi
- [ ] Verificato `dividends_calendar.json` popolato
- [ ] Controllato log PHP per errori
- [ ] Testato con diversi ETF (distribuenti e ad accumulo)
- [ ] Verificato calcolo portfolio yield corretto

### Fase 3: Validazione
- [ ] Confrontato dividendi con dati Yahoo Finance/JustETF
- [ ] Verificato frequenze pagamento corrette
- [ ] Controllato forecast 6 mesi sensato
- [ ] Testato prossimo pagamento stimato accurato

---

## 📊 Esempio Output Completo

### portfolio.json (singolo holding con dividendi)
```json
{
  "name": "Vanguard FTSE All-World High Div. Yield UCITS ETF Dis",
  "isin": "IE00B8GKDB10",
  "ticker": "VHYL.MI",
  "quantity": 21,
  "avg_price": 67.94,
  "current_price": 68.8,
  "market_value": 1444.8,
  "unrealized_pnl": 18.06,
  "pnl_percentage": 1.27,

  "dividend_yield": 2.91,
  "annual_dividend": 2.0016,
  "dividend_frequency": "Quarterly",
  "has_dividends": true,
  "total_dividends_5y": 20,

  "fifty_two_week_high": 69.5,
  "fifty_two_week_low": 55.76,
  "ytd_change_percent": 6.11,
  "one_year_change_percent": 4.5,

  "asset_class": "Equity",
  "sector": "Dividend",
  "expense_ratio": 0.22
}
```

### dividends_calendar.json (completo)
```json
{
  "last_update": "2025-11-27T10:45:31+00:00",
  "forecast_6m": {
    "total_amount": 63.04,
    "period": "Nov - Apr"
  },
  "portfolio_yield": 3.11,
  "next_dividend": {
    "date": "2025-12-15",
    "ticker": "VHYL.MI",
    "amount": 10.51
  },
  "monthly_forecast": [
    { "month": "Nov", "amount": 0 },
    { "month": "Dec", "amount": 21.01 },
    { "month": "Jan", "amount": 0 },
    { "month": "Feb", "amount": 0 },
    { "month": "Mar", "amount": 21.01 },
    { "month": "Apr", "amount": 21.02 }
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
      "name": "VanEck Morn. Dev. Mkts Div Lead. UCITS ETF",
      "dividend_yield": 3.83,
      "annual_amount": 191.5,
      "frequency": "Quarterly",
      "last_div_date": null,
      "next_div_date": null
    }
  ],
  "ai_insight": "2 dividend-paying assets with moderate yield (3.11%). Balanced income strategy."
}
```

---

## 🎯 Prossimi Step

Dopo il testing:

1. **Verificare precisione dati**:
   - Confrontare dividendi con fonti ufficiali (JustETF, sito emittente)
   - Validare frequenze pagamento

2. **Ottimizzare insight AI**:
   - Migliorare testo generato in `generateDividendInsight()`
   - Aggiungere suggerimenti personalizzati

3. **Integrare con frontend**:
   - Mostrare calendario dividendi in vista "Dividendi"
   - Aggiungere widget "Prossimo Dividendo" in dashboard
   - Visualizzare forecast con grafico

4. **Estendere funzionalità**:
   - Salvare date effettive stacco/pagamento quando disponibili
   - Integrare con n8n per fetching date precise da siti emittenti
   - Tracking dividendi ricevuti vs attesi

---

**Fine documento** 📄
