@extends('layouts.app')
@section('header_title', 'CRM · Products')

@section('content')
<x-crm.page-header title="Products" subtitle="Product catalogue and volume pricing tiers.">
    <x-slot:actions>
        <a href="{{ route('crm.products.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold text-white bg-[#6366F1] hover:bg-[#4F46E5] transition-all shadow-lg shadow-[#6366F1]/20">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            New Product
        </a>
    </x-slot:actions>
</x-crm.page-header>

@if(session('success'))
<div class="mb-4 px-4 py-3 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-sm font-medium">
    {{ session('success') }}
</div>
@endif

<x-crm.panel>
    @if($products->count())
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-slate-800/30 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                    <th class="px-6 py-4 text-left">Name</th>
                    <th class="px-6 py-4 text-left">Type</th>
                    <th class="px-6 py-4 text-left">Material</th>
                    <th class="px-6 py-4 text-left">MOQ</th>
                    <th class="px-6 py-4 text-left">Base Price</th>
                    <th class="px-6 py-4 text-left">Tiers</th>
                    <th class="px-6 py-4 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-700/50">
                @foreach($products as $p)
                <tr class="hover:bg-slate-700/20 transition-colors">
                    <td class="px-6 py-4">
                        <a href="{{ route('crm.products.show', $p) }}" class="font-semibold text-slate-200 hover:text-[#6366F1] transition-colors">{{ $p->name }}</a>
                    </td>
                    <td class="px-6 py-4">
                        <x-crm.badge color="primary" :label="$p->type->getLabel()" />
                    </td>
                    <td class="px-6 py-4 text-slate-400">{{ $p->material ?: '—' }}</td>
                    <td class="px-6 py-4 text-slate-300">{{ number_format($p->moq) }}</td>
                    <td class="px-6 py-4 text-slate-300">PKR {{ number_format($p->base_price, 2) }}</td>
                    <td class="px-6 py-4 text-slate-400">{{ $p->price_tiers_count }}</td>
                    <td class="px-6 py-4">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('crm.products.edit', $p) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold text-slate-300 border border-slate-700 hover:bg-slate-800 transition-all">Edit</a>
                            <form method="POST" action="{{ route('crm.products.destroy', $p) }}" onsubmit="return confirm('Delete this product? This will remove its price tiers.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold text-rose-400 border border-rose-500/30 hover:bg-rose-500/10 transition-all">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @if($products->hasPages())
    <div class="px-6 py-4 border-t border-slate-700/50">{{ $products->links() }}</div>
    @endif
    @else
    <x-crm.empty message="No products yet.">
        <x-slot:action>
            <a href="{{ route('crm.products.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold text-white bg-[#6366F1] hover:bg-[#4F46E5] transition-all shadow-lg shadow-[#6366F1]/20">New Product</a>
        </x-slot:action>
    </x-crm.empty>
    @endif
</x-crm.panel>
@endsection
