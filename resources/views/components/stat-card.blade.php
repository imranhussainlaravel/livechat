@props(['title', 'value', 'icon' => null, 'color' => 'blue'])

@php
$bgClasses = [
'blue' => 'bg-[#F0644B]/10 text-[#F0644B] border-[#F0644B]/20 shadow-[#F0644B]/10',
'yellow' => 'bg-amber-500/10 text-amber-400 border-amber-500/20 shadow-amber-500/10',
'red' => 'bg-rose-500/10 text-rose-400 border-rose-500/20 shadow-rose-500/10',
'green' => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20 shadow-emerald-500/10',
'indigo' => 'bg-[#F0644B]/10 text-[#F0644B] border-[#F0644B]/20 shadow-[#F0644B]/10',
'teal' => 'bg-cyan-500/10 text-cyan-400 border-cyan-500/20 shadow-cyan-500/10',
'gray' => 'bg-slate-800/50 text-slate-400 border-slate-700/50 shadow-slate-900/10',
][$color] ?? 'bg-slate-800/50 text-slate-400 border-slate-700/50 shadow-slate-900/10';
@endphp

<div class="bg-slate-800/40 backdrop-blur-xl rounded-2xl p-6 border border-slate-700/50 flex items-center justify-between shadow-xl shadow-black/10 transition-all duration-300 hover:bg-slate-800/60 hover:shadow-2xl hover:border-slate-600/50 group relative overflow-hidden">
    <!-- Subtle background noise/gradient could go here -->
    <div class="relative z-10">
        <p class="text-sm font-semibold tracking-wide text-slate-400 mb-1.5">{{ $title }}</p>
        <h3 class="text-3xl font-bold tracking-tight text-white group-hover:text-slate-100 transition-colors">{{ $value }}</h3>
    </div>
    @if($icon)
    <div class="w-14 h-14 rounded-2xl {{ $bgClasses }} flex items-center justify-center border shadow-lg relative z-10 group-hover:scale-110 transition-transform duration-500 ease-out">
        @if(str_contains($icon, '<svg'))
            {!! $icon !!}
        @else
            <!-- Optional icon class fallback if string passed -->
            <span class="text-xl font-bold">{{ strtoupper(substr($icon, 0, 1)) }}</span>
        @endif
    </div>
    @endif
</div>