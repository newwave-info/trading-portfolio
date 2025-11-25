        </div>
    </div>
</div>

<script>
    // Performance Chart - Inizializzazione manuale con dati dinamici PHP
    const performanceCtxEl = document.getElementById('performanceChart');
    if (performanceCtxEl) {
        const performanceCtx = performanceCtxEl.getContext('2d');
        new Chart(performanceCtx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode(array_column($monthly_performance, 'month')); ?>,
                datasets: [{
                    label: 'Valore Portafoglio',
                    data: <?php echo json_encode(array_column($monthly_performance, 'value')); ?>,
                    borderColor: '#8b5cf6',
                    backgroundColor: pattern.draw('diagonal', 'rgba(139, 92, 246, 0.05)'),
                    borderWidth: 3,
                    fill: true,
                    tension: 0,
                    pointStyle: 'rect',
                    pointRadius: 4,
                    pointHoverRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: {
                        beginAtZero: false,
                        ticks: { callback: v => '€' + v.toLocaleString('it-IT') }
                    }
                }
            }
        });
    }

    // Allocation Chart - Inizializzazione manuale con dati dinamici PHP
    const allocationCtxEl = document.getElementById('allocationChart');
    if (allocationCtxEl) {
        const allocationCtx = allocationCtxEl.getContext('2d');
        new Chart(allocationCtx, {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode(array_column($allocation_by_asset_class, 'class')); ?>,
                datasets: [{
                    data: <?php echo json_encode(array_column($allocation_by_asset_class, 'percentage')); ?>,
                    backgroundColor: ['#8b5cf6', '#a78bfa', '#c4b5fd', '#52525b', '#71717a'],
                    borderColor: '#ffffff',
                    borderWidth: 0,
                    spacing: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '75%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { padding: 15, font: { size: 11 }, usePointStyle: true, pointStyle: 'rect' }
                    }
                }
            }
        });
    }
</script>
<script src="/assets/js/app.js?v=<?php echo time(); ?>"></script>
<script src="/assets/js/holdings.js?v=<?php echo time(); ?>"></script>
</body>
</html>
