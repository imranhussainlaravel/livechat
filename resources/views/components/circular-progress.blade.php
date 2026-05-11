@props([
    'title',
    'subtitle' => '', // keeping subtitle as optional, maybe hide it if not needed
    'current' => 0,
    'total' => 0,
    'color' => 'blue',
    'size' => 56, // reduced size to match stat-card better
    'strokeWidth' => 5, // thinner stroke
])

@php
    $percentage = $total > 0 ? min(100, round(($current / $total) * 100)) : 0;
    
    $radius = ($size / 2) - $strokeWidth;
    $circumference = 2 * pi() * $radius;
    $dashOffset = $circumference - ($percentage / 100) * $circumference;
    
    $strokeColors = [
        'blue' => 'text-[#6366F1]',
        'green' => 'text-emerald-500',
        'indigo' => 'text-[#6366F1]',
        'teal' => 'text-cyan-500',
        'yellow' => 'text-amber-500',
        'red' => 'text-rose-500',
        'purple' => 'text-indigo-500',
    ];
    
    $strokeColorClass = $strokeColors[$color] ?? 'text-blue-500';
@endphp

<div class="bg-slate-800/40 backdrop-blur-xl rounded-2xl p-6 border border-slate-700/50 flex items-center justify-between h-full shadow-xl shadow-black/10 transition-all duration-300 hover:bg-slate-800/60 hover:shadow-2xl hover:border-slate-600/50 group">
    <div class="min-w-0 pr-2 relative z-10">
        <p class="text-sm font-semibold tracking-wide text-slate-400 mb-1.5 leading-tight">{{ $title }}</p>
        <h3 class="text-3xl font-bold tracking-tight text-white flex items-baseline group-hover:text-slate-100 transition-colors">
            {{ $current }}
            <span class="text-lg font-medium text-slate-500 ml-1">/ {{ $total }}</span>
        </h3>
        @if($subtitle)
            <p class="text-xs text-slate-500 mt-1.5 font-medium truncate">{{ $subtitle }}</p>
        @endif
    </div>
    
    <div class="relative shrink-0 flex items-center justify-center relative z-10" style="width: {{ $size }}px; height: {{ $size }}px;">
        <svg class="w-full h-full transform -rotate-90" viewBox="0 0 {{ $size }} {{ $size }}">
            <!-- Background Track -->
            <circle 
                class="text-gray-700" 
                stroke-width="{{ $strokeWidth }}" 
                stroke="currentColor" 
                fill="transparent" 
                r="{{ $radius }}" 
                cx="{{ $size / 2 }}" 
                cy="{{ $size / 2 }}" 
            />
            <!-- Progress Fill -->
            <circle 
                class="{{ $strokeColorClass }} transition-all duration-1000 ease-out" 
                stroke-width="{{ $strokeWidth }}" 
                stroke-dasharray="{{ $circumference }}" 
                stroke-dashoffset="{{ $dashOffset }}" 
                stroke-linecap="round" 
                stroke="currentColor" 
                fill="transparent" 
                r="{{ $radius }}" 
                cx="{{ $size / 2 }}" 
                cy="{{ $size / 2 }}" 
            />
        </svg>
        
        <div class="absolute flex flex-col items-center justify-center drop-shadow-md">
            <span class="text-xs font-bold text-white">{{ $percentage }}%</span>
        </div>
    </div>
</div>
