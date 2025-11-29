            <div id="dashboard" class="view">
                <div class="mb-6 sm:mb-10">
                    <h1 class="text-[18px] sm:text-[20px] font-medium text-primary tracking-tight">Dashboard Overview</h1>
                </div>

                <?php
                // Dati AI/Health dal payload già caricato in portfolio_data.php
                $portfolio_health = $dashboardInsights['portfolio_health'] ?? [
                    'score' => null,
                    'score_label' => '-',
                    'diversification' => ['label' => '-', 'status' => 'neutral'],
                    'performance' => ['label' => '-', 'status' => 'neutral'],
                    'risk' => ['label' => '-', 'status' => 'neutral'],
                ];
                $portfolio_score = $portfolio_health['score'];
                $score_label = $portfolio_health['score_label'];
                ?>

                <!-- Salute Portafoglio + AI Insight -->
                <div class="grid grid-cols-1 lg:grid-cols-[1fr_auto] gap-6 mb-6">
                    <!-- Salute Portafoglio -->
                    <div class="widget-card widget-purple p-6">
                        <div class="flex justify-between items-center mb-5 pb-4 border-b border-gray-200">
                            <div class="flex items-center gap-2">
                                <i class="fa-solid fa-heart-pulse text-purple text-sm"></i>
                                <span class="text-[11px] font-medium text-gray-600 uppercase tracking-wider">Salute Portafoglio</span>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                            <div class="flex flex-col items-center justify-center">
                                <div class="text-5xl font-bold text-purple"><?php echo $portfolio_score ?? '-'; ?></div>
                                <div class="text-2xl font-semibold text-gray-600 mt-1">/100</div>
                                <div class="text-sm text-gray-500 mt-2 px-3 py-1 bg-purple-100 ">
                                    <?php echo $score_label; ?>
                                </div>
                            </div>
                            <?php
                            $sections = ['diversification' => 'Diversificazione', 'performance' => 'Performance', 'risk' => 'Rischio'];
                            foreach ($sections as $key => $label):
                                $item = $portfolio_health[$key];
                                $statusClass = $item['status'] === 'success'
                                    ? 'bg-success-light text-success-dark'
                                    : ($item['status'] === 'warning'
                                        ? 'bg-warning-light text-warning'
                                        : 'bg-gray-100 text-gray-600');
                            ?>
                            <div class="flex items-center gap-3">
                                <div>
                                    <div class="text-xs text-gray-500 mb-1"><?php echo $label; ?>:</div>
                                    <span class="px-3 py-1 <?php echo $statusClass; ?> text-xs font-semibold"><?php echo $item['label']; ?></span>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- AI Insight Riepilogo -->
                    <div class="widget-ai-insight px-6 py-4 transition-all duration-300">
                        <div class="flex items-center gap-2.5 mb-3 pb-2.5 border-b border-purple/20">
                            <span class="badge-ai bg-purple text-white text-[9px] font-bold px-1.5 py-0.5 uppercase tracking-wide">AI Insight</span>
                            <span class="text-[11px] font-medium text-purple uppercase tracking-wider">
                                <?php echo htmlspecialchars($dashboardInsights['ai_insights']['summary_title'] ?? 'Riepilogo Portafoglio'); ?>
                                <?php echo date('Y', strtotime($metadata['last_update'] ?? date('c'))); ?>
                            </span>
                        </div>
                        <div class="text-[13px] leading-relaxed text-gray-700">
                            <?php
                            $insights = $dashboardInsights['ai_insights']['insights'] ?? [];
                            if (empty($insights)):
                            ?>
                                <div class="pl-4 relative py-2 text-gray-500 italic">
                                    <span class="absolute left-0 text-gray-400 font-bold">→</span>
                                    Nessuna analisi AI disponibile. I dati verranno popolati dal workflow automatico.
                                </div>
                            <?php else: ?>
                                <?php foreach ($insights as $index => $insight): ?>
                                    <?php
                                    $isLast = ($index === count($insights) - 1);
                                    $colorClass = $insight['type'] === 'warning' ? 'text-danger' : 'text-purple';
                                    ?>
                                    <div class="pl-4 relative py-2 <?php echo !$isLast ? 'border-b border-dashed border-gray-300' : ''; ?>">
                                        <span class="absolute left-0 <?php echo $colorClass; ?> font-bold">→</span>
                                        <?php echo htmlspecialchars($insight['text']); ?>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Performance Chart -->
                <div class="mb-8">
                    <div class="widget-card widget-purple p-6">
                        <div class="flex justify-between items-center mb-5 pb-4 border-b border-gray-200">
                            <div class="flex items-center gap-2">
                                <i class="fa-solid fa-chart-area text-purple text-sm"></i>
                                <span class="text-[11px] font-medium text-gray-600 uppercase tracking-wider">Andamento Mensile (<?php echo date('Y'); ?>)</span>
                            </div>
                        </div>
                        <div class="relative h-[300px]">
                            <canvas id="performanceChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
