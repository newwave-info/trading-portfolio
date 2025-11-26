# Workflow v2.2 - Changelog & Fixes

## 🎯 Problemi Risolti

### ✅ FIX 1: Classificazione Sector - Priorità Dividend

**Problema originale:**
```javascript
// ETF con "dividend" E "world" nel nome venivano classificati come "Global"
"Vanguard FTSE All-World High Div. Yield" → sector: "Global" ❌
"VanEck Morn. Dev. Mkts Div Lead."       → sector: "Mixed"  ❌
```

**Causa:**
La logica controllava `world/global` PRIMA di `dividend`, quindi ETF come "All-World High Div. Yield" venivano classificati come "Global" invece di "Dividend".

**Soluzione applicata:**
Invertita la priorità di check - ora controlla `dividend` PRIMA di `world/global`:

```javascript
// FIXED LOGIC (v2.2)
if (instrumentType === 'ETF') {
  asset_class = 'Equity';

  // CHECK DIVIDEND FIRST ✅
  if (name.includes('dividend') || name.includes('aristocrat') || name.includes('div')) {
    sector = 'Dividend';
  }
  // Then check world/global
  else if (name.includes('world') || name.includes('global')) {
    sector = 'Global';
  }
  // ... rest
}
```

**Nodi modificati:**
- ✅ TWD Success?
- ✅ FMP Success?
- ✅ Yahoo Success?
- ✅ JustETF Success?
- ✅ Fallback - Keep Existing

**Aggiunta:**
- Ora controlla anche `name.includes('div')` per catturare pattern come "Div Lead."

**Risultato atteso dopo re-run:**
```javascript
"Vanguard FTSE All-World High Div. Yield" → sector: "Dividend" ✅
"VanEck Morn. Dev. Mkts Div Lead."       → sector: "Dividend" ✅
```

---

### ✅ FIX 2: Fondi Comuni - Gestione Ticker Mancanti

**Problema originale:**
```
PICTET ROBOT R EUR   → ticker: "-" → Prezzo: 46.72 (non aggiornato)
PICTET BIOTECH R EUR → ticker: "-" → Prezzo: 46.72 (identico, sospetto)
```

**Causa ROOT:**
I **fondi comuni** (instrument_type: "Fondo") NON hanno ticker quotati su exchange pubblici. Hanno solo ISIN.
Le API (Yahoo, FMP, etc.) funzionano solo con ticker validi → con ticker "-" tutte le API falliscono → workflow usa fallback che mantiene prezzo esistente.

**Questo NON è un bug del workflow** - è una limitazione strutturale:
- ETF/ETC → hanno ticker pubblici → aggiornabili via API ✅
- Fondi comuni → solo ISIN → NON aggiornabili via API pubbliche ❌

**Soluzioni implementate:**

1. **Workflow comportamento:**
   - Il workflow già gestisce correttamente questo scenario
   - Quando tutte le API falliscono → Fallback - Keep Existing
   - Mantiene prezzo esistente + classifica correttamente asset_class/sector

2. **Documentazione creata:**
   - `workflow/FONDI-MANUAL-UPDATE.md` - Guida completa gestione fondi
   - Spiega il problema e le soluzioni disponibili

3. **Script PHP per aggiornamento manuale:**
   - `update-funds.php` - Script ready-to-use per aggiornare prezzi NAV manualmente
   - L'utente può prendere prezzi da sito Pictet/Morningstar e aggiornare facilmente

**Come aggiornare i fondi:**
```bash
# 1. Modifica update-funds.php con prezzi reali da sito Pictet
# 2. Esegui:
php update-funds.php
```

**Dove trovare prezzi NAV:**
- Pictet: https://www.assetmanagement.pictet/it/italy
- Morningstar: https://www.morningstar.it/
- Cerca per ISIN (es. LU1279334483)

---

## 📦 File Creati/Modificati

### Nuovi file:
1. **n8n-portfolio-enrichment-v2.2.json** - Workflow corretto
2. **FONDI-MANUAL-UPDATE.md** - Guida gestione fondi
3. **update-funds.php** - Script aggiornamento prezzi fondi
4. **classification-logic-fixed.js** - Logica di classificazione corretta (riferimento)
5. **fix-classification.js** - Script Node.js per applicare fix (tool interno)

### File modificati:
- n8n-portfolio-enrichment-v2.2.json (5 nodi con classificazione corretta)

---

## 🚀 Come Procedere

### 1️⃣ Importa workflow v2.2 in n8n

```bash
workflow/n8n-portfolio-enrichment-v2.2.json
```

**Configurazione richiesta:**
- Config Variables → WEBHOOK_SECRET: `a1b2c3d4e5f7349012345678901234567890abcdef1234567890abcdef123456`
- (Opzionale) TWELVE_DATA_KEY, FMP_KEY se hai API keys

### 2️⃣ Esegui il workflow

Clicca "Execute workflow" in n8n.

**Risultati attesi:**
- ✅ ETF classificati correttamente (dividend priorità su global)
- ✅ Prezzi ETF/ETC aggiornati da Yahoo/JustETF
- ⚠️ Prezzi fondi invariati (ticker "-") → gestione manuale necessaria

### 3️⃣ (Opzionale) Aggiorna prezzi fondi manualmente

```bash
# Prendi prezzi NAV da sito Pictet
# Modifica update-funds.php con prezzi reali
php update-funds.php
```

### 4️⃣ Verifica risultati

```bash
cat data/portfolio.json | jq -r '.holdings[] | "\(.name) → \(.sector)"'
```

**Output atteso:**
```
Invesco Physical Gold ETC → Gold
Vanguard FTSE All-World High Div. Yield → Dividend ✅ (era "Global")
VanEck Morn. Dev. Mkts Div Lead. → Dividend ✅ (era "Mixed")
SPDR S&P U.S. Dividend Aristocrats → Dividend
...
```

---

## 📊 Riepilogo Tecnico

| Fix | Tipo | Impatto | Soluzione |
|-----|------|---------|-----------|
| **Classificazione Sector** | Bug logica | 2 ETF mal classificati | Workflow v2.2 |
| **Fondi senza ticker** | Limitazione API | 3 fondi non aggiornabili | Script manuale PHP |

**Linea di fondo:**
- ✅ Workflow v2.2 risolve la classificazione
- ✅ Documentazione e script per gestione fondi
- ⚠️ Fondi richiedono aggiornamento manuale (1x settimana/mese)

---

## 🔮 Miglioramenti Futuri (Opzionali)

1. **API premium per fondi**
   - Morningstar API (a pagamento)
   - Bloomberg Terminal (costoso)

2. **Web scraping**
   - Scraping automatico sito Pictet
   - Fragile (cambiano layout)

3. **Integrazione banca**
   - Se la banca fornisce API
   - Più affidabile per fondi gestiti

Per ora, **aggiornamento manuale è la soluzione più pratica**.
