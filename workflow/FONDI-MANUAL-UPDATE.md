# Gestione Fondi Comuni - Aggiornamento Manuale Prezzi

## 🎯 Problema

I **fondi comuni** (instrument_type: "Fondo") non hanno ticker quotati su exchange pubblici come Yahoo Finance, quindi le API non possono fetchare prezzi automaticamente.

### Fondi nel tuo portfolio:

| Nome | ISIN | Ticker | Prezzo Attuale | Problema |
|------|------|--------|----------------|----------|
| PICTET ROBOT R EUR | LU1279334483 | `-` | 46.72 | ❌ Prezzo non aggiornato (fallback) |
| PICTET BIOTECH R EUR | LU0255977539 | `-` | 46.72 | ❌ Prezzo non aggiornato (fallback) |
| CORE DIVIDEND E E IN | LU0575777627 | `-` | 7.79343 | ⚠️ Prezzo fallback |

**Perché 46.72 per entrambi?** Il workflow usa il fallback che mantiene il prezzo esistente. Se i due fondi avevano lo stesso prezzo iniziale, rimangono uguali.

---

## ✅ Soluzione: Aggiornamento Manuale

### Opzione 1: Aggiornamento tramite API PHP (Consigliato)

Crea uno script PHP per aggiornare manualmente i prezzi NAV:

```php
<?php
// update-funds.php
require_once 'lib/PortfolioManager.php';

$manager = new PortfolioManager('data/portfolio.json');

// Aggiorna PICTET ROBOT (CONTROLLA IL PREZZO REALE SUL SITO PICTET)
$manager->updateHolding('LU1279334483', [
    'current_price' => 52.30  // ← INSERISCI PREZZO REALE
]);

// Aggiorna PICTET BIOTECH
$manager->updateHolding('LU0255977539', [
    'current_price' => 41.85  // ← INSERISCI PREZZO REALE
]);

// Aggiorna CORE DIVIDEND
$manager->updateHolding('LU0575777627', [
    'current_price' => 7.95  // ← INSERISCI PREZZO REALE
]);

$manager->recalculateMetrics();
$manager->save();

echo "✅ Prezzi fondi aggiornati!\n";
?>
```

**Esegui:**
```bash
php update-funds.php
```

---

### Opzione 2: Aggiornamento Diretto JSON

Modifica direttamente `data/portfolio.json`:

1. Apri il file
2. Cerca l'ISIN del fondo (es. `"LU1279334483"`)
3. Modifica il campo `"current_price"` con il valore aggiornato
4. Salva il file

⚠️ **ATTENZIONE**: Dopo aver modificato il JSON, devi ricalcolare le metriche:

```bash
php -r "require 'lib/PortfolioManager.php'; \$m = new PortfolioManager('data/portfolio.json'); \$m->recalculateMetrics(); \$m->save();"
```

---

### Opzione 3: Trovare Prezzi NAV Reali

**Dove trovare i prezzi aggiornati:**

1. **Sito Pictet Asset Management:**
   - https://www.assetmanagement.pictet/it/italy
   - Cerca per ISIN: LU1279334483 (ROBOT), LU0255977539 (BIOTECH)
   - Copia il NAV giornaliero

2. **Morningstar:**
   - https://www.morningstar.it/
   - Cerca per ISIN
   - Usa il "NAV" come current_price

3. **Google Finance / Bloomberg:**
   - Alcuni fondi potrebbero avere ticker alternativi
   - Prova a cercare per ISIN

---

## 🔄 Workflow Behavior

Il workflow v2.2 gestisce correttamente i fondi senza ticker:

```
Preprocess Symbols
  ↓
Yahoo/JustETF API Call (ticker = "-")
  ↓ (FAIL)
Fallback - Keep Existing
  ↓
✅ Mantiene prezzo esistente
✅ Classifica asset_class e sector corretti
```

**Non è un bug** - è il comportamento corretto per fondi senza ticker pubblico.

---

## 📅 Frequenza Aggiornamento

- **ETF/ETC**: Automatico tramite workflow n8n (daily)
- **Fondi**: Manuale (consigliato: weekly o monthly)

---

## 🚀 Soluzione Futura (Opzionale)

Se vuoi automatizzare anche i fondi:

1. **API a pagamento**: Morningstar, Bloomberg (costoso)
2. **Web scraping**: Scraping del sito Pictet (fragile)
3. **Integrazione banca**: Se la banca fornisce API
4. **Ticker alternativi**: Cercare se esistono ticker equivalenti su exchange

Per ora, l'aggiornamento manuale è la soluzione più pratica e affidabile.
