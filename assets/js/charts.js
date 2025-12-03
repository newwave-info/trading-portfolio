/**
 * Portfolio Charts Manager
 * Centralizza la gestione di tutti i grafici Chart.js dell'applicazione
 */

// ============================================================================
// CONFIGURAZIONI GLOBALI
// ============================================================================

const ChartManager = {
  // Colori tema
  colors: {
    purple: "#8b5cf6",
    purpleLight: "rgba(139, 92, 246, 0.4)",
    purpleArea: "rgba(139, 92, 246, 0.1)",
    purpleAreaLight: "rgba(139, 92, 246, 0.05)",
    grayDark: "#4b5563",
    grayMedium: "#6b7280",
    grayLight: "rgba(75, 85, 99, 0.08)",
    positive: "#10b981",
    negative: "#ef4444",
  },

  // Opzioni comuni animazione (linee che crescono dal valore minimo)
  animation: {
    x: {
      duration: 600,
      easing: "easeOutCubic",
      from: 0,
    },
    y: {
      duration: 900,
      easing: "easeOutCubic",
      from: (ctx) => {
        const yScale = ctx?.chart?.scales?.y;
        return yScale ? yScale.min : 0;
      },
    },
  },

  // Stile punti standard
  pointStyle: {
    style: "rect", // Quadrato
    radius: 6, // Dimensione standard
    hoverRadius: 6, // Dimensione hover
    borderWidth: 2, // Bordo
    hitRadius: 8, // Area cliccabile
  },

  // Registro grafici inizializzati
  initialized: new Set(),

  // ============================================================================
  // UTILITY FUNCTIONS
  // ============================================================================

  /**
   * Ottiene il pattern diagonale per un colore
   */
  getPattern(color) {
    return typeof pattern !== "undefined"
      ? pattern.draw("diagonal", color)
      : color;
  },

  /**
   * Formatta valore in euro
   */
  formatEuro(value, decimals = 2) {
    return (
      "€" +
      value.toLocaleString("it-IT", {
        minimumFractionDigits: decimals,
        maximumFractionDigits: decimals,
      })
    );
  },

  /**
   * Formatta percentuale
   */
  formatPercent(value, decimals = 2) {
    return value.toFixed(decimals) + "%";
  },

  // ============================================================================
  // FACTORY FUNCTIONS - PERFORMANCE CHARTS
  // ============================================================================

  /**
   * Crea grafico Andamento Annuale (dati mensili)
   */
  createPerformanceDetailChart(canvasId, labels, values, gainPct) {
    const ctx = document.getElementById(canvasId);
    if (!ctx || this.initialized.has(canvasId)) return null;

    const chart = new Chart(ctx.getContext("2d"), {
      type: "line",
      data: {
        labels: labels,
        datasets: [
          {
            label: "Valore Portafoglio",
            data: values,
            borderColor: this.colors.purple,
            backgroundColor: this.getPattern(this.colors.purpleAreaLight),
            borderWidth: 3,
            fill: true,
            tension: 0,
            pointStyle: this.pointStyle.style,
            pointRadius: this.pointStyle.radius,
            pointHoverRadius: this.pointStyle.hoverRadius,
            pointBackgroundColor: this.colors.purple,
            pointBorderColor: "#fff",
            pointBorderWidth: this.pointStyle.borderWidth,
            order: 2,
          },
          {
            label: "Performance %",
            data: gainPct,
            borderColor: this.colors.grayDark,
            backgroundColor: "transparent",
            borderWidth: 2,
            fill: false,
            tension: 0,
            pointStyle: this.pointStyle.style,
            pointRadius: this.pointStyle.radius,
            pointHoverRadius: this.pointStyle.hoverRadius,
            pointBackgroundColor: this.colors.grayDark,
            pointBorderColor: "#fff",
            pointBorderWidth: this.pointStyle.borderWidth,
            yAxisID: "y1",
            order: 1,
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        animations: this.animation,
        plugins: {
          legend: {
            display: true,
            position: "bottom",
            labels: { boxWidth: 12, font: { size: 10 } },
          },
          tooltip: {
            callbacks: {
              label: (context) => {
                const label = context.dataset.label || "";
                if (context.dataset.yAxisID === "y1") {
                  return label + ": " + this.formatPercent(context.parsed.y);
                }
                return label + ": " + this.formatEuro(context.parsed.y);
              },
            },
          },
        },
        scales: {
          y: {
            beginAtZero: false,
            ticks: { callback: (v) => this.formatEuro(v, 0) },
          },
          y1: {
            position: "right",
            grid: { drawOnChartArea: false },
            ticks: { callback: (v) => this.formatPercent(v, 1) },
          },
        },
      },
    });

    this.initialized.add(canvasId);
    return chart;
  },

  /**
   * Crea grafico Guadagno Cumulativo YTD
   */
  createCumulativeGainChart(canvasId, labels, cumulGain, gainPct) {
    const ctx = document.getElementById(canvasId);
    if (!ctx || this.initialized.has(canvasId)) return null;

    const chart = new Chart(ctx.getContext("2d"), {
      type: "line",
      data: {
        labels: labels,
        datasets: [
          {
            label: "Guadagno Cumulativo",
            data: cumulGain,
            borderColor: this.colors.purple,
            backgroundColor: this.getPattern(this.colors.purpleAreaLight),
            borderWidth: 3,
            fill: true,
            tension: 0,
            pointStyle: this.pointStyle.style,
            pointRadius: this.pointStyle.radius,
            pointHoverRadius: this.pointStyle.hoverRadius,
            pointBackgroundColor: this.colors.purple,
            pointBorderColor: "#fff",
            pointBorderWidth: this.pointStyle.borderWidth,
            order: 2,
          },
          {
            label: "Performance %",
            data: gainPct,
            borderColor: this.colors.grayDark,
            backgroundColor: "transparent",
            borderWidth: 2,
            fill: false,
            tension: 0,
            pointStyle: this.pointStyle.style,
            pointRadius: this.pointStyle.radius,
            pointHoverRadius: this.pointStyle.hoverRadius,
            pointBackgroundColor: this.colors.grayDark,
            pointBorderColor: "#fff",
            pointBorderWidth: this.pointStyle.borderWidth,
            yAxisID: "y1",
            order: 1,
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        animations: this.animation,
        plugins: {
          legend: {
            display: true,
            position: "bottom",
            labels: { boxWidth: 12, font: { size: 10 } },
          },
          tooltip: {
            callbacks: {
              label: (context) => {
                const label = context.dataset.label || "";
                if (context.dataset.yAxisID === "y1") {
                  return label + ": " + this.formatPercent(context.parsed.y);
                }
                return label + ": " + this.formatEuro(context.parsed.y);
              },
            },
          },
        },
        scales: {
          y: {
            beginAtZero: true,
            ticks: { callback: (v) => this.formatEuro(v, 0) },
          },
          y1: {
            position: "right",
            grid: { drawOnChartArea: false },
            ticks: { callback: (v) => this.formatPercent(v, 1) },
          },
        },
      },
    });

    this.initialized.add(canvasId);
    return chart;
  },

  /**
   * Crea grafico Ultimi 5 Giorni
   */
  createValueOverTimeChart(canvasId, labels, values, gainPct) {
    const ctx = document.getElementById(canvasId);
    if (!ctx || this.initialized.has(canvasId)) return null;

    const chart = new Chart(ctx.getContext("2d"), {
      type: "line",
      data: {
        labels: labels,
        datasets: [
          {
            label: "Valore Portafoglio",
            data: values,
            borderColor: this.colors.purple,
            backgroundColor: this.getPattern(this.colors.purpleAreaLight),
            borderWidth: 3,
            fill: true,
            tension: 0,
            pointStyle: this.pointStyle.style,
            pointRadius: this.pointStyle.radius,
            pointHoverRadius: this.pointStyle.hoverRadius,
            pointBackgroundColor: this.colors.purple,
            pointBorderColor: "#fff",
            pointBorderWidth: this.pointStyle.borderWidth,
            order: 2,
          },
          {
            label: "Performance %",
            data: gainPct,
            borderColor: this.colors.grayDark,
            backgroundColor: "transparent",
            borderWidth: 2,
            fill: false,
            tension: 0,
            pointStyle: this.pointStyle.style,
            pointRadius: this.pointStyle.radius,
            pointHoverRadius: this.pointStyle.hoverRadius,
            pointBackgroundColor: this.colors.grayDark,
            pointBorderColor: "#fff",
            pointBorderWidth: this.pointStyle.borderWidth,
            yAxisID: "y1",
            order: 1,
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        animations: this.animation,
        plugins: {
          legend: {
            display: true,
            position: "bottom",
            labels: { boxWidth: 12, font: { size: 10 } },
          },
          tooltip: {
            callbacks: {
              label: (context) => {
                const label = context.dataset.label || "";
                if (context.dataset.yAxisID === "y1") {
                  return label + ": " + this.formatPercent(context.parsed.y);
                }
                return label + ": " + this.formatEuro(context.parsed.y);
              },
            },
          },
        },
        scales: {
          y: {
            beginAtZero: false,
            ticks: { callback: (v) => this.formatEuro(v, 0) },
          },
          y1: {
            position: "right",
            grid: { drawOnChartArea: false },
            ticks: { callback: (v) => this.formatPercent(v, 1) },
          },
        },
      },
    });

    this.initialized.add(canvasId);
    return chart;
  },

  // ============================================================================
  // FACTORY FUNCTIONS - DIVIDENDS CHARTS
  // ============================================================================

  /**
   * Crea grafico Dividendi Mensili (bar chart)
   */
  createDividendsMonthlyChart(canvasId, labels, received, forecast) {
    const ctx = document.getElementById(canvasId);
    if (!ctx || this.initialized.has(canvasId)) return null;

    const chart = new Chart(ctx.getContext("2d"), {
      type: "bar",
      data: {
        labels: labels,
        datasets: [
          {
            label: "Ricevuti",
            data: received,
            backgroundColor: this.getPattern(this.colors.purple),
            borderColor: this.colors.purple,
            borderRadius: 0,
            categoryPercentage: 0.6,
            barPercentage: 0.9,
          },
          {
            label: "Previsti",
            data: forecast,
            backgroundColor: this.getPattern(this.colors.purpleLight),
            borderColor: this.colors.purple,
            borderWidth: 1,
            borderDash: [5, 5],
            borderRadius: 0,
            categoryPercentage: 0.6,
            barPercentage: 0.9,
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        animations: this.animation,
        plugins: {
          legend: {
            display: true,
            position: "bottom",
            labels: { boxWidth: 12, font: { size: 10 } },
          },
        },
        scales: {
          x: {
            stacked: false,
            ticks: { autoSkip: false, maxRotation: 45, minRotation: 45 },
          },
          y: {
            stacked: false,
            beginAtZero: true,
            ticks: { callback: (v) => this.formatEuro(v, 0) },
          },
        },
      },
    });

    this.initialized.add(canvasId);
    return chart;
  },

  /**
   * Crea grafico Dividendi Cumulativi (line chart)
   */
  createDividendsCumulativeChart(canvasId, labels, received, total) {
    const ctx = document.getElementById(canvasId);
    if (!ctx || this.initialized.has(canvasId)) return null;

    const chart = new Chart(ctx.getContext("2d"), {
      type: "line",
      data: {
        labels: labels,
        datasets: [
          {
            label: "Ricevuti",
            data: received,
            borderColor: this.colors.purple,
            backgroundColor: this.getPattern(this.colors.purpleArea),
            borderWidth: 3,
            fill: true,
            tension: 0,
            pointStyle: this.pointStyle.style,
            pointRadius: this.pointStyle.radius,
            pointHoverRadius: this.pointStyle.hoverRadius,
            pointBackgroundColor: this.colors.purple,
            pointBorderColor: "#fff",
            pointBorderWidth: this.pointStyle.borderWidth,
          },
          {
            label: "Totale (+ Previsti)",
            data: total,
            borderColor: this.colors.purpleLight,
            backgroundColor: this.getPattern(this.colors.purpleAreaLight),
            borderWidth: 2,
            borderDash: [5, 5],
            fill: true,
            tension: 0,
            pointStyle: this.pointStyle.style,
            pointRadius: this.pointStyle.radius,
            pointHoverRadius: this.pointStyle.hoverRadius,
            pointBackgroundColor: this.colors.purpleLight,
            pointBorderColor: "#fff",
            pointBorderWidth: this.pointStyle.borderWidth,
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        animations: this.animation,
        plugins: {
          legend: {
            display: true,
            position: "bottom",
            labels: { boxWidth: 12, font: { size: 10 } },
          },
        },
        scales: {
          y: {
            beginAtZero: true,
            ticks: { callback: (v) => this.formatEuro(v, 0) },
          },
        },
      },
    });

    this.initialized.add(canvasId);
    return chart;
  },

  // ============================================================================
  // FACTORY FUNCTIONS - DASHBOARD CHARTS
  // ============================================================================

  /**
   * Crea grafico Performance Dashboard (da implementare con dati reali)
   */
  createPerformanceChart(canvasId, labels, values) {
    const ctx = document.getElementById(canvasId);
    if (!ctx || this.initialized.has(canvasId)) return null;

    const chart = new Chart(ctx.getContext("2d"), {
      type: "line",
      data: {
        labels: labels,
        datasets: [
          {
            label: "Valore Portafoglio",
            data: values,
            borderColor: this.colors.purple,
            backgroundColor: this.getPattern(this.colors.purpleAreaLight),
            borderWidth: 3,
            fill: true,
            tension: 0,
            pointStyle: this.pointStyle.style,
            pointRadius: this.pointStyle.radius,
            pointHoverRadius: this.pointStyle.hoverRadius,
            pointBackgroundColor: this.colors.purple,
            pointBorderColor: "#fff",
            pointBorderWidth: this.pointStyle.borderWidth,
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        animations: this.animation,
        plugins: {
          legend: { display: false },
        },
        scales: {
          y: {
            beginAtZero: false,
            ticks: { callback: (v) => this.formatEuro(v, 0) },
          },
        },
      },
    });

    this.initialized.add(canvasId);
    return chart;
  },

  /**
   * Crea grafico Allocazione Dashboard (donut chart)
   */
  createAllocationChart(canvasId, labels, values, colors) {
    const ctx = document.getElementById(canvasId);
    if (!ctx || this.initialized.has(canvasId)) return null;

    const chart = new Chart(ctx.getContext("2d"), {
      type: "doughnut",
      data: {
        labels: labels,
        datasets: [
          {
            data: values,
            backgroundColor: colors || [
              this.colors.purple,
              "#a78bfa",
              "#c4b5fd",
              "#ddd6fe",
              "#52525b",
              "#71717a",
            ],
            borderColor: "#ffffff",
            borderWidth: 0,
            spacing: 6,
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        animations: this.animation,
        cutout: "75%",
        plugins: {
          legend: {
            display: true,
            position: "bottom",
            labels: { boxWidth: 12, font: { size: 10 } },
          },
          tooltip: {
            callbacks: {
              label: (ctx) => `${ctx.label}: ${ctx.parsed.toFixed(2)}%`,
            },
          },
        },
      },
    });

    this.initialized.add(canvasId);
    return chart;
  },
};

// Rendi ChartManager globalmente disponibile
window.ChartManager = ChartManager;

console.log("✅ Chart Manager inizializzato");
