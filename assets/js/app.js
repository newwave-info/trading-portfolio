// Perspect Dashboard - Main JavaScript
console.log('app.js loaded');

// Toggle Recommendation Accordion
function toggleRecommendationAccordion(sectionId) {
    const section = document.getElementById(sectionId);
    const icon = document.getElementById(sectionId + '-icon');

    if (section && icon) {
        section.classList.toggle('hidden');
        icon.classList.toggle('rotate-180');
    }
}

// Theme Toggle
function toggleTheme() {
    const html = document.documentElement;
    const themeBtn = document.getElementById('themeToggle');
    const icon = themeBtn?.querySelector('i');

    if (html.classList.contains('dark-mode')) {
        html.classList.remove('dark-mode');
        localStorage.setItem('theme', 'light');
        if (icon) {
            icon.classList.remove('fa-sun');
            icon.classList.add('fa-moon');
        }
        updateChartColors('light');
    } else {
        html.classList.add('dark-mode');
        localStorage.setItem('theme', 'dark');
        if (icon) {
            icon.classList.remove('fa-moon');
            icon.classList.add('fa-sun');
        }
        updateChartColors('dark');
    }
}

// Update Chart.js colors for theme
function updateChartColors(theme) {
    const textColor = theme === 'dark' ? '#94a3b8' : '#64748b';
    const gridColor = theme === 'dark' ? 'rgba(71, 85, 105, 0.4)' : 'rgba(0, 0, 0, 0.1)';
    const borderColor = theme === 'dark' ? '#334155' : '#e2e8f0';

    Chart.defaults.color = textColor;
    Chart.defaults.borderColor = borderColor;

    // Update all existing charts
    Object.values(Chart.instances).forEach(chart => {
        // Update x and y scales
        if (chart.options.scales) {
            ['x', 'y', 'r'].forEach(axis => {
                if (chart.options.scales[axis]) {
                    // Ensure grid object exists
                    chart.options.scales[axis].grid = chart.options.scales[axis].grid || {};
                    chart.options.scales[axis].grid.color = gridColor;
                    chart.options.scales[axis].grid.borderColor = borderColor;

                    // Ensure ticks object exists
                    chart.options.scales[axis].ticks = chart.options.scales[axis].ticks || {};
                    chart.options.scales[axis].ticks.color = textColor;

                    // For radar charts
                    if (axis === 'r') {
                        chart.options.scales[axis].angleLines = chart.options.scales[axis].angleLines || {};
                        chart.options.scales[axis].angleLines.color = gridColor;
                        chart.options.scales[axis].pointLabels = chart.options.scales[axis].pointLabels || {};
                        chart.options.scales[axis].pointLabels.color = textColor;
                    }
                }
            });
        }

        chart.update();
    });
}

// Get pattern color based on theme
function getPatternColor() {
    const isDark = document.documentElement.classList.contains('dark-mode');
    return isDark ? 'rgba(30, 41, 59, 0.7)' : 'rgba(255, 255, 255, 0.7)';
}

// Initialize theme on page load
function initTheme() {
    const savedTheme = localStorage.getItem('theme');
    const themeBtn = document.getElementById('themeToggle');
    const icon = themeBtn?.querySelector('i');

    if (savedTheme === 'dark') {
        document.documentElement.classList.add('dark-mode');
        if (icon) {
            icon.classList.remove('fa-moon');
            icon.classList.add('fa-sun');
        }
        // Apply chart colors after a delay to ensure charts are loaded
        setTimeout(() => updateChartColors('dark'), 500);
    }
}

// Sidebar Toggle
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');
    sidebar.classList.toggle('-translate-x-full');
    overlay.classList.toggle('hidden');
}

// Accordion Toggle
function toggleAccordion(targetId) {
    const content = document.getElementById(targetId);
    if (!content) return;

    content.classList.toggle('hidden');

    const isOpen = !content.classList.contains('hidden');
    const toggleButton = document.querySelector(`[data-accordion-toggle="${targetId}"]`);
    const icon = toggleButton?.querySelector('.accordion-icon');
    if (icon) {
        icon.classList.toggle('rotate-180', isOpen);
    }

    // Gestione stato active/current
    if (toggleButton) {
        const allIcons = toggleButton.querySelectorAll('i');
        const label = toggleButton.querySelector('span');

        if (isOpen) {
            toggleButton.classList.add('text-purple-600');
            toggleButton.classList.remove('text-gray-600', 'text-gray-800');
            allIcons.forEach(i => {
                i.classList.add('text-purple-600');
                i.classList.remove('text-gray-400', 'text-gray-600');
            });
            if (label) {
                label.classList.add('text-purple-600');
            }
        } else {
            toggleButton.classList.remove('text-purple-600');
            toggleButton.classList.add('text-gray-600');
            allIcons.forEach(i => {
                i.classList.remove('text-purple-600');
                i.classList.add('text-gray-400');
            });
            if (label) {
                label.classList.remove('text-purple-600');
            }
        }
    }
}

// Chart.js Defaults - check for dark mode from localStorage
const savedThemeForDefaults = localStorage.getItem('theme');
const isDarkOnLoad = savedThemeForDefaults === 'dark';

Chart.defaults.font.family = "'Roboto Mono', monospace";
Chart.defaults.color = isDarkOnLoad ? '#94a3b8' : '#64748b';
Chart.defaults.borderColor = isDarkOnLoad ? '#334155' : '#e2e8f0';
Chart.defaults.font.size = 11;

// Set default scale options with grid colors
Chart.defaults.scales = Chart.defaults.scales || {};
Chart.defaults.scales.linear = Chart.defaults.scales.linear || {};
Chart.defaults.scales.linear.grid = {
    color: isDarkOnLoad ? 'rgba(71, 85, 105, 0.4)' : 'rgba(0, 0, 0, 0.1)',
    borderColor: isDarkOnLoad ? '#334155' : '#e2e8f0'
};
Chart.defaults.scales.category = Chart.defaults.scales.category || {};
Chart.defaults.scales.category.grid = {
    color: isDarkOnLoad ? 'rgba(71, 85, 105, 0.4)' : 'rgba(0, 0, 0, 0.1)',
    borderColor: isDarkOnLoad ? '#334155' : '#e2e8f0'
};
Chart.defaults.scales.radialLinear = Chart.defaults.scales.radialLinear || {};
Chart.defaults.scales.radialLinear.grid = {
    color: isDarkOnLoad ? 'rgba(71, 85, 105, 0.4)' : 'rgba(0, 0, 0, 0.1)'
};
Chart.defaults.scales.radialLinear.angleLines = {
    color: isDarkOnLoad ? 'rgba(71, 85, 105, 0.4)' : 'rgba(0, 0, 0, 0.1)'
};

// Use square points instead of circles
Chart.defaults.elements.point.pointStyle = 'rect';
Chart.defaults.elements.point.rotation = 0;

// HTML Legend Plugin
const htmlLegendPlugin = {
    id: 'htmlLegend',
    afterUpdate(chart, args, options) {
        const legendContainer = document.getElementById(options.containerID);
        if (!legendContainer) return;

        // Clear existing legend
        legendContainer.innerHTML = '';

        const items = chart.options.plugins.legend.labels.generateLabels(chart);

        items.forEach((item, index) => {
            const legendItem = document.createElement('div');
            legendItem.style.cssText = 'display: flex; align-items: center; gap: 6px; cursor: pointer;';

            // Get color from dataset borderColor (works with patterns)
            // For doughnut/pie charts, use the backgroundColor array from the single dataset
            const dataset = chart.data.datasets[0];
            let color;
            if (chart.config.type === 'doughnut' || chart.config.type === 'pie') {
                color = Array.isArray(dataset?.backgroundColor) ? dataset.backgroundColor[index] : item.fillStyle;
            } else {
                const ds = chart.data.datasets[index];
                color = ds?.borderColor || item.strokeStyle || item.fillStyle;
            }

            // Square indicator (hollow)
            const box = document.createElement('span');
            box.style.cssText = `
                width: 8px;
                height: 8px;
                border: 2px solid ${color};
                background: transparent;
                display: inline-block;
            `;

            // Label text
            const label = document.createElement('span');
            label.style.cssText = 'font-size: 10px; color: #52525b; font-family: "Roboto Mono", monospace;';
            label.textContent = item.text;

            if (item.hidden) {
                label.style.textDecoration = 'line-through';
                label.style.opacity = '0.5';
            }

            legendItem.appendChild(box);
            legendItem.appendChild(label);

            // Toggle dataset on click
            legendItem.onclick = () => {
                if (chart.config.type === 'doughnut' || chart.config.type === 'pie') {
                    chart.toggleDataVisibility(index);
                } else {
                    chart.setDatasetVisibility(index, !chart.isDatasetVisible(index));
                }
                chart.update();
            };

            legendContainer.appendChild(legendItem);
        });
    }
};

Chart.register(htmlLegendPlugin);

// View Navigation
function showView(viewId) {
    document.querySelectorAll('.view').forEach(v => v.classList.add('hidden'));
    document.getElementById(viewId)?.classList.remove('hidden');

    document.querySelectorAll('.nav-item').forEach(item => {
        item.classList.remove('active', 'text-purple-600', 'font-semibold');
        item.classList.add('text-gray-600');
    });

    // Find and activate current nav item by onclick attribute
    const navItems = document.querySelectorAll('.nav-item');
    navItems.forEach(item => {
        const onclickAttr = item.getAttribute('onclick');
        if (onclickAttr && onclickAttr.includes(`showView('${viewId}')`)) {
            item.classList.remove('text-gray-600');
            item.classList.add('active', 'text-purple-600', 'font-semibold');
        }
    });

    // Scroll to top on view change
    document.querySelector('.flex-1.overflow-y-auto')?.scrollTo(0, 0);

    setTimeout(() => {
        Object.values(Chart.instances).forEach(chart => chart.resize());
    }, 100);
}

// Track initialized charts
const initializedCharts = new Set();

// Animation config
const animationConfig = {
    duration: 800,
    easing: 'easeOutQuart'
};

// Intersection Observer for chart animations
const chartObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            const chartId = entry.target.id;
            if (chartId && !initializedCharts.has(chartId)) {
                initChart(chartId);
                initializedCharts.add(chartId);
            }
        }
    });
}, {
    threshold: 0.2
});

// Intersection Observer for element animations
const animationObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.classList.add('animate-in');
            animationObserver.unobserve(entry.target);
        }
    });
}, {
    threshold: 0.1,
    rootMargin: '0px 0px -50px 0px'
});

// Initialize sortable tables
document.addEventListener('DOMContentLoaded', function() {
    // Initialize theme
    initTheme();

    if (typeof Tablesort !== 'undefined') {
        document.querySelectorAll('.sortable-table').forEach(table => {
            new Tablesort(table);
        });
    }

    // Observe all chart canvases
    document.querySelectorAll('canvas').forEach(canvas => {
        chartObserver.observe(canvas);
    });

    // Observe elements for animations
    document.querySelectorAll('.widget-card, .widget-metric-large, .widget-metric-medium, .ai-insight-box').forEach(el => {
        animationObserver.observe(el);
    });

    // Initialize sidebar state - Portfolio Manager open, Dashboard active
    const portfolioMenu = document.getElementById('portfolioMenu');
    const portfolioButton = document.querySelector('[data-accordion-toggle="portfolioMenu"]');
    if (portfolioMenu && portfolioButton) {
        // Ensure accordion is open
        portfolioMenu.classList.remove('hidden');

        // Ensure button state is active (purple)
        portfolioButton.classList.add('text-purple-600');
        portfolioButton.classList.remove('text-gray-600', 'text-gray-800');

        const portfolioIcons = portfolioButton.querySelectorAll('i');
        portfolioIcons.forEach(i => {
            i.classList.add('text-purple-600');
            i.classList.remove('text-gray-400', 'text-gray-600');
        });

        const portfolioLabel = portfolioButton.querySelector('span');
        if (portfolioLabel) {
            portfolioLabel.classList.add('text-purple-600');
        }

        // Ensure chevron is rotated (open state)
        const chevron = portfolioButton.querySelector('.accordion-icon');
        if (chevron) {
            chevron.classList.add('rotate-180');
        }
    }
});

// Initialize individual chart by ID
function initChart(chartId) {
    const chartConfigs = getChartConfigs();
    if (chartConfigs[chartId]) {
        new Chart(document.getElementById(chartId), chartConfigs[chartId]);
    }
}

// Get all chart configurations
function getChartConfigs() {
    return {
        'radarChart': {
            type: 'radar',
            data: {
                labels: ['Crescita', 'EBITDA %', 'ROE', 'Liquidità', 'Leva', 'Efficienza'],
                datasets: [
                    {
                        label: '2025',
                        data: [95, 95, 100, 50, 85, 90],
                        borderColor: '#8b5cf6',
                        backgroundColor: 'rgba(139, 92, 246, 0.1)',
                        borderWidth: 2,
                        pointRadius: 4,
                        pointBackgroundColor: '#8b5cf6',
                        pointBorderColor: '#8b5cf6'
                    },
                    {
                        label: 'Target',
                        data: [75, 80, 85, 75, 70, 80],
                        borderColor: '#3f3f46',
                        backgroundColor: 'rgba(63, 63, 70, 0.05)',
                        borderWidth: 2,
                        borderDash: [5, 5],
                        pointRadius: 3
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: animationConfig,
                plugins: {
                    legend: { display: false },
                    htmlLegend: { containerID: 'radarChart-legend' }
                },
                scales: { r: { beginAtZero: true, max: 100, ticks: { stepSize: 20 } } }
            }
        },

        'revenueChart': {
            type: 'line',
            data: {
                labels: ['2023', '2024', '2025'],
                datasets: [
                    {
                        label: 'Ricavi',
                        data: [526, 553, 825],
                        borderColor: '#8b5cf6',
                        backgroundColor: pattern.draw('diagonal', 'rgba(139, 92, 246, 0.05)'),
                        borderWidth: 3,
                        fill: true,
                        tension: 0,
                        pointRadius: 5,
                        order: 2
                    },
                    {
                        label: 'EBITDA',
                        data: [40, 65, 205],
                        borderColor: '#3f3f46',
                        backgroundColor: 'rgba(63, 63, 70, 0.05)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0,
                        pointRadius: 5,
                        order: 1
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: animationConfig,
                plugins: { legend: { display: false }, htmlLegend: { containerID: 'revenueChart-legend' } },
                scales: { y: { beginAtZero: true, ticks: { callback: v => v + ' €k' } } }
            }
        },

        'profitChart': {
            type: 'bar',
            data: {
                labels: ['2023', '2024', '2025'],
                datasets: [
                    { label: 'EBIT', data: [16, 33, 180], backgroundColor: pattern.draw('diagonal', '#8b5cf6'), borderColor: '#8b5cf6', borderRadius: 0 },
                    { label: 'Utile', data: [3, 4, 151], backgroundColor: pattern.draw('diagonal', '#52525b'), borderColor: '#52525b', borderRadius: 0 }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: animationConfig,
                plugins: { legend: { display: false }, htmlLegend: { containerID: 'profitChart-legend' } },
                scales: { y: { beginAtZero: true, ticks: { callback: v => v + ' €k' } } }
            }
        },

        'currentRatioChart': {
            type: 'bar',
            data: {
                labels: ['2023', '2024', '2025'],
                datasets: [{
                    data: [1.92, 0.92, 1.52],
                    backgroundColor: [pattern.draw('diagonal', '#a78bfa'), pattern.draw('diagonal', '#52525b'), pattern.draw('diagonal', '#8b5cf6')],
                    borderRadius: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: animationConfig,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, max: 2.5 } }
            }
        },

        'cashRatioChart': {
            type: 'bar',
            data: {
                labels: ['2023', '2024', '2025'],
                datasets: [{
                    data: [0.20, 0.02, 0.06],
                    backgroundColor: [pattern.draw('diagonal', '#a78bfa'), pattern.draw('diagonal', '#52525b'), pattern.draw('diagonal', '#8b5cf6')],
                    borderRadius: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: animationConfig,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, max: 0.3 } }
            }
        },

        'treasuryMarginChart': {
            type: 'bar',
            data: {
                labels: ['2023', '2024', '2025'],
                datasets: [{
                    data: [-43, -63, 100],
                    backgroundColor: [pattern.draw('diagonal', '#71717a'), pattern.draw('diagonal', '#52525b'), pattern.draw('diagonal', '#8b5cf6')],
                    borderRadius: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: animationConfig,
                plugins: { legend: { display: false } },
                scales: { y: { ticks: { callback: v => v + ' €k' } } }
            }
        },

        'cashFlowWaterfallChart': {
            type: 'bar',
            data: {
                labels: ['Utile Netto', 'Ammortamenti', 'Δ TFR', 'Δ Crediti', 'Δ Debiti', 'Investimenti', 'Δ Cassa'],
                datasets: [{
                    data: [150.7, 25.6, 12.6, 31.2, -69.4, -66.3, 9.8],
                    backgroundColor: [
                        pattern.draw('diagonal', '#8b5cf6'),
                        pattern.draw('diagonal', '#a78bfa'),
                        pattern.draw('diagonal', '#c4b5fd'),
                        pattern.draw('diagonal', '#ddd6fe'),
                        pattern.draw('diagonal', '#71717a'),
                        pattern.draw('diagonal', '#52525b'),
                        pattern.draw('diagonal', '#3f3f46')
                    ],
                    borderRadius: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: animationConfig,
                plugins: { legend: { display: false } },
                scales: { y: { ticks: { callback: v => v + ' €k' } } }
            }
        },

        'cashFlowTrendChart': {
            type: 'line',
            data: {
                labels: ['2023', '2024', '2025'],
                datasets: [
                    {
                        label: 'Cash Flow Operativo',
                        data: [62, 97, 189],
                        borderColor: '#8b5cf6',
                        backgroundColor: pattern.draw('diagonal', 'rgba(139, 92, 246, 0.05)'),
                        borderWidth: 3,
                        fill: true,
                        tension: 0,
                        pointRadius: 5,
                        order: 2
                    },
                    {
                        label: 'Disponibilità Liquide',
                        data: [20, 7, 17],
                        borderColor: '#52525b',
                        backgroundColor: 'rgba(82, 82, 91, 0.05)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0,
                        pointRadius: 5,
                        order: 1
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: animationConfig,
                plugins: { legend: { display: false }, htmlLegend: { containerID: 'cashFlowTrendChart-legend' } },
                scales: { y: { beginAtZero: true, ticks: { callback: v => v + ' €k' } } }
            }
        },

        'debtStructureChart': {
            type: 'bar',
            data: {
                labels: ['2023', '2024', '2025'],
                datasets: [
                    { label: 'Breve', data: [229, 393, 272], backgroundColor: pattern.draw('diagonal', '#8b5cf6'), borderColor: '#8b5cf6', borderRadius: 0 },
                    { label: 'Lungo', data: [200, 182, 234], backgroundColor: pattern.draw('diagonal', '#52525b'), borderColor: '#52525b', borderRadius: 0 }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: animationConfig,
                plugins: { legend: { display: false }, htmlLegend: { containerID: 'debtStructureChart-legend' } },
                scales: { y: { beginAtZero: true, ticks: { callback: v => v + ' €k' } } }
            }
        },

        'icrChart': {
            type: 'bar',
            data: {
                labels: ['2023', '2024', '2025'],
                datasets: [{
                    label: 'ICR',
                    data: [2.2, 1.5, 8.2],
                    backgroundColor: [pattern.draw('diagonal', '#a78bfa'), pattern.draw('diagonal', '#52525b'), pattern.draw('diagonal', '#8b5cf6')],
                    borderRadius: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: animationConfig,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, max: 10 } }
            }
        },

        'dupontMarginChart': {
            type: 'bar',
            data: {
                labels: ['2023', '2024', '2025'],
                datasets: [{
                    data: [0.5, 0.7, 18.3],
                    backgroundColor: [pattern.draw('diagonal', '#71717a'), pattern.draw('diagonal', '#52525b'), pattern.draw('diagonal', '#8b5cf6')],
                    borderRadius: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: animationConfig,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, max: 20, ticks: { callback: v => v + '%' } } }
            }
        },

        'dupontTurnoverChart': {
            type: 'bar',
            data: {
                labels: ['2023', '2024', '2025'],
                datasets: [{
                    data: [0.86, 0.69, 0.92],
                    backgroundColor: [pattern.draw('diagonal', '#71717a'), pattern.draw('diagonal', '#52525b'), pattern.draw('diagonal', '#8b5cf6')],
                    borderRadius: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: animationConfig,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, max: 1.2 } }
            }
        },

        'dupontLeverageChart': {
            type: 'bar',
            data: {
                labels: ['2023', '2024', '2025'],
                datasets: [{
                    data: [3.80, 4.41, 2.69],
                    backgroundColor: [pattern.draw('diagonal', '#a78bfa'), pattern.draw('diagonal', '#52525b'), pattern.draw('diagonal', '#8b5cf6')],
                    borderRadius: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: animationConfig,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, max: 5 } }
            }
        },

        'roaChart': {
            type: 'bar',
            data: {
                labels: ['2023', '2024', '2025'],
                datasets: [{
                    data: [0.43, 0.45, 16.8],
                    backgroundColor: [pattern.draw('diagonal', '#71717a'), pattern.draw('diagonal', '#52525b'), pattern.draw('diagonal', '#8b5cf6')],
                    borderRadius: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: animationConfig,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, max: 20, ticks: { callback: v => v + '%' } } }
            }
        },

        'productivityChart': {
            type: 'line',
            data: {
                labels: ['2023', '2024', '2025'],
                datasets: [
                    {
                        label: 'Ricavi/Personale',
                        data: [2.66, 2.73, 2.92],
                        borderColor: '#8b5cf6',
                        borderWidth: 3,
                        fill: false,
                        tension: 0,
                        pointRadius: 5
                    },
                    {
                        label: 'VA/Personale (€10k)',
                        data: [4.8, 6.1, 10.0],
                        borderColor: '#3f3f46',
                        borderWidth: 3,
                        fill: false,
                        tension: 0,
                        pointRadius: 5
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: animationConfig,
                plugins: { legend: { display: false }, htmlLegend: { containerID: 'productivityChart-legend' } },
                scales: { y: { beginAtZero: true } }
            }
        },

        'capexChart': {
            type: 'bar',
            data: {
                labels: ['2023→2024', '2024→2025'],
                datasets: [{
                    data: [319, 66],
                    backgroundColor: [pattern.draw('diagonal', '#52525b'), pattern.draw('diagonal', '#8b5cf6')],
                    borderRadius: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: animationConfig,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, ticks: { callback: v => v + ' €k' } } }
            }
        },

        'breakEvenChart': {
            type: 'line',
            data: {
                labels: ['0', '200', '400', '552', '600', '825'],
                datasets: [
                    {
                        label: 'Ricavi',
                        data: [0, 200, 400, 552, 600, 825],
                        borderColor: '#8b5cf6',
                        borderWidth: 3,
                        fill: false,
                        tension: 0,
                        pointRadius: 5
                    },
                    {
                        label: 'Costi Totali',
                        data: [350, 423, 495, 552, 580, 652],
                        borderColor: '#3f3f46',
                        borderWidth: 3,
                        fill: false,
                        tension: 0,
                        pointRadius: 5
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: animationConfig,
                plugins: { legend: { display: false }, htmlLegend: { containerID: 'breakEvenChart-legend' } },
                scales: {
                    x: { display: true },
                    y: { beginAtZero: true, ticks: { callback: v => v + ' €k' } }
                }
            }
        },

        'debtROEChart': {
            type: 'line',
            data: {
                labels: ['2023', '2024', '2025'],
                datasets: [
                    {
                        label: 'D/E',
                        data: [2.66, 3.16, 1.52],
                        borderColor: '#3f3f46',
                        backgroundColor: 'rgba(63, 63, 70, 0.05)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0,
                        pointRadius: 5,
                        order: 1
                    },
                    {
                        label: 'ROE %',
                        data: [1.6, 2.0, 45.3],
                        borderColor: '#8b5cf6',
                        backgroundColor: pattern.draw('diagonal', 'rgba(139, 92, 246, 0.05)'),
                        borderWidth: 3,
                        fill: true,
                        tension: 0,
                        pointRadius: 5,
                        order: 2
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: animationConfig,
                plugins: { legend: { display: false }, htmlLegend: { containerID: 'debtROEChart-legend' } },
                scales: { y: { beginAtZero: true, max: 50 } }
            }
        },

        'costDSOChart': {
            type: 'line',
            data: {
                labels: ['2023', '2024', '2025'],
                datasets: [
                    {
                        label: 'Costi %',
                        data: [83.5, 82.5, 72.3],
                        borderColor: '#8b5cf6',
                        backgroundColor: pattern.draw('diagonal', 'rgba(139, 92, 246, 0.05)'),
                        borderWidth: 3,
                        fill: true,
                        tension: 0,
                        pointRadius: 5,
                        order: 2
                    },
                    {
                        label: 'DSO (10gg)',
                        data: [25.4, 21.4, 15.7],
                        borderColor: '#52525b',
                        backgroundColor: 'rgba(82, 82, 91, 0.05)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0,
                        pointRadius: 5,
                        order: 1
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: animationConfig,
                plugins: { legend: { display: false }, htmlLegend: { containerID: 'costDSOChart-legend' } },
                scales: { y: { beginAtZero: true, max: 90 } }
            }
        },

        'zscoreChart': {
            type: 'bar',
            data: {
                labels: ['2023', '2024', '2025'],
                datasets: [{
                    data: [1.85, 1.42, 3.18],
                    backgroundColor: [pattern.draw('diagonal', '#a78bfa'), pattern.draw('diagonal', '#52525b'), pattern.draw('diagonal', '#8b5cf6')],
                    borderRadius: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: animationConfig,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, max: 4 } }
            }
        },

        'attivoDonutChart': {
            type: 'doughnut',
            data: {
                labels: ['Immob. Materiali', 'Crediti', 'Attività Fin.', 'Liquidità', 'Immob. Fin.', 'Ratei', 'Immob. Immat.'],
                datasets: [{
                    data: [471, 355, 43, 17, 5, 4, 1],
                    backgroundColor: ['#8b5cf6', '#a78bfa', '#c4b5fd', '#ddd6fe', '#52525b', '#71717a', '#a1a1aa'],
                    borderColor: '#ffffff',
                    borderWidth: 3,
                    offset: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: animationConfig,
                cutout: '75%',
                plugins: {
                    legend: { display: false },
                    htmlLegend: { containerID: 'attivoDonutChart-legend' },
                    tooltip: {
                        callbacks: {
                            label: ctx => `${ctx.label}: €${ctx.raw}k`
                        }
                    }
                }
            }
        },

        'passivoDonutChart': {
            type: 'doughnut',
            data: {
                labels: ['Debiti Breve', 'Debiti Lungo', 'Utile Esercizio', 'Capitale', 'Utili a Nuovo', 'TFR', 'Altre Riserve', 'Riserva Legale', 'Ratei'],
                datasets: [{
                    data: [272, 234, 151, 100, 61, 54, 17, 5, 3],
                    backgroundColor: ['#8b5cf6', '#a78bfa', '#c4b5fd', '#ddd6fe', '#52525b', '#71717a', '#a1a1aa', '#d4d4d8', '#e4e4e7'],
                    borderColor: '#ffffff',
                    borderWidth: 3,
                    offset: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: animationConfig,
                cutout: '75%',
                plugins: {
                    legend: { display: false },
                    htmlLegend: { containerID: 'passivoDonutChart-legend' },
                    tooltip: {
                        callbacks: {
                            label: ctx => `${ctx.label}: €${ctx.raw}k`
                        }
                    }
                }
            }
        },

        'costiDonutChart': {
            type: 'doughnut',
            data: {
                labels: ['Servizi', 'Personale', 'Ammortamenti', 'Oneri Diversi', 'Godim. Beni', 'Materie Prime'],
                datasets: [{
                    data: [314, 282, 26, 13, 10, 7],
                    backgroundColor: ['#8b5cf6', '#a78bfa', '#c4b5fd', '#52525b', '#71717a', '#a1a1aa'],
                    borderColor: '#ffffff',
                    borderWidth: 3,
                    offset: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: animationConfig,
                cutout: '75%',
                plugins: {
                    legend: { display: false },
                    htmlLegend: { containerID: 'costiDonutChart-legend' },
                    tooltip: {
                        callbacks: {
                            label: ctx => `${ctx.label}: €${ctx.raw}k`
                        }
                    }
                }
            }
        },

        'patrimonioLineChart': {
            type: 'line',
            data: {
                labels: ['2023', '2024', '2025'],
                datasets: [
                    {
                        label: 'Ricavi',
                        data: [526, 553, 825],
                        borderColor: '#8b5cf6',
                        backgroundColor: pattern.draw('diagonal', 'rgba(139, 92, 246, 0.05)'),
                        borderWidth: 3,
                        fill: true,
                        tension: 0,
                        pointRadius: 5,
                        order: 7
                    },
                    {
                        label: 'Costi Servizi',
                        data: [241, 254, 314],
                        borderColor: '#a78bfa',
                        backgroundColor: 'rgba(167, 139, 250, 0.05)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0,
                        pointRadius: 5,
                        order: 6
                    },
                    {
                        label: 'Costi Personale',
                        data: [198, 202, 282],
                        borderColor: '#c4b5fd',
                        backgroundColor: 'rgba(196, 181, 253, 0.05)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0,
                        pointRadius: 5,
                        order: 5
                    },
                    {
                        label: 'EBITDA',
                        data: [40, 65, 205],
                        borderColor: '#52525b',
                        backgroundColor: 'rgba(82, 82, 91, 0.05)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0,
                        pointRadius: 5,
                        order: 4
                    },
                    {
                        label: 'EBIT',
                        data: [16, 33, 180],
                        borderColor: '#71717a',
                        backgroundColor: 'rgba(113, 113, 122, 0.05)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0,
                        pointRadius: 5,
                        order: 3
                    },
                    {
                        label: 'Altri Costi',
                        data: [54, 32, 30],
                        borderColor: '#a1a1aa',
                        backgroundColor: 'rgba(161, 161, 170, 0.05)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0,
                        pointRadius: 5,
                        order: 2
                    },
                    {
                        label: 'Ammortamenti',
                        data: [19, 29, 26],
                        borderColor: '#d4d4d8',
                        backgroundColor: 'rgba(212, 212, 216, 0.05)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0,
                        pointRadius: 5,
                        order: 1
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: animationConfig,
                plugins: { legend: { display: false }, htmlLegend: { containerID: 'patrimonioLineChart-legend' } },
                scales: { y: { beginAtZero: true, ticks: { callback: v => v + ' €k' } } }
            }
        },

        'cumulativeGainChart': {
            type: 'line',
            data: {
                labels: ['14/11', '17/11', '20/11', '23/11', '24/11'],
                datasets: [{
                    label: 'Guadagno Cumulativo',
                    data: [0, 2500, 10000, 18500, 25750.50],
                    borderColor: '#8b5cf6',
                    backgroundColor: 'rgba(139, 92, 246, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 5
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: animationConfig,
                plugins: { legend: { display: false } },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { callback: v => '€' + v.toLocaleString('it-IT') }
                    }
                }
            }
        },

        'valueOverTimeChart': {
            type: 'line',
            data: {
                labels: ['14/11', '17/11', '20/11', '23/11', '24/11'],
                datasets: [{
                    label: 'Valore Portfolio',
                    data: [100000, 102500, 110000, 118500, 125750.50],
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 5
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: animationConfig,
                plugins: { legend: { display: false } },
                scales: {
                    y: {
                        beginAtZero: false,
                        ticks: { callback: v => '€' + v.toLocaleString('it-IT') }
                    }
                }
            }
        },

        'dividendsMonthlyChart': {
            type: 'bar',
            data: {
                labels: ['Gen', 'Feb', 'Mar', 'Apr', 'Mag', 'Giu', 'Lug', 'Ago', 'Set', 'Ott', 'Nov', 'Dic'],
                datasets: [{
                    label: 'Dividendi Mensili',
                    data: [0, 0, 42.04, 0, 0, 48.50, 0, 0, 90.54, 0, 0, 90.54],
                    backgroundColor: '#8b5cf6',
                    borderRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: animationConfig,
                plugins: { legend: { display: false } },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { callback: v => '€' + v.toFixed(2) }
                    }
                }
            }
        },

        'dividendsCumulativeChart': {
            type: 'line',
            data: {
                labels: ['Gen', 'Feb', 'Mar', 'Apr', 'Mag', 'Giu', 'Lug', 'Ago', 'Set', 'Ott', 'Nov', 'Dic'],
                datasets: [{
                    label: 'Rendita Cumulativa',
                    data: [0, 0, 42.04, 42.04, 42.04, 90.54, 90.54, 90.54, 181.08, 181.08, 181.08, 271.62],
                    borderColor: '#22c55e',
                    backgroundColor: 'rgba(34, 197, 94, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 5
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: animationConfig,
                plugins: { legend: { display: false } },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { callback: v => '€' + v.toFixed(2) }
                    }
                }
            }
        },

        'performanceDetailChart': {
            type: 'line',
            data: {
                labels: ['Gen', 'Feb', 'Mar', 'Apr', 'Mag', 'Giu', 'Lug', 'Ago', 'Set', 'Ott', 'Nov', 'Dic'],
                datasets: [{
                    label: 'Valore Portfolio',
                    data: [100000, 102000, 105000, 107500, 110000, 112000, 115000, 118000, 120000, 123000, 125000, 125750.50],
                    borderColor: '#8b5cf6',
                    backgroundColor: 'rgba(139, 92, 246, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 5
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: animationConfig,
                plugins: { legend: { display: false } },
                scales: {
                    y: {
                        beginAtZero: false,
                        ticks: { callback: v => '€' + v.toLocaleString('it-IT') }
                    }
                }
            }
        }
    };
}
