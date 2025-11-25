# Trading Portfolio Dashboard - Style Guide

**Versione:** 1.1
**Data:** 25 Novembre 2025
**Tipo Progetto:** ETF Portfolio Manager Dashboard
**Ultima Modifica:** Aggiunta sezione REGOLE FERREE

---

## 📋 Indice

1. [⚠️ REGOLE FERREE](#️-regole-ferree)
2. [Filosofia del Design](#filosofia-del-design)
3. [Palette Colori](#palette-colori)
4. [Typography](#typography)
5. [Layout & Spacing](#layout--spacing)
6. [Componenti UI](#componenti-ui)
7. [Grafici e Visualizzazioni](#grafici-e-visualizzazioni)
8. [Tabelle](#tabelle)
9. [Animazioni](#animazioni)
10. [Dark Mode](#dark-mode)
11. [Responsive Design](#responsive-design)
12. [Naming Conventions](#naming-conventions)
13. [Best Practices](#best-practices)

---

## ⚠️ REGOLE FERREE

**LEGGERE PRIMA DI QUALSIASI ALTRA SEZIONE**

Queste regole sono **OBBLIGATORIE** e **NON NEGOZIABILI**. Qualsiasi codice che le violi deve essere rifiutato.

### 🎯 Regola #1: Widget - Background Gradiente Viola

**TUTTI i widget devono avere sfondo con gradiente viola (colore primario) e bordo in tinta.**

```css
/* ✅ CORRETTO - Widget principale */
.widget-card {
  background: linear-gradient(135deg, rgba(139, 92, 246, 0.06) 0%, rgba(255, 255, 255, 0) 100%);
  border: 1px solid rgba(139, 92, 246, 0.25);
  border-radius: 0;
}
```

```html
<!-- ✅ CORRETTO -->
<div class="widget-card widget-purple p-6">
  <h3>Titolo Widget</h3>
  <div class="text-2xl font-bold">€125,750.50</div>
</div>
```

```html
<!-- ❌ SBAGLIATO - Widget bianco -->
<div class="widget-card p-6">
  <!-- NO! Manca widget-purple -->
</div>

<!-- ❌ SBAGLIATO - Colore diverso -->
<div class="widget-card widget-blue p-6">
  <!-- NO! Solo viola -->
</div>
```

**Eccezione**: Sub-widget contenuti in widget più grande possono essere bianchi/neutrali.

```html
<!-- ✅ CORRETTO - Sub-widget in widget principale -->
<div class="widget-card widget-purple p-6">
  <h3>Widget Principale</h3>

  <div class="bg-white p-4 border border-gray-200">
    <!-- Sub-widget può essere bianco -->
    <p>Dettaglio</p>
  </div>
</div>
```

---

### 🎯 Regola #2: Testi - Sempre Neri/Neutri

**TUTTI i testi devono essere neri o grigi neutri. Colore sui testi SOLO per semantica dei dati.**

```css
/* ✅ CORRETTO - Testi standard */
color: #18181b;  /* Nero primario */
color: #27272a;  /* Grigio scuro */
color: #3f3f46;  /* Grigio medio */
color: #71717a;  /* Grigio chiaro per captions */
```

**Colori permessi SOLO per semantica dati:**

```css
/* ✅ Verde - Valore positivo */
.text-positive { color: #10b981; }

/* ✅ Rosso - Valore negativo */
.text-negative { color: #ef4444; }

/* ✅ Giallo - Attenzione/Warning */
.text-warning { color: #f59e0b; }

/* ✅ Viola - Ticker/Codici/Highlight dati */
.text-purple { color: #8b5cf6; }
```

```html
<!-- ✅ CORRETTO -->
<div class="widget-card widget-purple p-6">
  <h3 class="text-gray-700">Performance</h3>
  <div class="text-2xl font-bold text-gray-900">€125,750.50</div>
  <div class="text-sm text-positive">+€25,750.50 (+25.75%)</div>
</div>

<!-- ❌ SBAGLIATO - Colori decorativi -->
<div class="widget-card widget-purple p-6">
  <h3 class="text-blue-500">Performance</h3> <!-- NO! -->
  <div class="text-2xl font-bold text-pink-600">€125,750.50</div> <!-- NO! -->
</div>
```

---

### 🎯 Regola #3: Widget Alert - Sfondo e Bordo Rosso

**Widget in stato di alert devono virare sfondo e bordo verso il rosso.**

```css
/* ✅ CORRETTO - Widget alert/negativo */
.widget-negative {
  background: linear-gradient(135deg, rgba(220, 38, 38, 0.08) 0%, rgba(255, 255, 255, 0) 100%);
  border: 1px solid rgba(220, 38, 38, 0.35);
}

.widget-negative:hover {
  border-color: #dc2626;
  box-shadow: 0 2px 8px rgba(220, 38, 38, 0.25);
}
```

```html
<!-- ✅ CORRETTO - Widget alert -->
<div class="widget-card widget-negative p-6">
  <h3 class="text-gray-700">Attenzione</h3>
  <div class="text-2xl font-bold text-negative">-€5,250.30</div>
</div>

<!-- ❌ SBAGLIATO - Widget alert ma sfondo viola -->
<div class="widget-card widget-purple p-6">
  <div class="text-negative">-€5,250.30</div> <!-- Testo rosso ma sfondo viola NO! -->
</div>
```

**Regola decisionale:**
- Dato **positivo** → Widget viola (standard)
- Dato **negativo/alert** → Widget rosso (gradient + bordo rosso)
- Dato **warning** → Widget giallo/arancione (gradient + bordo giallo)

---

### 🎯 Regola #4: Tabelle - Tutte Sortabili

**OGNI tabella deve essere sortabile con righe alternate e hover.**

```html
<!-- ✅ CORRETTO -->
<table class="w-full text-sm sortable-table">
  <thead class="bg-gray-50 border-b border-gray-200">
    <tr>
      <th class="px-4 py-3 text-left font-semibold text-gray-700 text-[11px] uppercase cursor-pointer" role="button">
        Ticker
      </th>
      <th class="px-4 py-3 text-right font-semibold text-gray-700 text-[11px] uppercase cursor-pointer" role="button">
        Valore
      </th>
    </tr>
  </thead>
  <tbody>
    <tr class="border-b border-gray-200 hover:bg-gray-50">
      <td class="px-4 py-3 font-semibold text-purple">VWCE</td>
      <td class="px-4 py-3 text-right">€49,779.49</td>
    </tr>
  </tbody>
</table>

<!-- ❌ SBAGLIATO - Tabella non sortable -->
<table class="w-full text-sm">
  <thead>
    <tr>
      <th>Ticker</th> <!-- Manca cursor-pointer, role, classi -->
    </tr>
  </thead>
</table>
```

**Obbligatorio:**
- Classe `sortable-table` sulla table
- `cursor-pointer` e `role="button"` su ogni th
- `hover:bg-gray-50` su ogni tr
- Righe alternate con `border-b border-gray-200`

---

### 🎯 Regola #5: Grafici - Solo Viola e Grigio Scuro

**Grafici usano ESCLUSIVAMENTE viola (#8b5cf6) e grigio scuro (#52525b). NO altri colori.**

```javascript
// ✅ CORRETTO - Palette grafici
const chartColors = {
  primary: '#8b5cf6',           // Viola
  secondary: '#52525b',         // Grigio scuro
  tertiary: '#3f3f46',          // Grigio medio
  quaternary: '#71717a'         // Grigio chiaro
};

// Per dataset multipli usare variazioni di tonalità
const purpleShades = [
  '#8b5cf6',  // Primary
  '#7c3aed',  // Dark
  '#a78bfa',  // Light
  '#c4b5fd'   // Lighter
];

const grayShades = [
  '#52525b',  // Primary
  '#3f3f46',  // Darker
  '#71717a',  // Lighter
  '#9ca3af'   // Lightest
];
```

```javascript
// ❌ SBAGLIATO - Colori non permessi
const chartColors = {
  blue: '#3b82f6',     // NO!
  green: '#22c55e',    // NO! (eccetto semantica positivo/negativo)
  orange: '#f97316'    // NO!
};
```

**Eccezioni semantiche:**
- Verde `#10b981` SOLO per indicare valori positivi in contesto finanziario
- Rosso `#ef4444` SOLO per indicare valori negativi in contesto finanziario

---

### 🎯 Regola #6: Grafici - Pattern a Righe Diagonali

**Tutti i grafici a barre/istogrammi devono usare pattern a righe diagonali.**

```javascript
// ✅ CORRETTO - Bar chart con pattern
{
  type: 'bar',
  data: {
    datasets: [{
      backgroundColor: pattern.draw('diagonal', '#8b5cf6'),
      borderColor: '#8b5cf6',
      borderRadius: 0  // SEMPRE 0
    }]
  }
}

// ❌ SBAGLIATO - Colore piatto
{
  type: 'bar',
  data: {
    datasets: [{
      backgroundColor: '#8b5cf6',  // NO! Manca pattern
      borderRadius: 8              // NO! Deve essere 0
    }]
  }
}
```

**Libreria richiesta:**
```html
<script src="https://cdn.jsdelivr.net/npm/patternomaly@1.3.2/dist/patternomaly.min.js"></script>
```

---

### 🎯 Regola #7: NO Linee Curve - Sempre Dritte

**Grafici a linea devono avere tension: 0 (linee dritte, NO curve).**

```javascript
// ✅ CORRETTO - Linee dritte
{
  type: 'line',
  data: {
    datasets: [{
      tension: 0,  // Linee dritte
      borderWidth: 3,
      borderColor: '#8b5cf6'
    }]
  }
}

// ❌ SBAGLIATO - Linee curve
{
  type: 'line',
  data: {
    datasets: [{
      tension: 0.4,  // NO! Deve essere 0
      borderColor: '#8b5cf6'
    }]
  }
}
```

---

### 🎯 Regola #8: NO Border Radius - Mai

**ZERO border-radius su QUALSIASI elemento.**

```css
/* ✅ CORRETTO */
.widget-card { border-radius: 0; }
.button { border-radius: 0; }
.badge { border-radius: 0; }
.chart-bar { borderRadius: 0; }

/* ❌ SBAGLIATO */
.widget-card { border-radius: 8px; }   /* NO! */
.button { border-radius: 4px; }        /* NO! */
.badge { border-radius: 9999px; }      /* NO! */
```

**Nessuna eccezione. Nemmeno per:**
- Avatar
- Badge circolari
- Pillole
- Modali
- Tooltip
- Immagini

**Tutti devono avere `border-radius: 0`.**

---

### ✅ Checklist Rapida

Prima di scrivere qualsiasi codice UI, verifica:

- [ ] Widget ha gradiente viola + bordo viola?
- [ ] Widget alert ha gradiente rosso + bordo rosso?
- [ ] Testi sono neri/grigi neutri?
- [ ] Colori sui testi solo per semantica dati?
- [ ] Tabella è sortable con classe `sortable-table`?
- [ ] Grafico usa SOLO viola/grigio (+ variazioni)?
- [ ] Istogrammi hanno pattern a righe diagonali?
- [ ] Line chart ha `tension: 0`?
- [ ] TUTTO ha `border-radius: 0`?

**Se anche solo UNA risposta è NO → Codice va rifiutato.**

---

## 🎨 Filosofia del Design

### Principi Fondamentali
- **Corporate & Professional**: Design pulito, sobrio, orientato ai dati finanziari
- **Zero Border Radius**: Tutti gli elementi hanno `border-radius: 0` per un look professionale
- **Data First**: I dati sono il focus principale, non le decorazioni
- **Consistenza Assoluta**: Ogni elemento segue le stesse regole in tutta l'applicazione
- **Microinterazioni**: Feedback visivo sottile ma presente su ogni interazione

### Linee Guida Generali
- ❌ **NON usare** colori diversi da quelli definiti nella palette
- ❌ **NON usare** border-radius (sempre 0)
- ❌ **NON usare** emoji a meno che non sia esplicitamente richiesto
- ✅ **SEMPRE** usare la palette viola/grigio scuro
- ✅ **SEMPRE** mantenere contrasti leggibili (WCAG AA minimo)
- ✅ **SEMPRE** testare in dark mode

---

## 🎨 Palette Colori

### Colori Primari

#### Viola (Accent/Primary)
```css
/* Viola principale - Usato per elementi interattivi, highlight, accent */
--purple-primary: #8b5cf6;
--purple-light: #ede9fe;
--purple-dark: #7c3aed;

/* Opacity variants per backgrounds */
rgba(139, 92, 246, 0.06)  /* Subtle background */
rgba(139, 92, 246, 0.08)  /* Light background */
rgba(139, 92, 246, 0.10)  /* Medium background */
rgba(139, 92, 246, 0.15)  /* Strong background (dark mode) */
rgba(139, 92, 246, 0.25)  /* Border accent */
rgba(139, 92, 246, 0.40)  /* Border active */
```

**Quando usare:**
- Elementi cliccabili (buttons, links)
- Indicatori di stato attivo
- Hover states
- Badges AI/Premium
- Grafici principali (linee di trend, highlight)

#### Grigio Scuro (Neutral/Text)
```css
/* Testi e elementi neutrali */
--gray-900: #18181b;  /* Testo primario */
--gray-800: #27272a;  /* Testo secondario */
--gray-700: #3f3f46;  /* Testo terziario */
--gray-600: #52525b;  /* Labels, captions */
--gray-500: #71717a;  /* Placeholder, disabled */
--gray-400: #9ca3af;  /* Icons secondari */
--gray-300: #d1d5db;  /* Borders */
--gray-200: #e5e7eb;  /* Borders light */
--gray-100: #f3f4f6;  /* Background subtle */
--gray-50: #f9fafb;   /* Background lightest */
```

**Quando usare:**
- Tutti i testi (usa le varianti appropriate)
- Bordi e separatori
- Background neutri
- Grafici secondari (grigio nelle visualizzazioni)

### Colori Semantici

#### Positive/Success (Verde)
```css
--positive: #10b981;       /* Primary green */
--success: #10b981;
--success-light: #d1fae5;  /* Background */
--success-dark: #059669;   /* Dark variant */
```

**Quando usare:**
- Gain positivi (€, %)
- Indicatori di crescita
- Status badge positivi
- Conferme di azioni

#### Negative/Danger (Rosso)
```css
--negative: #ef4444;       /* Primary red */
--danger: #ef4444;
--danger-light: #fee2e2;   /* Background */
--danger-dark: #dc2626;    /* Dark variant */
```

**Quando usare:**
- Perdite (€, %)
- Indicatori di decrescita
- Errori e warning critici
- Status badge negativi

#### Warning (Arancione)
```css
--warning: #f59e0b;
--warning-light: #fef3c7;
```

**Quando usare:**
- Alert non critici
- Richieste di attenzione
- Status badge "watch" o "hold"

### Background
```css
/* Page background */
--bg-page: #e2e8f0;

/* Card backgrounds (light mode) */
--bg-card: #ffffff;
```

---

## ✍️ Typography

### Font Family
```css
font-family: 'Roboto Mono', monospace;
```

**Fallback:**
```css
font-family: 'Roboto Mono', 'Courier New', Courier, monospace;
```

### Font Sizes

#### Display & Headers
```css
/* Page Title */
h1: 18px (mobile) / 20px (desktop)
font-weight: 500
tracking: tight

/* Section Title */
h2: 16px
font-weight: 500

/* Subsection */
h3: 14px
font-weight: 500
```

#### Metrics & Data
```css
/* Large Metrics (Main KPIs) */
.widget-metric-large: 22px (mobile) / 28px (desktop)
font-weight: 600
color: #18181b

/* Medium Metrics */
.widget-metric-medium: 16px (mobile) / 18px (desktop)
font-weight: 600
color: #18181b

/* Small Metrics */
.widget-metric-small: 13px (mobile) / 14px (desktop)
font-weight: 500
color: #18181b
```

#### Body Text
```css
/* Regular Body */
.widget-text: 12px (mobile) / 12px (desktop)
font-weight: 400
line-height: 1.6
color: #27272a

/* Widget Title */
.widget-title: 13px
font-weight: 500
color: #09090b
```

#### Labels & Captions
```css
/* Labels (Uppercase) */
.widget-label: 10px
font-weight: 500
text-transform: uppercase
letter-spacing: 0.08em
color: #27272a

/* Medium Labels */
.widget-label-medium: 11px
font-weight: 500
color: #27272a

/* Table Headers */
th: 11px
font-weight: 600-700
text-transform: uppercase
color: #374151
```

#### Status & Changes
```css
/* Change Indicators */
.widget-change-positive: 10px, font-weight: 500, color: #22c55e
.widget-change-negative: 10px, font-weight: 500, color: #ef4444
.widget-change-neutral: 10px, font-weight: 500, color: #71717a
```

### Font Weight Guidelines
```
300 - Light: NON USARE
400 - Regular: Body text, descrizioni
500 - Medium: Labels, titles, subtitles
600 - SemiBold: Metrics, valori importanti
700 - Bold: Emphasis su dati critici
```

---

## 📐 Layout & Spacing

### Grid System
```css
/* Main Grid (Dashboard Cards) */
grid-cols-1 md:grid-cols-2 lg:grid-cols-4
gap: 16px (mobile) / 24px (desktop)
```

### Spacing Scale
```css
/* Spacing units (Tailwind) */
1 = 4px
2 = 8px
3 = 12px
4 = 16px
5 = 20px
6 = 24px
8 = 32px
10 = 40px
```

### Component Spacing
```css
/* Card Padding */
.widget-card: p-6 (24px)

/* Section Margins */
mb-6 (mobile) / mb-10 (desktop) tra sezioni

/* Element Gaps */
gap-2 (8px) per icon + text
gap-4 (16px) per elementi correlati
gap-6 (24px) per gruppi separati
```

### Container Widths
```css
/* Main Content */
max-width: none (full width con sidebar)
padding: px-4 md:px-6 py-6 sm:py-10

/* Sidebar */
width: 242px (fixed)
```

---

## 🧩 Componenti UI

### Widget Cards

#### Base Card
```html
<div class="widget-card p-6">
  <!-- Content -->
</div>
```

```css
.widget-card {
  background: #ffffff;
  border: 1px solid #6b7280;
  border-radius: 0;
  transition: all 200ms ease;
  box-shadow: none;
}

.widget-card:hover {
  border-color: #8b5cf6;
  box-shadow: 0 4px 12px rgba(139, 92, 246, 0.15);
  transform: translateY(-2px);
}
```

#### Widget con Gradient Viola
```html
<div class="widget-card widget-purple p-6">
  <!-- Content -->
</div>
```

```css
.widget-purple {
  background: linear-gradient(135deg, rgba(139, 92, 246, 0.06) 0%, rgba(255, 255, 255, 0) 100%);
  border-color: rgba(139, 92, 246, 0.25);
}

.widget-purple:hover {
  border-color: #8b5cf6;
  box-shadow: 0 2px 8px rgba(139, 92, 246, 0.22);
}
```

#### Widget Positive/Negative
```css
/* Positive (Green) */
.widget-positive {
  background: linear-gradient(135deg, rgba(5, 150, 105, 0.08) 0%, rgba(255, 255, 255, 0) 100%);
  border-color: rgba(5, 150, 105, 0.35);
}

/* Negative (Red) */
.widget-negative {
  background: linear-gradient(135deg, rgba(220, 38, 38, 0.08) 0%, rgba(255, 255, 255, 0) 100%);
  border-color: rgba(220, 38, 38, 0.35);
}
```

#### AI Insight Widget
```html
<div class="widget-ai-insight px-6 py-4">
  <div class="flex items-center gap-2.5 mb-3 pb-2.5 border-b border-blue-200/40">
    <span class="badge-ai bg-purple text-white text-[9px] font-bold px-1.5 py-0.5 uppercase tracking-wide">AI Insight</span>
    <span class="text-xs font-semibold text-primary uppercase tracking-wide">Titolo</span>
  </div>
  <div class="text-[13px] leading-relaxed text-gray-700">
    <div class="pl-4 relative py-2 border-b border-dashed border-gray-300">
      <span class="absolute left-0 text-purple font-bold">→</span>
      Contenuto insight
    </div>
  </div>
</div>
```

```css
.widget-ai-insight {
  background: linear-gradient(135deg, rgba(139, 92, 246, 0.08) 0%, rgba(255, 255, 255, 0) 100%);
  border: 1px dashed rgba(139, 92, 246, 0.4);
}

.widget-ai-insight:hover {
  border-color: #7c3aed;
  box-shadow: 0 4px 12px rgba(139, 92, 246, 0.25);
}
```

### Buttons

#### Primary Button (Viola)
```html
<button class="px-3 py-1 bg-purple text-white text-[11px] font-semibold rounded hover:bg-purple-dark transition-colors">
  <i class="fa-solid fa-download mr-1"></i> Button Text
</button>
```

**Regole:**
- Background: `bg-purple` (#8b5cf6)
- Hover: `hover:bg-purple-dark` (#7c3aed)
- Text: `text-white`, `text-[11px]`, `font-semibold`
- Padding: `px-3 py-1`
- Border-radius: `rounded` (ma meglio usare 0 se possibile)
- Transition: `transition-colors`
- Active: `transform: scale(0.97)`

#### Secondary/Filter Button
```html
<button class="px-3 py-1 text-xs font-semibold rounded bg-gray-200 text-gray-700 hover:bg-gray-300 transition-colors">
  Filter
</button>
```

#### Icon Button
```html
<button class="text-gray-400 hover:text-purple transition-colors">
  <i class="fa-solid fa-icon"></i>
</button>
```

### Badges

#### Status Badge
```html
<!-- Positive -->
<span class="px-2 py-1 rounded text-[11px] font-semibold bg-green-100 text-green-700">
  BUY
</span>

<!-- Neutral -->
<span class="px-2 py-1 rounded text-[11px] font-semibold bg-yellow-100 text-yellow-700">
  HOLD
</span>

<!-- Watch -->
<span class="px-2 py-1 rounded text-[11px] font-semibold bg-blue-100 text-blue-700">
  WATCH
</span>
```

#### AI Badge
```html
<span class="badge-ai bg-purple text-white text-[9px] font-bold px-1.5 py-0.5 uppercase tracking-wide">
  AI Insight
</span>
```

### Tooltips
```html
<div class="tooltip-container">
  <i class="fa-solid fa-circle-info text-gray-400 text-xs"></i>
  <div class="tooltip-content">
    Tooltip text here
  </div>
</div>
```

```css
.tooltip-content {
  visibility: hidden;
  position: absolute;
  bottom: 125%;
  left: 50%;
  transform: translateX(-50%);
  background: #18181b;
  color: white;
  padding: 10px 14px;
  border-radius: 0;
  font-size: 11px;
  width: 220px;
  text-align: left;
  z-index: 100;
  box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}

.tooltip-container:hover .tooltip-content {
  visibility: visible;
  opacity: 1;
}
```

---

## 📊 Grafici e Visualizzazioni

### Librerie
```javascript
// Chart.js per tutti i grafici
Chart.js v3.9.1

// Patternomaly per pattern a linee negli istogrammi
patternomaly v1.3.2
```

### Palette Colori Grafici

**SOLO questi colori nei grafici:**

```javascript
const chartColors = {
  // Primary (Viola)
  purple: '#8b5cf6',
  purpleLight: 'rgba(139, 92, 246, 0.05)',
  purplePattern: pattern.draw('diagonal', '#8b5cf6'),

  // Secondary (Grigio)
  gray: '#52525b',
  grayLight: 'rgba(82, 82, 91, 0.05)',
  grayDark: '#3f3f46',

  // Positive/Negative (solo per dati semantici)
  positive: '#10b981',
  negative: '#ef4444'
};
```

### Configurazione Grafici

#### Line Chart (Andamento)
```javascript
{
  type: 'line',
  data: {
    labels: [...],
    datasets: [{
      label: 'Dataset',
      data: [...],
      borderColor: '#8b5cf6',                              // Viola
      backgroundColor: pattern.draw('diagonal', 'rgba(139, 92, 246, 0.05)'), // Pattern viola
      borderWidth: 3,
      fill: true,
      tension: 0,                                          // Linee dritte, NON curve
      pointRadius: 5
    }]
  },
  options: {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      legend: { display: false }                          // Nascondi legend
    },
    scales: {
      y: {
        beginAtZero: false,                               // O true se appropriato
        ticks: {
          callback: v => '€' + v.toLocaleString('it-IT') // Formatta valori
        }
      }
    }
  }
}
```

#### Bar Chart (Istogrammi)
```javascript
{
  type: 'bar',
  data: {
    labels: [...],
    datasets: [{
      label: 'Dataset',
      data: [...],
      backgroundColor: pattern.draw('diagonal', '#8b5cf6'), // Pattern a linee
      borderColor: '#8b5cf6',
      borderRadius: 0                                        // NO border radius
    }]
  }
}
```

#### Doughnut Chart (Allocazione)
```javascript
{
  type: 'doughnut',
  data: {
    labels: [...],
    datasets: [{
      data: [...],
      backgroundColor: [
        '#8b5cf6',      // Viola per categoria principale
        '#52525b',      // Grigio per altre
        '#3f3f46',
        '#71717a'
      ],
      borderWidth: 0
    }]
  },
  options: {
    cutout: '70%',     // Donut thickness
    plugins: {
      legend: {
        position: 'right'
      }
    }
  }
}
```

### Regole Grafici

✅ **DA FARE:**
- Usare `tension: 0` per linee dritte
- Usare pattern diagonali per istogrammi: `pattern.draw('diagonal', color)`
- Mantenere `borderRadius: 0` per le barre
- Nascondere legend se non necessaria
- Formattare valori con `toLocaleString('it-IT')`

❌ **NON FARE:**
- Usare colori diversi da viola/grigio (eccetto positive/negative semantici)
- Usare curve (tension > 0) nei line chart
- Usare colori piatti negli istogrammi (sempre pattern)
- Usare border-radius nei grafici

---

## 📋 Tabelle

### Struttura Base
```html
<div class="overflow-x-auto">
  <table class="w-full text-sm sortable-table">
    <thead class="bg-gray-50 border-b border-gray-200">
      <tr>
        <th class="px-4 py-3 text-left font-semibold text-gray-700 text-[11px] uppercase cursor-pointer" role="button">
          Colonna 1
        </th>
        <th class="px-4 py-3 text-right font-semibold text-gray-700 text-[11px] uppercase cursor-pointer" role="button">
          Colonna 2
        </th>
      </tr>
    </thead>
    <tbody>
      <tr class="border-b border-gray-200 hover:bg-gray-50">
        <td class="px-4 py-3 font-semibold text-purple">Valore 1</td>
        <td class="px-4 py-3 text-right">Valore 2</td>
      </tr>
    </tbody>
  </table>
</div>
```

### Regole Tabelle

#### Header (thead)
```css
/* Background */
bg-gray-50

/* Border */
border-b border-gray-200

/* Text */
text-[11px]
font-semibold (600-700)
text-gray-700
uppercase
tracking-wider

/* Sortable */
cursor-pointer
role="button"
```

#### Celle (tbody td)
```css
/* Padding */
px-4 py-3

/* Text alignment */
text-left     (default, ticker, nomi)
text-right    (numeri, valori, percentuali)
text-center   (badges, status)

/* Font */
text-sm (14px) o text-xs (12px)

/* Border */
border-b border-gray-200 (ultima riga no border)
```

#### Hover State
```css
tbody tr:hover {
  background: rgba(139, 92, 246, 0.06);
  transform: translateX(4px);
}
```

#### Ticker/Valori Prominenti
```css
.text-purple      /* Ticker, codici */
.font-semibold    /* Valori principali */
.text-positive    /* Gain positivi */
.text-negative    /* Perdite */
```

### Sorting
- Tutte le tabelle devono avere classe `sortable-table`
- Header con `cursor-pointer` e `role="button"`
- Indicatori: `↕` (non ordinato), `↑` (crescente), `↓` (decrescente)

---

## ⚡ Animazioni

### Principi
- **Subtili**: Gli utenti non devono "sentire" le animazioni
- **Veloci**: 150ms-400ms max
- **Purposeful**: Solo dove migliora UX

### Fade Slide In (Cards)
```css
@keyframes fadeSlideIn {
  from {
    opacity: 0;
    transform: translateY(8px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.widget-card.animate-in {
  animation: fadeSlideIn 0.4s ease-out forwards;
}

/* Staggered delay */
.grid > .widget-card.animate-in:nth-child(1) { animation-delay: 0.05s; }
.grid > .widget-card.animate-in:nth-child(2) { animation-delay: 0.1s; }
.grid > .widget-card.animate-in:nth-child(3) { animation-delay: 0.15s; }
.grid > .widget-card.animate-in:nth-child(4) { animation-delay: 0.2s; }
```

### Hover Effects
```css
/* Card Hover */
.widget-card.animate-in:hover {
  transform: translateY(-2px);
  transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
}

/* Table Row Hover */
tbody tr {
  transition: all 0.15s ease;
}
tbody tr:hover {
  transform: translateX(4px);
}

/* Button Active */
button:active {
  transform: scale(0.97);
}
```

### Count Up (Metrics)
```css
@keyframes countUp {
  from {
    opacity: 0;
    transform: scale(0.95);
  }
  to {
    opacity: 1;
    transform: scale(1);
  }
}

.widget-metric-large.animate-in {
  animation: countUp 0.5s ease-out forwards;
  animation-delay: 0.3s;
}
```

### Transizioni Standard
```css
/* Default per elementi interattivi */
transition: all 0.15s ease;

/* Per colori */
transition: color 0.15s ease, background-color 0.15s ease, border-color 0.15s ease;

/* Per trasformazioni */
transition: transform 0.2s ease, box-shadow 0.2s ease;
```

---

## 🌙 Dark Mode

### Implementazione
```css
/* Body background */
.dark-mode {
  --bg-primary: #0f172a;      /* Page background */
  --bg-secondary: #1e293b;    /* Sidebar, cards */
  --bg-card: #1e293b;         /* Card background */
  --text-primary: #f1f5f9;    /* Main text */
  --text-secondary: #94a3b8;  /* Secondary text */
  --border-color: #334155;    /* Borders */
}
```

### Widget Gradient in Dark Mode
```css
/* Viola */
.dark-mode .widget-purple {
  background: linear-gradient(135deg, rgba(139, 92, 246, 0.15) 0%, rgba(30, 41, 59, 0) 100%);
  border-color: rgba(139, 92, 246, 0.4);
}

/* Positive */
.dark-mode .widget-positive {
  background: linear-gradient(135deg, rgba(5, 150, 105, 0.15) 0%, rgba(30, 41, 59, 0) 100%);
  border-color: rgba(5, 150, 105, 0.4);
}

/* Negative */
.dark-mode .widget-negative {
  background: linear-gradient(135deg, rgba(220, 38, 38, 0.15) 0%, rgba(30, 41, 59, 0) 100%);
  border-color: rgba(220, 38, 38, 0.4);
}
```

### Regole Dark Mode
- Tutti i background bianchi diventano `var(--bg-secondary)`
- Testi grigi diventano `var(--text-primary)` o `var(--text-secondary)`
- Bordi diventano `var(--border-color)`
- Aumentare opacity dei gradient background (da 0.06-0.08 a 0.15)
- Mantenere i colori semantici (viola, verde, rosso)

---

## 📱 Responsive Design

### Breakpoints
```css
/* Mobile First */
Default: < 640px   (mobile)
sm: 640px          (small tablet)
md: 768px          (tablet)
lg: 1024px         (desktop)
xl: 1280px         (large desktop)
```

### Grid Responsive
```html
<!-- 1 colonna mobile, 2 tablet, 4 desktop -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
```

### Typography Responsive
```css
/* Titles */
text-[18px] sm:text-[20px]

/* Metrics */
text-2xl (mobile) / text-3xl (desktop)

/* Spacing */
mb-6 sm:mb-10
px-4 md:px-6
py-6 sm:py-10
```

### Mobile Specific
```css
@media (max-width: 639px) {
  /* Smaller fonts */
  .widget-title { font-size: 12px; }
  .widget-text { font-size: 11px; }

  /* Adjust padding */
  .widget-card { padding: 16px; }

  /* Touch targets */
  .nav-item { padding: 10px 0; }
  tbody tr { min-height: 44px; }

  /* Scrollbar styling */
  .overflow-x-auto::-webkit-scrollbar { height: 6px; }
}
```

### Sidebar Mobile
```css
/* Hidden by default on mobile */
#sidebar {
  transform: -translate-x-full;
  transition: transform 0.3s ease;
}

/* Visible on desktop */
@media (min-width: 768px) {
  #sidebar {
    transform: translateX(0);
  }
}
```

---

## 🏷️ Naming Conventions

### Classi CSS

#### Component-Based Naming
```css
/* Pattern: .component-variant-state */
.widget-card              /* Base component */
.widget-card-purple       /* Variant */
.widget-card:hover        /* State */
```

#### Widget Classes
```css
.widget-card              /* Base card */
.widget-purple            /* Purple gradient */
.widget-positive          /* Positive/success */
.widget-negative          /* Negative/danger */
.widget-ai-insight        /* AI insight box */

.widget-metric-large      /* Large numbers */
.widget-metric-medium     /* Medium numbers */
.widget-metric-small      /* Small numbers */

.widget-title             /* Card title */
.widget-text              /* Body text */
.widget-label             /* Small labels */
```

#### Utility Classes (Tailwind)
```css
/* Spacing */
p-6, px-4, py-3, mb-8, gap-6

/* Colors */
text-purple, bg-purple, border-purple
text-positive, text-negative

/* Typography */
text-[11px], font-semibold, uppercase, tracking-wider

/* Layout */
grid, grid-cols-4, gap-6, flex, items-center
```

### IDs HTML
```html
<!-- Views -->
#dashboard, #holdings, #performance, #technical

<!-- Navigation -->
#sidebar, #sidebarOverlay, #mainContent

<!-- Charts -->
#performanceChart, #allocationChart, #technicalTable
```

### File Naming
```
styles.css                 /* Main styles */
app.js                     /* Main JavaScript */
index.php                  /* Main page */
STYLE_GUIDE.md            /* This file */
README.md                 /* Project docs */
```

---

## ✅ Best Practices

### HTML
```html
<!-- ✅ Buono -->
<div class="widget-card widget-purple p-6">
  <div class="text-[11px] font-medium text-gray-600 uppercase tracking-wider mb-3">
    Label
  </div>
  <div class="text-2xl font-bold text-primary">
    €125,750.50
  </div>
</div>

<!-- ❌ Cattivo -->
<div style="background: purple; padding: 20px;">
  <p>Label</p>
  <h2>€125,750.50</h2>
</div>
```

### CSS
```css
/* ✅ Buono - Utilities + Custom classes */
.widget-card {
  background: #ffffff;
  border: 1px solid #6b7280;
  border-radius: 0;
}

/* ❌ Cattivo - Troppo specifico */
.dashboard-section-card-container-wrapper {
  /* ... */
}
```

### JavaScript
```javascript
// ✅ Buono - Naming chiaro
function filterTechnicalTable(signal) {
  const table = document.getElementById('technicalTable');
  const rows = table.querySelectorAll('tbody tr');
  // ...
}

// ❌ Cattivo - Naming poco chiaro
function ftt(s) {
  const t = document.getElementById('tt');
  // ...
}
```

### Performance
```javascript
// ✅ Buono - Debounce animations
setTimeout(() => {
  initAllCharts();
}, 100);

// ❌ Cattivo - Troppo presto
initAllCharts(); // Può causare race conditions
```

### Accessibilità
```html
<!-- ✅ Buono -->
<button class="..." role="button" aria-label="Download CSV">
  <i class="fa-solid fa-download mr-1"></i> Export CSV
</button>

<!-- ❌ Cattivo -->
<div onclick="download()">
  <i class="fa-solid fa-download"></i>
</div>
```

---

## 🚫 Anti-Patterns (Da Evitare)

### ❌ NON fare mai:

1. **Usare colori non nella palette**
   ```css
   /* MALE */
   color: #ff00ff;
   background: blue;
   ```

2. **Usare border-radius**
   ```css
   /* MALE */
   border-radius: 8px;
   border-radius: 50%;
   ```

3. **Inline styles**
   ```html
   <!-- MALE -->
   <div style="color: red; font-size: 14px;">
   ```

4. **Animazioni troppo lente**
   ```css
   /* MALE */
   transition: all 2s ease;
   ```

5. **Testi hardcoded senza formattazione**
   ```javascript
   // MALE
   value = "12500.50";

   // BENE
   value = number.toLocaleString('it-IT', {
     style: 'currency',
     currency: 'EUR'
   });
   ```

6. **ID o classi generiche**
   ```html
   <!-- MALE -->
   <div id="box1" class="container">

   <!-- BENE -->
   <div id="performanceChart" class="widget-card widget-purple">
   ```

---

## 📝 Checklist Pre-Commit

Prima di committare codice, verifica:

- [ ] **Colori**: Solo viola/grigio (o semantici)
- [ ] **Border Radius**: Sempre 0
- [ ] **Font**: Roboto Mono ovunque
- [ ] **Spacing**: Segue scala Tailwind
- [ ] **Typography**: Font size corretti
- [ ] **Tabelle**: Sortable e con hover
- [ ] **Grafici**: Pattern a linee, colori corretti, tension: 0
- [ ] **Animazioni**: < 400ms, subtle
- [ ] **Dark Mode**: Testato e funzionante
- [ ] **Responsive**: Testato mobile/tablet/desktop
- [ ] **Accessibilità**: role, aria-label dove necessario
- [ ] **Performance**: No animazioni bloccanti
- [ ] **Console**: No errori JavaScript

---

## 📞 Domande Frequenti

### "Posso usare un colore diverso per questo caso specifico?"
**No.** La palette è definita per coprire tutti i casi. Se pensi serva un nuovo colore, rivaluta il design.

### "Posso arrotondare gli angoli solo un po'?"
**No.** Border-radius: 0 sempre. È una scelta stilistica fondamentale.

### "Devo usare Roboto Mono anche per i numeri?"
**Sì.** Font monospace rende i numeri più leggibili e allineati.

### "Posso aumentare la durata delle animazioni?"
**No.** 400ms è il massimo. L'app deve sembrare veloce e responsive.

### "Dark mode è obbligatorio?"
**Sì.** Ogni nuovo componente deve funzionare in dark mode.

### "Posso saltare il sorting su una tabella?"
**No.** Tutte le tabelle devono essere sortable per colonna.

---

## 🔄 Versioning

### Versione 1.1 - 25 Novembre 2025
- **AGGIUNTA SEZIONE CRITICA:** REGOLE FERREE
- 8 regole non negoziabili con esempi ✅/❌
- Comportamenti obbligatori definiti in modo granitico
- Checklist rapida pre-commit aggiornata
- Eliminata ambiguità su widget bianchi vs viola

### Versione 1.0 - 25 Novembre 2025
- Release iniziale della guida di stile
- Definizione palette colori viola/grigio
- Standardizzazione componenti UI
- Regole grafici con pattern a linee
- Convenzioni naming e best practices

---

## 📄 Licenza & Contributi

Questa guida è parte del progetto **Trading Portfolio Dashboard**.

Per modifiche o suggerimenti alla guida:
1. Proponi le modifiche
2. Discuti con il team
3. Aggiorna questo documento
4. Incrementa il versioning

**Ultima modifica:** 25 Novembre 2025
**Autore:** Claude Code Assistant
**Reviewer:** Nicola
