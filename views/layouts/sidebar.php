    <!-- Sidebar -->
    <div id="sidebar" class="w-[242px] bg-white border-r border-gray-200 fixed h-screen left-0 top-[60px] z-40 overflow-y-auto flex flex-col transform -translate-x-full md:translate-x-0 transition-transform duration-300">
        <div class="px-4 py-6 flex-1">
            <button class="w-full flex items-center text-[13px] font-semibold text-gray-400 px-2 py-2 pb-4 cursor-not-allowed transition-colors duration-200 border-b border-gray-200 btn-reset" disabled>
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-wallet text-[12px]"></i>
                    <span class="text-left">ETF Portfolio</span>
                </div>
            </button>
            <button class="w-full flex items-center text-[13px] font-semibold text-purple-600 px-2 py-2 pt-4 group transition-colors duration-200 btn-reset" data-accordion-toggle="portfolioMenu" onclick="toggleAccordion('portfolioMenu')">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-chart-line text-[12px] text-purple-600 transition-colors duration-200"></i>
                    <span class="text-purple-600 transition-colors duration-200 text-left">Portfolio Manager</span>
                </div>
                <i class="accordion-icon fa-solid fa-chevron-down text-[10px] text-purple-600 transition-transform duration-200 rotate-180 ml-auto"></i>
            </button>
            <div id="portfolioMenu" class="accordion-content mt-2">
            <div class="ml-3 border-l border-gray-200 space-y-1">
                <div class="nav-item active flex items-center gap-2.5 pl-4 py-1.5 cursor-pointer text-[13px] text-purple-600 font-medium transition-colors duration-200 hover:text-purple-600" onclick="showView('holdings'); toggleSidebar()">
                    <i class="fa-solid fa-list text-[11px] text-current"></i>
                    <span>Holdings</span>
                </div>
                <div class="nav-item flex items-center gap-2.5 pl-4 py-1.5 cursor-pointer text-[13px] text-gray-600 transition-colors duration-200 hover:text-purple-600" onclick="showView('performance'); toggleSidebar()">
                        <i class="fa-solid fa-chart-area text-[11px] text-current"></i>
                        <span>Performance & Flussi</span>
                    </div>
                    <div class="nav-item flex items-center gap-2.5 pl-4 py-1.5 cursor-pointer text-[13px] text-gray-600 transition-colors duration-200 hover:text-purple-600" onclick="showView('technical'); toggleSidebar()">
                        <i class="fa-solid fa-magnifying-glass-chart text-[11px] text-current"></i>
                        <span>Analisi Tecnica</span>
                    </div>
                    <div class="nav-item flex items-center gap-2.5 pl-4 py-1.5 cursor-pointer text-[13px] text-gray-600 transition-colors duration-200 hover:text-purple-600" onclick="showView('charts'); toggleSidebar()">
                        <i class="fa-solid fa-chart-line text-[11px] text-current"></i>
                        <span>Grafici Storici</span>
                    </div>
                    <div class="nav-item flex items-center gap-2.5 pl-4 py-1.5 cursor-pointer text-[13px] text-gray-600 transition-colors duration-200 hover:text-purple-600" onclick="showView('dividends'); toggleSidebar()">
                        <i class="fa-solid fa-gift text-[11px] text-current"></i>
                        <span>Dividendi</span>
                    </div>
                    <div class="nav-item flex items-center gap-2.5 pl-4 py-1.5 cursor-pointer text-[13px] text-gray-600 transition-colors duration-200 hover:text-purple-600" onclick="showView('recommendations'); toggleSidebar()">
                        <i class="fa-solid fa-lightbulb text-[11px] text-current"></i>
                        <span>Raccomandazioni</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div id="mainContent" class="ml-0 md:ml-[242px] flex-1 flex flex-col overflow-hidden">
        <!-- Content -->
        <div class="flex-1 overflow-y-auto p-4 sm:p-6">
