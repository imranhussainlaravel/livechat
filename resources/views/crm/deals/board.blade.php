@extends('layouts.app')
@section('header_title', 'Deals Board')

@section('content')
<div class="h-full flex flex-col">
    <x-crm.page-header title="Deals Board" subtitle="Drag a deal to move it through the pipeline">
        <x-slot:actions>
            <div class="inline-flex rounded-xl border border-slate-700 overflow-hidden">
                <a href="{{ route('crm.deals.index') }}" class="px-3 py-2 text-xs font-semibold bg-[#6366F1]/10 text-[#6366F1]">Board</a>
                <a href="{{ route('crm.deals.index', ['view' => 'table']) }}" class="px-3 py-2 text-xs font-semibold text-slate-400 hover:bg-slate-800 border-l border-slate-700">Table</a>
            </div>
            <a href="{{ route('crm.deals.create') }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold text-white bg-[#6366F1] hover:bg-[#4F46E5] transition-all shadow-lg shadow-[#6366F1]/20">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                New Deal
            </a>
        </x-slot:actions>
    </x-crm.page-header>

    <div class="flex-1 min-h-0 flex gap-4 overflow-x-auto pb-2">
        @foreach($stages as $stage)
        @php
            $columnDeals = $dealsByStage[$stage->value] ?? collect();
            $columnValue = $columnDeals->sum('value');
        @endphp
        <div class="w-72 shrink-0 flex flex-col min-h-0">
            <div class="flex items-center justify-between px-3 py-2 mb-2 rounded-xl bg-slate-800/40 border border-slate-700/50">
                <div class="flex items-center gap-2">
                    <x-crm.badge :color="$stage->getColor()" :label="$stage->getLabel()" />
                    <span data-count-for="{{ $stage->value }}" class="text-xs font-bold text-slate-500">{{ $columnDeals->count() }}</span>
                </div>
                <span class="text-[10px] font-semibold text-slate-500">PKR {{ number_format((float) $columnValue, 0) }}</span>
            </div>

            <div data-kanban-list data-stage="{{ $stage->value }}"
                 class="flex-1 overflow-y-auto space-y-2 min-h-[8rem] rounded-xl p-2 bg-slate-900/30 border border-dashed border-slate-700/40">
                @forelse($columnDeals as $deal)
                <div data-deal-id="{{ $deal->id }}"
                     class="cursor-move rounded-xl bg-slate-800/70 border border-slate-700/60 p-3 shadow-sm hover:border-[#6366F1]/40 transition-colors">
                    <div class="flex items-start justify-between mb-1">
                        <a href="{{ route('crm.deals.show', $deal) }}" class="text-[11px] font-bold text-slate-500 hover:text-[#6366F1]">#{{ $deal->id }}</a>
                        @if($deal->order && $deal->order->status)
                        <x-crm.badge :color="$deal->order->status->getColor()" :label="$deal->order->status->getLabel()" />
                        @endif
                    </div>
                    <a href="{{ route('crm.deals.show', $deal) }}" class="block text-sm font-bold text-slate-100 hover:text-white leading-snug">
                        {{ $deal->lead?->contact?->company?->name ?? 'No company' }}
                    </a>
                    <p class="text-xs text-slate-400 mt-0.5">{{ $deal->lead?->contact?->name ?? '—' }}</p>
                    <div class="mt-3 space-y-1.5 text-[11px]">
                        <div class="flex justify-between"><span class="text-slate-500">Value</span><span class="text-slate-200 font-semibold">{{ $deal->value !== null ? 'PKR ' . number_format((float) $deal->value, 0) : '—' }}</span></div>
                        <div class="flex justify-between"><span class="text-slate-500">Close</span><span class="text-slate-300">{{ $deal->expected_close_date?->format('M d') ?? '—' }}</span></div>
                        <div class="flex justify-between"><span class="text-slate-500">Rep</span><span class="text-slate-300">{{ $deal->salesRep?->name ?? '—' }}</span></div>
                    </div>
                </div>
                @empty
                <div class="flex flex-col items-center justify-center py-10 text-slate-600">
                    <svg class="w-8 h-8 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
                    <span class="text-[11px]">No deals</span>
                </div>
                @endforelse
            </div>
        </div>
        @endforeach
    </div>
</div>

@push('scripts')
<script>
(function () {
    var CSRF = '{{ csrf_token() }}';

    function updateCounts() {
        document.querySelectorAll('[data-kanban-list]').forEach(function (list) {
            var count = list.querySelectorAll('[data-deal-id]').length;
            var badge = document.querySelector('[data-count-for="' + list.getAttribute('data-stage') + '"]');
            if (badge) badge.textContent = count;
        });
    }

    function initDealBoard() {
        if (!document.querySelector('[data-kanban-list][data-stage]')) return;
        if (!window.Sortable) {
            var s = document.createElement('script');
            s.src = 'https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js';
            s.onload = initDealBoard;
            document.head.appendChild(s);
            return;
        }
        document.querySelectorAll('[data-kanban-list][data-stage]').forEach(function (list) {
            if (list._sortableInit) return;
            list._sortableInit = true;
            new Sortable(list, {
                group: 'deals',
                animation: 150,
                ghostClass: 'opacity-40',
                onEnd: function (evt) {
                    if (evt.from === evt.to) return;
                    var stage = evt.to.getAttribute('data-stage');
                    var id = evt.item.getAttribute('data-deal-id');
                    updateCounts();
                    fetch('/crm/deals/' + id + '/stage', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF },
                        body: JSON.stringify({ stage: stage })
                    })
                    .then(function (r) { if (!r.ok) throw r; return r.json(); })
                    .then(function (d) { if (window.showToast && d.message) showToast(d.message); })
                    .catch(function () {
                        if (window.showToast) showToast('Could not move deal.', 'error');
                        evt.from.insertBefore(evt.item, evt.from.children[evt.oldIndex] || null);
                        updateCounts();
                    });
                }
            });
        });
    }

    document.addEventListener('DOMContentLoaded', initDealBoard);
})();
</script>
@endpush
@endsection
