@extends('layouts.app')
@section('header_title', 'Deals')

@section('content')
<div class="max-w-7xl mx-auto">
    <x-crm.page-header title="Deals" subtitle="Track your sales pipeline">
        <x-slot:actions>
            <div class="inline-flex rounded-xl border border-slate-700 overflow-hidden">
                <a href="{{ route('crm.deals.index') }}" class="px-3 py-2 text-xs font-semibold text-slate-400 hover:bg-slate-800">Board</a>
                <a href="{{ route('crm.deals.index', ['view' => 'table']) }}" class="px-3 py-2 text-xs font-semibold bg-[#6366F1]/10 text-[#6366F1] border-l border-slate-700">Table</a>
            </div>
            <a href="{{ route('crm.deals.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold text-white bg-[#6366F1] hover:bg-[#4F46E5] transition-all shadow-lg shadow-[#6366F1]/20">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                New Deal
            </a>
        </x-slot:actions>
    </x-crm.page-header>

    @if(session('success'))
    <div class="mb-4 px-4 py-3 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-sm font-medium">{{ session('success') }}</div>
    @endif
    @if(session('error'))
    <div class="mb-4 px-4 py-3 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-400 text-sm font-medium">{{ session('error') }}</div>
    @endif

    {{-- Stats strip --}}
    <div class="mb-6 grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3">
        <div class="bg-slate-800/40 border border-slate-700/50 rounded-2xl px-4 py-3">
            <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Open Value</p>
            <p class="text-lg font-bold text-white mt-1">PKR {{ number_format((float) $openValue, 2) }}</p>
        </div>
        @foreach($stages as $stage)
        <div class="bg-slate-800/40 border border-slate-700/50 rounded-2xl px-4 py-3">
            <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">{{ $stage->getLabel() }}</p>
            <p class="text-lg font-bold text-white mt-1">{{ $stageCounts[$stage->value] ?? 0 }}</p>
        </div>
        @endforeach
    </div>

    {{-- Stage filter --}}
    <form method="GET" class="mb-4 flex items-center gap-2">
        <input type="hidden" name="view" value="table">
        <select name="stage" onchange="this.form.submit()" class="w-auto bg-slate-900/50 border border-slate-700 rounded-xl px-3 py-2 text-sm text-slate-200 placeholder-slate-500 focus:ring-2 focus:ring-[#6366F1]/20 focus:border-[#6366F1] outline-none transition-all">
            <option value="">All stages</option>
            @foreach($stages as $stage)
            <option value="{{ $stage->value }}" @selected(request('stage') === $stage->value)>{{ $stage->getLabel() }}</option>
            @endforeach
        </select>
        @if(request('stage'))
        <a href="{{ route('crm.deals.index', ['view' => 'table']) }}" class="text-xs font-semibold text-slate-400 hover:text-slate-200">Clear</a>
        @endif
    </form>

    <x-crm.panel>
        @if($deals->count())
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-800/30 text-[11px] font-bold uppercase tracking-wider text-slate-500 text-left">
                        <th class="px-6 py-4">Deal</th>
                        <th class="px-6 py-4">Company / Contact</th>
                        <th class="px-6 py-4">Stage</th>
                        <th class="px-6 py-4">Value</th>
                        <th class="px-6 py-4">Expected Close</th>
                        <th class="px-6 py-4">Sales Rep</th>
                        <th class="px-6 py-4">Order</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700/50">
                    @foreach($deals as $deal)
                    <tr class="hover:bg-slate-700/20 transition-colors">
                        <td class="px-6 py-4">
                            <a href="{{ route('crm.deals.show', $deal) }}" class="font-semibold text-[#6366F1] hover:underline">#{{ $deal->id }}</a>
                        </td>
                        <td class="px-6 py-4">
                            <div class="font-medium text-slate-200">{{ $deal->lead?->contact?->company?->name ?? '—' }}</div>
                            <div class="text-xs text-slate-500">{{ $deal->lead?->contact?->name ?? '—' }}</div>
                        </td>
                        <td class="px-6 py-4">
                            @if($deal->stage)
                            <x-crm.badge :color="$deal->stage->getColor()" :label="$deal->stage->getLabel()" />
                            @endif
                        </td>
                        <td class="px-6 py-4 text-slate-300">{{ $deal->value !== null ? 'PKR ' . number_format((float) $deal->value, 2) : '—' }}</td>
                        <td class="px-6 py-4 text-slate-400">{{ $deal->expected_close_date?->format('M d, Y') ?? '—' }}</td>
                        <td class="px-6 py-4 text-slate-300">{{ $deal->salesRep?->name ?? '—' }}</td>
                        <td class="px-6 py-4">
                            @if($deal->order && $deal->order->status)
                            <x-crm.badge :color="$deal->order->status->getColor()" :label="$deal->order->status->getLabel()" />
                            @else
                            <span class="text-slate-600 text-xs">—</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if($deals->hasPages())
        <div class="px-6 py-4 border-t border-slate-700/50">{{ $deals->links() }}</div>
        @endif
        @else
        <x-crm.empty message="No deals yet.">
            <x-slot:action>
                <a href="{{ route('crm.deals.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold text-white bg-[#6366F1] hover:bg-[#4F46E5] transition-all shadow-lg shadow-[#6366F1]/20">New Deal</a>
            </x-slot:action>
        </x-crm.empty>
        @endif
    </x-crm.panel>
</div>
@endsection
