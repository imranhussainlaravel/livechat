@extends('layouts.app')
@section('header_title', 'Edit Quotation')

@section('content')
@php($company = $quotation->deal?->lead?->contact?->company)
@php($contact = $quotation->deal?->lead?->contact)
<x-crm.page-header title="Edit Quotation v{{ $quotation->version }}" subtitle="{{ $company?->name ?? 'Deal #' . $quotation->deal_id }}">
    <x-slot:actions>
        <a href="{{ route('crm.quotations.show', $quotation) }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold text-slate-300 border border-slate-700 hover:bg-slate-800 transition-all">Back</a>
    </x-slot:actions>
</x-crm.page-header>

<form method="POST" action="{{ route('crm.quotations.update', $quotation) }}"
      x-data="quotationForm(@js($products->map(fn($p) => ['id' => $p->id, 'base_price' => (float) $p->base_price])->values()), @js($quotation->items->map(fn($i) => ['product_id' => $i->product_id, 'quantity' => (int) $i->quantity, 'unit_price' => (float) $i->unit_price])->values()))">
    @csrf
    @method('PUT')

    <x-crm.panel title="Details">
        <div class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-5">
            <x-crm.field label="Client">
                <div class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-3 py-2 text-sm text-slate-300">
                    {{ $company?->name ?? '—' }}@if($contact) <span class="text-slate-500">· {{ $contact->name }}</span>@endif
                </div>
            </x-crm.field>
            <x-crm.field label="Status" name="status" :required="true">
                <select name="status" id="status"
                        class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-3 py-2 text-sm text-slate-200 focus:ring-2 focus:ring-[#6366F1]/20 focus:border-[#6366F1] outline-none transition-all">
                    @foreach(\App\Enums\QuotationStatus::cases() as $status)
                    <option value="{{ $status->value }}" @selected(old('status', $quotation->status->value) === $status->value)>{{ $status->getLabel() }}</option>
                    @endforeach
                </select>
            </x-crm.field>
        </div>
    </x-crm.panel>

    <div class="mt-6">
    <x-crm.panel title="Line Items">
        <div class="p-6 space-y-4">
            @error('items')<p class="text-[11px] text-rose-400">{{ $message }}</p>@enderror
            <template x-for="(row, index) in rows" :key="row._key">
                <div class="grid grid-cols-12 gap-3 items-end">
                    <div class="col-span-12 sm:col-span-6">
                        <label class="block text-xs font-semibold text-slate-300 mb-1.5">Product</label>
                        <select :name="`items[${index}][product_id]`" x-model="row.product_id" @change="applyBasePrice(row)"
                                class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-3 py-2 text-sm text-slate-200 focus:ring-2 focus:ring-[#6366F1]/20 focus:border-[#6366F1] outline-none transition-all">
                            <option value="">Select a product…</option>
                            @foreach($products as $product)
                            <option value="{{ $product->id }}">{{ $product->name }} — PKR {{ number_format((float) $product->base_price, 2) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-span-5 sm:col-span-2">
                        <label class="block text-xs font-semibold text-slate-300 mb-1.5">Qty</label>
                        <input type="number" min="1" step="1" :name="`items[${index}][quantity]`" x-model="row.quantity"
                               class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-3 py-2 text-sm text-slate-200 focus:ring-2 focus:ring-[#6366F1]/20 focus:border-[#6366F1] outline-none transition-all">
                    </div>
                    <div class="col-span-5 sm:col-span-3">
                        <label class="block text-xs font-semibold text-slate-300 mb-1.5">Unit Price</label>
                        <input type="number" min="0" step="0.01" :name="`items[${index}][unit_price]`" x-model="row.unit_price"
                               class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-3 py-2 text-sm text-slate-200 focus:ring-2 focus:ring-[#6366F1]/20 focus:border-[#6366F1] outline-none transition-all">
                    </div>
                    <div class="col-span-2 sm:col-span-1 flex justify-end">
                        <button type="button" @click="removeRow(index)" x-show="rows.length > 1"
                                class="inline-flex items-center justify-center w-10 h-10 rounded-xl text-rose-400 border border-slate-700 hover:bg-slate-800 transition-all" title="Remove">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>
                </div>
            </template>

            <div>
                <button type="button" @click="addRow()"
                        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold text-slate-300 border border-slate-700 hover:bg-slate-800 transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Add Item
                </button>
            </div>
        </div>
    </x-crm.panel>
    </div>

    <div class="mt-6 flex items-center gap-3">
        <button type="submit" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold text-white bg-[#6366F1] hover:bg-[#4F46E5] transition-all shadow-lg shadow-[#6366F1]/20">Save Changes</button>
        <a href="{{ route('crm.quotations.show', $quotation) }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold text-slate-300 border border-slate-700 hover:bg-slate-800 transition-all">Cancel</a>
    </div>
</form>

<script>
    function quotationForm(products, existing) {
        const seed = (existing && existing.length)
            ? existing.map((i, idx) => ({ _key: idx, product_id: String(i.product_id), quantity: i.quantity, unit_price: i.unit_price }))
            : [{ _key: Date.now(), product_id: '', quantity: 1, unit_price: '' }];
        return {
            products: products,
            rows: seed,
            addRow() {
                this.rows.push({ _key: Date.now() + Math.random(), product_id: '', quantity: 1, unit_price: '' });
            },
            removeRow(index) {
                this.rows.splice(index, 1);
            },
            applyBasePrice(row) {
                const p = this.products.find(p => String(p.id) === String(row.product_id));
                if (p && (row.unit_price === '' || row.unit_price === null)) {
                    row.unit_price = p.base_price;
                }
            },
        };
    }
</script>
@endsection
