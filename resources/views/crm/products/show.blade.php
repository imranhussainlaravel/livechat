@extends('layouts.app')
@section('header_title', 'CRM · Product')

@section('content')
<x-crm.page-header title="{{ $product->name }}" subtitle="Product details and volume pricing.">
    <x-slot:actions>
        <a href="{{ route('crm.products.edit', $product) }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold text-slate-300 border border-slate-700 hover:bg-slate-800 transition-all">Edit</a>
        <a href="{{ route('crm.products.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold text-slate-300 border border-slate-700 hover:bg-slate-800 transition-all">Back</a>
    </x-slot:actions>
</x-crm.page-header>

@if(session('success'))
<div class="mb-4 px-4 py-3 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-sm font-medium">
    {{ session('success') }}
</div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 max-w-5xl">
    <div class="lg:col-span-1">
        <x-crm.panel title="Details">
            <dl class="p-6 space-y-4 text-sm">
                <div>
                    <dt class="text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1">Type</dt>
                    <dd><x-crm.badge color="primary" :label="$product->type->getLabel()" /></dd>
                </div>
                <div>
                    <dt class="text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1">Material</dt>
                    <dd class="text-slate-300">{{ $product->material ?: '—' }}</dd>
                </div>
                <div>
                    <dt class="text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1">Size Options</dt>
                    <dd class="text-slate-300">{{ $product->size_options ?: '—' }}</dd>
                </div>
                <div>
                    <dt class="text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1">Minimum Order Qty</dt>
                    <dd class="text-slate-300">{{ number_format($product->moq) }}</dd>
                </div>
                <div>
                    <dt class="text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1">Base Price</dt>
                    <dd class="text-slate-200 font-semibold">PKR {{ number_format($product->base_price, 2) }}</dd>
                </div>
            </dl>
        </x-crm.panel>

        <div class="mt-6 px-5 py-4 rounded-2xl bg-[#6366F1]/5 border border-[#6366F1]/20">
            <p class="text-[11px] font-bold uppercase tracking-wider text-[#6366F1] mb-1">Example — Qty 100</p>
            <p class="text-slate-300 text-sm">Unit price: <span class="font-semibold text-slate-100">PKR {{ number_format($product->unitPriceForQuantity(100), 2) }}</span></p>
        </div>
    </div>

    <div class="lg:col-span-2">
        <x-crm.panel title="Price Tiers">
            @if($product->priceTiers->count())
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-slate-800/30 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                            <th class="px-6 py-4 text-left">Min Quantity</th>
                            <th class="px-6 py-4 text-left">Unit Price</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700/50">
                        @foreach($product->priceTiers as $tier)
                        <tr class="hover:bg-slate-700/20 transition-colors">
                            <td class="px-6 py-4 text-slate-300">{{ number_format($tier->min_quantity) }}+</td>
                            <td class="px-6 py-4 text-slate-200 font-medium">PKR {{ number_format($tier->unit_price, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <x-crm.empty message="No price tiers. Base price applies to all quantities." />
            @endif
        </x-crm.panel>
    </div>
</div>
@endsection
