@props(['color' => 'gray', 'label' => ''])
@php
    $map = [
        'gray'    => 'bg-slate-700/30 text-slate-300 border-slate-600/30',
        'info'    => 'bg-sky-500/10 text-sky-400 border-sky-500/20',
        'warning' => 'bg-amber-500/10 text-amber-400 border-amber-500/20',
        'success' => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
        'danger'  => 'bg-rose-500/10 text-rose-400 border-rose-500/20',
        'primary' => 'bg-[#6366F1]/10 text-[#6366F1] border-[#6366F1]/20',
    ];
    $classes = $map[$color] ?? $map['gray'];
@endphp
<span {{ $attributes->merge(['class' => "inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold border $classes"]) }}>
    {{ $label !== '' ? $label : $slot }}
</span>
