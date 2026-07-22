@extends('layouts.app')
@section('header_title', 'Order #' . $order->id)

@section('content')
@php
    $company = $order->deal?->lead?->contact?->company;
    $contact = $order->deal?->lead?->contact;
    $dispatch = $order->dispatch;
@endphp

<x-crm.page-header title="Order #{{ $order->id }}" subtitle="{{ $company?->name ?? 'Order details' }}">
    <x-slot:actions>
        <a href="{{ route('crm.orders.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold text-slate-300 border border-slate-700 hover:bg-slate-800 transition-all">Back</a>
        <a href="{{ route('crm.orders.edit', $order) }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold text-white bg-[#6366F1] hover:bg-[#4F46E5] transition-all shadow-lg shadow-[#6366F1]/20">Edit</a>
    </x-slot:actions>
</x-crm.page-header>

@if(session('success'))
<div class="mb-4 px-4 py-3 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-sm font-medium">{{ session('success') }}</div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <x-crm.panel title="Order Details">
        <div class="px-6 py-5 space-y-4">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Status</span>
                <x-crm.badge :color="$order->status->getColor()" :label="$order->status->getLabel()" />
            </div>
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Deal</span>
                @if($order->deal)
                <a href="{{ route('crm.deals.show', $order->deal) }}" class="text-sm font-semibold text-[#6366F1] hover:text-[#4F46E5] transition-colors">Deal #{{ $order->deal->id }}</a>
                @else
                <span class="text-sm text-slate-400">—</span>
                @endif
            </div>
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Company</span>
                <span class="text-sm text-slate-200">{{ $company?->name ?? '—' }}</span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Contact</span>
                <span class="text-sm text-slate-200">{{ $contact?->name ?? '—' }}</span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Deadline</span>
                <span class="text-sm text-slate-200">{{ $order->deadline ? $order->deadline->format('M d, Y') : '—' }}</span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Delivered At</span>
                <span class="text-sm text-slate-200">{{ $order->delivered_at ? $order->delivered_at->format('M d, Y H:i') : '—' }}</span>
            </div>
            <div>
                <span class="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1.5">Special Instructions</span>
                <p class="text-sm text-slate-300 whitespace-pre-line">{{ $order->special_instructions ?: '—' }}</p>
            </div>
        </div>
    </x-crm.panel>

    <x-crm.panel title="Dispatch">
        <div class="px-6 py-5 space-y-4">
            @if($dispatch)
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Vehicle Info</span>
                <span class="text-sm text-slate-200">{{ $dispatch->vehicle_info ?: '—' }}</span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Dispatch Date</span>
                <span class="text-sm text-slate-200">{{ $dispatch->dispatch_date ? $dispatch->dispatch_date->format('M d, Y') : '—' }}</span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Invoice No</span>
                <span class="text-sm text-slate-200">{{ $dispatch->invoice_no ?: '—' }}</span>
            </div>
            <div>
                <span class="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1.5">Delivery Address</span>
                <p class="text-sm text-slate-300 whitespace-pre-line">{{ $dispatch->delivery_address ?: '—' }}</p>
            </div>
            @else
            <x-crm.empty message="No dispatch recorded yet." />
            @endif
        </div>
    </x-crm.panel>
</div>
@endsection
