@extends('layouts.app')
@section('header_title', 'Deal #' . $deal->id)

@section('content')
@php
    $inputClass = 'w-full bg-slate-900/50 border border-slate-700 rounded-xl px-3 py-2 text-sm text-slate-200 placeholder-slate-500 focus:ring-2 focus:ring-[#6366F1]/20 focus:border-[#6366F1] outline-none transition-all';
    $isClosed = in_array($deal->stage?->value, ['won', 'lost'], true);
@endphp
<div class="max-w-5xl mx-auto">
    <x-crm.page-header title="Deal #{{ $deal->id }}" subtitle="{{ $deal->lead?->contact?->company?->name ?? '' }}">
        <x-slot:actions>
            <a href="{{ route('crm.deals.edit', $deal) }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold text-slate-300 border border-slate-700 hover:bg-slate-800 transition-all">Edit</a>
            <form method="POST" action="{{ route('crm.deals.destroy', $deal) }}" onsubmit="return confirm('Delete this deal? This cannot be undone.')">
                @csrf
                @method('DELETE')
                <button type="submit" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold text-rose-400 border border-rose-500/30 hover:bg-rose-500/10 transition-all">Delete</button>
            </form>
        </x-slot:actions>
    </x-crm.page-header>

    @if(session('success'))
    <div class="mb-4 px-4 py-3 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-sm font-medium">{{ session('success') }}</div>
    @endif
    @if(session('error'))
    <div class="mb-4 px-4 py-3 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-400 text-sm font-medium">{{ session('error') }}</div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Detail card --}}
        <div class="lg:col-span-2 space-y-6">
            <x-crm.panel title="Deal Details">
                <div class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-5">
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1">Stage</p>
                        @if($deal->stage)
                        <x-crm.badge :color="$deal->stage->getColor()" :label="$deal->stage->getLabel()" />
                        @else
                        <span class="text-slate-500">—</span>
                        @endif
                    </div>
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1">Value</p>
                        <p class="text-sm font-semibold text-white">{{ $deal->value !== null ? 'PKR ' . number_format((float) $deal->value, 2) : '—' }}</p>
                    </div>
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1">Probability</p>
                        <p class="text-sm text-slate-300">{{ $deal->probability !== null ? $deal->probability . '%' : '—' }}</p>
                    </div>
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1">Expected Close</p>
                        <p class="text-sm text-slate-300">{{ $deal->expected_close_date?->format('M d, Y') ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1">Company / Contact</p>
                        <p class="text-sm text-slate-300">{{ $deal->lead?->contact?->company?->name ?? '—' }}</p>
                        <p class="text-xs text-slate-500">{{ $deal->lead?->contact?->name ?? '' }}</p>
                    </div>
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1">Sales Rep</p>
                        <p class="text-sm text-slate-300">{{ $deal->salesRep?->name ?? '—' }}</p>
                    </div>
                    @if($deal->lead)
                    <div class="sm:col-span-2">
                        <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1">Linked Lead</p>
                        <a href="{{ route('crm.leads.show', $deal->lead) }}" class="text-sm font-semibold text-[#6366F1] hover:underline">View Lead #{{ $deal->lead->id }}</a>
                    </div>
                    @endif
                    @if($deal->stage?->value === 'lost' && $deal->lost_reason)
                    <div class="sm:col-span-2">
                        <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1">Lost Reason</p>
                        <p class="text-sm text-rose-400">{{ $deal->lost_reason->getLabel() }}</p>
                    </div>
                    @endif
                </div>
            </x-crm.panel>

            {{-- Quotations panel --}}
            <x-crm.panel title="Quotations">
                <x-slot:headerActions>
                    <a href="{{ route('crm.quotations.create', ['deal' => $deal->id]) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold text-white bg-[#6366F1] hover:bg-[#4F46E5] transition-all">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        New Quotation
                    </a>
                </x-slot:headerActions>
                @if($deal->quotations->count())
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-slate-800/30 text-[11px] font-bold uppercase tracking-wider text-slate-500 text-left">
                                <th class="px-6 py-4">Version</th>
                                <th class="px-6 py-4">Status</th>
                                <th class="px-6 py-4">Total</th>
                                <th class="px-6 py-4"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-700/50">
                            @foreach($deal->quotations as $quotation)
                            <tr class="hover:bg-slate-700/20 transition-colors">
                                <td class="px-6 py-4 font-medium text-slate-200">v{{ $quotation->version }}</td>
                                <td class="px-6 py-4">
                                    @if($quotation->status)
                                    <x-crm.badge :color="$quotation->status->getColor()" :label="$quotation->status->getLabel()" />
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-slate-300">PKR {{ number_format((float) $quotation->total_value, 2) }}</td>
                                <td class="px-6 py-4 text-right">
                                    <a href="{{ route('crm.quotations.show', $quotation) }}" class="text-xs font-semibold text-[#6366F1] hover:underline">View</a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <x-crm.empty message="No quotations yet." />
                @endif
            </x-crm.panel>
        </div>

        {{-- Sidebar: actions + order --}}
        <div class="space-y-6">
            @unless($isClosed)
            <x-crm.panel title="Actions">
                <div class="p-6 space-y-4" x-data="{ showLost: false }">
                    <form method="POST" action="{{ route('crm.deals.markWon', $deal) }}" onsubmit="return confirm('Mark this deal as won? An order will be created.')">
                        @csrf
                        <button type="submit" class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold text-white bg-emerald-600 hover:bg-emerald-500 transition-all">Mark Won</button>
                    </form>

                    <button type="button" @click="showLost = !showLost" class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold text-rose-400 border border-rose-500/30 hover:bg-rose-500/10 transition-all">Mark Lost</button>

                    <form method="POST" action="{{ route('crm.deals.markLost', $deal) }}" x-show="showLost" x-cloak class="space-y-3 pt-1">
                        @csrf
                        <x-crm.field label="Lost Reason" name="lost_reason" :required="true">
                            <select name="lost_reason" id="lost_reason" class="{{ $inputClass }}" required>
                                <option value="">Select a reason…</option>
                                @foreach($lostReasons as $reason)
                                <option value="{{ $reason->value }}">{{ $reason->getLabel() }}</option>
                                @endforeach
                            </select>
                        </x-crm.field>
                        <button type="submit" class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold text-white bg-rose-600 hover:bg-rose-500 transition-all">Confirm Lost</button>
                    </form>
                </div>
            </x-crm.panel>
            @endunless

            <x-crm.panel title="Order">
                <div class="p-6">
                    @if($deal->order)
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-sm text-slate-400">Status</span>
                        @if($deal->order->status)
                        <x-crm.badge :color="$deal->order->status->getColor()" :label="$deal->order->status->getLabel()" />
                        @endif
                    </div>
                    <a href="{{ route('crm.orders.show', $deal->order) }}" class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold text-slate-300 border border-slate-700 hover:bg-slate-800 transition-all">View Order #{{ $deal->order->id }}</a>
                    @else
                    <p class="text-sm text-slate-500">No order yet. Mark this deal as won to create one.</p>
                    @endif
                </div>
            </x-crm.panel>
        </div>
    </div>
</div>
@endsection
