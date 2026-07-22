@extends('layouts.app')
@section('header_title', 'CRM · Edit Product')

@php
    $inputClass = 'w-full bg-slate-900/50 border border-slate-700 rounded-xl px-3 py-2 text-sm text-slate-200 placeholder-slate-500 focus:ring-2 focus:ring-[#6366F1]/20 focus:border-[#6366F1] outline-none transition-all';
    $tierSource = old('tiers', $product->priceTiers->map(fn ($t) => ['min_quantity' => $t->min_quantity, 'unit_price' => $t->unit_price])->all());
    $initialTiers = collect($tierSource)
        ->map(fn ($t) => ['min_quantity' => $t['min_quantity'] ?? '', 'unit_price' => $t['unit_price'] ?? ''])
        ->values();
@endphp

@section('content')
<x-crm.page-header title="Edit Product" subtitle="{{ $product->name }}" />

<form method="POST" action="{{ route('crm.products.update', $product) }}" class="max-w-3xl space-y-6">
    @csrf
    @method('PUT')

    <x-crm.panel title="Product Details">
        <div class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-5">
            <x-crm.field label="Name" name="name" :required="true" class="sm:col-span-2">
                <input type="text" id="name" name="name" value="{{ old('name', $product->name) }}" class="{{ $inputClass }}" placeholder="e.g. Kraft Mailer Box">
            </x-crm.field>

            <x-crm.field label="Type" name="type" :required="true">
                <select id="type" name="type" class="{{ $inputClass }}">
                    @foreach(\App\Enums\ProductInterest::cases() as $case)
                    <option value="{{ $case->value }}" @selected(old('type', $product->type->value) === $case->value)>{{ $case->getLabel() }}</option>
                    @endforeach
                </select>
            </x-crm.field>

            <x-crm.field label="Material" name="material">
                <input type="text" id="material" name="material" value="{{ old('material', $product->material) }}" class="{{ $inputClass }}" placeholder="e.g. 3-ply corrugated">
            </x-crm.field>

            <x-crm.field label="Size Options" name="size_options" class="sm:col-span-2">
                <input type="text" id="size_options" name="size_options" value="{{ old('size_options', $product->size_options) }}" class="{{ $inputClass }}" placeholder="e.g. S / M / L, or 20x15x10 cm">
            </x-crm.field>

            <x-crm.field label="MOQ" name="moq" :required="true">
                <input type="number" id="moq" name="moq" value="{{ old('moq', $product->moq) }}" min="1" step="1" class="{{ $inputClass }}">
            </x-crm.field>

            <x-crm.field label="Base Price (PKR)" name="base_price" :required="true">
                <input type="number" id="base_price" name="base_price" value="{{ old('base_price', $product->base_price) }}" min="0" step="0.01" class="{{ $inputClass }}" placeholder="0.00">
            </x-crm.field>
        </div>
    </x-crm.panel>

    <div x-data="{ tiers: {{ Illuminate\Support\Js::from($initialTiers) }} }">
        <x-crm.panel title="Price Tiers">
            <div class="p-6 space-y-4">
                <p class="text-xs text-slate-500">Optional volume pricing. Each tier sets the unit price when the ordered quantity is at least the given minimum.</p>

                <template x-if="tiers.length === 0">
                    <p class="text-sm text-slate-500 italic">No tiers added. Base price applies to all quantities.</p>
                </template>

                <template x-for="(tier, index) in tiers" :key="index">
                    <div class="flex items-end gap-3">
                        <div class="flex-1">
                            <label class="block text-xs font-semibold text-slate-300 mb-1.5">Min Quantity</label>
                            <input type="number" min="1" step="1" :name="`tiers[${index}][min_quantity]`" x-model="tier.min_quantity" class="{{ $inputClass }}" placeholder="e.g. 100">
                        </div>
                        <div class="flex-1">
                            <label class="block text-xs font-semibold text-slate-300 mb-1.5">Unit Price (PKR)</label>
                            <input type="number" min="0" step="0.01" :name="`tiers[${index}][unit_price]`" x-model="tier.unit_price" class="{{ $inputClass }}" placeholder="0.00">
                        </div>
                        <button type="button" @click="tiers.splice(index, 1)" class="mb-0.5 inline-flex items-center justify-center w-10 h-10 rounded-xl text-rose-400 border border-rose-500/30 hover:bg-rose-500/10 transition-all" title="Remove tier">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>
                </template>

                <button type="button" @click="tiers.push({ min_quantity: '', unit_price: '' })" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold text-slate-300 border border-slate-700 hover:bg-slate-800 transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Add Tier
                </button>
            </div>
        </x-crm.panel>
    </div>

    <div class="flex items-center gap-3">
        <button type="submit" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold text-white bg-[#6366F1] hover:bg-[#4F46E5] transition-all shadow-lg shadow-[#6366F1]/20">Save Changes</button>
        <a href="{{ route('crm.products.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold text-slate-300 border border-slate-700 hover:bg-slate-800 transition-all">Cancel</a>
    </div>
</form>
@endsection
