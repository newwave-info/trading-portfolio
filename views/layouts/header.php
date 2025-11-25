<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ETF Portfolio Manager - Dashboard</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/patternomaly@1.3.2/dist/patternomaly.min.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/tablesort@5.3.0/dist/tablesort.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/tablesort@5.3.0/dist/sorts/tablesort.number.min.js"></script>
    <link rel="stylesheet" href="/assets/css/styles.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#1e293b',
                        secondary: '#334155',
                        accent: '#3b82f6',
                        positive: '#10b981',
                        success: '#10b981',
                        'success-light': '#d1fae5',
                        'success-dark': '#059669',
                        danger: '#ef4444',
                        'danger-light': '#fee2e2',
                        'danger-dark': '#dc2626',
                        purple: '#8b5cf6',
                        'purple-light': '#ede9fe',
                        'purple-dark': '#7c3aed',
                        warning: '#f59e0b',
                        'warning-light': '#fef3c7',
                        negative: '#ef4444',
                    }
                }
            }
        }
    </script>
    <script>
        // Funzioni base inline per evitare errori prima del caricamento di app.js
        function showView(viewId) {
            document.querySelectorAll('.view').forEach(v => v.classList.add('hidden'));
            const view = document.getElementById(viewId);
            if (view) view.classList.remove('hidden');
            document.querySelectorAll('.nav-item').forEach(item => {
                item.classList.remove('active', 'text-purple-600', 'font-medium');
                item.classList.add('text-gray-600');
            });
        }
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            if (sidebar && overlay) {
                sidebar.classList.toggle('-translate-x-full');
                overlay.classList.toggle('hidden');
            }
        }
        function toggleAccordion(targetId) {
            const content = document.getElementById(targetId);
            const button = document.querySelector(`[data-accordion-toggle="${targetId}"]`);
            if (content && button) {
                content.classList.toggle('hidden');
                const icon = button.querySelector('.accordion-icon');
                if (icon) icon.classList.toggle('rotate-180');
            }
        }
        console.log('Inline functions loaded');
    </script>
</head>
<body class="flex flex-col h-screen overflow-hidden bg-gray-50">

    <!-- Top Header -->
    <div class="h-[60px] bg-white border-b border-gray-200 px-6 flex items-center justify-between z-50 shrink-0">
        <div>
            <h2 class="text-[18px] font-semibold text-primary"><?php echo htmlspecialchars($metadata['portfolio_name']); ?></h2>
            <p class="text-[11px] text-gray-500"><?php echo htmlspecialchars($metadata['owner']); ?></p>
        </div>
        <div class="flex items-center gap-4">
            <div class="text-[11px] text-gray-500 hidden sm:block">
                Ultimo aggiornamento: <strong class="text-gray-700"><?php echo htmlspecialchars($metadata['last_update']); ?></strong>
            </div>
            <button id="themeToggle" class="text-gray-500 hover:text-purple text-lg transition-colors" onclick="toggleTheme()">
                <div class="tooltip-container inline-flex">
                    <i class="fa-solid fa-moon"></i>
                    <div class="tooltip-content">Passa alla modalità tema scuro o chiaro</div>
                </div>
            </button>
            <button id="mobileMenuBtn" class="md:hidden text-primary text-xl" onclick="toggleSidebar()">
                <i class="fa-solid fa-bars"></i>
            </button>
        </div>
    </div>

    <!-- Overlay Mobile -->
    <div id="sidebarOverlay" class="fixed inset-0 bg-black/50 z-30 hidden md:hidden" onclick="toggleSidebar()"></div>
