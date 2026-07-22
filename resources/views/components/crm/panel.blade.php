@props(['title' => null])
<div {{ $attributes->merge(['class' => 'bg-slate-800/40 backdrop-blur-xl border border-slate-700/50 rounded-2xl shadow-xl shadow-black/10 overflow-hidden']) }}>
    @if($title)
    <div class="px-6 py-4 border-b border-slate-700/50 bg-slate-800/30 flex items-center justify-between">
        <h3 class="font-semibold text-slate-200">{{ $title }}</h3>
        @isset($headerActions){{ $headerActions }}@endisset
    </div>
    @endif
    <div class="{{ $title ? '' : '' }}">{{ $slot }}</div>
</div>
