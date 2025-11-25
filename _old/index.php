<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Business Intelligence - Perspect SRL</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/patternomaly@1.3.2/dist/patternomaly.min.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/tablesort@5.3.0/dist/tablesort.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/tablesort@5.3.0/dist/sorts/tablesort.number.min.js"></script>
    <link rel="stylesheet" href="assets/css/styles.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#18181b',
                        secondary: '#27272a',
                        accent: '#8b5cf6',
                        positive: '#22c55e',
                        success: '#22c55e',
                        'success-light': '#dcfce7',
                        'success-dark': '#16a34a',
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
</head>
<body class="flex flex-col h-screen overflow-hidden bg-gray-50">
    <!-- Top Header - Full Width -->
    <div class="h-[60px] bg-white border-b border-gray-200 px-6 flex items-center justify-between z-50 shrink-0">
        <div>
            <h2 class="text-[15px] font-medium text-primary">Perspect srl</h2>
            <p class="text-[11px] text-gray-500">Business Intelligence Suite</p>
        </div>
        <div class="flex items-center gap-4">
            <div class="text-[11px] text-gray-500 hidden sm:block">
                Ultimo aggiornamento: <strong class="text-gray-700">18 nov 2025</strong>
            </div>
            <button id="themeToggle" class="text-gray-500 hover:text-purple text-lg transition-colors" onclick="toggleTheme()" title="Cambia tema">
                <i class="fa-solid fa-moon"></i>
            </button>
            <button id="mobileMenuBtn" class="md:hidden text-primary text-xl" onclick="toggleSidebar()">
                <i class="fa-solid fa-bars"></i>
            </button>
        </div>
    </div>

    <!-- Overlay Mobile -->
    <div id="sidebarOverlay" class="fixed inset-0 bg-black/50 z-30 hidden md:hidden" onclick="toggleSidebar()"></div>

    <!-- Sidebar -->
    <div id="sidebar" class="w-[242px] bg-white border-r border-gray-200 fixed h-screen left-0 top-[60px] z-40 overflow-y-auto flex flex-col transform -translate-x-full md:translate-x-0 transition-transform duration-300">
        <div class="px-4 py-6 flex-1">
            <button class="w-full flex items-center text-[13px] font-semibold text-gray-400 px-2 py-2 pb-4 cursor-not-allowed transition-colors duration-200 border-b border-gray-200" style="outline: none; -webkit-tap-highlight-color: transparent; border-radius: 6px;" disabled>
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-building text-[12px]"></i>
                    <span class="text-left">Company Overview</span>
                </div>
            </button>
            <button class="w-full flex items-center text-[13px] font-semibold text-purple-600 px-2 py-2 pt-4 group transition-colors duration-200" style="outline: none; -webkit-tap-highlight-color: transparent; border-radius: 6px;" data-accordion-toggle="cgMenu" onclick="toggleAccordion('cgMenu')">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-chart-line text-[12px] text-purple-600 transition-colors duration-200"></i>
                    <span class="text-purple-600 transition-colors duration-200 text-left">Controllo di Gestione</span>
                </div>
                <i class="accordion-icon fa-solid fa-chevron-down text-[10px] text-purple-600 transition-transform duration-200 rotate-180 ml-auto"></i>
            </button>
            <div id="cgMenu" class="accordion-content mt-2">
                <div class="ml-3 border-l border-gray-200 space-y-1">
                    <div class="nav-item active flex items-center gap-2.5 pl-4 py-1.5 cursor-pointer text-[13px] text-purple-600 font-medium transition-colors duration-200 hover:text-purple-600" onclick="showView('dashboard'); toggleSidebar()">
                        <i class="fa-solid fa-gauge text-[11px] text-current"></i>
                        <span>Dashboard</span>
                    </div>
                    <div class="nav-item flex items-center gap-2.5 pl-4 py-1.5 cursor-pointer text-[13px] text-gray-600 transition-colors duration-200 hover:text-purple-600" onclick="showView('liquidity'); toggleSidebar()">
                        <i class="fa-solid fa-droplet text-[11px] text-current"></i>
                        <span>Liquidità e Flussi</span>
                    </div>
                    <div class="nav-item flex items-center gap-2.5 pl-4 py-1.5 cursor-pointer text-[13px] text-gray-600 transition-colors duration-200 hover:text-purple-600" onclick="showView('reports'); toggleSidebar()">
                        <i class="fa-solid fa-file text-[11px] text-current"></i>
                        <span>Bilanci</span>
                    </div>
                    <div class="nav-item flex items-center gap-2.5 pl-4 py-1.5 cursor-pointer text-[13px] text-gray-600 transition-colors duration-200 hover:text-purple-600" onclick="showView('analysis'); toggleSidebar()">
                        <i class="fa-solid fa-magnifying-glass text-[11px] text-current"></i>
                        <span>Analisi Avanzata</span>
                    </div>
                    <div class="nav-item flex items-center gap-2.5 pl-4 py-1.5 cursor-pointer text-[13px] text-gray-600 transition-colors duration-200 hover:text-purple-600" onclick="showView('risk'); toggleSidebar()">
                        <i class="fa-solid fa-shield text-[11px] text-current"></i>
                        <span>Rischio e Scoring</span>
                    </div>
                    <div class="nav-item flex items-center gap-2.5 pl-4 py-1.5 cursor-pointer text-[13px] text-gray-600 transition-colors duration-200 hover:text-purple-600" onclick="showView('documents'); toggleSidebar()">
                        <i class="fa-solid fa-folder text-[11px] text-current"></i>
                        <span>Archivio Documenti</span>
                    </div>
                </div>
            </div>
            <div class="border-t border-gray-200 mt-4 pt-4">
                <button class="w-full flex items-center text-[13px] font-semibold text-gray-600 px-2 py-2 group transition-colors duration-200" style="outline: none; -webkit-tap-highlight-color: transparent; border-radius: 6px;" data-accordion-toggle="availableMenu" onclick="toggleAccordion('availableMenu')">
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-layer-group text-[12px] text-gray-400 transition-colors duration-200"></i>
                        <span class="transition-colors duration-200 text-left">Analisi Disponibili</span>
                    </div>
                    <i class="accordion-icon fa-solid fa-chevron-down text-[10px] text-gray-400 transition-transform duration-200 ml-auto"></i>
                </button>
                <div id="availableMenu" class="accordion-content hidden mt-2">
                <div class="ml-3 border-l border-gray-200 space-y-1">
                    <div class="flex items-center gap-2 pl-4 py-1 text-[12px] cursor-not-allowed" style="color: #cbd5e1; opacity: 0.6;">
                        <i class="fa-solid fa-circle text-[6px]" style="color: #cbd5e1;"></i>
                        <span>Brand</span>
                    </div>
                    <div class="flex items-center gap-2 pl-4 py-1 text-[12px] cursor-not-allowed" style="color: #cbd5e1; opacity: 0.6;">
                        <i class="fa-solid fa-circle text-[6px]" style="color: #cbd5e1;"></i>
                        <span>Digital</span>
                    </div>
                    <div class="flex items-center gap-2 pl-4 py-1 text-[12px] cursor-not-allowed" style="color: #cbd5e1; opacity: 0.6;">
                        <i class="fa-solid fa-circle text-[6px]" style="color: #cbd5e1;"></i>
                        <span>Social</span>
                    </div>
                    <div class="flex items-center gap-2 pl-4 py-1 text-[12px] cursor-not-allowed" style="color: #cbd5e1; opacity: 0.6;">
                        <i class="fa-solid fa-circle text-[6px]" style="color: #cbd5e1;"></i>
                        <span>Web</span>
                    </div>
                    <div class="flex items-center gap-2 pl-4 py-1 text-[12px] cursor-not-allowed" style="color: #cbd5e1; opacity: 0.6;">
                        <i class="fa-solid fa-circle text-[6px]" style="color: #cbd5e1;"></i>
                        <span>ESG</span>
                    </div>
                    <div class="flex items-center gap-2 pl-4 py-1 text-[12px] cursor-not-allowed" style="color: #cbd5e1; opacity: 0.6;">
                        <i class="fa-solid fa-circle text-[6px]" style="color: #cbd5e1;"></i>
                        <span>EAA</span>
                    </div>
                    <div class="flex items-center gap-2 pl-4 py-1 text-[12px] cursor-not-allowed" style="color: #cbd5e1; opacity: 0.6;">
                        <i class="fa-solid fa-circle text-[6px]" style="color: #cbd5e1;"></i>
                        <span>Innovation</span>
                    </div>
                </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div id="mainContent" class="ml-0 md:ml-[242px] flex-1 flex flex-col overflow-hidden">
        <!-- Content -->
        <div class="flex-1 overflow-y-auto px-4 md:px-6 py-6 sm:py-10">
            
            <!-- View: Dashboard -->
            <div id="dashboard" class="view">
                <div class="mb-6 sm:mb-10">
                    <h1 class="text-[18px] sm:text-[20px] font-medium text-primary tracking-tight">Dashboard Esecutivo</h1>
                </div>

                <!-- Radar + Growth -->
                <div class="mb-5">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">
                        <div class="widget-card widget-purple p-6 transition-all duration-300">
                            <div class="flex justify-between items-center mb-5 pb-4 border-b border-gray-200">
                                <div class="flex items-center gap-2">
                                    <span class="text-[11px] font-medium text-gray-600 uppercase tracking-wider">Indicatori Chiave</span>
                                    <div class="tooltip-container">
                                        <i class="fa-solid fa-circle-info text-gray-400 text-xs cursor-help"></i>
                                        <div class="tooltip-content">Confronto tra performance effettiva 2025 e target ottimali. Valori normalizzati 0-100.</div>
                                    </div>
                                </div>
                                <div id="radarChart-legend" class="flex gap-4"></div>
                            </div>
                            <div class="relative h-[250px] sm:h-[320px] mb-5">
                                <canvas id="radarChart"></canvas>
                            </div>
                        </div>

                        <div class="widget-card widget-purple p-6 h-full flex flex-col">
                            <div class="flex items-center gap-2 mb-5 pb-4 border-b border-gray-200">
                                <div class="text-[11px] font-medium text-gray-600 uppercase tracking-wider">Executive Summary 2025</div>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4 mt-auto h-full">
                                <div class="p-4 bg-gray-50 border border-gray-200 flex flex-col justify-between">
                                    <div>
                                        <div class="text-[10px] text-gray-500 uppercase mb-1">Redditività</div>
                                        <div class="flex items-center gap-1 text-sm font-semibold text-gray-800">
                                            <i class="fa-solid fa-check text-purple"></i>
                                            Ottima
                                        </div>
                                    </div>
                                    <div class="text-[11px] text-gray-500 mt-2">ROE 45.3%</div>
                                </div>
                                <div class="p-4 bg-gray-50 border border-gray-200 flex flex-col justify-between">
                                    <div>
                                        <div class="text-[10px] text-gray-500 uppercase mb-1">Crescita</div>
                                        <div class="flex items-center gap-1 text-sm font-semibold text-gray-800">
                                            <i class="fa-solid fa-check text-purple"></i>
                                            Forte
                                        </div>
                                    </div>
                                    <div class="text-[11px] text-gray-500 mt-2">+49% Ricavi</div>
                                </div>
                                <div class="p-4 bg-gray-50 border border-gray-200 flex flex-col justify-between">
                                    <div>
                                        <div class="text-[10px] text-gray-500 uppercase mb-1">Solidità</div>
                                        <div class="flex items-center gap-1 text-sm font-semibold text-gray-800">
                                            <i class="fa-solid fa-check text-purple"></i>
                                            Buona
                                        </div>
                                    </div>
                                    <div class="text-[11px] text-gray-500 mt-2">D/E 1.52x</div>
                                </div>
                                <div class="p-4 border flex flex-col justify-between" style="background: linear-gradient(135deg, rgba(220, 38, 38, 0.06) 0%, rgba(255, 255, 255, 0) 100%); border-color: rgba(220, 38, 38, 0.25);">
                                    <div>
                                        <div class="text-[10px] text-gray-500 uppercase mb-1">Liquidità</div>
                                        <div class="flex items-center gap-1 text-sm font-semibold text-gray-800">
                                            <i class="fa-solid fa-triangle-exclamation text-danger"></i>
                                            Attenzione
                                        </div>
                                    </div>
                                    <div class="text-[11px] text-gray-500 mt-2">Cash 0.06x</div>
                                </div>
                                <div class="p-4 border flex flex-col justify-between" style="background: linear-gradient(135deg, rgba(220, 38, 38, 0.06) 0%, rgba(255, 255, 255, 0) 100%); border-color: rgba(220, 38, 38, 0.25);">
                                    <div>
                                        <div class="text-[10px] text-gray-500 uppercase mb-1">Efficienza</div>
                                        <div class="flex items-center gap-1 text-sm font-semibold text-gray-800">
                                            <i class="fa-solid fa-triangle-exclamation text-danger"></i>
                                            Migliorabile
                                        </div>
                                    </div>
                                    <div class="text-[11px] text-gray-500 mt-2">DSO 157gg</div>
                                </div>
                                <div class="p-4 bg-gray-50 border border-gray-200 flex flex-col justify-between">
                                    <div>
                                        <div class="text-[10px] text-gray-500 uppercase mb-1">Rischio</div>
                                        <div class="flex items-center gap-1 text-sm font-semibold text-gray-800">
                                            <i class="fa-solid fa-check text-purple"></i>
                                            Basso
                                        </div>
                                    </div>
                                    <div class="text-[11px] text-gray-500 mt-2">Z-Score 3.18</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- AI Insight -->
                <div class="mb-12 sm:mb-20">
                    <div class="widget-ai-insight px-6 py-4 transition-all duration-300">
                        <div class="flex items-center gap-2.5 mb-3 pb-2.5 border-b border-blue-200/40">
                            <span class="badge-ai bg-purple text-white text-[9px] font-bold px-1.5 py-0.5 uppercase tracking-wide">AI Insight</span>
                            <span class="text-xs font-semibold text-primary uppercase tracking-wide">Riepilogo Esecutivo 2025</span>
                        </div>
                        <div class="text-[13px] leading-relaxed text-gray-700">
                            <div class="pl-4 relative py-2 border-b border-dashed border-gray-300"><span class="absolute left-0 text-danger font-bold">→</span> Cash Ratio 0.06x critico: liquidità immediata insufficiente per coprire i debiti a breve</div>
                            <div class="pl-4 relative py-2 border-b border-dashed border-gray-300"><span class="absolute left-0 text-danger font-bold">→</span> DSO 157 giorni ancora sopra target (120gg): €75k di cassa bloccata nei crediti</div>
                            <div class="pl-4 relative py-2 border-b border-dashed border-gray-300"><span class="absolute left-0 text-danger font-bold">→</span> Dipendenza da debiti a breve: €272k (54% del totale debiti) in scadenza entro 12 mesi</div>
                            <div class="pl-4 relative py-2 border-b border-dashed border-gray-300"><span class="absolute left-0 text-purple font-bold">→</span> Margine struttura negativo -€144k: immobilizzazioni finanziate parzialmente con debito</div>
                            <div class="pl-4 relative py-2 border-b border-dashed border-gray-300"><span class="absolute left-0 text-purple font-bold">→</span> Ricavi +49% (€825k): crescita organica trainata da nuovi clienti e upselling</div>
                            <div class="pl-4 relative py-2 border-b border-dashed border-gray-300"><span class="absolute left-0 text-purple font-bold">→</span> EBITDA margin 24.9% (+12.2pp): efficienza operativa ai massimi storici</div>
                            <div class="pl-4 relative py-2 border-b border-dashed border-gray-300"><span class="absolute left-0 text-purple font-bold">→</span> Utile netto €151k (+4039%): trasformazione da quasi break-even a forte profittabilità</div>
                            <div class="pl-4 relative py-2 border-b border-dashed border-gray-300"><span class="absolute left-0 text-purple font-bold">→</span> Z-Score 3.18 (zona sicura): rischio insolvenza azzerato vs 1.42 nel 2024</div>
                            <div class="pl-4 relative py-2 border-b border-dashed border-gray-300"><span class="absolute left-0 text-purple font-bold">→</span> ROE 45.3%: rendimento eccezionale del capitale proprio (+43.3pp)</div>
                            <div class="pl-4 relative py-2"><span class="absolute left-0 text-purple font-bold">→</span> ICR 8.2x: capacità di copertura interessi eccellente (+447% vs 2024)</div>
                        </div>
                    </div>
                </div>


                <!-- Strategic Insights -->
                <div class="mb-12 sm:mb-20">
                    <div class="text-[11px] font-medium text-gray-600 uppercase tracking-wider mb-3">Insight Strategici</div>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
                        <div class="widget-card widget-purple p-6">
                            <div class="widget-title">ROE 45.3% (+43.3pp)</div>
                            <div class="widget-text">Rendimento eccezionale del capitale proprio. Utile netto €151k su patrimonio €334k.</div>
                        </div>
                        <div class="widget-card widget-purple p-6">
                            <div class="widget-title">Z-Score 3.18 (+124%)</div>
                            <div class="widget-text">Zona sicura raggiunta (>2.9). Rischio insolvenza azzerato vs 1.42 nel 2024.</div>
                        </div>
                        <div class="widget-card widget-purple p-6">
                            <div class="widget-title">Leva Operativa 8.9x</div>
                            <div class="widget-text">EBIT cresce 8.9x più veloce dei ricavi vs 2024. Modello ad alta sensibilità al volume.</div>
                        </div>
                        <div class="widget-card widget-purple p-6">
                            <div class="widget-title">Break-Even €552k</div>
                            <div class="widget-text">Margine di sicurezza 33% vs 2024. Ricavi possono scendere di €273k prima di perdite.</div>
                        </div>
                        <div class="widget-card widget-purple p-6">
                            <div class="widget-title">Produttività +39%</div>
                            <div class="widget-text">EBITDA €205k (+216%). Per dipendente +€26k vs 2024. Team scalabile.</div>
                        </div>
                        <div class="widget-card widget-negative p-6">
                            <div class="widget-title"><i class="fa-solid fa-triangle-exclamation text-negative"></i> DSO 157gg</div>
                            <div class="widget-text">-57gg vs 2024 ma target 120gg. Riduzione libera €75k cassa.</div>
                        </div>
                    </div>
                </div>

                <!-- KPI Strategici Aggiunti -->
                <div class="mb-5">
                    <div class="text-[11px] font-medium text-gray-600 uppercase tracking-wider mb-3">Grafici Storici</div>
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">
                        <div class="widget-card widget-purple p-6">
                            <div class="flex justify-between items-center mb-5 pb-4 border-b border-gray-200">
                                <div class="flex items-center gap-2">
                                    <span class="text-[11px] font-medium text-gray-600 uppercase tracking-wider">Andamento D/E e ROE</span>
                                    <div class="tooltip-container">
                                        <i class="fa-solid fa-circle-info text-gray-400 text-xs cursor-help"></i>
                                        <div class="tooltip-content">Evoluzione storica del rapporto debiti/equity e del rendimento sul capitale proprio.</div>
                                    </div>
                                </div>
                                <div id="debtROEChart-legend" class="flex gap-4"></div>
                            </div>
                            <div class="relative h-[220px] sm:h-[280px]"><canvas id="debtROEChart"></canvas></div>
                            <div class="ai-insight-box">
                                <div class="flex items-center gap-2 mb-2 pb-2 border-b border-purple/20">
                                    <span class="badge-ai bg-purple text-white text-[9px] font-bold px-1.5 py-0.5 uppercase tracking-wide">AI Insight</span>
                                </div>
                                <strong><i class="fa-solid fa-scale-balanced text-purple"></i> Leva Finanziaria Ottimale:</strong> Il D/E sceso da 3.17x a 1.52x (-52%) indica un riequilibrio della struttura finanziaria. Il ROE esploso al 45.3% (+43pp) deriva dalla combinazione di maggior leverage (1.52x) e margine netto 18.3%. La struttura è ora più sostenibile.
                            </div>
                        </div>
                        <div class="widget-card widget-purple p-6">
                            <div class="flex justify-between items-center mb-5 pb-4 border-b border-gray-200">
                                <div class="flex items-center gap-2">
                                    <span class="text-[11px] font-medium text-gray-600 uppercase tracking-wider">Andamento Costi e DSO</span>
                                    <div class="tooltip-container">
                                        <i class="fa-solid fa-circle-info text-gray-400 text-xs cursor-help"></i>
                                        <div class="tooltip-content">Evoluzione storica dell'incidenza costi sui ricavi e dei giorni di incasso.</div>
                                    </div>
                                </div>
                                <div id="costDSOChart-legend" class="flex gap-4"></div>
                            </div>
                            <div class="relative h-[220px] sm:h-[280px]"><canvas id="costDSOChart"></canvas></div>
                            <div class="ai-insight-box">
                                <div class="flex items-center gap-2 mb-2 pb-2 border-b border-purple/20">
                                    <span class="badge-ai bg-purple text-white text-[9px] font-bold px-1.5 py-0.5 uppercase tracking-wide">AI Insight</span>
                                </div>
                                <strong><i class="fa-solid fa-chart-line text-purple"></i> Efficienza Operativa:</strong> I costi sono scesi dal 82.5% al 72.3% dei ricavi (-10.2pp), segnalando forte leva operativa. Il DSO a 157gg rimane critico (era 214gg nel 2023). Priorità: ridurre ulteriormente i giorni incasso per liberare €75k di cassa. I margini operativi sono ormai ottimali.
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts Row -->
                <div class="mb-12 sm:mb-20">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">
                        <div class="widget-card widget-purple p-6">
                            <div class="flex justify-between items-center mb-5 pb-4 border-b border-gray-200">
                                <div class="flex items-center gap-2">
                                    <span class="text-[11px] font-medium text-gray-600 uppercase tracking-wider">Andamento Storico Ricavi e EBITDA</span>
                                    <div class="tooltip-container">
                                        <i class="fa-solid fa-circle-info text-gray-400 text-xs cursor-help"></i>
                                        <div class="tooltip-content">Evoluzione triennale dei ricavi e dell'EBITDA. Valori in migliaia di euro.</div>
                                    </div>
                                </div>
                                <div id="revenueChart-legend" class="flex gap-4"></div>
                            </div>
                            <div class="relative h-[220px] sm:h-[280px]"><canvas id="revenueChart"></canvas></div>
                            <div class="ai-insight-box">
                                <div class="flex items-center gap-2 mb-2 pb-2 border-b border-purple/20">
                                    <span class="badge-ai bg-purple text-white text-[9px] font-bold px-1.5 py-0.5 uppercase tracking-wide">AI Insight</span>
                                </div>
                                <strong><i class="fa-solid fa-chart-line text-purple"></i> Crescita Accelerata:</strong> Ricavi +49% (€825k) con EBITDA +216% (€205k). Il margine EBITDA è passato dal 12.7% al 24.9% (+12.2pp), segnalando forte leva operativa. La crescita è organica, trainata da nuovi clienti e upselling.
                            </div>
                        </div>

                        <div class="widget-card widget-purple p-6">
                            <div class="flex justify-between items-center mb-5 pb-4 border-b border-gray-200">
                                <div class="flex items-center gap-2">
                                    <span class="text-[11px] font-medium text-gray-600 uppercase tracking-wider">Andamento Storico EBIT e Utile</span>
                                    <div class="tooltip-container">
                                        <i class="fa-solid fa-circle-info text-gray-400 text-xs cursor-help"></i>
                                        <div class="tooltip-content">Evoluzione triennale dell'utile operativo (EBIT) e dell'utile netto.</div>
                                    </div>
                                </div>
                                <div id="profitChart-legend" class="flex gap-4"></div>
                            </div>
                            <div class="relative h-[220px] sm:h-[280px]"><canvas id="profitChart"></canvas></div>
                            <div class="ai-insight-box">
                                <div class="flex items-center gap-2 mb-2 pb-2 border-b border-purple/20">
                                    <span class="badge-ai bg-purple text-white text-[9px] font-bold px-1.5 py-0.5 uppercase tracking-wide">AI Insight</span>
                                </div>
                                <strong><i class="fa-solid fa-coins text-purple"></i> Profittabilità Esplosa:</strong> EBIT +452% (€180k) e Utile Netto +4039% (€151k). Il margine netto è salito dallo 0.7% al 18.3%. La trasformazione da quasi break-even a forte profittabilità riflette il controllo dei costi e la scalabilità del modello.
                            </div>
                        </div>
                    </div>
                </div>


            </div>

            <div id="liquidity" class="view hidden">
                <div class="mb-6 sm:mb-10">
                    <h1 class="text-[18px] sm:text-[20px] font-medium text-primary tracking-tight">Liquidità e Flussi di Cassa</h1>
                </div>

                <!-- Working Capital Cycle -->
                <div class="mb-12 sm:mb-20">
                    <div class="flex items-center gap-2 mb-4">
                        <div class="text-[11px] font-medium text-gray-600 uppercase tracking-wider">Ciclo del Capitale Circolante</div>
                        <div class="tooltip-container">
                            <i class="fa-solid fa-circle-info text-gray-400 text-xs cursor-help"></i>
                            <div class="tooltip-content">DSO - DPO = giorni di finanziamento necessario. Minore è meglio.</div>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4 sm:gap-6">
                        <div class="widget-card widget-negative p-6 text-left">
                            <div class="widget-label mb-2">Giorni Incasso (DSO)</div>
                            <div class="widget-metric-large text-negative">-27%</div>
                            <div class="text-sm text-gray-500">157 giorni</div>
                            <div class="text-xs text-gray-400 mt-1">Target: 120 giorni</div>
                            <div class="widget-status-badge mt-2 bg-red-100 text-red-700 text-[9px]"><i class="fa-solid fa-triangle-exclamation"></i> Sopra target</div>
                        </div>
                        <div class="widget-card widget-purple p-6 text-left">
                            <div class="widget-label mb-2">Giorni Pagamento (DPO)</div>
                            <div class="widget-metric-large text-gray-500">0%</div>
                            <div class="text-sm text-gray-500">56 giorni</div>
                        </div>
                        <div class="widget-card widget-purple p-6 text-left">
                            <div class="widget-label mb-2">Ciclo Finanziario</div>
                            <div class="widget-metric-large text-positive">-29%</div>
                            <div class="text-sm text-gray-500">101 giorni</div>
                        </div>
                        <div class="widget-card widget-purple p-6 text-left">
                            <div class="widget-label mb-2">Cassa Liberabile</div>
                            <div class="widget-metric-large">€75k</div>
                            <div class="text-xs text-gray-500 mt-1">Con DSO a 120gg</div>
                        </div>
                    </div>
                    <div class="ai-insight-box">
                        <div class="flex items-center gap-2 mb-2 pb-2 border-b border-purple/20">
                            <span class="badge-ai bg-purple text-white text-[9px] font-bold px-1.5 py-0.5 uppercase tracking-wide">AI Insight</span>
                        </div>
                        <strong><i class="fa-solid fa-clock text-purple"></i> Efficienza Operativa:</strong> Il ciclo finanziario è sceso del 29% (da 142 a 101 giorni), ma rimane elevato. Il DSO a 157gg (vs target 120gg) congela €75k di cassa. Migliorare la gestione crediti per liberare liquidità.
                    </div>
                </div>

                <!-- Liquidity Ratios -->
                <div class="mb-12 sm:mb-20">
                    <div class="text-[11px] font-medium text-gray-600 uppercase tracking-wider mb-3">Indici di Liquidità - Andamento Storico</div>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
                        <!-- Current Ratio -->
                        <div class="widget-card widget-purple p-4 sm:p-6">
                            <div class="flex items-center gap-2 mb-4">
                                <span class="text-[11px] font-medium text-gray-600 uppercase tracking-wider">Current Ratio</span>
                                <div class="tooltip-container">
                                    <i class="fa-solid fa-circle-info text-gray-400 text-xs cursor-help"></i>
                                    <div class="tooltip-content">Attivo Circolante / Debiti a Breve. Misura la capacità di coprire i debiti a breve con attività correnti. Ottimale >1.5x.</div>
                                </div>
                            </div>
                            <div class="relative h-[180px] sm:h-[240px] mb-3"><canvas id="currentRatioChart"></canvas></div>
                            <div class="text-left">
                                <div class="widget-metric-large">1.52x</div>
                                <div class="flex items-center justify-center gap-2 mt-1">
                                    <span class="widget-change-positive">+65% vs 2024</span>
                                    <span class="widget-status-badge bg-positive/10 text-positive"><i class="fa-solid fa-check"></i> Accettabile</span>
                                </div>
                            </div>
                        </div>

                        <!-- Cash Ratio -->
                        <div class="widget-card widget-negative p-4 sm:p-6">
                            <div class="flex items-center gap-2 mb-4">
                                <span class="text-[11px] font-medium text-gray-600 uppercase tracking-wider">Cash Ratio</span>
                                <div class="tooltip-container">
                                    <i class="fa-solid fa-circle-info text-gray-400 text-xs cursor-help"></i>
                                    <div class="tooltip-content">Disponibilità Liquide / Debiti a Breve. Misura la copertura immediata con cassa. Ottimale >0.2x.</div>
                                </div>
                            </div>
                            <div class="relative h-[180px] sm:h-[240px] mb-3"><canvas id="cashRatioChart"></canvas></div>
                            <div class="text-left">
                                <div class="widget-metric-large text-negative">0.06x</div>
                                <div class="flex items-center justify-center gap-2 mt-1">
                                    <span class="widget-change-positive">+200% vs 2024</span>
                                    <span class="widget-status-badge bg-negative/10 text-negative"><i class="fa-solid fa-triangle-exclamation"></i> Critico</span>
                                </div>
                            </div>
                        </div>

                        <!-- Treasury Margin -->
                        <div class="widget-card widget-purple p-4 sm:p-6">
                            <div class="flex items-center gap-2 mb-4">
                                <span class="text-[11px] font-medium text-gray-600 uppercase tracking-wider">Margine di Tesoreria</span>
                                <div class="tooltip-container">
                                    <i class="fa-solid fa-circle-info text-gray-400 text-xs cursor-help"></i>
                                    <div class="tooltip-content">(Liquidità + Crediti) - Debiti a Breve. Misura stringente della capacità di pagamento. Positivo = solvibile.</div>
                                </div>
                            </div>
                            <div class="relative h-[180px] sm:h-[240px] mb-3"><canvas id="treasuryMarginChart"></canvas></div>
                            <div class="text-left">
                                <div class="widget-metric-large text-positive">€100k</div>
                                <div class="flex items-center justify-center gap-2 mt-1">
                                    <span class="widget-change-positive">da -€63k</span>
                                    <span class="widget-status-badge bg-positive/10 text-positive"><i class="fa-solid fa-check"></i> Positivo</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="ai-insight-box">
                        <div class="flex items-center gap-2 mb-2 pb-2 border-b border-purple/20">
                            <span class="badge-ai bg-purple text-white text-[9px] font-bold px-1.5 py-0.5 uppercase tracking-wide">AI Insight</span>
                        </div>
                        <strong><i class="fa-solid fa-droplet text-purple"></i> Liquidità:</strong> Il Current Ratio è migliorato al 1.52x (+65% vs 2024), ma il Cash Ratio rimane critico a 0.06x. L'azienda può coprire i debiti a breve con le attività correnti, ma ha scarsa liquidità immediata. Priorità: accelerare incassi e aumentare disponibilità liquide.
                    </div>
                </div>

                <!-- Cash Flow Waterfall -->
                <div class="mb-12 sm:mb-20">
                    <div class="flex items-center gap-2 mb-4">
                        <div class="text-[11px] font-medium text-gray-600 uppercase tracking-wider">Flusso di Cassa 2025</div>
                        <div class="tooltip-container">
                            <i class="fa-solid fa-circle-info text-gray-400 text-xs cursor-help"></i>
                            <div class="tooltip-content">Scomposizione del flusso di cassa: da utile netto a variazione liquidità attraverso gestione operativa, investimenti e finanziamenti.</div>
                        </div>
                    </div>
                    <div class="widget-card widget-purple p-6">
                        <div class="relative h-[280px] sm:h-[350px]"><canvas id="cashFlowWaterfallChart"></canvas></div>
                        <div class="mt-4 pt-4 border-t border-gray-200">
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-6 text-left">
                                <div>
                                    <div class="widget-label">Autofinanziamento</div>
                                    <div class="widget-metric-medium text-positive">€188.9k</div>
                                </div>
                                <div>
                                    <div class="widget-label">Δ Capitale Circolante</div>
                                    <div class="widget-metric-medium text-positive">+€100.6k</div>
                                </div>
                                <div>
                                    <div class="widget-label">Investimenti</div>
                                    <div class="widget-metric-medium text-negative">-€66.3k</div>
                                </div>
                                <div>
                                    <div class="widget-label">Δ Debiti Finanziari</div>
                                    <div class="widget-metric-medium text-negative">-€69.4k</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="ai-insight-box">
                        <div class="flex items-center gap-2 mb-2 pb-2 border-b border-purple/20">
                            <span class="badge-ai bg-purple text-white text-[9px] font-bold px-1.5 py-0.5 uppercase tracking-wide">AI Insight</span>
                        </div>
                        <strong><i class="fa-solid fa-arrow-trend-up text-purple"></i> Generazione Cassa:</strong> Il flusso operativo è positivo (€289k = €189k autofinanziamento + €100k Δ capitale circolante). I CAPEX (-€66k) e il rimborso debiti (-€69k) sono coperti dalla gestione corrente. La cassa finale è in aumento, segnalando buona salute finanziaria.
                    </div>
                </div>

                <!-- Cash Flow Trend -->
                <div class="mb-12 sm:mb-20">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-2">
                            <div class="text-[11px] font-medium text-gray-600 uppercase tracking-wider">Andamento Flusso di Cassa</div>
                            <div class="tooltip-container">
                                <i class="fa-solid fa-circle-info text-gray-400 text-xs cursor-help"></i>
                                <div class="tooltip-content">Cash Flow Operativo = Utile + Ammortamenti + Δ TFR. Mostra la capacità di generare liquidità dalla gestione corrente.</div>
                            </div>
                        </div>
                        <div id="cashFlowTrendChart-legend" class="flex gap-4"></div>
                    </div>
                    <div class="widget-card widget-purple p-6">
                        <div class="relative h-[220px] sm:h-[280px]"><canvas id="cashFlowTrendChart"></canvas></div>
                    </div>
                    <div class="ai-insight-box">
                        <div class="flex items-center gap-2 mb-2 pb-2 border-b border-purple/20">
                            <span class="badge-ai bg-purple text-white text-[9px] font-bold px-1.5 py-0.5 uppercase tracking-wide">AI Insight</span>
                        </div>
                        <strong><i class="fa-solid fa-money-bill-trend-up text-purple"></i> Trend Cash Flow:</strong> Il CFO è triplicato nel triennio (€62k → €189k, +205%). La liquidità 2024 è scesa a €7k per assorbimento del capitale circolante e investimenti, ma nel 2025 si è ripresa a €17k (+143%). La crescita del CFO garantisce capacità di autofinanziamento per futuri investimenti.
                    </div>
                </div>

                <!-- Debt Structure -->
                <div class="mb-12 sm:mb-20">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-2">
                            <div class="text-[11px] font-medium text-gray-600 uppercase tracking-wider">Struttura Debiti - Breve vs Lungo Termine</div>
                            <div class="tooltip-container">
                                <i class="fa-solid fa-circle-info text-gray-400 text-xs cursor-help"></i>
                                <div class="tooltip-content">Ripartizione dei debiti per scadenza. Troppi debiti a breve termine creano pressione sulla liquidità.</div>
                            </div>
                        </div>
                        <div id="debtStructureChart-legend" class="flex gap-4"></div>
                    </div>
                    <div class="grid grid-cols-1 lg:grid-cols-5 gap-4 sm:gap-6">
                        <div class="widget-card widget-purple p-4 sm:p-6 lg:col-span-2">
                            <div class="relative h-[220px] sm:h-[280px]"><canvas id="debtStructureChart"></canvas></div>
                        </div>
                        <div class="widget-card widget-purple p-4 sm:p-6 lg:col-span-3">
                            <div class="text-[11px] font-medium text-gray-600 uppercase tracking-wider mb-6">Dettaglio per Anno</div>
                            <div class="space-y-8">
                                <div>
                                    <div class="flex justify-between mb-1.5">
                                        <span class="text-[10px] font-medium text-gray-700">2023</span>
                                        <span class="text-[10px] text-gray-500">€430k totali</span>
                                    </div>
                                    <div class="flex h-6 overflow-hidden rounded">
                                        <div class="bg-purple flex items-center justify-center text-white text-[10px] font-medium" style="width: 53%">53% Breve</div>
                                        <div class="bg-zinc-500 flex items-center justify-center text-white text-[10px] font-medium" style="width: 47%">47% Lungo</div>
                                    </div>
                                </div>
                                <div>
                                    <div class="flex justify-between mb-1.5">
                                        <span class="text-[10px] font-medium text-gray-700">2024</span>
                                        <span class="text-[10px] text-gray-500">€575k totali</span>
                                    </div>
                                    <div class="flex h-6 overflow-hidden rounded">
                                        <div class="bg-purple flex items-center justify-center text-white text-[10px] font-medium" style="width: 68%"><i class="fa-solid fa-triangle-exclamation mr-1 text-[8px]"></i> 68% Breve</div>
                                        <div class="bg-zinc-500 flex items-center justify-center text-white text-[10px] font-medium" style="width: 32%">32% Lungo</div>
                                    </div>
                                </div>
                                <div>
                                    <div class="flex justify-between mb-1.5">
                                        <span class="text-[10px] font-medium text-gray-700">2025</span>
                                        <span class="text-[10px] text-gray-500">€506k totali (-12%)</span>
                                    </div>
                                    <div class="flex h-6 overflow-hidden rounded">
                                        <div class="bg-purple flex items-center justify-center text-white text-[10px] font-medium" style="width: 54%">54% Breve</div>
                                        <div class="bg-zinc-500 flex items-center justify-center text-white text-[10px] font-medium" style="width: 46%"><i class="fa-solid fa-check mr-1 text-[8px]"></i> 46% Lungo</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="ai-insight-box">
                        <div class="flex items-center gap-2 mb-2 pb-2 border-b border-purple/20">
                            <span class="badge-ai bg-purple text-white text-[9px] font-bold px-1.5 py-0.5 uppercase tracking-wide">AI Insight</span>
                        </div>
                        <strong><i class="fa-solid fa-scale-balanced text-purple"></i> Struttura Debiti:</strong> Mix migliorato di 14pp vs 2024 (da 68% a 54% breve termine). Il riequilibrio riduce la pressione sulla liquidità a breve. Debito totale -12% (€506k vs €575k).
                    </div>
                </div>

                <!-- Interest Coverage -->
                <div class="mb-12 sm:mb-20">
                    <div class="flex items-center gap-2 mb-4">
                        <div class="text-[11px] font-medium text-gray-600 uppercase tracking-wider">Copertura Oneri Finanziari (ICR)</div>
                        <div class="tooltip-container">
                            <i class="fa-solid fa-circle-info text-gray-400 text-xs cursor-help"></i>
                            <div class="tooltip-content">EBIT / Oneri Finanziari. Misura quante volte l'utile operativo copre gli interessi. Ottimale >3x.</div>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 lg:grid-cols-5 gap-4 sm:gap-6">
                        <div class="widget-card widget-purple p-4 sm:p-6 lg:col-span-2">
                            <div class="relative h-[220px] sm:h-[280px]"><canvas id="icrChart"></canvas></div>
                        </div>
                        <div class="widget-card widget-purple p-4 sm:p-6 lg:col-span-3">
                            <div class="widget-detail-header">Analisi Sostenibilità Debito</div>
                            <div>
                                <div class="widget-detail-row">
                                    <span class="widget-detail-label">EBIT 2025</span>
                                    <span class="widget-detail-value">€180.0k</span>
                                </div>
                                <div class="widget-detail-row">
                                    <span class="widget-detail-label">Oneri Finanziari 2025</span>
                                    <span class="widget-detail-value">€21.9k (-10% vs 2024)</span>
                                </div>
                                <div class="widget-detail-row">
                                    <span class="widget-detail-label">Interest Coverage Ratio</span>
                                    <span class="widget-detail-value">8.2x (+447% vs 2024)</span>
                                </div>
                                <div class="widget-detail-row">
                                    <span class="widget-detail-label">Costo Medio Debito</span>
                                    <span class="widget-detail-value">4.33%</span>
                                </div>
                                <div class="widget-detail-row">
                                    <span class="widget-detail-label">DSCR</span>
                                    <span class="widget-detail-value">9.06x</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="ai-insight-box">
                        <div class="flex items-center gap-2 mb-2 pb-2 border-b border-purple/20">
                            <span class="badge-ai bg-purple text-white text-[9px] font-bold px-1.5 py-0.5 uppercase tracking-wide">AI Insight</span>
                        </div>
                        <strong><i class="fa-solid fa-shield-halved text-purple"></i> Sostenibilità Debito:</strong> ICR da 1.5x (2024) a 8.2x (+447%). DSCR a 9.06x garantisce ampia capacità di servire il debito. Costo medio 4.33% sostenibile con EBIT di €180k.
                    </div>
                </div>


            </div>

            <!-- View: Reports (Bilanci) -->
            <div id="reports" class="view hidden">
                <div class="mb-6 sm:mb-10">
                    <h1 class="text-[18px] sm:text-[20px] font-medium text-primary tracking-tight">Bilanci e Conto Economico</h1>
                </div>

                <!-- Margine di Struttura -->
                <div class="mb-12 sm:mb-20">
                    <div class="flex items-center gap-2 mb-4">
                        <div class="text-[11px] font-medium text-gray-600 uppercase tracking-wider">Margine di Struttura</div>
                        <div class="tooltip-container">
                            <i class="fa-solid fa-circle-info text-gray-400 text-xs cursor-help"></i>
                            <div class="tooltip-content">Patrimonio Netto - Immobilizzazioni. Se positivo, le immobilizzazioni sono coperte da mezzi propri. Se negativo, sono finanziate anche con debiti.</div>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="widget-card widget-purple p-6 text-left">
                            <div class="widget-label mb-2">2023</div>
                            <div class="widget-metric-large">+€16k</div>
                            <div class="text-xs text-gray-500 mt-1">PN €162k > Immob. €146k</div>
                            <div class="widget-status-badge mt-2 bg-positive/10 text-positive"><i class="fa-solid fa-check"></i> Equilibrato</div>
                        </div>
                        <div class="widget-card widget-purple p-6 text-left">
                            <div class="widget-label mb-2">2024</div>
                            <div class="widget-metric-large">-€254k</div>
                            <div class="text-xs text-gray-500 mt-1">PN €182k < Immob. €436k</div>
                            <div class="widget-status-badge mt-2 bg-negative/10 text-negative"><i class="fa-solid fa-triangle-exclamation"></i> Investimenti</div>
                        </div>
                        <div class="widget-card widget-purple p-6 text-left">
                            <div class="widget-label mb-2">2025</div>
                            <div class="widget-metric-large">-€144k</div>
                            <div class="text-xs text-gray-500 mt-1">PN €333k < Immob. €477k</div>
                            <div class="widget-change-positive mt-1">+43% vs 2024</div>
                        </div>
                    </div>
                    <div class="ai-insight-box">
                        <div class="flex items-center gap-2 mb-2 pb-2 border-b border-purple/20">
                            <span class="badge-ai bg-purple text-white text-[9px] font-bold px-1.5 py-0.5 uppercase tracking-wide">AI Insight</span>
                        </div>
                        <strong><i class="fa-solid fa-scale-balanced text-purple"></i> Analisi Strutturale:</strong> Il margine di struttura è migliorato del 43% (da -€254k a -€144k) grazie all'aumento del PN (+83%). La struttura patrimoniale si sta riequilibrando, riducendo la dipendenza dal debito per finanziare le immobilizzazioni.
                    </div>
                </div>

                <!-- Conto Economico -->
                <div class="mb-12 sm:mb-20">
                    <div class="flex items-center gap-2 mb-4">
                        <div class="text-[11px] font-medium text-gray-600 uppercase tracking-wider">Conto Economico Comparativo</div>
                        <div class="tooltip-container">
                            <i class="fa-solid fa-circle-info text-gray-400 text-xs cursor-help"></i>
                            <div class="tooltip-content">Clicca sulle intestazioni delle colonne per ordinare la tabella. La variazione % è calcolata rispetto all'esercizio 2024.</div>
                        </div>
                    </div>
                    <div class="widget-card p-6 overflow-x-auto">
                        <table class="w-full text-sm border-collapse sortable-table" id="incomeTable">
                            <thead class="bg-gray-50 border-b border-gray-200">
                                <tr>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-700 text-[11px] uppercase tracking-wide" data-sort="string">Voce</th>
                                    <th class="px-4 py-3 text-right font-semibold text-gray-700 text-[11px] uppercase tracking-wide" data-sort="number">2023</th>
                                    <th class="px-4 py-3 text-right font-semibold text-gray-700 text-[11px] uppercase tracking-wide" data-sort="number">2024</th>
                                    <th class="px-4 py-3 text-right font-semibold text-gray-700 text-[11px] uppercase tracking-wide" data-sort="number">2025</th>
                                    <th class="px-4 py-3 text-right font-semibold text-gray-700 text-[11px] uppercase tracking-wide" data-sort="number">Var % vs 2024</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="font-semibold bg-gray-50 border-b border-gray-200">
                                    <td class="px-4 py-3">Ricavi</td>
                                    <td class="px-4 py-3 text-right" data-sort-value="526061">€526.061</td>
                                    <td class="px-4 py-3 text-right" data-sort-value="553475">€553.475</td>
                                    <td class="px-4 py-3 text-right" data-sort-value="824708">€824.708</td>
                                    <td class="px-4 py-3 text-right text-positive font-bold" data-sort-value="49">+49.0%</td>
                                </tr>
                                <tr class="border-b border-gray-200 hover:bg-purple-50">
                                    <td class="px-4 py-3 pl-6">Costi per Servizi</td>
                                    <td class="px-4 py-3 text-right" data-sort-value="-241142">(€241.142)</td>
                                    <td class="px-4 py-3 text-right" data-sort-value="-254242">(€254.242)</td>
                                    <td class="px-4 py-3 text-right" data-sort-value="-314464">(€314.464)</td>
                                    <td class="px-4 py-3 text-right text-negative" data-sort-value="23.7">+23.7%</td>
                                </tr>
                                <tr class="border-b border-gray-200 hover:bg-purple-50">
                                    <td class="px-4 py-3 pl-6">Costi del Personale</td>
                                    <td class="px-4 py-3 text-right" data-sort-value="-198004">(€198.004)</td>
                                    <td class="px-4 py-3 text-right" data-sort-value="-202405">(€202.405)</td>
                                    <td class="px-4 py-3 text-right" data-sort-value="-282113">(€282.113)</td>
                                    <td class="px-4 py-3 text-right text-negative" data-sort-value="39.4">+39.4%</td>
                                </tr>
                                <tr class="border-b border-gray-200 hover:bg-purple-50">
                                    <td class="px-4 py-3 pl-6">Altri Costi Operativi</td>
                                    <td class="px-4 py-3 text-right" data-sort-value="-53785">(€53.785)</td>
                                    <td class="px-4 py-3 text-right" data-sort-value="-31995">(€31.995)</td>
                                    <td class="px-4 py-3 text-right" data-sort-value="-29520">(€29.520)</td>
                                    <td class="px-4 py-3 text-right text-positive" data-sort-value="-7.7">-7.7%</td>
                                </tr>
                                <tr class="font-semibold bg-purple/5 border-b border-gray-200">
                                    <td class="px-4 py-3">EBITDA</td>
                                    <td class="px-4 py-3 text-right" data-sort-value="40039">€40.039</td>
                                    <td class="px-4 py-3 text-right" data-sort-value="64833">€64.833</td>
                                    <td class="px-4 py-3 text-right" data-sort-value="205335">€205.335</td>
                                    <td class="px-4 py-3 text-right text-positive font-bold" data-sort-value="216.7">+216.7%</td>
                                </tr>
                                <tr class="border-b border-gray-200 hover:bg-gray-50">
                                    <td class="px-4 py-3 pl-6">Ammortamenti</td>
                                    <td class="px-4 py-3 text-right" data-sort-value="-18921">(€18.921)</td>
                                    <td class="px-4 py-3 text-right" data-sort-value="-28687">(€28.687)</td>
                                    <td class="px-4 py-3 text-right" data-sort-value="-25621">(€25.621)</td>
                                    <td class="px-4 py-3 text-right text-positive" data-sort-value="-10.7">-10.7%</td>
                                </tr>
                                <tr class="font-semibold bg-purple/5 border-b border-gray-200">
                                    <td class="px-4 py-3">EBIT (Utile Operativo)</td>
                                    <td class="px-4 py-3 text-right" data-sort-value="16311">€16.311</td>
                                    <td class="px-4 py-3 text-right" data-sort-value="32605">€32.605</td>
                                    <td class="px-4 py-3 text-right" data-sort-value="180049">€180.049</td>
                                    <td class="px-4 py-3 text-right text-positive font-bold" data-sort-value="452.2">+452.2%</td>
                                </tr>
                                <tr class="border-b border-gray-200 hover:bg-gray-50">
                                    <td class="px-4 py-3 pl-6">Oneri Finanziari Netti</td>
                                    <td class="px-4 py-3 text-right" data-sort-value="-7774">(€7.774)</td>
                                    <td class="px-4 py-3 text-right" data-sort-value="-21104">(€21.104)</td>
                                    <td class="px-4 py-3 text-right" data-sort-value="-18948">(€18.948)</td>
                                    <td class="px-4 py-3 text-right text-positive" data-sort-value="-10.2">-10.2%</td>
                                </tr>
                                <tr class="border-b border-gray-200 hover:bg-gray-50">
                                    <td class="px-4 py-3 pl-6">Imposte</td>
                                    <td class="px-4 py-3 text-right" data-sort-value="-5871">(€5.871)</td>
                                    <td class="px-4 py-3 text-right" data-sort-value="-7861">(€7.861)</td>
                                    <td class="px-4 py-3 text-right" data-sort-value="-10424">(€10.424)</td>
                                    <td class="px-4 py-3 text-right text-negative" data-sort-value="32.6">+32.6%</td>
                                </tr>
                                <tr class="font-semibold bg-purple/5">
                                    <td class="px-4 py-3">Utile Netto</td>
                                    <td class="px-4 py-3 text-right" data-sort-value="2666">€2.666</td>
                                    <td class="px-4 py-3 text-right" data-sort-value="3640">€3.640</td>
                                    <td class="px-4 py-3 text-right" data-sort-value="150677">€150.677</td>
                                    <td class="px-4 py-3 text-right text-positive font-bold" data-sort-value="4039">+4039%</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="widget-card widget-purple p-6 mt-6">
                        <div class="h-[220px] sm:h-[280px]">
                            <canvas id="patrimonioLineChart"></canvas>
                        </div>
                        <div id="patrimonioLineChart-legend" class="flex flex-wrap justify-center gap-6 mt-4"></div>
                    </div>
                    <div class="ai-insight-box">
                        <div class="flex items-center gap-2 mb-2 pb-2 border-b border-purple/20">
                            <span class="badge-ai bg-purple text-white text-[9px] font-bold px-1.5 py-0.5 uppercase tracking-wide">AI Insight</span>
                        </div>
                        <strong><i class="fa-solid fa-chart-column text-purple"></i> Performance Economica:</strong> I ricavi 2025 sono cresciuti del 49% (€825k). L'utile netto è esploso del +4039% (€151k), evidenziando un'efficienza operativa eccezionale con margini significativamente migliorati. La gestione è passata da fragile (2024) a molto solida (2025).
                    </div>
                </div>

                <!-- Stato Patrimoniale -->
                <div class="mb-12 sm:mb-20">
                    <div class="flex items-center gap-2 mb-4">
                        <div class="text-[11px] font-medium text-gray-600 uppercase tracking-wider">Stato Patrimoniale Sintetico</div>
                        <div class="tooltip-container">
                            <i class="fa-solid fa-circle-info text-gray-400 text-xs cursor-help"></i>
                            <div class="tooltip-content">Situazione patrimoniale al termine di ciascun esercizio. Variazioni calcolate vs 2024.</div>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">
                        <div class="widget-card widget-purple p-4 sm:p-6">
                            <div class="widget-detail-header">Attivo</div>
                            <div>
                                <div class="widget-detail-row">
                                    <span class="widget-detail-label">Immobilizzazioni</span>
                                    <div class="text-right">
                                        <span class="widget-detail-value">€477.0k</span>
                                        <span class="ml-2 widget-text text-positive">+9% vs 2024</span>
                                    </div>
                                </div>
                                <div class="widget-detail-row">
                                    <span class="widget-detail-label">Crediti Commerciali</span>
                                    <div class="text-right">
                                        <span class="widget-detail-value">€355.0k</span>
                                        <span class="ml-2 widget-text text-positive">+10% vs 2024</span>
                                    </div>
                                </div>
                                <div class="widget-detail-row">
                                    <span class="widget-detail-label">Attività Finanziarie</span>
                                    <div class="text-right">
                                        <span class="widget-detail-value">€42.9k</span>
                                        <span class="ml-2 widget-text text-positive">+32% vs 2024</span>
                                    </div>
                                </div>
                                <div class="widget-detail-row">
                                    <span class="widget-detail-label">Disponibilità Liquide</span>
                                    <div class="text-right">
                                        <span class="widget-detail-value">€17.0k</span>
                                        <span class="ml-2 widget-text text-positive">+136% vs 2024</span>
                                    </div>
                                </div>
                                <div class="flex justify-between items-center font-bold">
                                    <span class="widget-label">Totale Attivo</span>
                                    <div class="text-right">
                                        <span class="widget-detail-value">€896.2k</span>
                                        <span class="ml-2 widget-text text-positive">+12% vs 2024</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="widget-card widget-purple p-6">
                            <div class="widget-detail-header">Passivo</div>
                            <div>
                                <div class="widget-detail-row">
                                    <span class="widget-detail-label">Patrimonio Netto</span>
                                    <div class="text-right">
                                        <span class="widget-detail-value">€332.7k</span>
                                        <span class="ml-2 widget-text text-positive">+83% vs 2024</span>
                                    </div>
                                </div>
                                <div class="widget-detail-row">
                                    <span class="widget-detail-label">TFR</span>
                                    <div class="text-right">
                                        <span class="widget-detail-value">€54.1k</span>
                                        <span class="ml-2 widget-text text-positive">+30% vs 2024</span>
                                    </div>
                                </div>
                                <div class="widget-detail-row">
                                    <span class="widget-detail-label">Debiti a Breve</span>
                                    <div class="text-right">
                                        <span class="widget-detail-value">€272.3k</span>
                                        <span class="ml-2 widget-text text-negative">-31% vs 2024</span>
                                    </div>
                                </div>
                                <div class="widget-detail-row">
                                    <span class="widget-detail-label">Debiti a Lungo</span>
                                    <div class="text-right">
                                        <span class="widget-detail-value">€233.7k</span>
                                        <span class="ml-2 widget-text text-positive">+28% vs 2024</span>
                                    </div>
                                </div>
                                <div class="flex justify-between items-center font-bold">
                                    <span class="widget-label">Totale Passivo</span>
                                    <div class="text-right">
                                        <span class="widget-detail-value">€896.2k</span>
                                        <span class="ml-2 widget-text text-positive">+12% vs 2024</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
                        <div class="widget-card widget-purple p-6">
                            <div class="widget-detail-header text-left">Attivo</div>
                            <div class="h-[200px]">
                                <canvas id="attivoDonutChart"></canvas>
                            </div>
                            <div id="attivoDonutChart-legend" class="flex flex-wrap justify-center gap-3 mt-4"></div>
                        </div>
                        <div class="widget-card widget-purple p-6">
                            <div class="widget-detail-header text-left">Passivo</div>
                            <div class="h-[200px]">
                                <canvas id="passivoDonutChart"></canvas>
                            </div>
                            <div id="passivoDonutChart-legend" class="flex flex-wrap justify-center gap-3 mt-4"></div>
                        </div>
                        <div class="widget-card widget-purple p-6">
                            <div class="widget-detail-header text-left">Costi di Produzione</div>
                            <div class="h-[200px]">
                                <canvas id="costiDonutChart"></canvas>
                            </div>
                            <div id="costiDonutChart-legend" class="flex flex-wrap justify-center gap-3 mt-4"></div>
                        </div>
                    </div>
                    <div class="ai-insight-box">
                        <div class="flex items-center gap-2 mb-2 pb-2 border-b border-purple/20">
                            <span class="badge-ai bg-purple text-white text-[9px] font-bold px-1.5 py-0.5 uppercase tracking-wide">AI Insight</span>
                        </div>
                        <strong><i class="fa-solid fa-building-columns text-purple"></i> Solidità Patrimoniale:</strong> Il patrimonio netto è cresciuto dell'83% (€333k), rafforzando la base finanziaria. I crediti commerciali (€355k) rappresentano il 43% dell'attivo circolante. Le immobilizzazioni (€477k) sono coperte al 70% dal PN, segnalando una struttura sana e equilibrata.
                    </div>
                </div>
            </div>

            <!-- View: Analysis -->
            <div id="analysis" class="view hidden">
                <div class="mb-6 sm:mb-10">
                    <h1 class="text-[18px] sm:text-[20px] font-medium text-primary tracking-tight">Analisi Avanzata</h1>
                </div>

                <!-- Break-Even Analysis -->
                <div class="mb-12 sm:mb-20">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-2">
                            <div class="text-[11px] font-medium text-gray-600 uppercase tracking-wider">Analisi Punto di Pareggio</div>
                            <div class="tooltip-container">
                                <i class="fa-solid fa-circle-info text-gray-400 text-xs cursor-help"></i>
                                <div class="tooltip-content">Break-Even Point = Costi Fissi / Margine di Contribuzione %. Indica i ricavi minimi per coprire tutti i costi.</div>
                            </div>
                        </div>
                        <div id="breakEvenChart-legend" class="flex gap-4"></div>
                    </div>
                    <div class="widget-card widget-purple p-6">
                        <div class="grid grid-cols-1 lg:grid-cols-5 gap-4 sm:gap-6">
                            <div class="relative h-[220px] sm:h-[280px] lg:col-span-3"><canvas id="breakEvenChart"></canvas></div>
                            <div class="lg:col-span-2">
                                <div class="text-[11px] font-medium text-gray-600 uppercase tracking-wider mb-4">Struttura Costi e BEP</div>
                                <div class="space-y-3">
                                    <div class="flex justify-between items-center pb-3 border-b border-gray-200">
                                        <span class="text-xs text-gray-600">Costi Fissi Stimati</span>
                                        <span class="text-sm font-bold text-gray-700">€350k</span>
                                    </div>
                                    <div class="flex justify-between items-center pb-3 border-b border-gray-200">
                                        <span class="text-xs text-gray-600">Costi Variabili</span>
                                        <span class="text-sm font-bold text-gray-700">€302k</span>
                                    </div>
                                    <div class="flex justify-between items-center pb-3 border-b border-gray-200">
                                        <span class="text-xs text-gray-600">Margine di Contribuzione %</span>
                                        <span class="text-sm font-bold text-gray-700">63.4%</span>
                                    </div>
                                    <div class="flex justify-between items-center pb-3 border-b border-gray-200">
                                        <span class="text-xs text-gray-600">Punto di Pareggio</span>
                                        <span class="font-bold text-xl text-primary">€552k</span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-xs text-gray-600">Margine di Sicurezza</span>
                                        <span class="font-semibold text-positive">33%</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="ai-insight-box">
                            <div class="flex items-center gap-2 mb-2 pb-2 border-b border-purple/20">
                                <span class="badge-ai bg-purple text-white text-[9px] font-bold px-1.5 py-0.5 uppercase tracking-wide">AI Insight</span>
                            </div>
                            <strong><i class="fa-solid fa-check text-purple"></i> Solidità:</strong> I ricavi possono scendere del 33% (€273k) prima di entrare in perdita.
                        </div>
                    </div>
                </div>

                <!-- Produttività Personale -->
                <div class="mb-12 sm:mb-20">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-2">
                            <div class="text-[11px] font-medium text-gray-600 uppercase tracking-wider">Produttività del Personale</div>
                            <div class="tooltip-container">
                                <i class="fa-solid fa-circle-info text-gray-400 text-xs cursor-help"></i>
                                <div class="tooltip-content">Metriche di efficienza per dipendente. Valori più alti indicano maggiore produttività.</div>
                            </div>
                        </div>
                        <div id="productivityChart-legend" class="flex gap-4"></div>
                    </div>
                    <div class="widget-card widget-purple p-6">
                        <div class="grid grid-cols-1 lg:grid-cols-5 gap-4 sm:gap-6 mb-4">
                            <div class="lg:col-span-3">
                                <div class="relative h-[220px] sm:h-[280px]"><canvas id="productivityChart"></canvas></div>
                            </div>
                            <div class="lg:col-span-2">
                                <div class="widget-detail-header">Metriche di Efficienza</div>
                                <div class="space-y-3">
                                    <div class="widget-detail-row">
                                        <span class="widget-detail-label">Ricavi / Costo Personale</span>
                                        <div class="text-right">
                                            <span class="widget-detail-value">2.92x</span>
                                            <span class="ml-2 widget-text text-positive">+7% vs 2024</span>
                                        </div>
                                    </div>
                                    <div class="widget-detail-row">
                                        <span class="widget-detail-label">Valore Aggiunto / Personale</span>
                                        <div class="text-right">
                                            <span class="widget-detail-value">€100k</span>
                                            <span class="ml-2 widget-text text-positive">+64% vs 2024</span>
                                        </div>
                                    </div>
                                    <div class="widget-detail-row">
                                        <span class="widget-detail-label">EBITDA / Personale</span>
                                        <div class="text-right">
                                            <span class="widget-detail-value">€73k</span>
                                            <span class="ml-2 widget-text text-positive">+127% vs 2024</span>
                                        </div>
                                    </div>
                                    <div class="widget-detail-row">
                                        <span class="widget-detail-label">Costo Medio Dipendente</span>
                                        <div class="text-right">
                                            <span class="widget-detail-value">€47k</span>
                                            <span class="ml-2 widget-text text-gray-600">+39% vs 2024</span>
                                        </div>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="widget-detail-label">N. Dipendenti (stimato)</span>
                                        <span class="widget-detail-value">~12</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="ai-insight-box">
                            <div class="flex items-center gap-2 mb-2 pb-2 border-b border-purple/20">
                                <span class="badge-ai bg-purple text-white text-[9px] font-bold px-1.5 py-0.5 uppercase tracking-wide">AI Insight</span>
                            </div>
                            <strong><i class="fa-solid fa-chart-line text-purple"></i> Performance:</strong> L'EBITDA per dipendente è aumentato del 127% (€73k), segnalando un'efficienza operativa eccezionale. Il team attuale massimizza il valore generato.
                        </div>
                    </div>
                </div>

                <!-- CAPEX Analysis -->
                <div class="mb-12 sm:mb-20">
                    <div class="flex items-center gap-2 mb-4">
                        <div class="text-[11px] font-medium text-gray-600 uppercase tracking-wider">Analisi Investimenti (CAPEX)</div>
                        <div class="tooltip-container">
                            <i class="fa-solid fa-circle-info text-gray-400 text-xs cursor-help"></i>
                            <div class="tooltip-content">Capital Expenditure. Investimenti in immobilizzazioni = Δ Immobilizzazioni + Ammortamenti.</div>
                        </div>
                    </div>
                    <div class="widget-card widget-purple p-6">
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">
                            <div class="relative h-[220px] sm:h-[280px]"><canvas id="capexChart"></canvas></div>
                            <div>
                                <div class="widget-label-medium mb-4">Dettaglio Investimenti</div>
                                <div class="space-y-4">
                                    <div class="p-3 bg-gray-50 border border-gray-200">
                                        <div class="flex justify-between items-center mb-2">
                                            <span class="widget-label-medium">2023→2024</span>
                                            <span class="widget-metric-small">€319k</span>
                                        </div>
                                        <div class="widget-text text-gray-500">Δ Immob. €291k + Ammort. €29k</div>
                                        <div class="widget-change-negative mt-1">Ciclo investimento pesante</div>
                                    </div>
                                    <div class="p-3 bg-gray-50 border border-gray-200">
                                        <div class="flex justify-between items-center mb-2">
                                            <span class="widget-label-medium">2024→2025</span>
                                            <span class="widget-metric-small">€66k</span>
                                        </div>
                                        <div class="widget-text text-gray-500">Δ Immob. €41k + Ammort. €26k</div>
                                        <div class="widget-change-positive mt-1">-79% vs periodo precedente</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="ai-insight-box">
                            <div class="flex items-center gap-2 mb-2 pb-2 border-b border-purple/20">
                                <span class="badge-ai bg-purple text-white text-[9px] font-bold px-1.5 py-0.5 uppercase tracking-wide">AI Insight</span>
                            </div>
                            <strong><i class="fa-solid fa-check text-purple"></i> Strategia:</strong> I CAPEX sono calati del 79% vs 2024, segnalando la fine del ciclo di investimenti. Prossimo step: ottimizzare l'efficienza degli asset acquisiti.
                        </div>
                    </div>
                </div>

                <!-- DuPont Analysis -->
                <div class="mb-12 sm:mb-20">
                    <div class="flex items-center gap-2 mb-4">
                        <div class="text-[11px] font-medium text-gray-600 uppercase tracking-wider">Scomposizione DuPont del ROE</div>
                        <div class="tooltip-container">
                            <i class="fa-solid fa-circle-info text-gray-400 text-xs cursor-help"></i>
                            <div class="tooltip-content">ROE = Margine Netto × Rotazione Attivo × Leva Finanziaria. Permette di capire da quale leva proviene il rendimento.</div>
                        </div>
                    </div>
                    <div class="widget-card widget-purple p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                            <div class="bg-gray-50  border border-gray-200 p-6">
                                <div class="flex items-center justify-center gap-2 mb-2">
                                    <span class="text-[11px] text-gray-500 uppercase font-semibold">Margine Netto</span>
                                    <div class="tooltip-container">
                                        <i class="fa-solid fa-circle-info text-gray-400 text-[10px] cursor-help"></i>
                                        <div class="tooltip-content">Utile Netto / Ricavi. Quanto profitto rimane per ogni euro di vendita.</div>
                                    </div>
                                </div>
                                <div class="relative h-[200px]"><canvas id="dupontMarginChart"></canvas></div>
                                <div class="text-left mt-2">
                                    <div class="text-xl font-semibold text-primary">18.3%</div>
                                    <div class="text-xs text-positive">+17.6pp vs 2024</div>
                                </div>
                            </div>
                            <div class="bg-gray-50  border border-gray-200 p-6">
                                <div class="flex items-center justify-center gap-2 mb-2">
                                    <span class="text-[11px] text-gray-500 uppercase font-semibold">Rotazione Attivo</span>
                                    <div class="tooltip-container">
                                        <i class="fa-solid fa-circle-info text-gray-400 text-[10px] cursor-help"></i>
                                        <div class="tooltip-content">Ricavi / Totale Attivo. Efficienza nell'utilizzo delle risorse.</div>
                                    </div>
                                </div>
                                <div class="relative h-[200px]"><canvas id="dupontTurnoverChart"></canvas></div>
                                <div class="text-left mt-2">
                                    <div class="text-xl font-semibold text-primary">0.92x</div>
                                    <div class="text-xs text-positive">+33% vs 2024</div>
                                </div>
                            </div>
                            <div class="bg-gray-50  border border-gray-200 p-6">
                                <div class="flex items-center justify-center gap-2 mb-2">
                                    <span class="text-[11px] text-gray-500 uppercase font-semibold">Leva Finanziaria</span>
                                    <div class="tooltip-container">
                                        <i class="fa-solid fa-circle-info text-gray-400 text-[10px] cursor-help"></i>
                                        <div class="tooltip-content">Totale Attivo / Patrimonio Netto. Grado di indebitamento.</div>
                                    </div>
                                </div>
                                <div class="relative h-[200px]"><canvas id="dupontLeverageChart"></canvas></div>
                                <div class="text-left mt-2">
                                    <div class="text-xl font-semibold text-primary">2.69x</div>
                                    <div class="text-xs text-positive">-39% vs 2024</div>
                                </div>
                            </div>
                            <div class="bg-gray-50  border border-gray-200 p-6">
                                <div class="flex items-center justify-center gap-2 mb-2">
                                    <span class="text-[11px] text-gray-500 uppercase font-semibold">ROA</span>
                                    <div class="tooltip-container">
                                        <i class="fa-solid fa-circle-info text-gray-400 text-[10px] cursor-help"></i>
                                        <div class="tooltip-content">Return on Assets = Utile Netto / Totale Attivo. Redditività del capitale investito.</div>
                                    </div>
                                </div>
                                <div class="relative h-[200px]"><canvas id="roaChart"></canvas></div>
                                <div class="text-left mt-2">
                                    <div class="text-xl font-semibold text-primary">16.8%</div>
                                    <div class="text-xs text-positive">+16.4pp vs 2024</div>
                                </div>
                            </div>
                        </div>
                        <div class="ai-insight-box">
                            <div class="flex items-center gap-2 mb-2 pb-2 border-b border-purple/20">
                                <span class="badge-ai bg-purple text-white text-[9px] font-bold px-1.5 py-0.5 uppercase tracking-wide">AI Insight</span>
                            </div>
                            <strong>Insight:</strong> Il ROE 2025 è trainato dal margine netto (+17.6pp vs 2024), non dalla leva (in calo). Crescita sana e sostenibile.
                        </div>
                    </div>
                </div>
            </div>

            <!-- View: Risk & Scoring -->
            <div id="risk" class="view hidden">
                <div class="mb-10">
                    <h1 class="text-[18px] sm:text-[20px] font-medium text-primary tracking-tight">Analisi Rischio e Scoring</h1>
                </div>

                <!-- Action Items -->
                <div class="mb-12 sm:mb-20">
                    <div class="text-[11px] font-medium text-gray-600 uppercase tracking-wider mb-3">Azioni Prioritarie</div>
                    <div class="widget-card widget-purple p-6">
                        <div class="space-y-4">
                            <div class="flex items-center gap-6 p-4 bg-red-50 border border-red-300">
                                <div class="bg-red-500 text-white text-xs font-bold px-2 py-1">P1</div>
                                <div>
                                    <div class="font-semibold text-primary">Migliorare Cash Ratio</div>
                                    <div class="text-xs text-gray-600">Target: >0.2x (attuale 0.06x). Azioni: accelerare incassi, factoring, linea RBF. Con DSO a 120gg si liberano €75k.</div>
                                </div>
                            </div>
                            <div class="flex items-center gap-6 p-4 bg-red-50/60 border border-red-200">
                                <div class="bg-red-400 text-white text-xs font-bold px-2 py-1">P2</div>
                                <div>
                                    <div class="font-semibold text-primary">Ridurre DSO a 120 giorni</div>
                                    <div class="text-xs text-gray-600">Da 157gg attuali (-27% vs 2024 ma ancora alto). Azioni: sconti pagamento anticipato, rinegoziazione termini, credit management.</div>
                                </div>
                            </div>
                            <div class="flex items-center gap-6 p-4 bg-purple/3 border border-purple/15">
                                <div class="bg-purple/60 text-white text-xs font-bold px-2 py-1">P3</div>
                                <div>
                                    <div class="font-semibold text-primary">Consolidare margini 22-25% EBITDA</div>
                                    <div class="text-xs text-gray-600">Validare sostenibilità del 24.9% raggiunto. Scenari: diversificazione clienti, protezione volume, controllo costi.</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Risk Matrix -->
                <div class="mb-12 sm:mb-20">
                    <div class="text-[11px] font-medium text-gray-600 uppercase tracking-wider mb-3">Matrice dei Rischi</div>
                    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
                        <div class="widget-card widget-purple p-6">
                            <div class="flex items-center gap-2.5 mb-3 pb-2.5 border-b border-gray-200">
                                <span class="risk-badge text-[9px] font-bold px-1.5 py-0.5 uppercase">Basso</span>
                                <span class="text-xs font-semibold text-primary uppercase">Rischio Operativo</span>
                            </div>
                            <div class="text-[13px] leading-relaxed text-gray-700">
                                <ul class="space-y-1.5">
                                    <li class="pl-3.5 relative before:content-['✓'] before:absolute before:left-0 before:text-purple">Margine EBITDA 24.9% (+13.2pp vs 2024)</li>
                                    <li class="pl-3.5 relative before:content-['✓'] before:absolute before:left-0 before:text-purple">Break-even con margine sicurezza 33%</li>
                                    <li class="pl-3.5 relative before:content-['✓'] before:absolute before:left-0 before:text-purple">Leva operativa controllata</li>
                                </ul>
                            </div>
                        </div>
                        <div class="widget-card widget-purple p-6">
                            <div class="flex items-center gap-2.5 mb-3 pb-2.5 border-b border-gray-200">
                                <span class="risk-badge text-[9px] font-bold px-1.5 py-0.5 uppercase">Basso</span>
                                <span class="text-xs font-semibold text-primary uppercase">Rischio Finanziario</span>
                            </div>
                            <div class="text-[13px] leading-relaxed text-gray-700">
                                <ul class="space-y-1.5">
                                    <li class="pl-3.5 relative before:content-['✓'] before:absolute before:left-0 before:text-purple">D/E 1.52x (-52% vs 2024)</li>
                                    <li class="pl-3.5 relative before:content-['✓'] before:absolute before:left-0 before:text-purple">ICR 8.2x (+447% vs 2024)</li>
                                    <li class="pl-3.5 relative before:content-['✓'] before:absolute before:left-0 before:text-purple">Struttura debiti riequilibrata</li>
                                </ul>
                            </div>
                        </div>
                        <div class="widget-card widget-purple p-6">
                            <div class="flex items-center gap-2.5 mb-3 pb-2.5 border-b border-gray-200">
                                <span class="risk-badge text-[9px] font-bold px-1.5 py-0.5 uppercase">Medio</span>
                                <span class="text-xs font-semibold text-primary uppercase">Rischio Liquidità</span>
                            </div>
                            <div class="text-[13px] leading-relaxed text-gray-700">
                                <ul class="space-y-1.5">
                                    <li class="pl-3.5 relative before:content-['⚠'] before:absolute before:left-0 before:text-red-500">Cash Ratio 0.06x (+200% vs 2024 ma critico)</li>
                                    <li class="pl-3.5 relative before:content-['⚠'] before:absolute before:left-0 before:text-red-500">DSO 157gg (-27% vs 2024, target 120gg)</li>
                                    <li class="pl-3.5 relative before:content-['✓'] before:absolute before:left-0 before:text-purple">Current Ratio 1.52x (+65% vs 2024)</li>
                                </ul>
                            </div>
                        </div>
                        <div class="widget-card widget-purple p-6">
                            <div class="flex items-center gap-2.5 mb-3 pb-2.5 border-b border-gray-200">
                                <span class="risk-badge text-[9px] font-bold px-1.5 py-0.5 uppercase">Info</span>
                                <span class="text-xs font-semibold text-primary uppercase">Rischio Concentrazione</span>
                            </div>
                            <div class="text-[13px] leading-relaxed text-gray-700">
                                <ul class="space-y-1.5">
                                    <li class="pl-3.5 relative before:content-['?'] before:absolute before:left-0 before:text-purple">Dipendenza top client da verificare</li>
                                    <li class="pl-3.5 relative before:content-['?'] before:absolute before:left-0 before:text-purple">Mix settoriale da analizzare</li>
                                    <li class="pl-3.5 relative before:content-['→'] before:absolute before:left-0 before:text-purple">Dati non disponibili nei bilanci</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Z-Score Altman -->
                <div class="mb-12 sm:mb-20">
                    <div class="flex items-center gap-2 mb-4">
                        <div class="text-[11px] font-medium text-gray-600 uppercase tracking-wider">Z-Score Altman (Rischio Insolvenza)</div>
                        <div class="tooltip-container">
                            <i class="fa-solid fa-circle-info text-gray-400 text-xs cursor-help"></i>
                            <div class="tooltip-content">Modello predittivo di insolvenza. Formula per PMI private: Z = 0.717X1 + 0.847X2 + 3.107X3 + 0.420X4 + 0.998X5.</div>
                        </div>
                    </div>
                    <div class="widget-card widget-purple p-6">
                        <!-- Legend Section -->
                        <div class="mb-10 p-6 bg-gray-50  border border-gray-200">
                            <div class="widget-label mb-3">Zone di Rischio</div>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div class="flex items-center gap-2 px-3 py-2 bg-gray-100 border border-gray-300">
                                    <span class="w-2.5 h-2.5 bg-gray-700"></span>
                                    <span class="widget-label text-gray-700">Zona Sicura (>2.9)</span>
                                </div>
                                <div class="flex items-center gap-2 px-3 py-2 bg-gray-100 border border-gray-300">
                                    <span class="w-2.5 h-2.5 bg-gray-500"></span>
                                    <span class="widget-label text-gray-700">Zona Grigia (1.23-2.9)</span>
                                </div>
                                <div class="flex items-center gap-2 px-3 py-2 bg-gray-100 border border-gray-300">
                                    <span class="w-2.5 h-2.5 bg-gray-400"></span>
                                    <span class="widget-label text-gray-700">Zona Rischio (<1.23)</span>
                                </div>
                            </div>
                        </div>

                        <!-- Main Z-Score Section -->
                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6 mb-10">
                            <!-- Chart - larger -->
                            <div class="lg:col-span-2">
                                <div class="relative h-[250px] sm:h-[320px] bg-gray-50 p-4 sm:p-6"><canvas id="zscoreChart"></canvas></div>
                            </div>

                            <!-- Z-Score Value - compact -->
                            <div>
                                <div class="border border-gray-200 p-5 mb-3">
                                    <div class="text-xs text-gray-500 uppercase tracking-wide mb-2">Z-Score 2025</div>
                                    <div class="text-2xl font-semibold text-primary mb-1">3.18</div>
                                    <div class="text-xs text-positive font-semibold mb-3">+124% vs 2024</div>
                                    <div class="inline-block px-2 py-1 bg-positive/10 text-positive font-semibold text-[10px] border border-positive/30">ZONA SICURA</div>
                                </div>

                                <!-- Components compact -->
                                <div class="text-[10px] font-semibold text-gray-700 uppercase tracking-wide mb-2">Componenti</div>
                                <div class="space-y-0">
                                    <div class="flex justify-between items-center py-1.5 border-b border-gray-100">
                                        <span class="text-[10px] text-gray-600">X1</span>
                                        <span class="font-semibold text-[10px]">0.11</span>
                                    </div>
                                    <div class="flex justify-between items-center py-1.5 border-b border-gray-100">
                                        <span class="text-[10px] text-gray-600">X2</span>
                                        <span class="font-semibold text-[10px]">0.06</span>
                                    </div>
                                    <div class="flex justify-between items-center py-1.5 border-b border-gray-100">
                                        <span class="text-[10px] text-gray-600">X3</span>
                                        <span class="font-semibold text-[10px]">0.62</span>
                                    </div>
                                    <div class="flex justify-between items-center py-1.5 border-b border-gray-100">
                                        <span class="text-[10px] text-gray-600">X4</span>
                                        <span class="font-semibold text-[10px]">0.25</span>
                                    </div>
                                    <div class="flex justify-between items-center py-1.5">
                                        <span class="text-[10px] text-gray-600">X5</span>
                                        <span class="font-semibold text-[10px]">0.92</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="ai-insight-box">
                            <div class="flex items-center gap-2 mb-2 pb-2 border-b border-purple/20">
                                <span class="badge-ai bg-purple text-white text-[9px] font-bold px-1.5 py-0.5 uppercase tracking-wide">AI Insight</span>
                            </div>
                            <strong><i class="fa-solid fa-shield-check text-purple"></i> Solidità Finanziaria:</strong> Il punteggio Z-Score di 3.18 posiziona l'azienda in "ZONA SICURA". La probabilità di insolvenza a 2 anni è <2%. I fondamentali sono sani e sostenibili.
                        </div>
                    </div>
                </div>
            </div>

            <!-- View: Documents -->
            <div id="documents" class="view hidden">
                <div class="mb-10">
                    <h1 class="text-[18px] sm:text-[20px] font-medium text-primary tracking-tight">Archivio Documenti</h1>
                </div>

                <div class="mb-12 sm:mb-20">
                    <div class="flex items-center gap-2 mb-4">
                        <div class="text-[11px] font-medium text-gray-600 uppercase tracking-wider">Bilanci Utilizzati per l'Analisi</div>
                        <div class="tooltip-container">
                            <i class="fa-solid fa-circle-info text-gray-400 text-xs cursor-help"></i>
                            <div class="tooltip-content">Documenti finanziari ufficiali depositati. Clicca sulle intestazioni per ordinare la tabella.</div>
                        </div>
                    </div>
                    <div class="widget-card p-6 overflow-x-auto">
                        <table class="w-full text-sm border-collapse sortable-table" id="documentsTable">
                            <thead class="bg-gray-50 border-b border-gray-200">
                                <tr>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-700 text-[11px] uppercase tracking-wide" data-sort="string">Nome File</th>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-700 text-[11px] uppercase tracking-wide" data-sort="string">Tipo</th>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-700 text-[11px] uppercase tracking-wide" data-sort="string">Esercizio</th>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-700 text-[11px] uppercase tracking-wide" data-sort="string">Data Chiusura</th>
                                    <th class="px-4 py-3 text-center font-semibold text-gray-700 text-[11px] uppercase tracking-wide">Azioni</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="border-b border-gray-200 hover:bg-gray-50">
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-2">
                                            <i class="fa-regular fa-file-pdf text-purple"></i>
                                            <a href="docs/bilanci/IT04572610-2025-Esercizio-1-101239144.pdf" target="_blank" class="font-medium text-purple hover:underline">IT04572610-2025-Esercizio-1.pdf</a>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">Bilancio CEE</td>
                                    <td class="px-4 py-3">2025</td>
                                    <td class="px-4 py-3">31/05/2025</td>
                                    <td class="px-4 py-3 text-center">
                                        <a href="docs/bilanci/IT04572610-2025-Esercizio-1-101239144.pdf" download class="action-btn inline-flex items-center justify-center"><i class="fa-solid fa-download"></i></a>
                                    </td>
                                </tr>
                                <tr class="border-b border-gray-200 hover:bg-gray-50">
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-2">
                                            <i class="fa-regular fa-file-pdf text-purple"></i>
                                            <a href="docs/bilanci/IT04572610-2024-Esercizio-2-101239144.pdf" target="_blank" class="font-medium text-purple hover:underline">IT04572610-2024-Esercizio-2.pdf</a>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">Bilancio CEE</td>
                                    <td class="px-4 py-3">2024</td>
                                    <td class="px-4 py-3">31/05/2024</td>
                                    <td class="px-4 py-3 text-center">
                                        <a href="docs/bilanci/IT04572610-2024-Esercizio-2-101239144.pdf" download class="action-btn inline-flex items-center justify-center"><i class="fa-solid fa-download"></i></a>
                                    </td>
                                </tr>
                                <tr class="border-b border-gray-200 hover:bg-gray-50">
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-2">
                                            <i class="fa-regular fa-file-pdf text-purple"></i>
                                            <a href="docs/bilanci/IT04572610-2023-Esercizio-3-101239144.pdf" target="_blank" class="font-medium text-purple hover:underline">IT04572610-2023-Esercizio-3.pdf</a>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">Bilancio CEE</td>
                                    <td class="px-4 py-3">2023</td>
                                    <td class="px-4 py-3">31/05/2023</td>
                                    <td class="px-4 py-3 text-center">
                                        <a href="docs/bilanci/IT04572610-2023-Esercizio-3-101239144.pdf" download class="action-btn inline-flex items-center justify-center"><i class="fa-solid fa-download"></i></a>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="mb-12 sm:mb-20">
                    <div class="bg-gradient-to-br from-gray-100 to-gray-50 border border-gray-300  p-6">
                        <div class="flex items-center gap-2.5 mb-3">
                            <i class="fa-solid fa-circle-info text-gray-500 text-lg"></i>
                            <span class="text-xs font-medium text-primary">Informazioni sull'Archivio</span>
                        </div>
                        <div class="text-sm leading-relaxed text-gray-700">
                            <p class="mb-2">I bilanci sono estratti dal sistema informativo aziendale e rappresentano le dichiarazioni finanziarie ufficiali depositate.</p>
                            <p><strong>Periodo di Copertura:</strong> Esercizi chiusi al 31 maggio 2023, 2024, 2025</p>
                            <p><strong>Formato:</strong> PDF conformi alla tassonomia XBRL itcc-ci-2018-11-04</p>
                            <p><strong>Note:</strong> Nel 2023 la società era denominata "Tessari Associati SRL", successivamente rinominata in "Perspect S.R.L."</p>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script src="assets/js/app.js"></script>
</body>
</html>
