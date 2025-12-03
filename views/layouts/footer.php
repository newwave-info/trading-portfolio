        </div>
    </div>
</div>

<script>
    // Dashboard Charts - Usando ChartManager
    if (document.getElementById('performanceChart')) {
        try {
            window.ChartManager.createPerformanceChart(
                'performanceChart',
                <?php echo json_encode(array_column($monthly_performance, 'month')); ?>,
                <?php echo json_encode(array_column($monthly_performance, 'value')); ?>
            );
            console.log('✅ Dashboard Performance Chart created');
        } catch (error) {
            console.error('Errore inizializzazione Performance Chart:', error);
        }
    }

    if (document.getElementById('allocationChart')) {
        try {
            window.ChartManager.createAllocationChart(
                'allocationChart',
                <?php echo json_encode(array_column($allocation_by_asset_class, 'asset_class')); ?>,
                <?php echo json_encode(array_column($allocation_by_asset_class, 'percentage')); ?>,
                ['#8b5cf6', '#a78bfa', '#c4b5fd', '#52525b', '#71717a']
            );
            console.log('✅ Dashboard Allocation Chart created');
        } catch (error) {
            console.error('Errore inizializzazione Allocation Chart:', error);
        }
    }

    // Allocation Evolution Chart - Performance Tab
    if (document.getElementById('allocationEvolutionChart')) {
        loadAllocationHistory();
    }

    async function loadAllocationHistory(days = 30) {
        const loadingEl = document.getElementById('allocationHistoryLoading');
        const errorEl = document.getElementById('allocationHistoryError');
        const emptyEl = document.getElementById('allocationHistoryEmpty');
        const chartEl = document.getElementById('allocationHistoryChart');

        try {
            const response = await fetch(`/api/allocation-history.php?days=${days}`);
            const json = await response.json();

            if (!json.success) {
                throw new Error(json.error || 'API error');
            }

            if (!json.data.dates || json.data.dates.length === 0) {
                loadingEl.classList.add('hidden');
                emptyEl.classList.remove('hidden');
                return;
            }

            // Hide loading, show chart
            loadingEl.classList.add('hidden');
            chartEl.classList.remove('hidden');

            // Create chart
            createAllocationEvolutionChart(json.data);

        } catch (error) {
            console.error('Error loading allocation history:', error);
            loadingEl.classList.add('hidden');
            errorEl.classList.remove('hidden');
        }
    }

    function createAllocationEvolutionChart(data) {
        const ctx = document.getElementById('allocationEvolutionChart');
        if (!ctx) return;

        // Formatta date per display
        const labels = data.dates.map(d => {
            const date = new Date(d);
            const day = String(date.getDate()).padStart(2, '0');
            const month = String(date.getMonth() + 1).padStart(2, '0');
            return `${day}/${month}`;
        });

        // Colori per ticker (palette viola/grigio secondo style guide)
        const purpleShades = [
            'rgba(124, 58, 237, 0.6)',   // Purple dark
            'rgba(139, 92, 246, 0.6)',   // Purple primary
            'rgba(167, 139, 250, 0.6)',  // Purple light
            'rgba(196, 181, 253, 0.6)'   // Purple lighter
        ];

        const grayShades = [
            'rgba(82, 82, 91, 0.6)',     // Gray dark
            'rgba(113, 113, 122, 0.6)',  // Gray
            'rgba(161, 161, 170, 0.6)',  // Gray light
            'rgba(212, 212, 216, 0.6)'   // Gray lighter
        ];

        // Alterna viola e grigio
        const colors = [...purpleShades, ...grayShades];

        // Crea datasets per ogni ticker
        const datasets = data.tickers.map((ticker, index) => ({
            label: ticker,
            data: data.allocations[ticker],
            backgroundColor: colors[index % colors.length],
            borderColor: colors[index % colors.length].replace('0.6', '1'),
            borderWidth: 2,
            tension: 0,
            pointStyle: 'rect',
            pointRadius: 3,
            fill: false
        }));

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: datasets
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false
                },
                animation: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'bottom',
                        labels: {
                            boxWidth: 12,
                            padding: 10,
                            font: {
                                size: 11
                            }
                        }
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': ' + context.parsed.y.toFixed(1) + '%';
                            },
                            footer: function(tooltipItems) {
                                const total = tooltipItems.reduce((sum, item) => sum + item.parsed.y, 0);
                                return 'Totale: ' + total.toFixed(1) + '%';
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        stacked: false,
                        title: {
                            display: true,
                            text: 'Data'
                        },
                        ticks: {
                            maxRotation: 45,
                            minRotation: 45
                        }
                    },
                    y: {
                        stacked: false,
                        min: 0,
                        max: 100,
                        title: {
                            display: true,
                            text: 'Allocazione (%)'
                        },
                        ticks: {
                            callback: value => value + '%'
                        }
                    }
                }
            }
        });

        console.log('✅ Allocation Evolution Chart created');
    }
</script>
<script src="/assets/js/app.js?v=<?php echo time(); ?>"></script>
<script src="/assets/js/holdings.js?v=<?php echo time(); ?>"></script>
<script src="/assets/js/technical-modal.js?v=<?php echo time(); ?>"></script>
<script src="/assets/js/technical-charts.js?v=<?php echo time(); ?>"></script>
<script src="/assets/js/recommendations.js?v=<?php echo time(); ?>"></script>
</body>
</html>
