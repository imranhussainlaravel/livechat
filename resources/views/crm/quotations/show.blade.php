@extends('layouts.app')
@section('header_title', 'Quotation')

@section('content')
@php($company = $quotation->deal?->lead?->contact?->company)
@php($contact = $quotation->deal?->lead?->contact)
<x-crm.page-header title="Quotation v{{ $quotation->version }}" subtitle="{{ $company?->name ?? 'Deal #' . $quotation->deal_id }}">
    <x-slot:actions>
        <a href="{{ route('crm.quotations.pdf', $quotation) }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold text-white bg-[#6366F1] hover:bg-[#4F46E5] transition-all shadow-lg shadow-[#6366F1]/20">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
            Download PDF
        </a>
        <a href="{{ route('crm.quotations.edit', $quotation) }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold text-slate-300 border border-slate-700 hover:bg-slate-800 transition-all">Edit</a>
        <a href="{{ route('crm.deals.show', $quotation->deal_id) }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold text-slate-300 border border-slate-700 hover:bg-slate-800 transition-all">Back to Deal</a>
    </x-slot:actions>
</x-crm.page-header>

@if(session('success'))
<div class="mb-4 px-4 py-3 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-sm font-medium">{{ session('success') }}</div>
@endif

@if($quotation->needsDiscountApproval())
    @if(auth()->user()->isAdmin())
    <div class="mb-4 px-4 py-3 rounded-xl bg-amber-500/10 border border-amber-500/20 text-amber-400 text-sm font-medium flex items-center justify-between gap-4">
        <span>This quotation has a discount of {{ number_format((float) $quotation->discount_percent, 2) }}% and requires admin approval.</span>
        <form method="POST" action="{{ route('crm.quotations.approveDiscount', $quotation) }}">
            @csrf
            <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold text-white bg-[#6366F1] hover:bg-[#4F46E5] transition-all shadow-lg shadow-[#6366F1]/20">Approve discount</button>
        </form>
    </div>
    @else
    <div class="mb-4 px-4 py-3 rounded-xl bg-amber-500/10 border border-amber-500/20 text-amber-400 text-sm font-medium">
        Awaiting admin discount approval ({{ number_format((float) $quotation->discount_percent, 2) }}% discount).
    </div>
    @endif
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">
        <x-crm.panel title="Line Items">
            <div class="overflow-x-auto">
                <table class="min-w-full text-left">
                    <thead>
                        <tr class="bg-slate-800/30 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                            <th class="px-6 py-4">Product</th>
                            <th class="px-6 py-4 text-right">Qty</th>
                            <th class="px-6 py-4 text-right">Unit Price</th>
                            <th class="px-6 py-4 text-right">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700/50">
                        @forelse($quotation->items as $item)
                        <tr class="hover:bg-slate-700/20 transition-colors">
                            <td class="px-6 py-4 text-sm font-semibold text-slate-200">{{ $item->product?->name ?? '—' }}</td>
                            <td class="px-6 py-4 text-sm text-slate-400 text-right">{{ number_format($item->quantity) }}</td>
                            <td class="px-6 py-4 text-sm text-slate-400 text-right">PKR {{ number_format((float) $item->unit_price, 2) }}</td>
                            <td class="px-6 py-4 text-sm font-semibold text-slate-200 text-right">PKR {{ number_format($item->subtotal(), 2) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="px-6 py-6 text-sm text-slate-500 text-center">No line items.</td></tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr class="border-t border-slate-700/50">
                            <td colspan="3" class="px-6 py-4 text-sm font-semibold text-slate-400 text-right">Total</td>
                            <td class="px-6 py-4 text-base font-bold text-white text-right">PKR {{ number_format((float) $quotation->total_value, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </x-crm.panel>
    </div>

    <div class="space-y-6">
        <x-crm.panel title="Summary">
            <div class="p-6 space-y-4 text-sm">
                <div class="flex items-center justify-between">
                    <span class="text-slate-500 font-medium">Client</span>
                    <span class="text-slate-200 font-semibold text-right">{{ $company?->name ?? '—' }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-slate-500 font-medium">Contact</span>
                    <span class="text-slate-200 font-semibold text-right">{{ $contact?->name ?? '—' }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-slate-500 font-medium">Version</span>
                    <span class="text-slate-200 font-semibold">v{{ $quotation->version }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-slate-500 font-medium">Status</span>
                    <x-crm.badge :color="$quotation->status->getColor()" :label="$quotation->status->getLabel()" />
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-slate-500 font-medium">Total Value</span>
                    <span class="text-slate-200 font-semibold">PKR {{ number_format((float) $quotation->total_value, 2) }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-slate-500 font-medium">Discount</span>
                    <span class="text-slate-200 font-semibold">{{ number_format((float) $quotation->discount_percent, 2) }}%</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-slate-500 font-medium">Created by</span>
                    <span class="text-slate-200 font-semibold text-right">{{ $quotation->createdBy?->name ?? '—' }}</span>
                </div>
                @if($quotation->discount_approved_by)
                <div class="flex items-center justify-between">
                    <span class="text-slate-500 font-medium">Discount approved</span>
                    <span class="text-emerald-400 font-semibold text-right">{{ $quotation->discountApprovedBy?->name ?? '—' }}@if($quotation->discount_approved_at) <span class="text-slate-500 font-normal">· {{ $quotation->discount_approved_at->format('M d, Y') }}</span>@endif</span>
                </div>
                @endif
            </div>
        </x-crm.panel>

        <x-crm.panel title="Change Status">
            <div class="p-6">
                <form method="POST" action="{{ route('crm.quotations.update', $quotation) }}" class="space-y-4">
                    @csrf
                    @method('PUT')
                    <x-crm.field label="Status" name="status">
                        <select name="status" id="status"
                                class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-3 py-2 text-sm text-slate-200 focus:ring-2 focus:ring-[#6366F1]/20 focus:border-[#6366F1] outline-none transition-all">
                            @foreach(\App\Enums\QuotationStatus::cases() as $status)
                            <option value="{{ $status->value }}" @selected($quotation->status->value === $status->value)>{{ $status->getLabel() }}</option>
                            @endforeach
                        </select>
                    </x-crm.field>
                    <button type="submit" class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold text-white bg-[#6366F1] hover:bg-[#4F46E5] transition-all shadow-lg shadow-[#6366F1]/20">Update Status</button>
                </form>

                <form method="POST" action="{{ route('crm.quotations.destroy', $quotation) }}" onsubmit="return confirm('Delete this quotation?')" class="mt-4 pt-4 border-t border-slate-700/50">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold text-rose-400 border border-slate-700 hover:bg-slate-800 transition-all">Delete Quotation</button>
                </form>
            </div>
        </x-crm.panel>
    </div>
</div>
@endsection
