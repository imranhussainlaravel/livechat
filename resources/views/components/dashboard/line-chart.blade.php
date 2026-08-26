@props(['graphData'])
<div class="bg-slate-800/50 border border-slate-700/50 rounded-xl p-5 flex flex-col h-full lg:col-span-2">
    <div class="flex items-center justify-between mb-5">
        <div>
            <h3 class="text-sm font-bold text-slate-200">Leads & Deals Overview</h3>
            <p class="text-[11px] text-slate-500 mt-0.5">Pipeline growth</p>
        </div>
        <div class="flex items-center gap-4">
            <div class="flex items-center gap-3">
                <div class="flex items-center gap-1.5 text-[11px] text-[#6366F1] font-semibold">
                    <span class="w-1.5 h-1.5 rounded-full bg-[#6366F1]"></span>
                    Leads
                </div>
                <div class="flex items-center gap-1.5 text-[11px] text-sky-400 font-semibold">
                    <span class="w-1.5 h-1.5 rounded-full bg-sky-400"></span>
                    Deals
                </div>
            </div>
        </div>
    </div>
    
    <div class="relative flex-1 min-h-[260px]">
        {{-- Stubbed Canvas --}}
        <canvas id="lineChartWidget"></canvas>
    </div>
</div>

<script>
    // TODO: Wire real data
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('lineChartWidget');
        if (!ctx) return;
        const context = ctx.getContext('2d');
        
        let gradientLeads = context.createLinearGradient(0, 0, 0, 300);
        gradientLeads.addColorStop(0, 'rgba(99, 102, 241, 0.25)');
        gradientLeads.addColorStop(1, 'rgba(99, 102, 241, 0)');
        
        let gradientDeals = context.createLinearGradient(0, 0, 0, 300);
        gradientDeals.addColorStop(0, 'rgba(56, 189, 248, 0.25)');
        gradientDeals.addColorStop(1, 'rgba(56, 189, 248, 0)');

        new Chart(context, {
            type: 'line',
            data: {
                labels: {!! json_encode($graphData['labels'] ?? []) !!},
                datasets: [
                    {
                        label: 'Leads',
                        data: {!! json_encode($graphData['leads'] ?? []) !!},
                        borderColor: '#6366F1',
                        backgroundColor: gradientLeads,
                        borderWidth: 2,
                        pointBackgroundColor: '#6366F1',
                        pointBorderColor: '#0f172a',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        fill: true,
                        tension: 0.4,
                    },
                    {
                        label: 'Deals',
                        data: {!! json_encode($graphData['deals'] ?? []) !!},
                        borderColor: '#38bdf8',
                        backgroundColor: gradientDeals,
                        borderWidth: 2,
                        pointBackgroundColor: '#38bdf8',
                        pointBorderColor: '#0f172a',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        fill: true,
                        tension: 0.4,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#1e293b',
                        titleColor: '#f1f5f9',
                        bodyColor: '#94a3b8',
                        borderColor: '#334155',
                        borderWidth: 1,
                        padding: 10,
                        usePointStyle: true,
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: 'rgba(51,65,85,0.4)', drawBorder: false },
                        border: { display: false },
                        ticks: { color: '#475569', stepSize: 10, padding: 8, font: { size: 11 } }
                    },
                    x: {
                        grid: { display: false },
                        border: { display: false },
                        ticks: { color: '#475569', padding: 8, font: { size: 11 } }
                    }
                }
            }
        });
    });
</script>
