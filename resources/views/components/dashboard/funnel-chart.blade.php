@props(['funnelData'])
<div class="bg-slate-800/50 border border-slate-700/50 rounded-xl p-5 flex flex-col h-full col-span-1 lg:col-span-1">
    <div class="flex items-center justify-between mb-5">
        <div>
            <h3 class="text-sm font-bold text-slate-200">Conversions Funnel</h3>
            <p class="text-[11px] text-slate-500 mt-0.5">Stage drop-offs</p>
        </div>
    </div>
    
    @php
        $leads = $funnelData['leads'] ?? 0;
        $dealsCreated = $funnelData['deals_created'] ?? 0;
        $dealsWon = $funnelData['deals_won'] ?? 0;
        $max = max($leads, $dealsCreated, $dealsWon, 1);
        $stages = [
            ['label' => 'Leads', 'count' => $leads, 'color' => 'bg-slate-700', 'width' => round(($leads / $max) * 100) . '%'],
            ['label' => 'Deals', 'count' => $dealsCreated, 'color' => 'bg-[#6366F1]', 'width' => round(($dealsCreated / $max) * 100) . '%'],
            ['label' => 'Won', 'count' => $dealsWon, 'color' => 'bg-emerald-500', 'width' => round(($dealsWon / $max) * 100) . '%'],
        ];
    @endphp

    <div class="flex-1 flex flex-col justify-center space-y-4 min-h-[220px]">
        @foreach($stages as $index => $stage)
            <div class="flex items-center group relative">
                <div class="w-24 shrink-0 text-right pr-4">
                    <p class="text-xs font-semibold text-slate-300 group-hover:text-white transition-colors">{{ $stage['label'] }}</p>
                    @if($index > 0)
                        @php
                            $prevCount = $stages[$index - 1]['count'];
                            $conversion = $prevCount > 0 ? round(($stage['count'] / $prevCount) * 100) : 0;
                        @endphp
                        <p class="text-[9px] text-slate-500">{{ $conversion }}% from prev</p>
                    @endif
                </div>
                <div class="flex-1 h-10 flex items-center">
                    <div class="h-full rounded-r-lg {{ $stage['color'] }} shadow-sm transition-all duration-500 ease-out group-hover:opacity-90 flex items-center px-3" style="width: {{ $stage['width'] }}; min-width: 4rem;">
                        <span class="text-xs font-bold text-white drop-shadow-sm">{{ number_format($stage['count']) }}</span>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
