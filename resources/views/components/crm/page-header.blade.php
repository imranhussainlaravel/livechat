@props(['title' => '', 'subtitle' => null])
<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div class="flex items-start gap-3">
        {{-- Back button — shown on every CRM page except the list/index pages --}}
        @unless(request()->routeIs('*.index'))
        <a href="#" onclick="history.back(); return false;" title="Back"
           class="mt-0.5 shrink-0 inline-flex items-center justify-center w-9 h-9 rounded-xl border border-slate-700 text-slate-400 hover:text-white hover:bg-slate-800 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
        </a>
        @endunless
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-white">{{ $title }}</h1>
            @if($subtitle)
            <p class="text-sm font-medium text-slate-400 mt-1">{{ $subtitle }}</p>
            @endif
        </div>
    </div>
    @if(isset($actions))
    <div class="flex flex-wrap items-center gap-2">{{ $actions }}</div>
    @endif
</div>
