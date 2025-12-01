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
</script>
<script src="/assets/js/app.js?v=<?php echo time(); ?>"></script>
<script src="/assets/js/holdings.js?v=<?php echo time(); ?>"></script>
<script src="/assets/js/technical-modal.js?v=<?php echo time(); ?>"></script>
</body>
</html>
