@props([
    'title',
    'value',
    'subtitle' => null,
    'icon' => null,
    'color' => 'blue',
    'trend' => null, /* e.g. 'up', 'down', 'flat' */
    'trendValue' => null, /* e.g. '12%' */
    'trendLabel' => 'vs last 7d',
    'sparkline' => null, /* svg path d="" or similar */
    'href' => null
])

@php
$colors = [
    'blue'   => ['bg' => 'bg-[#6366F1]/10', 'text' => 'text-[#6366F1]', 'border' => 'border-[#6366F1]/20', 'sparkline' => '#6366F1', 'trend' => 'text-[#6366F1]'],
    'yellow' => ['bg' => 'bg-amber-500/10', 'text' => 'text-amber-400', 'border' => 'border-amber-500/20', 'sparkline' => '#fbbf24', 'trend' => 'text-amber-400'],
    'amber'  => ['bg' => 'bg-amber-500/10', 'text' => 'text-amber-400', 'border' => 'border-amber-500/20', 'sparkline' => '#fbbf24', 'trend' => 'text-amber-400'],
    'red'    => ['bg' => 'bg-rose-500/10',   'text' => 'text-rose-400',   'border' => 'border-rose-500/20',   'sparkline' => '#fb7185', 'trend' => 'text-rose-400'],
    'green'  => ['bg' => 'bg-emerald-500/10','text' => 'text-emerald-400','border' => 'border-emerald-500/20','sparkline' => '#34d399', 'trend' => 'text-emerald-400'],
    'emerald'=> ['bg' => 'bg-emerald-500/10','text' => 'text-emerald-400','border' => 'border-emerald-500/20','sparkline' => '#34d399', 'trend' => 'text-emerald-400'],
    'indigo' => ['bg' => 'bg-[#6366F1]/10',  'text' => 'text-[#6366F1]',  'border' => 'border-[#6366F1]/20',  'sparkline' => '#6366F1', 'trend' => 'text-[#6366F1]'],
    'cyan'   => ['bg' => 'bg-cyan-500/10',   'text' => 'text-cyan-400',   'border' => 'border-cyan-500/20',   'sparkline' => '#22d3ee', 'trend' => 'text-cyan-400'],
    'teal'   => ['bg' => 'bg-cyan-500/10',   'text' => 'text-cyan-400',   'border' => 'border-cyan-500/20',   'sparkline' => '#22d3ee', 'trend' => 'text-cyan-400'],
    'sky'    => ['bg' => 'bg-sky-500/10',    'text' => 'text-sky-400',    'border' => 'border-sky-500/20',    'sparkline' => '#38bdf8', 'trend' => 'text-sky-400'],
    'violet' => ['bg' => 'bg-violet-500/10', 'text' => 'text-violet-400', 'border' => 'border-violet-500/20', 'sparkline' => '#a78bfa', 'trend' => 'text-violet-400'],
    'gray'   => ['bg' => 'bg-slate-800/50',  'text' => 'text-slate-400',  'border' => 'border-slate-700/50',  'sparkline' => '#94a3b8', 'trend' => 'text-slate-400'],
][$color] ?? ['bg' => 'bg-slate-800/50', 'text' => 'text-slate-400', 'border' => 'border-slate-700/50', 'sparkline' => '#94a3b8', 'trend' => 'text-slate-400'];

// If no value, fallback to "—"
$displayValue = (is_numeric($value) && $value === 0) ? 0 : ($value ?: '—');
$hasData = ($displayValue !== '—');

// If no data, use flat line for sparkline
if (!$hasData) {
    $trend = 'flat';
    $trendValue = '0%';
    $sparkline = 'M0,15 L50,15';
}

@endphp

@if($href)
<a href="{{ $href }}" class="bg-slate-800/50 border border-slate-700/50 rounded-xl p-4 hover:border-{{ $color }}-500/30 transition-colors group relative overflow-hidden flex flex-col h-full cursor-pointer no-underline block">
@else
<div class="bg-slate-800/50 border border-slate-700/50 rounded-xl p-4 hover:border-{{ $color }}-500/30 transition-colors group relative overflow-hidden flex flex-col h-full">
@endif
    {{-- Top Row: Title + Icon Chip --}}
    <div class="flex items-start justify-between mb-1">
        <p class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">{{ $title }}</p>
        @if($icon)
        <div class="w-7 h-7 rounded-lg {{ $colors['bg'] }} {{ $colors['border'] }} border flex items-center justify-center {{ $colors['text'] }} transition-colors flex-shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                {!! $icon !!}
            </svg>
        </div>
        @endif
    </div>

    {{-- Middle: Big Number + Subtitle (optional) --}}
    <div class="mb-1">
        <p class="text-3xl font-extrabold text-white leading-none mb-0.5 group-hover:scale-[1.02] transform transition-transform origin-left">{{ $displayValue }}</p>
        @if($subtitle)
            <p class="text-[10px] text-slate-500">{{ $subtitle }}</p>
        @endif
    </div>

    {{-- Bottom Row: Trend + Sparkline --}}
    <div class="mt-auto flex items-end justify-between">
        {{-- Trend --}}
        <div class="flex items-center gap-1.5">
            @if($trend === 'up')
                <svg class="w-3.5 h-3.5 {{ $colors['trend'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
            @elseif($trend === 'down')
                <svg class="w-3.5 h-3.5 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"></path></svg>
            @else
                <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14"></path></svg>
            @endif
            
            <div class="flex flex-col">
                <span class="text-xs font-bold {{ $trend === 'up' ? $colors['trend'] : ($trend === 'down' ? 'text-rose-400' : 'text-slate-500') }}">
                    {{ $trendValue }}
                </span>
                <span class="text-[9px] text-slate-500 tracking-wide uppercase whitespace-nowrap">{{ $trendLabel }}</span>
            </div>
        </div>
        
        {{-- Sparkline --}}
        <div class="w-20 h-6 flex-shrink-0 ml-1 opacity-80 group-hover:opacity-100 transition-opacity">
            <svg class="w-full h-full" viewBox="0 0 50 20" preserveAspectRatio="none">
                <path d="{{ $sparkline ?? 'M0,15 L10,12 L20,18 L30,5 L40,10 L50,2' }}" 
                      fill="none" 
                      stroke="{{ $hasData ? $colors['sparkline'] : '#64748b' }}" 
                      stroke-width="2" 
                      stroke-linecap="round" 
                      stroke-linejoin="round" 
                      class="{{ $hasData ? 'drop-shadow-sm' : '' }}"></path>
            </svg>
        </div>
    </div>
@if($href)
</a>
@else
</div>
@endif