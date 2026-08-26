@props(['chatVolume'])
@php
    $high = $chatVolume['high'] ?? 0;
    $normal = $chatVolume['normal'] ?? 0;
    $low = $chatVolume['low'] ?? 0;
    $total = $high + $normal + $low;
    $highPct = $total > 0 ? round(($high / $total) * 100) : 0;
    $normalPct = $total > 0 ? round(($normal / $total) * 100) : 0;
    $lowPct = $total > 0 ? round(($low / $total) * 100) : 0;
@endphp
<div class="bg-slate-800/50 border border-slate-700/50 rounded-xl p-5 flex flex-col h-full col-span-1 lg:col-span-1">
    <div class="flex items-center justify-between mb-5">
        <div>
            <h3 class="text-sm font-bold text-slate-200">Chat Volume</h3>
            <p class="text-[11px] text-slate-500 mt-0.5">By Priority</p>
        </div>
    </div>
    
    <div class="relative flex-1 flex flex-col justify-center min-h-[220px]">
        {{-- Stubbed Canvas --}}
        <canvas id="donutChartWidget"></canvas>
        <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none pb-4">
            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total</span>
            <span class="text-2xl font-black text-white">{{ number_format($total) }}</span>
        </div>
    </div>
    
    <div class="mt-4 grid grid-cols-2 gap-2">
        <div class="flex items-center gap-2">
            <span class="w-2.5 h-2.5 rounded-full bg-rose-400"></span>
            <span class="text-[11px] text-slate-400 font-medium">High</span>
            <span class="text-[11px] font-bold text-white ml-auto">{{ $highPct }}%</span>
        </div>
        <div class="flex items-center gap-2">
            <span class="w-2.5 h-2.5 rounded-full bg-[#6366F1]"></span>
            <span class="text-[11px] text-slate-400 font-medium">Normal</span>
            <span class="text-[11px] font-bold text-white ml-auto">{{ $normalPct }}%</span>
        </div>
        <div class="flex items-center gap-2">
            <span class="w-2.5 h-2.5 rounded-full bg-slate-400"></span>
            <span class="text-[11px] text-slate-400 font-medium">Low</span>
            <span class="text-[11px] font-bold text-white ml-auto">{{ $lowPct }}%</span>
        </div>
    </div>
</div>

<script>
    // TODO: Wire real data
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('donutChartWidget');
        if (!ctx) return;
        new Chart(ctx.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: ['High', 'Normal', 'Low'],
                datasets: [{
                    data: [{{ $high }}, {{ $normal }}, {{ $low }}],
                    backgroundColor: ['#fb7185', '#6366F1', '#94a3b8'],
                    borderWidth: 0,
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '75%',
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#1e293b',
                        titleColor: '#f1f5f9',
                        bodyColor: '#94a3b8',
                        borderColor: '#334155',
                        borderWidth: 1,
                        padding: 10,
                    }
                }
            }
        });
    });
</script>
